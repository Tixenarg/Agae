<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class EstadisticasRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function obtenerResumen(): array
    {
        return [
            'accesos_emitidos' =>
                $this->obtenerAccesosEmitidos(),

            'capacidad' =>
                1700,

            'recaudacion' =>
                $this->obtenerRecaudacion(),

            'checkins' =>
                $this->obtenerCheckins(),

            'emails' =>
                $this->obtenerEmailsEnviados(),
        ];
    }

    public function obtenerActividadReciente(
        int $limite = 12
    ): array {
        $limite =
            max(
                1,
                min(
                    $limite,
                    50
                )
            );

        $sql = '
            SELECT
                actividad.tipo,
                actividad.titulo,
                actividad.detalle,
                actividad.fecha

            FROM (

                SELECT
                    "pago" AS tipo,
                    "Pago confirmado" AS titulo,

                    CONCAT(
                        "Orden ",
                        o.codigo
                    ) AS detalle,

                    o.pagada_en AS fecha

                FROM ordenes o

                WHERE o.estado = "pagada"
                  AND o.pagada_en IS NOT NULL

                UNION ALL

                SELECT
                    "email" AS tipo,
                    "Email enviado" AS titulo,

                    e.destinatario AS detalle,

                    e.enviado_en AS fecha

                FROM emails e

                WHERE e.enviado_en IS NOT NULL

                UNION ALL

                SELECT
                    "checkin" AS tipo,
                    "Check-in realizado" AS titulo,

                    TRIM(
                        CONCAT(
                            COALESCE(
                                a.nombre,
                                ""
                            ),
                            " ",
                            COALESCE(
                                a.apellido,
                                ""
                            )
                        )
                    ) AS detalle,

                    c.registrado_en AS fecha

                FROM checkins c

                INNER JOIN accesos a
                    ON a.id = c.acceso_id

            ) actividad

            WHERE actividad.fecha IS NOT NULL

            ORDER BY actividad.fecha DESC

            LIMIT '
            . $limite;

        $statement =
            $this->pdo->query(
                $sql
            );

        return $statement->fetchAll();
    }

    private function obtenerAccesosEmitidos(): int
    {
        $sql = '
            SELECT COUNT(*)

            FROM accesos

            WHERE estado IN (
                "emitido",
                "utilizado"
            )
        ';

        return (int) $this->pdo
            ->query($sql)
            ->fetchColumn();
    }

    private function obtenerRecaudacion(): float
    {
        $sql = '
            SELECT
                COALESCE(
                    SUM(total),
                    0
                )

            FROM ordenes

            WHERE estado = "pagada"
        ';

        return (float) $this->pdo
            ->query($sql)
            ->fetchColumn();
    }

    private function obtenerCheckins(): int
    {
        $sql = '
            SELECT COUNT(*)

            FROM checkins
        ';

        return (int) $this->pdo
            ->query($sql)
            ->fetchColumn();
    }

    private function obtenerEmailsEnviados(): int
    {
        $sql = '
            SELECT COUNT(*)

            FROM emails

            WHERE estado = "enviado"
              AND enviado_en IS NOT NULL
        ';

        return (int) $this->pdo
            ->query($sql)
            ->fetchColumn();
    }
}