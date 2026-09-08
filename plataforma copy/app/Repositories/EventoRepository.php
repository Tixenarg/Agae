<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class EventoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function obtenerPorSlug(string $slug): ?array
    {
        $sql = "
            SELECT
                *
            FROM eventos
            WHERE slug = :slug
            LIMIT 1
        ";

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'slug' => $slug,
        ]);

        $evento = $statement->fetch();

        return $evento ?: null;
    }

    public function obtenerAccesoWeb(int $eventoId): ?array
    {
        $sql = "
            SELECT
                id,
                nombre,
                codigo,
                precio,
                requiere_pago,
                computa_cupo
            FROM tipos_acceso
            WHERE evento_id = :evento_id
              AND venta_web = 1
              AND activo = 1
            LIMIT 1
        ";

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'evento_id' => $eventoId,
        ]);

        $tipoAcceso = $statement->fetch();

        return $tipoAcceso ?: null;
    }
}