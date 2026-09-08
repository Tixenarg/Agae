<?php

declare(strict_types=1);

use App\Repositories\EmailRepository;
use App\Support\AdminGuard;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

AdminGuard::requiereRol([
    'administrador_general',
    'administracion',
]);

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    !== 'POST'
) {
    http_response_code(405);
    exit('Método no permitido.');
}

/** @var PDO $pdo */
$pdo =
    require dirname(__DIR__, 2)
        . '/config/database.php';

$emailId =
    (int) (
        $_POST['email_id']
        ?? 0
    );

if ($emailId < 1) {
    http_response_code(400);
    exit('Email inválido.');
}

$repository =
    new EmailRepository($pdo);

try {
    $nuevoEmailId =
        $repository
            ->reencolarDesdeId(
                $emailId
            );

    header(
        'Location: /public/admin/email-detalle.php?id='
        . $nuevoEmailId
        . '&reenviado=1'
    );

    exit;

} catch (Throwable $exception) {
    error_log(
        '[email-reenviar] '
        . $exception->getMessage()
    );

    http_response_code(500);

    exit(
        'No se pudo reenviar el email.'
    );
}