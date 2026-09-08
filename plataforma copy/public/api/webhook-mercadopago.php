<?php

declare(strict_types=1);

use App\Services\WebhookMercadoPagoService;
use MercadoPago\Exceptions\InvalidWebhookSignatureException;
use MercadoPago\Webhook\WebhookSignatureValidator;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

header(
    'Content-Type: application/json; charset=utf-8'
);

header('Cache-Control: no-store');

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    !== 'POST'
) {
    header('Allow: POST');
    http_response_code(405);

    echo json_encode([
        'ok' => false,
        'mensaje' => 'Método no permitido.',
    ]);

    exit;
}

try {
    $config =
        require dirname(__DIR__, 2)
            . '/config/app.php';

    $webhookSecret =
        trim((string) (
            $config['mercadopago']
                ['webhook_secret']
            ?? ''
        ));

    if ($webhookSecret === '') {
        throw new RuntimeException(
            'No está configurado MP_WEBHOOK_SECRET.'
        );
    }

    $xSignature =
        (string) (
            $_SERVER['HTTP_X_SIGNATURE']
            ?? ''
        );

    $xRequestId =
        (string) (
            $_SERVER['HTTP_X_REQUEST_ID']
            ?? ''
        );

    $dataId =
        (string) (
            $_GET['data_id']
            ?? $_GET['data.id']
            ?? ''
        );

    if (
        $xSignature === ''
        || $xRequestId === ''
        || $dataId === ''
    ) {
        throw new InvalidWebhookSignatureException(
            'Faltan datos para validar la firma.'
        );
    }

    WebhookSignatureValidator::validate(
        $xSignature,
        $xRequestId,
        $dataId,
        $webhookSecret
    );

    $contenido =
        file_get_contents('php://input');

    $body =
        $contenido !== false
            ? $contenido
            : '';

    $service =
        new WebhookMercadoPagoService();

    $service->procesar(
        $_GET,
        $body
    );

    http_response_code(200);

    echo json_encode([
        'ok' => true,
    ]);
} catch (
    InvalidWebhookSignatureException
    $exception
) {
    error_log(
        '[webhook-mp] Firma inválida: '
        . $exception->getMessage()
    );

    http_response_code(401);

    echo json_encode([
        'ok' => false,
        'mensaje' =>
            'La firma de la notificación no es válida.',
    ]);
} catch (Throwable $exception) {
    error_log(
        sprintf(
            '[webhook-mp] %s en %s:%d',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        )
    );

    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'mensaje' =>
            'No se pudo procesar la notificación.',
    ]);
}