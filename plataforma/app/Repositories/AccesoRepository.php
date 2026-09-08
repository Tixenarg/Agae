<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AccesoRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function obtenerParaPdf(
        int $accesoId
    ): ?array {
        $sql = '
            SELECT
                a.id,
                a.codigo,
                a.numero_en_orden,
                a.nombre,
                a.apellido,
                a.sin_nombre,
                a.qr_token,
                a.estado,
                a.ruta_pdf,
                a.pdf_generado_en,

                o.id AS orden_id,
                o.codigo AS orden_codigo,
                o.estado AS orden_estado,
                o.moneda,
                o.total,

                c.nombre AS comprador_nombre,
                c.apellido AS comprador_apellido,
                c.email AS comprador_email,

                e.id AS evento_id,
                e.nombre AS evento_nombre,
                e.slug AS evento_slug,
                e.fecha_inicio,
                e.fecha_fin,
                e.lugar,
                e.direccion,
                e.codigo_prefijo,

                ta.id AS tipo_acceso_id,
                ta.nombre AS tipo_acceso_nombre,
                ta.codigo AS tipo_acceso_codigo,

                pp.id AS plantilla_pdf_id,
                pp.nombre AS plantilla_nombre,
                pp.codigo AS plantilla_codigo,
                pp.titulo AS plantilla_titulo,
                pp.mensaje AS plantilla_mensaje,
                pp.archivo_plantilla

            FROM accesos a

            INNER JOIN ordenes o
                ON o.id = a.orden_id

            LEFT JOIN compradores c
                ON c.id = o.comprador_id

            INNER JOIN eventos e
                ON e.id = o.evento_id

            INNER JOIN tipos_acceso ta
                ON ta.id = a.tipo_acceso_id

            LEFT JOIN plantillas_pdf pp
                ON pp.id = ta.plantilla_pdf_id

            WHERE a.id = :id

            LIMIT 1
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'id' => $accesoId,
        ]);

        $acceso =
            $statement->fetch();

        return $acceso ?: null;
    }

    public function marcarPdfGenerado(
        int $accesoId,
        string $rutaPdf
    ): void {
        $sql = '
            UPDATE accesos
            SET
                ruta_pdf = :ruta_pdf,
                pdf_generado_en =
                    CURRENT_TIMESTAMP

            WHERE id = :id
            LIMIT 1
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'id' =>
                $accesoId,

            'ruta_pdf' =>
                $rutaPdf,
        ]);
    }

    public function obtenerPorOrden(
    int $ordenId
    ): array {
        $sql = '
            SELECT
                a.id,
                a.orden_id,
                a.codigo,
                a.nombre,
                a.apellido,
                a.estado,
                a.email_enviado_en,
                a.utilizado_en,
                a.ruta_pdf,
                a.pdf_generado_en,

                ta.nombre AS tipo_acceso_nombre

            FROM accesos a

            INNER JOIN tipos_acceso ta
                ON ta.id = a.tipo_acceso_id

            WHERE a.orden_id = :orden_id

            ORDER BY a.numero_en_orden ASC
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'orden_id' =>
                $ordenId,
        ]);

        return $statement->fetchAll();
    }

    public function obtenerPorId(
    int $accesoId
): ?array {

    $sql = '
        SELECT
            a.id,
            a.orden_id,
            a.tipo_acceso_id,
            a.codigo,
            a.numero_en_orden,
            a.nombre,
            a.apellido,
            a.email_individual,
            a.dni_individual,
            a.estado,
            a.ruta_pdf,
            a.pdf_generado_en,
            a.email_enviado_en,
            a.utilizado_en,
            a.anulado_en,
            a.motivo_anulacion,
            a.creado_en,

            ta.nombre AS tipo_acceso_nombre,
            ta.codigo AS tipo_acceso_codigo,

            o.codigo AS orden_codigo,
            o.estado AS orden_estado,
            o.evento_id,

            e.nombre AS evento_nombre

        FROM accesos a

        INNER JOIN tipos_acceso ta
            ON ta.id = a.tipo_acceso_id

        INNER JOIN ordenes o
            ON o.id = a.orden_id

        INNER JOIN eventos e
            ON e.id = o.evento_id

        WHERE a.id = :acceso_id

        LIMIT 1
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->execute([
        'acceso_id' => $accesoId,
    ]);

    $acceso =
        $statement->fetch();

    return $acceso ?: null;
}

public function marcarEmailEnviadoPorOrden(
    int $ordenId
): void {
    $sql = '
        UPDATE accesos
        SET email_enviado_en = CURRENT_TIMESTAMP
        WHERE orden_id = :orden_id
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->execute([
        'orden_id' => $ordenId,
    ]);
}
}