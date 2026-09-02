<?php

declare(strict_types=1);

namespace App\Services;

use DomainException;
use JsonException;

final class WebhookMercadoPagoService
{
    public function __construct(
        private readonly ProcesadorPagoMercadoPagoService $procesador
    ) {
    }

    public function procesar(
        array $query,
        string $body
    ): void {
        try {
            $payload =
                json_decode(
                    $body,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
        } catch (JsonException $exception) {
            throw new DomainException(
                'El cuerpo del webhook no contiene JSON válido.',
                0,
                $exception
            );
        }

        if (!is_array($payload)) {
            throw new DomainException(
                'El payload del webhook no es válido.'
            );
        }

        $tipo =
            strtolower(
                trim(
                    (string) (
                        $payload['type']
                        ?? $query['type']
                        ?? ''
                    )
                )
            );

        /*
         * Nuestro endpoint está configurado para pagos.
         * Si Mercado Pago enviara accidentalmente otro
         * recurso, no debe convertirse en una compra.
         */
        if (
            $tipo !== ''
            && $tipo !== 'payment'
        ) {
            return;
        }

        $paymentId =
            $this->obtenerPaymentId(
                $query,
                $payload
            );

        if ($paymentId < 1) {
            throw new DomainException(
                'La notificación no contiene un payment ID válido.'
            );
        }

        $this->procesador->procesar(
            $paymentId,
            $payload
        );
    }

    private function obtenerPaymentId(
        array $query,
        array $payload
    ): int {
        $valor =
            $payload['data']['id']
            ?? $query['data_id']
            ?? $query['data.id']
            ?? null;

        if (
            is_int($valor)
            && $valor > 0
        ) {
            return $valor;
        }

        if (
            is_string($valor)
            && ctype_digit($valor)
        ) {
            return (int) $valor;
        }

        return 0;
    }
}