<?php

declare(strict_types=1);

use App\Repositories\PagoRepository;
use App\Services\MercadoPagoService;
use App\Services\PagoService;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

header(
    'Content-Type: application/json; charset=utf-8'
);

header('Cache-Control: no-store');

function responderJsonPago(
    int $estadoHttp,
    array $contenido
): never {
    http_response_code($estadoHttp);

    echo json_encode(
        $contenido,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
    );

    exit;
}

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    !== 'POST'
) {
    header('Allow: POST');

    responderJsonPago(405, [
        'ok' => false,
        'mensaje' =>
            'Método no permitido.',
    ]);
}

try {
    $contenido =
        file_get_contents('php://input');

    $datos =
        json_decode(
            $contenido ?: '',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

    if (!is_array($datos)) {
        throw new InvalidArgumentException(
            'Los datos enviados no son válidos.'
        );
    }

    /** @var PDO $pdo */
    $pdo =
        require dirname(__DIR__, 2)
            . '/config/database.php';

    $pagoRepository =
        new PagoRepository($pdo);

    $mercadoPagoService =
        new MercadoPagoService();

    $pagoService =
        new PagoService(
            $pagoRepository,
            $mercadoPagoService
        );

    $pago =
        $pagoService->prepararCheckout(
            (string) (
                $datos['codigo_orden']
                ?? ''
            ),
            (string) (
                $datos['checkout_token']
                ?? ''
            )
        );

    responderJsonPago(201, [
        'ok' =>
            true,

        'pago' =>
            $pago,
    ]);
} catch (JsonException) {
    responderJsonPago(400, [
        'ok' => false,
        'mensaje' =>
            'El JSON enviado no es válido.',
    ]);
} catch (
    InvalidArgumentException
    | DomainException $exception
) {
    responderJsonPago(422, [
        'ok' => false,
        'mensaje' =>
            $exception->getMessage(),
    ]);
} catch (Throwable $exception) {
    error_log(
        sprintf(
            '[crear-pago] %s en %s:%d',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        )
    );

    responderJsonPago(500, [
        'ok' => false,
        'mensaje' =>
            'No se pudo preparar el pago.',
    ]);
}