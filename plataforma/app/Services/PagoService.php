<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PagoRepository;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use RuntimeException;

final class PagoService
{
    public function __construct(
        private readonly PagoRepository $pagoRepository,
        private readonly MercadoPagoService $mercadoPagoService
    ) {
    }

    public function prepararCheckout(
        string $codigo,
        string $checkoutToken
    ): array {
        $codigo = trim($codigo);
        $checkoutToken = trim($checkoutToken);

        if (
            $codigo === ''
            || strlen($checkoutToken) !== 64
        ) {
            throw new DomainException(
                'Los datos de la orden no son válidos.'
            );
        }

        $orden =
            $this->pagoRepository
                ->obtenerOrdenParaCheckout(
                    $codigo,
                    $checkoutToken
                );

        if ($orden === null) {
            throw new DomainException(
                'No se encontró la orden.'
            );
        }

        if (
            $orden['estado']
            !== 'pendiente_pago'
        ) {
            throw new DomainException(
                'La orden ya no está disponible para pagar.'
            );
        }

        $zonaHoraria =
            new DateTimeZone(
                'America/Argentina/Buenos_Aires'
            );

        $ahora =
            new DateTimeImmutable(
                'now',
                $zonaHoraria
            );

        $reservaHasta =
            new DateTimeImmutable(
                $orden['reserva_hasta'],
                $zonaHoraria
            );

        if ($reservaHasta <= $ahora) {
            throw new DomainException(
                'La reserva de la orden venció.'
            );
        }

        /*
         * Si ya se creó una preferencia, reutilizamos
         * la URL almacenada y no generamos otra.
         */
        $pagoExistente =
            $this->pagoRepository
                ->obtenerPagoConPreferencia(
                    (int) $orden['id']
                );

        if ($pagoExistente !== null) {
            $datos =
                json_decode(
                    $pagoExistente['datos_respuesta'],
                    true
                );

            $checkoutUrl =
                $datos['checkout_url'] ?? null;

            if (
                is_string($checkoutUrl)
                && $checkoutUrl !== ''
            ) {
                return [
                    'pago_id' =>
                        (int) $pagoExistente['id'],

                    'preference_id' =>
                        $pagoExistente[
                            'proveedor_preferencia_id'
                        ],

                    'checkout_url' =>
                        $checkoutUrl,

                    'reutilizada' =>
                        true,
                ];
            }
        }

        $preferencia =
            $this->mercadoPagoService
                ->crearPreferencia($orden);

        $pagoId =
            $this->pagoRepository
                ->crearPagoPendiente([
                    'orden_id' =>
                        (int) $orden['id'],

                    'proveedor_preferencia_id' =>
                        $preferencia[
                            'preference_id'
                        ],

                    'importe' =>
                        $orden['total'],

                    'moneda' =>
                        $orden['moneda'],

                    'datos_respuesta' =>
                        $preferencia,
                ]);

        return [
            'pago_id' =>
                $pagoId,

            'preference_id' =>
                $preferencia['preference_id'],

            'checkout_url' =>
                $preferencia['checkout_url'],

            'reutilizada' =>
                false,
        ];
    }
}