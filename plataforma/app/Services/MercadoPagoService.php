<?php

declare(strict_types=1);

namespace App\Services;

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use RuntimeException;

final class MercadoPagoService
{
    private PreferenceClient $client;

    public function __construct()
    {
        $config =
            require dirname(__DIR__, 2)
                . '/config/app.php';

        $mercadoPagoConfig =
            $config['mercadopago'] ?? [];

        $accessToken =
            trim(
                (string) (
                    $mercadoPagoConfig['access_token']
                    ?? ''
                )
            );

        if ($accessToken === '') {
            throw new RuntimeException(
                'No está configurado MP_ACCESS_TOKEN en el archivo .env.'
            );
        }

        MercadoPagoConfig::setAccessToken(
            $accessToken
        );

        $this->client =
            new PreferenceClient();
    }

    public function crearPreferencia(
        array $orden
    ): array {
        $preference =
            $this->client->create([
                'items' => [
                    [
                        'id' =>
                            (string) $orden['codigo'],

                        'title' =>
                            'Acceso · '
                            . $orden['evento_nombre'],

                        'quantity' =>
                            (int) $orden[
                                'cantidad_accesos'
                            ],

                        'currency_id' =>
                            (string) $orden['moneda'],

                        'unit_price' =>
                            (float) $orden[
                                'precio_unitario'
                            ],
                    ],
                ],

                'payer' => [
                    'name' =>
                        (string) $orden[
                            'comprador_nombre'
                        ],

                    'surname' =>
                        (string) $orden[
                            'comprador_apellido'
                        ],

                    'email' =>
                        (string) $orden[
                            'comprador_email'
                        ],
                ],

                'external_reference' =>
                    (string) $orden['codigo'],

                'expires' =>
                    true,

                'expiration_date_from' =>
                    $this->fechaMercadoPago(
                        new \DateTimeImmutable(
                            'now',
                            new \DateTimeZone(
                                'America/Argentina/Buenos_Aires'
                            )
                        )
                    ),

                'expiration_date_to' =>
                    $this->fechaMercadoPago(
                        new \DateTimeImmutable(
                            (string) $orden['reserva_hasta'],
                            new \DateTimeZone(
                                'America/Argentina/Buenos_Aires'
                            )
                        )
                    ),
            ]);

        $initPoint =
            is_string($preference->init_point)
                ? $preference->init_point
                : '';

        $sandboxInitPoint =
            is_string(
                $preference->sandbox_init_point
            )
                ? $preference->sandbox_init_point
                : '';

        $checkoutUrl = $initPoint;

        if ($checkoutUrl === '') {
            throw new RuntimeException(
                'Mercado Pago no devolvió una URL de checkout.'
            );
        }


        return [
            'preference_id' =>
                (string) $preference->id,

            'checkout_url' =>
                $checkoutUrl,

            'init_point' =>
                $initPoint,

            'sandbox_init_point' =>
                $sandboxInitPoint,
        ];
    }

    private function fechaMercadoPago(
    \DateTimeImmutable $fecha
    ): string {
        return $fecha->format(
            'Y-m-d\TH:i:s.000P'
        );
    }
}