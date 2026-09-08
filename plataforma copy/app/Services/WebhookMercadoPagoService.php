<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

final class WebhookMercadoPagoService
{
    public function procesar(
        array $query,
        string $body
    ): void {

        $zonaHoraria =
            new DateTimeZone(
                'America/Argentina/Buenos_Aires'
            );

        $ahora =
            new DateTimeImmutable(
                'now',
                $zonaHoraria
            );

        $json =
            json_decode(
                $body,
                true
            );

        $paymentId =
            (int) (
                $json['data']['id']
                ?? 0
            );

        $payment = null;

        if ($paymentId > 0) {

            $mercadoPago =
                new MercadoPagoPaymentService();

            $payment =
                $mercadoPago->obtenerPago(
                    $paymentId
                );
        }

        $registro = [

            'recibido_en' =>
                $ahora->format(DATE_ATOM),

            'query' =>
                $query,

            'headers' => [

                'x-signature' =>
                    $_SERVER['HTTP_X_SIGNATURE']
                    ?? null,

                'x-request-id' =>
                    $_SERVER['HTTP_X_REQUEST_ID']
                    ?? null,

                'content-type' =>
                    $_SERVER['CONTENT_TYPE']
                    ?? null,
            ],

            'body_raw' =>
                $body,

            'body_json' =>
                $json,

            'payment_id' =>
                $paymentId,

            'payment' =>
                $payment,
        ];

        $directorioLogs =
            dirname(__DIR__, 2)
            . '/storage/logs';

        if (
            !is_dir($directorioLogs)
            && !mkdir(
                $directorioLogs,
                0775,
                true
            )
            && !is_dir($directorioLogs)
        ) {
            throw new RuntimeException(
                'No se pudo crear la carpeta de logs.'
            );
        }

        $archivo =
            $directorioLogs
            . '/webhook-mercadopago.log';

        $contenido =
            json_encode(
                $registro,
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            )
            . PHP_EOL
            . str_repeat('-', 80)
            . PHP_EOL;

        $resultado =
            file_put_contents(
                $archivo,
                $contenido,
                FILE_APPEND | LOCK_EX
            );

        if ($resultado === false) {
            throw new RuntimeException(
                'No se pudo escribir el log del webhook.'
            );
        }
    }
}