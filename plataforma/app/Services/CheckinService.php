<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CheckinRepository;
use PDO;
use Throwable;

final class CheckinService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly CheckinRepository $checkins
    ) {
    }

    public function registrar(
        string $codigo,
        ?int $usuarioAdminId = null,
        ?string $dispositivo = null,
        ?string $ip = null
    ): array {
        $codigo =
            strtoupper(
                trim($codigo)
            );

        if ($codigo === '') {
            return [
                'ok' => false,
                'resultado' => 'invalido',
                'mensaje' => 'ACCESO NO VÁLIDO',
            ];
        }

        try {
            $this->pdo->beginTransaction();

            $acceso =
                $this->checkins
                    ->obtenerAccesoBloqueadoPorCodigo(
                        $codigo
                    );

            if ($acceso === null) {
                $this->pdo->rollBack();

                return [
                    'ok' => false,
                    'resultado' => 'no_encontrado',
                    'mensaje' => 'ACCESO NO ENCONTRADO',
                ];
            }

            $accesoId =
                (int) $acceso['id'];

            $nombreCompleto =
                trim(
                    (string) (
                        $acceso['nombre']
                        ?? ''
                    )
                    . ' '
                    . (string) (
                        $acceso['apellido']
                        ?? ''
                    )
                );

            $datosAcceso = [
                'codigo' =>
                    (string) $acceso['codigo'],

                'nombre' =>
                    $nombreCompleto,

                'tipo' =>
                    (string) $acceso[
                        'tipo_acceso_nombre'
                    ],
            ];

            /*
             * -------------------------------------------------
             * ACCESO YA UTILIZADO
             * -------------------------------------------------
             */

            $checkinExistente =
                $this->checkins
                    ->obtenerCheckinPorAcceso(
                        $accesoId
                    );

            if ($checkinExistente !== null) {
                $this->pdo->rollBack();

                return [
                    'ok' => false,
                    'resultado' => 'ya_utilizado',
                    'mensaje' => 'ACCESO YA UTILIZADO',

                    'utilizado_en' =>
                        $checkinExistente[
                            'registrado_en'
                        ],

                    'acceso' =>
                        $datosAcceso,
                ];
            }

            /*
             * -------------------------------------------------
             * ESTADO DEL ACCESO
             * -------------------------------------------------
             */

            if (
                $acceso['estado']
                !== 'emitido'
            ) {
                $estado =
                    (string) $acceso['estado'];

                $this->pdo->rollBack();

                return [
                    'ok' => false,

                    'resultado' =>
                        $estado === 'anulado'
                            ? 'anulado'
                            : 'no_habilitado',

                    'mensaje' =>
                        $estado === 'anulado'
                            ? 'ACCESO ANULADO'
                            : 'ACCESO NO HABILITADO',

                    'acceso' =>
                        $datosAcceso,
                ];
            }

            /*
             * -------------------------------------------------
             * ESTADO DE LA ORDEN
             * Solo una orden efectivamente pagada ingresa.
             * -------------------------------------------------
             */

            if (
                $acceso['orden_estado']
                !== 'pagada'
            ) {
                $this->pdo->rollBack();

                return [
                    'ok' => false,
                    'resultado' =>
                        'orden_no_valida',
                    'mensaje' =>
                        'ACCESO NO HABILITADO',

                    'acceso' =>
                        $datosAcceso,
                ];
            }

            /*
             * -------------------------------------------------
             * CHECK-IN
             * -------------------------------------------------
             */

            $this->checkins
                ->registrar(
                    $accesoId,
                    $usuarioAdminId,
                    'qr',
                    $dispositivo,
                    $ip
                );

            $this->checkins
                ->marcarAccesoUtilizado(
                    $accesoId
                );

            $this->pdo->commit();

            return [
                'ok' => true,
                'resultado' => 'valido',
                'mensaje' => 'ACCESO VÁLIDO',
                'acceso' => $datosAcceso,
            ];

        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}