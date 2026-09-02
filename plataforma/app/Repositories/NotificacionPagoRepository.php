<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class NotificacionPagoRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function crearPendiente(
        string $proveedor,
        ?string $identificadorExterno,
        ?string $tipo,
        array $payload
    ): int {
        $sql = '
            INSERT INTO notificaciones_pago (
                proveedor,
                identificador_externo,
                tipo,
                payload,
                estado_procesamiento,
                intentos
            )
            VALUES (
                :proveedor,
                :identificador_externo,
                :tipo,
                :payload,
                :estado_procesamiento,
                :intentos
            )
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'proveedor' =>
                $proveedor,

            'identificador_externo' =>
                $identificadorExterno,

            'tipo' =>
                $tipo,

            'payload' =>
                json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_THROW_ON_ERROR
                ),

            'estado_procesamiento' =>
                'pendiente',

            'intentos' =>
                0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function marcarProcesada(
        int $id
    ): void {
        $sql = '
            UPDATE notificaciones_pago
            SET
                estado_procesamiento = :estado,
                procesada_en = CURRENT_TIMESTAMP,
                intentos = intentos + 1,
                ultimo_error = NULL

            WHERE id = :id
            LIMIT 1
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'id' =>
                $id,

            'estado' =>
                'procesada',
        ]);
    }

    public function marcarError(
        int $id,
        string $error
    ): void {
        $sql = '
            UPDATE notificaciones_pago
            SET
                estado_procesamiento = :estado,
                intentos = intentos + 1,
                ultimo_error = :ultimo_error

            WHERE id = :id
            LIMIT 1
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'id' =>
                $id,

            'estado' =>
                'error',

            'ultimo_error' =>
                $error,
        ]);
    }
}