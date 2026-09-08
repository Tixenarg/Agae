<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CupoRepository;
use App\Repositories\OrdenRepository;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use InvalidArgumentException;
use PDO;
use Throwable;

final class OrdenService
{
    private const EVENTO_SLUG = 'evento-6n-2026';
    private const CANTIDAD_MAXIMA = 10;

    private const CARACTERES_CODIGO =
        'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function __construct(
        private readonly PDO $pdo,
        private readonly OrdenRepository $ordenRepository,
        private readonly CupoRepository $cupoRepository
    ) {
    }

    public function crearOrdenWeb(
        array $datos
    ): array {
        $datosNormalizados =
            $this->validarYNormalizar($datos);

        try {
            $this->pdo->beginTransaction();

            /*
             * Bloqueamos la fila del evento durante la transacción.
             * Esto serializa la creación de órdenes y evita
             * sobreventa ante compras simultáneas.
             */
            $evento =
                $this->ordenRepository
                    ->obtenerEventoBloqueadoPorSlug(
                        self::EVENTO_SLUG
                    );

            if ($evento === null) {
                throw new DomainException(
                    'El evento solicitado no existe.'
                );
            }

            $eventoId =
                (int) $evento['id'];

            $tipoAcceso =
                $this->ordenRepository
                    ->obtenerTipoAccesoWeb(
                        $eventoId
                    );

            if ($tipoAcceso === null) {
                throw new DomainException(
                    'No hay accesos habilitados para venta web.'
                );
            }

            $ambitoProfesionalId =
                $datosNormalizados['comprador']
                    ['ambito_profesional_id'];

            $ambitoValido =
                $this->ordenRepository
                    ->ambitoProfesionalActivoExiste(
                        $ambitoProfesionalId
                    );

            if (!$ambitoValido) {
                throw new InvalidArgumentException(
                    'El ámbito profesional seleccionado no es válido.'
                );
            }

            $cantidad =
                count(
                    $datosNormalizados['accesos']
                );

            /*
             * El cupo se vuelve a calcular dentro de la
             * transacción y con el evento bloqueado.
             */
            $accesosConfirmados =
                $this->cupoRepository
                    ->contarAccesosConfirmados(
                        $eventoId
                    );

            $reservasVigentes =
                $this->cupoRepository
                    ->contarReservasVigentes(
                        $eventoId
                    );

            $disponibles =
                (int) $evento['cupo_total']
                - $accesosConfirmados
                - $reservasVigentes;

            if ($disponibles < $cantidad) {
                throw new DomainException(
                    'No hay cupo suficiente para completar la compra.'
                );
            }

            $codigoOrden =
                $this->generarCodigoOrden(
                    (string) $evento['codigo_prefijo']
                );

            /*
             * Token privado utilizado únicamente para iniciar
             * o reintentar el checkout de esta orden.
             */
            $checkoutToken =
                bin2hex(
                    random_bytes(32)
                );

            $compradorId =
                $this->ordenRepository
                    ->crearComprador(
                        $datosNormalizados['comprador']
                    );

            $precioUnitario =
                (float) $tipoAcceso['precio'];

            $total =
                $precioUnitario * $cantidad;

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
                $ahora->modify(
                    sprintf(
                        '+%d minutes',
                        (int) $evento[
                            'minutos_reserva'
                        ]
                    )
                );

            $ordenId =
                $this->ordenRepository
                    ->crearOrden([
                        'evento_id' =>
                            $eventoId,

                        'comprador_id' =>
                            $compradorId,

                        'codigo' =>
                            $codigoOrden,

                        'checkout_token' =>
                            $checkoutToken,

                        'origen' =>
                            'web',

                        'estado' =>
                            'pendiente_pago',

                        'cantidad_accesos' =>
                            $cantidad,

                        'precio_unitario' =>
                            number_format(
                                $precioUnitario,
                                2,
                                '.',
                                ''
                            ),

                        'subtotal' =>
                            number_format(
                                $total,
                                2,
                                '.',
                                ''
                            ),

                        'total' =>
                            number_format(
                                $total,
                                2,
                                '.',
                                ''
                            ),

                        'moneda' =>
                            'ARS',

                        'reserva_hasta' =>
                            $reservaHasta->format(
                                'Y-m-d H:i:s'
                            ),

                        'acepto_terminos' =>
                            1,

                        'acepto_privacidad' =>
                            1,
                    ]);

            $accesosCreados = [];

            foreach (
                $datosNormalizados['accesos']
                as $indice => $datosAcceso
            ) {
                $numeroEnOrden =
                    $indice + 1;

                $codigoAcceso =
                    $codigoOrden
                    . '-A'
                    . $numeroEnOrden;

                $sinNombre =
                    $datosAcceso['sin_nombre'];

                $accesoId =
                    $this->ordenRepository
                        ->crearAcceso([
                            'orden_id' =>
                                $ordenId,

                            'tipo_acceso_id' =>
                                (int) $tipoAcceso['id'],

                            'codigo' =>
                                $codigoAcceso,

                            'numero_en_orden' =>
                                $numeroEnOrden,

                            'nombre' =>
                                $sinNombre
                                    ? null
                                    : $datosAcceso[
                                        'nombre'
                                    ],

                            'apellido' =>
                                $sinNombre
                                    ? null
                                    : $datosAcceso[
                                        'apellido'
                                    ],

                            'sin_nombre' =>
                                $sinNombre ? 1 : 0,

                            /*
                             * Token único y seguro para el QR
                             * de este acceso individual.
                             */
                            'qr_token' =>
                                bin2hex(
                                    random_bytes(32)
                                ),

                            'estado' =>
                                'pendiente_emision',
                        ]);

                $accesosCreados[] = [
                    'id' =>
                        $accesoId,

                    'codigo' =>
                        $codigoAcceso,

                    'numero_en_orden' =>
                        $numeroEnOrden,

                    'sin_nombre' =>
                        $sinNombre,

                    'nombre' =>
                        $sinNombre
                            ? null
                            : $datosAcceso['nombre'],

                    'apellido' =>
                        $sinNombre
                            ? null
                            : $datosAcceso['apellido'],
                ];
            }

            $this->pdo->commit();

            return [
                'orden_id' =>
                    $ordenId,

                'codigo_orden' =>
                    $codigoOrden,

                'checkout_token' =>
                    $checkoutToken,

                'evento' =>
                    (string) $evento['nombre'],

                'estado' =>
                    'pendiente_pago',

                'cantidad_accesos' =>
                    $cantidad,

                'precio_unitario' =>
                    $precioUnitario,

                'total' =>
                    $total,

                'moneda' =>
                    'ARS',

                'reserva_hasta' =>
                    $reservaHasta->format(
                        DATE_ATOM
                    ),

                'comprador' => [
                    'nombre' =>
                        $datosNormalizados[
                            'comprador'
                        ]['nombre'],

                    'apellido' =>
                        $datosNormalizados[
                            'comprador'
                        ]['apellido'],

                    'email' =>
                        $datosNormalizados[
                            'comprador'
                        ]['email'],
                ],

                'accesos' =>
                    $accesosCreados,
            ];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function validarYNormalizar(
        array $datos
    ): array {
        $comprador =
            $datos['comprador'] ?? null;

        $accesos =
            $datos['accesos'] ?? null;

        if (!is_array($comprador)) {
            throw new InvalidArgumentException(
                'Los datos del comprador son obligatorios.'
            );
        }

        if (!is_array($accesos)) {
            throw new InvalidArgumentException(
                'Los accesos son obligatorios.'
            );
        }

        $cantidad =
            count($accesos);

        if (
            $cantidad < 1
            || $cantidad > self::CANTIDAD_MAXIMA
        ) {
            throw new InvalidArgumentException(
                'La cantidad de accesos no es válida.'
            );
        }

        $nombre =
            trim(
                (string) (
                    $comprador['nombre'] ?? ''
                )
            );

        $apellido =
            trim(
                (string) (
                    $comprador['apellido'] ?? ''
                )
            );

        $dni =
            preg_replace(
                '/\D+/',
                '',
                (string) (
                    $comprador['dni'] ?? ''
                )
            ) ?? '';

        $email =
            mb_strtolower(
                trim(
                    (string) (
                        $comprador['email'] ?? ''
                    )
                )
            );

        $telefono =
            trim(
                (string) (
                    $comprador['telefono'] ?? ''
                )
            );

        $ambitoProfesionalId =
            filter_var(
                $comprador[
                    'ambito_profesional_id'
                ] ?? null,
                FILTER_VALIDATE_INT
            );

        if (
            $nombre === ''
            || mb_strlen($nombre) > 100
        ) {
            throw new InvalidArgumentException(
                'El nombre del comprador no es válido.'
            );
        }

        if (
            $apellido === ''
            || mb_strlen($apellido) > 100
        ) {
            throw new InvalidArgumentException(
                'El apellido del comprador no es válido.'
            );
        }

        if (
            strlen($dni) < 7
            || strlen($dni) > 11
        ) {
            throw new InvalidArgumentException(
                'El DNI del comprador no es válido.'
            );
        }

        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
            || mb_strlen($email) > 180
        ) {
            throw new InvalidArgumentException(
                'El email del comprador no es válido.'
            );
        }

        if (
            $telefono === ''
            || mb_strlen($telefono) > 40
        ) {
            throw new InvalidArgumentException(
                'El teléfono del comprador no es válido.'
            );
        }

        if (
            $ambitoProfesionalId === false
            || $ambitoProfesionalId < 1
        ) {
            throw new InvalidArgumentException(
                'El ámbito profesional es obligatorio.'
            );
        }

        $aceptoTerminos =
            filter_var(
                $datos['acepto_terminos'] ?? false,
                FILTER_VALIDATE_BOOL
            );

        $aceptoPrivacidad =
            filter_var(
                $datos['acepto_privacidad'] ?? false,
                FILTER_VALIDATE_BOOL
            );

        if (
            !$aceptoTerminos
            || !$aceptoPrivacidad
        ) {
            throw new InvalidArgumentException(
                'Debés aceptar los términos y la política de privacidad.'
            );
        }

        $accesosNormalizados = [];

        foreach (
            $accesos
            as $indice => $acceso
        ) {
            if (!is_array($acceso)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Los datos del acceso %d no son válidos.',
                        $indice + 1
                    )
                );
            }

            $sinNombre =
                filter_var(
                    $acceso['sin_nombre'] ?? false,
                    FILTER_VALIDATE_BOOL
                );

            $nombreAcceso =
                trim(
                    (string) (
                        $acceso['nombre'] ?? ''
                    )
                );

            $apellidoAcceso =
                trim(
                    (string) (
                        $acceso['apellido'] ?? ''
                    )
                );

            if (!$sinNombre) {
                if (
                    $nombreAcceso === ''
                    || mb_strlen(
                        $nombreAcceso
                    ) > 100
                ) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'El nombre del acceso %d no es válido.',
                            $indice + 1
                        )
                    );
                }

                if (
                    $apellidoAcceso === ''
                    || mb_strlen(
                        $apellidoAcceso
                    ) > 100
                ) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'El apellido del acceso %d no es válido.',
                            $indice + 1
                        )
                    );
                }
            }

            $accesosNormalizados[] = [
                'sin_nombre' =>
                    $sinNombre,

                'nombre' =>
                    $sinNombre
                        ? null
                        : $nombreAcceso,

                'apellido' =>
                    $sinNombre
                        ? null
                        : $apellidoAcceso,
            ];
        }

        return [
            'comprador' => [
                'ambito_profesional_id' =>
                    (int) $ambitoProfesionalId,

                'nombre' =>
                    $nombre,

                'apellido' =>
                    $apellido,

                'dni' =>
                    $dni,

                'email' =>
                    $email,

                'telefono' =>
                    $telefono,
            ],

            'accesos' =>
                $accesosNormalizados,
        ];
    }

    private function generarCodigoOrden(
        string $prefijo
    ): string {
        for (
            $intento = 0;
            $intento < 20;
            $intento++
        ) {
            $codigo =
                strtoupper($prefijo)
                . '-'
                . $this->generarBloqueAleatorio(4)
                . '-'
                . $this->generarBloqueAleatorio(4);

            if (
                !$this->ordenRepository
                    ->codigoOrdenExiste($codigo)
            ) {
                return $codigo;
            }
        }

        throw new DomainException(
            'No se pudo generar un código de orden único.'
        );
    }

    private function generarBloqueAleatorio(
        int $longitud
    ): string {
        $resultado = '';

        $cantidadCaracteres =
            strlen(
                self::CARACTERES_CODIGO
            );

        for (
            $indice = 0;
            $indice < $longitud;
            $indice++
        ) {
            $posicion =
                random_int(
                    0,
                    $cantidadCaracteres - 1
                );

            $resultado .=
                self::CARACTERES_CODIGO[
                    $posicion
                ];
        }

        return $resultado;
    }
}