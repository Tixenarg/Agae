<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class OrdenRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function obtenerEventoBloqueadoPorSlug(
        string $slug
    ): ?array {
        $sql = '
            SELECT
                id,
                nombre,
                slug,
                codigo_prefijo,
                cupo_total,
                minutos_reserva,
                estado,
                venta_desde,
                venta_hasta
            FROM eventos
            WHERE slug = :slug
            LIMIT 1
            FOR UPDATE
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'slug' => $slug,
        ]);

        $evento = $statement->fetch();

        return $evento ?: null;
    }

    public function obtenerTipoAccesoWeb(
        int $eventoId
    ): ?array {
        $sql = '
            SELECT
                id,
                evento_id,
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
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'evento_id' => $eventoId,
        ]);

        $tipoAcceso = $statement->fetch();

        return $tipoAcceso ?: null;
    }

    public function ambitoProfesionalActivoExiste(
        int $ambitoProfesionalId
    ): bool {
        $sql = '
            SELECT COUNT(*)
            FROM ambitos_profesionales
            WHERE id = :id
              AND activo = 1
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'id' => $ambitoProfesionalId,
        ]);

        return (int) $statement->fetchColumn() === 1;
    }

    public function codigoOrdenExiste(
        string $codigo
    ): bool {
        $sql = '
            SELECT COUNT(*)
            FROM ordenes
            WHERE codigo = :codigo
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'codigo' => $codigo,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function crearComprador(
        array $comprador
    ): int {
        $sql = '
            INSERT INTO compradores (
                ambito_profesional_id,
                nombre,
                apellido,
                dni,
                email,
                telefono
            )
            VALUES (
                :ambito_profesional_id,
                :nombre,
                :apellido,
                :dni,
                :email,
                :telefono
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'ambito_profesional_id' =>
                $comprador['ambito_profesional_id'],

            'nombre' =>
                $comprador['nombre'],

            'apellido' =>
                $comprador['apellido'],

            'dni' =>
                $comprador['dni'],

            'email' =>
                $comprador['email'],

            'telefono' =>
                $comprador['telefono'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function crearOrden(
        array $orden
    ): int {
        $sql = '
            INSERT INTO ordenes (
                evento_id,
                comprador_id,
                codigo,
                checkout_token,
                origen,
                estado,
                cantidad_accesos,
                precio_unitario,
                subtotal,
                total,
                moneda,
                reserva_hasta,
                acepto_terminos,
                acepto_privacidad,
                aceptaciones_en
            )
            VALUES (
                :evento_id,
                :comprador_id,
                :codigo,
                :checkout_token,
                :origen,
                :estado,
                :cantidad_accesos,
                :precio_unitario,
                :subtotal,
                :total,
                :moneda,
                :reserva_hasta,
                :acepto_terminos,
                :acepto_privacidad,
                CURRENT_TIMESTAMP
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'evento_id' =>
                $orden['evento_id'],

            'comprador_id' =>
                $orden['comprador_id'],

            'codigo' =>
                $orden['codigo'],

            'checkout_token' =>
                $orden['checkout_token'],

            'origen' =>
                $orden['origen'],

            'estado' =>
                $orden['estado'],

            'cantidad_accesos' =>
                $orden['cantidad_accesos'],

            'precio_unitario' =>
                $orden['precio_unitario'],

            'subtotal' =>
                $orden['subtotal'],

            'total' =>
                $orden['total'],

            'moneda' =>
                $orden['moneda'],

            'reserva_hasta' =>
                $orden['reserva_hasta'],

            'acepto_terminos' =>
                $orden['acepto_terminos'],

            'acepto_privacidad' =>
                $orden['acepto_privacidad'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function crearAcceso(
        array $acceso
    ): int {
        $sql = '
            INSERT INTO accesos (
                orden_id,
                tipo_acceso_id,
                codigo,
                numero_en_orden,
                nombre,
                apellido,
                sin_nombre,
                qr_token,
                estado
            )
            VALUES (
                :orden_id,
                :tipo_acceso_id,
                :codigo,
                :numero_en_orden,
                :nombre,
                :apellido,
                :sin_nombre,
                :qr_token,
                :estado
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'orden_id' =>
                $acceso['orden_id'],

            'tipo_acceso_id' =>
                $acceso['tipo_acceso_id'],

            'codigo' =>
                $acceso['codigo'],

            'numero_en_orden' =>
                $acceso['numero_en_orden'],

            'nombre' =>
                $acceso['nombre'],

            'apellido' =>
                $acceso['apellido'],

            'sin_nombre' =>
                $acceso['sin_nombre'],

            'qr_token' =>
                $acceso['qr_token'],

            'estado' =>
                $acceso['estado'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function obtenerPorCodigoBloqueada(
    string $codigo
): ?array {
    $sql = '
        SELECT
            id,
            evento_id,
            codigo,
            estado,
            total,
            moneda,
            cantidad_accesos,
            pagada_en

        FROM ordenes

        WHERE codigo = :codigo

        LIMIT 1
        FOR UPDATE
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->execute([
        'codigo' =>
            $codigo,
    ]);

    $orden =
        $statement->fetch();

    return $orden ?: null;
    }

    public function marcarPagada(
        int $ordenId
    ): void {
        $sql = '
            UPDATE ordenes
            SET
                estado = :estado,
                pagada_en =
                    COALESCE(
                        pagada_en,
                        CURRENT_TIMESTAMP
                    )

            WHERE id = :id
            AND estado <> :estado_pagada

            LIMIT 1
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'id' =>
                $ordenId,

            'estado' =>
                'pagada',

            'estado_pagada' =>
                'pagada',
        ]);
    }

    public function emitirAccesosPendientes(
        int $ordenId
    ): void {
        $sql = '
            UPDATE accesos
            SET estado = :estado_emitido

            WHERE orden_id = :orden_id
            AND estado = :estado_pendiente
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'orden_id' =>
                $ordenId,

            'estado_emitido' =>
                'emitido',

            'estado_pendiente' =>
                'pendiente_emision',
        ]);
    }

    public function marcarPagoEnRevision(
        int $ordenId
    ): void {
        $sql = '
            UPDATE ordenes
            SET estado = :estado

            WHERE id = :id
            AND estado = :estado_actual

            LIMIT 1
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'id' =>
                $ordenId,

            'estado' =>
                'pago_en_revision',

            'estado_actual' =>
                'pendiente_pago',
        ]);
    }

    public function volverAPendienteSiEstabaEnRevision(
        int $ordenId
    ): void {
        $sql = '
            UPDATE ordenes
            SET estado = :estado

            WHERE id = :id
            AND estado = :estado_actual

            LIMIT 1
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'id' =>
                $ordenId,

            'estado' =>
                'pendiente_pago',

            'estado_actual' =>
                'pago_en_revision',
        ]);
    }

    public function obtenerDatosParaEmail(
    int $ordenId
    ): ?array {
        $sql = '
            SELECT
                o.id,
                o.codigo,
                o.cantidad_accesos,

                c.nombre AS comprador_nombre,
                c.apellido AS comprador_apellido,
                c.email AS comprador_email

            FROM ordenes o

            INNER JOIN compradores c
                ON c.id = o.comprador_id

            WHERE o.id = :orden_id

            LIMIT 1
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'orden_id' =>
                $ordenId,
        ]);

        $orden =
            $statement->fetch();

        return $orden ?: null;
    }

    public function obtenerDetalle(
    int $id
): ?array {

    $sql = "
        SELECT
            o.*,

            c.nombre,
            c.apellido,
            c.email,
            c.telefono,

            e.nombre AS evento_nombre

        FROM ordenes o

        INNER JOIN compradores c
            ON c.id = o.comprador_id

        INNER JOIN eventos e
            ON e.id = o.evento_id

        WHERE o.id = :id

        LIMIT 1
    ";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        'id' => $id
    ]);

    $orden = $stmt->fetch(PDO::FETCH_ASSOC);

    return $orden ?: null;
}
}