<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CheckinRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function obtenerAccesoBloqueadoPorCodigo(
        string $codigo
    ): ?array {
        $sql = '
            SELECT
                a.id,
                a.codigo,
                a.estado,
                a.nombre,
                a.apellido,

                o.id AS orden_id,
                o.estado AS orden_estado,

                ta.nombre AS tipo_acceso_nombre,
                ta.codigo AS tipo_acceso_codigo

            FROM accesos a

            INNER JOIN ordenes o
                ON o.id = a.orden_id

            INNER JOIN tipos_acceso ta
                ON ta.id = a.tipo_acceso_id

            WHERE a.codigo = :codigo

            LIMIT 1
            FOR UPDATE
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'codigo' =>
                $codigo,
        ]);

        $acceso =
            $statement->fetch();

        return $acceso ?: null;
    }

    public function obtenerCheckinPorAcceso(
        int $accesoId
    ): ?array {
        $sql = '
            SELECT
                id,
                acceso_id,
                metodo,
                dispositivo,
                registrado_en

            FROM checkins

            WHERE acceso_id = :acceso_id

            LIMIT 1
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'acceso_id' =>
                $accesoId,
        ]);

        $checkin =
            $statement->fetch();

        return $checkin ?: null;
    }

    public function registrar(
        int $accesoId,
        ?int $usuarioAdminId,
        string $metodo,
        ?string $dispositivo,
        ?string $ip
    ): int {
        $sql = '
            INSERT INTO checkins (
                acceso_id,
                usuario_admin_id,
                metodo,
                dispositivo,
                ip
            )
            VALUES (
                :acceso_id,
                :usuario_admin_id,
                :metodo,
                :dispositivo,
                :ip
            )
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'acceso_id' =>
                $accesoId,

            'usuario_admin_id' =>
                $usuarioAdminId,

            'metodo' =>
                $metodo,

            'dispositivo' =>
                $dispositivo,

            'ip' =>
                $ip,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function marcarAccesoUtilizado(
        int $accesoId
    ): void {
        $sql = '
            UPDATE accesos
            SET
                estado = :estado,
                utilizado_en =
                    COALESCE(
                        utilizado_en,
                        CURRENT_TIMESTAMP
                    )

            WHERE id = :id
              AND estado = :estado_emitido

            LIMIT 1
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'id' =>
                $accesoId,

            'estado' =>
                'utilizado',

            'estado_emitido' =>
                'emitido',
        ]);
    }
}