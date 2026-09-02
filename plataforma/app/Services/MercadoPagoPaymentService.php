<?php

declare(strict_types=1);

namespace App\Services;

use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use RuntimeException;

final class MercadoPagoPaymentService
{
    private PaymentClient $client;

    public function __construct()
    {
        $config =
            require __DIR__
                . '/../../config/app.php';

        $accessToken =
            trim(
                (string) (
                    $config['mercadopago']
                        ['access_token']
                    ?? ''
                )
            );

        if ($accessToken === '') {
            throw new RuntimeException(
                'MP_ACCESS_TOKEN no configurado.'
            );
        }

        MercadoPagoConfig::setAccessToken(
            $accessToken
        );

        $this->client =
            new PaymentClient();
    }

    public function obtenerPago(
        int $paymentId
    ): array {
        $payment =
            $this->client->get(
                $paymentId
            );

        return json_decode(
            json_encode(
                $payment,
                JSON_THROW_ON_ERROR
            ),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}