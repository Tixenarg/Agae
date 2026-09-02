<?php

declare(strict_types=1);

use App\Repositories\CheckinRepository;
use App\Services\CheckinService;

require_once dirname(__DIR__)
    . '/vendor/autoload.php';

/** @var PDO $pdo */
$pdo =
    require dirname(__DIR__)
        . '/config/database.php';

$repository =
    new CheckinRepository($pdo);

$service =
    new CheckinService(
        $pdo,
        $repository
    );

try {

    $resultado =
        $service->registrar(
            '6N26-WRRG-G3SB-A1'
        );

    header(
        'Content-Type: application/json; charset=utf-8'
    );

    echo json_encode(
        $resultado,
        JSON_PRETTY_PRINT
        | JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
    );

} catch (Throwable $exception) {

    http_response_code(500);

    echo $exception->getMessage();

}