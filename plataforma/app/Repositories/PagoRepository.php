<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class PagoRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function obtenerOrdenParaCheckout(
        string $codigo,
        string $checkoutToken
    ): ?array {
        $sql = '
            SELECT
                o.id,
                o.codigo,
                o.estado,
                o.cantidad_accesos,
                o.precio_unitario,
                o.total,
                o.moneda,
                o.reserva_hasta,

                e.nombre AS evento_nombre,

                c.nombre AS comprador_nombre,
                c.apellido AS comprador_apellido,
                c.email AS comprador_email

            FROM ordenes o

            INNER JOIN eventos e
                ON e.id = o.evento_id

            INNER JOIN compradores c
                ON c.id = o.comprador_id

            WHERE o.codigo = :codigo
              AND o.checkout_token = :checkout_token

            LIMIT 1
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'codigo' =>
                $codigo,

            'checkout_token' =>
                $checkoutToken,
        ]);

        $orden = $statement->fetch();

        return $orden ?: null;
    }

    public function obtenerPagoConPreferencia(
        int $ordenId
    ): ?array {
        $sql = '
            SELECT
                id,
                proveedor_preferencia_id,
                datos_respuesta
            FROM pagos
            WHERE orden_id = :orden_id
              AND proveedor = :proveedor
              AND proveedor_preferencia_id IS NOT NULL
            ORDER BY id DESC
            LIMIT 1
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'orden_id' =>
                $ordenId,

            'proveedor' =>
                'mercadopago',
        ]);

        $pago = $statement->fetch();

        return $pago ?: null;
    }

    public function crearPagoPendiente(
        array $pago
    ): int {
        $sql = '
            INSERT INTO pagos (
                orden_id,
                proveedor,
                proveedor_preferencia_id,
                estado,
                importe,
                moneda,
                datos_respuesta
            )
            VALUES (
                :orden_id,
                :proveedor,
                :proveedor_preferencia_id,
                :estado,
                :importe,
                :moneda,
                :datos_respuesta
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'orden_id' =>
                $pago['orden_id'],

            'proveedor' =>
                'mercadopago',

            'proveedor_preferencia_id' =>
                $pago['proveedor_preferencia_id'],

            'estado' =>
                'pending',

            'importe' =>
                $pago['importe'],

            'moneda' =>
                $pago['moneda'],

            'datos_respuesta' =>
                json_encode(
                    $pago['datos_respuesta'],
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_THROW_ON_ERROR
                ),
        ]);

        return (int) $this->pdo->lastInsertId();
    }
    

public function obtenerPorProveedorPagoIdBloqueado(
    string $proveedorPagoId
): ?array {
    $sql = '
        SELECT
            id,
            orden_id,
            proveedor_pago_id,
            proveedor_preferencia_id,
            estado,
            importe,
            moneda

        FROM pagos

        WHERE proveedor = :proveedor
          AND proveedor_pago_id = :proveedor_pago_id

        LIMIT 1
        FOR UPDATE
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->execute([
        'proveedor' =>
            'mercadopago',

        'proveedor_pago_id' =>
            $proveedorPagoId,
    ]);

    $pago =
        $statement->fetch();

    return $pago ?: null;
}

