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

    public function obtenerPorId(
    int $eventoId
): ?array {
    $sql = '
        SELECT
            id,
            nombre,
            slug,
            codigo_prefijo,
            descripcion,
            fecha_inicio,
            fecha_fin,
            lugar,
            direccion,
            cupo_total,
            minutos_reserva,
            estado,
            venta_desde,
            venta_hasta,
            creado_en,
            actualizado_en
        FROM eventos
        WHERE id = :evento_id
        LIMIT 1
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->execute([
        'evento_id' => $eventoId,
    ]);

    $evento =
        $statement->fetch();

    return $evento ?: null;
}

public function actualizarConfiguracion(
    int $eventoId,
    array $datos
): void {
    $sql = '
        UPDATE eventos
        SET
            nombre = :nombre,
            descripcion = :descripcion,
            fecha_inicio = :fecha_inicio,
            fecha_fin = :fecha_fin,
            lugar = :lugar,
            direccion = :direccion,
            cupo_total = :cupo_total,
            minutos_reserva = :minutos_reserva,
            estado = :estado
        WHERE id = :evento_id
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->execute([
        'evento_id' =>
            $eventoId,

        'nombre' =>
            trim((string) $datos['nombre']),

        'descripcion' =>
            trim(
                (string) $datos['descripcion']
            ),

        'fecha_inicio' =>
            $datos['fecha_inicio'],

        'fecha_fin' =>
            $datos['fecha_fin'],

        'lugar' =>
            trim((string) $datos['lugar']),

        'direccion' =>
            trim((string) $datos['direccion']),

        'cupo_total' =>
            (int) $datos['cupo_total'],

        'minutos_reserva' =>
            (int) $datos['minutos_reserva'],

        'estado' =>
            (string) $datos['estado'],
    ]);
}
}