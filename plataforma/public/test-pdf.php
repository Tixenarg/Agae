<?php

declare(strict_types=1);

use App\Repositories\AccesoRepository;
use App\Services\AccesoPdfService;
use App\Services\QrCodeService;

require_once dirname(__DIR__)
    . '/vendor/autoload.php';

try {
    /** @var PDO $pdo */
    $pdo =
        require dirname(__DIR__)
            . '/config/database.php';

    $repository =
        new AccesoRepository($pdo);

    $qrCodeService =
        new QrCodeService();

    $service =
        new AccesoPdfService(
            $repository,
            $qrCodeService
        );

    $ruta =
        $service->generar(23);

    header(
        'Content-Type: text/plain; charset=utf-8'
    );

    echo "PDF generado correctamente.\n\n";
    echo $ruta;

} catch (Throwable $exception) {
    http_response_code(500);

    header(
        'Content-Type: text/plain; charset=utf-8'
    );

    echo "ERROR\n\n";
    echo $exception->getMessage();
    echo "\n\n";
    echo $exception->getFile();
    echo ':';
    echo $exception->getLine();
}