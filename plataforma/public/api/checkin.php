<?php

declare(strict_types=1);

use App\Repositories\CheckinRepository;
use App\Services\CheckinService;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

header(
    'Content-Type: application/json; charset=utf-8'
);

header('Cache-Control: no-store');

function responderCheckin(
    int $estadoHttp,
    array $contenido
): never {
    http_response_code(
        $estadoHttp
    );

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

    responderCheckin(405, [
        'ok' => false,
        'resultado' => 'metodo_invalido',
        'mensaje' => 'Método no permitido.',
    ]);
}

try {
    $contenido =
        file_get_contents(
            'php://input'
        );

    if (
        $contenido === false
        || trim($contenido) === ''
    ) {
        responderCheckin(400, [
            'ok' => false,
            'resultado' => 'datos_invalidos',
            'mensaje' =>
                'No se recibió ningún código.',
        ]);
    }

    $datos =
        json_decode(
            $contenido,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

    if (!is_array($datos)) {
        responderCheckin(400, [
            'ok' => false,
            'resultado' => 'datos_invalidos',
            'mensaje' =>
                'Los datos enviados no son válidos.',
        ]);
    }

    $codigo =
        (string) (
            $datos['codigo']
            ?? ''
        );

    /** @var PDO $pdo */
    $pdo =
        require dirname(__DIR__, 2)
            . '/config/database.php';

    $repository =
        new CheckinRepository(
            $pdo
        );

    $service =
        new CheckinService(
            $pdo,
            $repository
        );

    $resultado =
        $service->registrar(
            $codigo,
            null,
            substr(
                (string) (
                    $_SERVER[
                        'HTTP_USER_AGENT'
                    ] ?? ''
                ),
                0,
                255
            ),
            $_SERVER[
                'REMOTE_ADDR'
            ] ?? null
        );

    /*
     * Para el scanner una respuesta inválida
     * también es un procesamiento correcto.
     *
     * La UI decide verde/rojo según "ok".
     */
    responderCheckin(
        200,
        $resultado
    );

} catch (JsonException) {
    responderCheckin(400, [
        'ok' => false,
        'resultado' => 'json_invalido',
        'mensaje' =>
            'El JSON enviado no es válido.',
    ]);

} catch (Throwable $exception) {
    error_log(
        sprintf(
            '[checkin] %s en %s:%d',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        )
    );

    responderCheckin(500, [
        'ok' => false,
        'resultado' => 'error',
        'mensaje' =>
            'NO SE PUDO VALIDAR EL ACCESO',
    ]);
}