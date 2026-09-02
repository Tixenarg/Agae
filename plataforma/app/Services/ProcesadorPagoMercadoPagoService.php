<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\NotificacionPagoRepository;
use App\Repositories\OrdenRepository;
use App\Repositories\PagoRepository;
use DomainException;
use PDO;
use Throwable;

final class ProcesadorPagoMercadoPagoService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly PagoRepository $pagos,
        private readonly OrdenRepository $ordenes,
        private readonly NotificacionPagoRepository $notificaciones,
        private readonly MercadoPagoPaymentService $mercadoPago,
        private readonly EmailQueueService $emailQueue
    ) {
    }

    public function procesar(
        int $paymentId,
        array $payload
    ): void {
        if ($paymentId < 1) {
            throw new DomainException(
                'El identificador del pago no es válido.'
            );
        }

        /*
         * Registramos primero la recepción.
         * Si luego falla Mercado Pago o nuestra BD,
         * queda evidencia persistente del webhook.
         */
        $notificacionId =
            $this->notificaciones
                ->crearPendiente(
                    'mercadopago',
                    $this->obtenerIdentificadorNotificacion(
                        $payload,
                        $paymentId
                    ),
                    $this->obtenerTipoNotificacion(
                        $payload
                    ),
                    $payload
                );

        try {
            /*
             * La API externa se consulta ANTES de abrir
             * la transacción para no mantener locks
             * mientras esperamos a Mercado Pago.
             */
            $payment =
                $this->mercadoPago
                    ->obtenerPago($paymentId);

            $this->validarPaymentBasico(
                $paymentId,
                $payment
            );

            $codigoOrden =
                trim(
                    (string) (
                        $payment[
                            'external_reference'
                        ] ?? ''
                    )
                );

            if ($codigoOrden === '') {
                throw new DomainException(
                    'El pago no contiene external_reference.'
                );
            }

            $this->pdo->beginTransaction();

            /*
             * Bloqueamos la orden. Esto serializa
             * webhooks simultáneos correspondientes
             * a una misma compra.
             */
            $orden =
                $this->ordenes
                    ->obtenerPorCodigoBloqueada(
                        $codigoOrden
                    );

            if ($orden === null) {
                throw new DomainException(
                    sprintf(
                        'No existe una orden con código %s.',
                        $codigoOrden
                    )
                );
            }

            $this->validarDatosEconomicos(
                $orden,
                $payment
            );

            /*
             * Primero buscamos si este payment ID
             * ya fue registrado anteriormente.
             */
            $pagoExistente =
                $this->pagos
                    ->obtenerPorProveedorPagoIdBloqueado(
                        (string) $paymentId
                    );

            if ($pagoExistente !== null) {
                /*
                 * Seguridad adicional: un payment ya
                 * persistido jamás puede cambiar de orden.
                 */
                if (
                    (int) $pagoExistente['orden_id']
                    !== (int) $orden['id']
                ) {
                    throw new DomainException(
                        'El pago ya está asociado a otra orden.'
                    );
                }

                $this->pagos
                    ->actualizarDesdeMercadoPago(
                        (int) $pagoExistente['id'],
                        $payment
                    );
            } else {
                /*
                 * La primera notificación utiliza el
                 * registro creado cuando generamos
                 * la Preference.
                 */
                $pagoBase =
                    $this->pagos
                        ->obtenerPagoBaseDeOrdenBloqueado(
                            (int) $orden['id']
                        );

                if ($pagoBase !== null) {
                    $this->pagos
                        ->actualizarDesdeMercadoPago(
                            (int) $pagoBase['id'],
                            $payment
                        );
                } else {
                    /*
                     * Si ya existió otro intento,
                     * conservamos su historial y
                     * creamos una nueva fila.
                     */
                    $this->pagos
                        ->crearPagoDesdeMercadoPago(
                            (int) $orden['id'],
                            $payment
                        );
                }
            }

            $this->actualizarEstadoOrden(
                $orden,
                $payment
            );

            $this->notificaciones
                ->marcarProcesada(
                    $notificacionId
                );

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            /*
             * Este UPDATE se hace después del rollback,
             * para que el error quede guardado aunque
             * haya fallado la transacción principal.
             */
            try {
                $this->notificaciones
                    ->marcarError(
                        $notificacionId,
                        $exception->getMessage()
                    );
            } catch (Throwable $errorRegistro) {
                error_log(
                    sprintf(
                        '[procesador-pago-mp] '
                        . 'No se pudo registrar el error '
                        . 'de la notificación %d: %s',
                        $notificacionId,
                        $errorRegistro->getMessage()
                    )
                );
            }

            throw $exception;
        }
    }

    private function validarPaymentBasico(
        int $paymentId,
        array $payment
    ): void {
        $idRecibido =
            trim(
                (string) (
                    $payment['id'] ?? ''
                )
            );

        if (
            $idRecibido === ''
            || $idRecibido !== (string) $paymentId
        ) {
            throw new DomainException(
                'El payment obtenido no coincide con el solicitado.'
            );
        }

        $estado =
            trim(
                (string) (
                    $payment['status'] ?? ''
                )
            );

        if ($estado === '') {
            throw new DomainException(
                'Mercado Pago no informó el estado del pago.'
            );
        }

        if (
            !array_key_exists(
                'transaction_amount',
                $payment
            )
        ) {
            throw new DomainException(
                'Mercado Pago no informó el importe del pago.'
            );
        }

        $moneda =
            trim(
                (string) (
                    $payment['currency_id'] ?? ''
                )
            );

        if ($moneda === '') {
            throw new DomainException(
                'Mercado Pago no informó la moneda del pago.'
            );
        }
    }

    private function validarDatosEconomicos(
        array $orden,
        array $payment
    ): void {
        $monedaOrden =
            strtoupper(
                trim(
                    (string) $orden['moneda']
                )
            );

        $monedaPago =
            strtoupper(
                trim(
                    (string) (
                        $payment['currency_id']
                        ?? ''
                    )
                )
            );

        if ($monedaOrden !== $monedaPago) {
            throw new DomainException(
                sprintf(
                    'La moneda del pago (%s) no coincide '
                    . 'con la moneda de la orden (%s).',
                    $monedaPago,
                    $monedaOrden
                )
            );
        }

        /*
         * Comparamos en centavos para evitar errores
         * típicos de precisión de float.
         */
        $totalOrdenCentavos =
            $this->importeACentavos(
                $orden['total']
            );

        $totalPagoCentavos =
            $this->importeACentavos(
                $payment[
                    'transaction_amount'
                ]
            );

        if (
            $totalOrdenCentavos
            !== $totalPagoCentavos
        ) {
            throw new DomainException(
                sprintf(
                    'El importe del pago (%s) no coincide '
                    . 'con el total de la orden (%s).',
                    (string) $payment[
                        'transaction_amount'
                    ],
                    (string) $orden['total']
                )
            );
        }
    }

    private function actualizarEstadoOrden(
        array $orden,
        array $payment
    ): void {
        $estadoPago =
            strtolower(
                trim(
                    (string) (
                        $payment['status'] ?? ''
                    )
                )
            );

        $ordenId =
            (int) $orden['id'];

    if ($estadoPago === 'approved') {
            /*
             * Una orden cancelada/reembolsada no debe
             * reactivarse silenciosamente.
             */
        if (
            in_array(
                $orden['estado'],
                [
                    'cancelada',
                    'reembolsada',
                    'expirada',
                ],
                true
            )
        ) {
            throw new DomainException(
                sprintf(
                    'El pago fue aprobado pero la orden '
                    . '%s se encuentra en estado %s.',
                    (string) $orden['codigo'],
                    (string) $orden['estado']
                )
            );
        }

            $this->ordenes
                ->marcarPagada(
                    $ordenId
                );

            $this->ordenes
                ->emitirAccesosPendientes(
                    $ordenId
                );

                $this->emailQueue
                ->encolarCompraConfirmada(
                    $ordenId
                );

            return;
        }

        /*
         * Estados en los que el pago todavía puede
         * terminar aprobado. Conservamos el cupo.
         */
        if (
            in_array(
                $estadoPago,
                [
                    'in_process',
                    'in_mediation',
                    'pending',
                    'authorized',
                ],
                true
            )
        ) {
            $this->ordenes
                ->marcarPagoEnRevision(
                    $ordenId
                );

            return;
        }

        /*
         * Si un pago en revisión termina rechazado,
         * liberamos la orden nuevamente para que el
         * comprador pueda intentar otro payment,
         * siempre sujeto a las reglas de reserva que
         * cerraremos después.
         */
        if (
            in_array(
                $estadoPago,
                [
                    'rejected',
                    'cancelled',
                ],
                true
            )
        ) {
            $this->ordenes
                ->volverAPendienteSiEstabaEnRevision(
                    $ordenId
                );
        }
    }

    private function importeACentavos(
        mixed $importe
    ): int {
        if (
            !is_numeric($importe)
        ) {
            throw new DomainException(
                'El importe recibido no es numérico.'
            );
        }

        return (int) round(
            ((float) $importe) * 100
        );
    }

    private function obtenerIdentificadorNotificacion(
        array $payload,
        int $paymentId
    ): string {
        $identificador =
            trim(
                (string) (
                    $payload['id'] ?? ''
                )
            );

        if ($identificador !== '') {
            return $identificador;
        }

        return 'payment-' . $paymentId;
    }

    private function obtenerTipoNotificacion(
        array $payload
    ): string {
        $tipo =
            trim(
                (string) (
                    $payload['type']
                    ?? $payload['action']
                    ?? 'payment'
                )
            );

        return $tipo !== ''
            ? $tipo
            : 'payment';
    }
}