public function obtenerPagoBaseDeOrdenBloqueado(
    int $ordenId
): ?array {
    $sql = '
        SELECT
            id,
            orden_id,
            proveedor_pago_id,
            proveedor_preferencia_id,
            estado,
            importe,
            moneda

        FROM pagos

        WHERE orden_id = :orden_id
          AND proveedor = :proveedor
          AND proveedor_pago_id IS NULL

        ORDER BY id DESC

        LIMIT 1
        FOR UPDATE
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->execute([
        'orden_id' =>
            $ordenId,

        'proveedor' =>
            'mercadopago',
    ]);

    $pago =
        $statement->fetch();

    return $pago ?: null;
}
public function actualizarDesdeMercadoPago(
    int $pagoId,
    array $payment
): void {
    $sql = '
        UPDATE pagos
        SET
            proveedor_pago_id =
                :proveedor_pago_id,

            estado =
                :estado,

            estado_detalle =
                :estado_detalle,

            metodo_pago =
                :metodo_pago,

            tipo_pago =
                :tipo_pago,

            cuotas =
                :cuotas,

            aprobado_en =
                :aprobado_en,

            rechazado_en =
                :rechazado_en,

            datos_respuesta =
                :datos_respuesta

        WHERE id = :id
        LIMIT 1
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->execute([
        'id' =>
            $pagoId,

        'proveedor_pago_id' =>
            (string) $payment['id'],

        'estado' =>
            (string) (
                $payment['status']
                ?? ''
            ),

        'estado_detalle' =>
            $payment['status_detail']
                ?? null,

        'metodo_pago' =>
            $payment['payment_method_id']
                ?? null,

        'tipo_pago' =>
            $payment['payment_type_id']
                ?? null,

        'cuotas' =>
            isset($payment['installments'])
                ? (int) $payment['installments']
                : null,

        'aprobado_en' =>
            $this->normalizarFecha(
                $payment['date_approved']
                    ?? null
            ),

        'rechazado_en' =>
            ($payment['status'] ?? null)
                === 'rejected'
                    ? (
                        new \DateTimeImmutable(
                            'now',
                            new \DateTimeZone(
                                'America/Argentina/Buenos_Aires'
                            )
                        )
                    )->format('Y-m-d H:i:s')
                    : null,

        'datos_respuesta' =>
            json_encode(
                $payment,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            ),
    ]);
}

private function normalizarFecha(
    mixed $fecha
): ?string {
    if (
        !is_string($fecha)
        || trim($fecha) === ''
    ) {
        return null;
    }

    try {
        return (
            new \DateTimeImmutable($fecha)
        )->setTimezone(
            new \DateTimeZone(
                'America/Argentina/Buenos_Aires'
            )
        )->format('Y-m-d H:i:s');
    } catch (\Throwable) {
        return null;
    }
}
public function crearPagoDesdeMercadoPago(
    int $ordenId,
    array $payment
): int {
    $sql = '
        INSERT INTO pagos (
            orden_id,
            proveedor,
            proveedor_pago_id,
            estado,
            estado_detalle,
            importe,
            moneda,
            metodo_pago,
            tipo_pago,
            cuotas,
            aprobado_en,
            rechazado_en,
            datos_respuesta
        )
        VALUES (
            :orden_id,
            :proveedor,
            :proveedor_pago_id,
            :estado,
            :estado_detalle,
            :importe,
            :moneda,
            :metodo_pago,
            :tipo_pago,
            :cuotas,
            :aprobado_en,
            :rechazado_en,
            :datos_respuesta
        )
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->execute([
        'orden_id' =>
            $ordenId,

        'proveedor' =>
            'mercadopago',

        'proveedor_pago_id' =>
            (string) $payment['id'],

        'estado' =>
            (string) (
                $payment['status']
                ?? ''
            ),

        'estado_detalle' =>
            $payment['status_detail']
                ?? null,

        'importe' =>
            (string) (
                $payment['transaction_amount']
                ?? '0'
            ),

        'moneda' =>
            (string) (
                $payment['currency_id']
                ?? 'ARS'
            ),

        'metodo_pago' =>
            $payment['payment_method_id']
                ?? null,

        'tipo_pago' =>
            $payment['payment_type_id']
                ?? null,

        'cuotas' =>
            isset($payment['installments'])
                ? (int) $payment['installments']
                : null,

        'aprobado_en' =>
            $this->normalizarFecha(
                $payment['date_approved']
                    ?? null
            ),

        'rechazado_en' =>
            ($payment['status'] ?? null)
                === 'rejected'
                    ? (
                        new \DateTimeImmutable(
                            'now',
                            new \DateTimeZone(
                                'America/Argentina/Buenos_Aires'
                            )
                        )
                    )->format('Y-m-d H:i:s')
                    : null,

        'datos_respuesta' =>
            json_encode(
                $payment,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            ),
    ]);

    return (int) $this->pdo->lastInsertId();
}
}

