<?php

declare(strict_types=1);

use App\Repositories\NotificacionPagoRepository;
use App\Repositories\OrdenRepository;
use App\Repositories\PagoRepository;
use App\Services\MercadoPagoPaymentService;
use App\Services\ProcesadorPagoMercadoPagoService;
use App\Services\WebhookMercadoPagoService;
use MercadoPago\Exceptions\InvalidWebhookSignatureException;
use MercadoPago\Webhook\WebhookSignatureValidator;
use App\Repositories\EmailRepository;
use App\Services\EmailQueueService;

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
        'mensaje' =>
            'Método no permitido.',
    ]);

    exit;
}

try {
    /*
     * --------------------------------------------------
     * CONFIGURACIÓN
     * --------------------------------------------------
     */

    $config =
        require dirname(__DIR__, 2)
            . '/config/app.php';

    $webhookSecret =
        trim(
            (string) (
                $config['mercadopago']
                    ['webhook_secret']
                ?? ''
            )
        );

    if ($webhookSecret === '') {
        throw new RuntimeException(
            'No está configurado MP_WEBHOOK_SECRET.'
        );
    }

    /*
     * --------------------------------------------------
     * VALIDACIÓN DE FIRMA
     * --------------------------------------------------
     */

    $xSignature =
        trim(
            (string) (
                $_SERVER['HTTP_X_SIGNATURE']
                ?? ''
            )
        );

    $xRequestId =
        trim(
            (string) (
                $_SERVER['HTTP_X_REQUEST_ID']
                ?? ''
            )
        );

    $dataId =
        trim(
            (string) (
                $_GET['data_id']
                ?? $_GET['data.id']
                ?? ''
            )
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

    /*
     * --------------------------------------------------
     * BODY
     * --------------------------------------------------
     */

    $contenido =
        file_get_contents(
            'php://input'
        );

    if ($contenido === false) {
        throw new RuntimeException(
            'No se pudo leer el cuerpo de la notificación.'
        );
    }

    /*
     * --------------------------------------------------
     * DEPENDENCIAS
     * --------------------------------------------------
     */

    /** @var PDO $pdo */
    $pdo =
        require dirname(__DIR__, 2)
            . '/config/database.php';

    $pagoRepository =
        new PagoRepository($pdo);

    $ordenRepository =
        new OrdenRepository($pdo);

    $notificacionRepository =
        new NotificacionPagoRepository(
            $pdo
        );

    $emailRepository =
    new EmailRepository($pdo);

    $emailQueueService =
    new EmailQueueService(
        $emailRepository,
        $ordenRepository
    );

    $mercadoPagoPaymentService =
        new MercadoPagoPaymentService();

    $procesador =
        new ProcesadorPagoMercadoPagoService(
            $pdo,
            $pagoRepository,
            $ordenRepository,
            $notificacionRepository,
            $mercadoPagoPaymentService,
            $emailQueueService
        );

    $webhookService =
        new WebhookMercadoPagoService(
            $procesador
        );

    /*
     * --------------------------------------------------
     * PROCESAMIENTO
     * --------------------------------------------------
     */

    $webhookService->procesar(
        $_GET,
        $contenido
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

    /*
     * IMPORTANTE:
     *
     * Si no pudimos consultar o procesar un pago
     * auténtico, NO respondemos 200.
     *
     * Así Mercado Pago puede reintentar
     * posteriormente la notificación.
     */
    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'mensaje' =>
            'No se pudo procesar la notificación.',
    ]);
}