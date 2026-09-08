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
}