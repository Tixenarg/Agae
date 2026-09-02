<?php

declare(strict_types=1);

$config = require __DIR__ . '/app.php';

$db = $config['database'];

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $db['host'],
    $db['port'],
    $db['name'],
    $db['charset']
);

try {
    return new \PDO(
        $dsn,
        $db['user'],
        $db['password'],
        [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (\PDOException $exception) {
    throw new \RuntimeException(
        'No se pudo establecer la conexión con la base de datos.',
        0,
        $exception
    );
}