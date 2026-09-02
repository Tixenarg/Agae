<?php

declare(strict_types=1);

use Dotenv\Dotenv;

$rootPath = dirname(__DIR__);

require_once $rootPath . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable($rootPath);
$dotenv->safeLoad();

return [
    'app' => [
        'environment' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => filter_var(
            $_ENV['APP_DEBUG'] ?? false,
            FILTER_VALIDATE_BOOL
        ),
        'url' => rtrim(
            $_ENV['APP_URL'] ?? '',
            '/'
        ),
    ],

    'database' => [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'name' => $_ENV['DB_NAME'] ?? '',
        'user' => $_ENV['DB_USER'] ?? '',
        'password' => $_ENV['DB_PASS'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    ],

    'mercadopago' => [
        'access_token' =>
            $_ENV['MP_ACCESS_TOKEN'] ?? '',

        'public_key' =>
            $_ENV['MP_PUBLIC_KEY'] ?? '',

        'environment' =>
            $_ENV['MP_ENVIRONMENT'] ?? 'test',

        'webhook_secret' =>
            $_ENV['MP_WEBHOOK_SECRET'] ?? '',
    ],

    'mail' => [
    'host' =>
        $_ENV['MAIL_HOST'] ?? '',

    'port' =>
        (int) ($_ENV['MAIL_PORT'] ?? 587),

    'username' =>
        $_ENV['MAIL_USERNAME'] ?? '',

    'password' =>
        $_ENV['MAIL_PASSWORD'] ?? '',

    'encryption' =>
        $_ENV['MAIL_ENCRYPTION'] ?? 'tls',

    'from_address' =>
        $_ENV['MAIL_FROM_ADDRESS'] ?? '',

    'from_name' =>
        $_ENV['MAIL_FROM_NAME'] ?? 'Evento 6N26',
    ],
];