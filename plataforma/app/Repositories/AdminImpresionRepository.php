<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AdminImpresionRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function obtenerCategoriasEvento(
        int $eventoId
    ): array {
        $sql = '
            SELECT
                ta.id,
                ta.nombre,
                ta.codigo,

                COUNT(a.id) AS cantidad_total,

                SUM(
                    CASE
                        WHEN a.utilizado_en IS NOT NULL
                        THEN 1
                        ELSE 0
                    END
                ) AS cantidad_ingresados,

                SUM(
                    CASE
                        WHEN a.utilizado_en IS NULL
                        AND a.estado NOT IN (
                            "anulado",
                            "reembolsado"
                        )
                        THEN 1
                        ELSE 0
                    END
                ) AS cantidad_pendientes

            FROM tipos_acceso ta

            LEFT JOIN accesos a
                ON a.tipo_acceso_id = ta.id

            WHERE ta.evento_id = :evento_id
              AND ta.activo = 1

            GROUP BY
                ta.id,
                ta.nombre,
                ta.codigo

            ORDER BY ta.id ASC
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'evento_id' => $eventoId,
        ]);

        return $statement->fetchAll();
    }
}