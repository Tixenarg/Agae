<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class CupoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function contarAccesosConfirmados(
        int $eventoId
    ): int {
        $sql = "
            SELECT COUNT(a.id)
            FROM accesos a
            INNER JOIN ordenes o
                ON o.id = a.orden_id
            INNER JOIN tipos_acceso ta
                ON ta.id = a.tipo_acceso_id
            WHERE o.evento_id = :evento_id
              AND ta.computa_cupo = 1
              AND a.estado IN (
                  'pendiente_emision',
                  'emitido',
                  'utilizado'
              )
              AND (
                  o.estado IN (
                      'pagada',
                      'pago_en_revision'
                  )
                  OR (
                      o.origen <> 'web'
                      AND o.estado NOT IN (
                          'expirada',
                          'cancelada',
                          'reembolsada'
                      )
                  )
              )
        ";

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'evento_id' => $eventoId,
        ]);

        return (int) $statement->fetchColumn();
    }

    public function contarReservasVigentes(
        int $eventoId
    ): int {
        $sql = "
            SELECT COALESCE(
                SUM(cantidad_accesos),
                0
            )
            FROM ordenes
            WHERE evento_id = :evento_id
              AND origen = 'web'
              AND estado = 'pendiente_pago'
              AND reserva_hasta IS NOT NULL
              AND reserva_hasta > NOW()
        ";

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'evento_id' => $eventoId,
        ]);

        return (int) $statement->fetchColumn();
    }
}