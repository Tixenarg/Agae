<?php

declare(strict_types=1);

use App\Repositories\CupoRepository;
use App\Repositories\OrdenRepository;
use App\Services\OrdenService;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responderJson(
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

    responderJson(405, [
        'ok' => false,
        'mensaje' =>
            'Método no permitido.',
    ]);
}

$contenidoRecibido =
    file_get_contents('php://input');

if (
    $contenidoRecibido === false
    || trim($contenidoRecibido) === ''
) {
    responderJson(400, [
        'ok' => false,
        'mensaje' =>
            'No se recibieron datos.',
    ]);
}

try {
    $datos =
        json_decode(
            $contenidoRecibido,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

    if (!is_array($datos)) {
        responderJson(400, [
            'ok' => false,
            'mensaje' =>
                'El contenido enviado no es válido.',
        ]);
    }

    /** @var PDO $pdo */
    $pdo = require dirname(__DIR__, 2)
        . '/config/database.php';

    $ordenRepository =
        new OrdenRepository($pdo);

    $cupoRepository =
        new CupoRepository($pdo);

    $ordenService =
        new OrdenService(
            $pdo,
            $ordenRepository,
            $cupoRepository
        );

    $orden =
        $ordenService->crearOrdenWeb(
            $datos
        );

    responderJson(201, [
        'ok' => true,
        'mensaje' =>
            'La orden fue creada correctamente.',

        'orden' =>
            $orden,
    ]);
} catch (JsonException) {
    responderJson(400, [
        'ok' => false,
        'mensaje' =>
            'El JSON enviado no es válido.',
    ]);
} catch (InvalidArgumentException $exception) {
    responderJson(422, [
        'ok' => false,
        'mensaje' =>
            $exception->getMessage(),
    ]);
} catch (DomainException $exception) {
    responderJson(409, [
        'ok' => false,
        'mensaje' =>
            $exception->getMessage(),
    ]);
} catch (Throwable $exception) {
    error_log(
        sprintf(
            '[crear-orden] %s en %s:%d',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        )
    );

    responderJson(500, [
        'ok' => false,
        'mensaje' =>
            'No se pudo crear la orden. Intentá nuevamente.',
    ]);
}