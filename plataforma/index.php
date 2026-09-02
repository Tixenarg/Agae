<?php

declare(strict_types=1);

use App\Repositories\CupoRepository;
use App\Repositories\EventoRepository;
use App\Services\EventoService;
use App\Services\CupoService;

require_once __DIR__ . '/vendor/autoload.php';

try {
    /** @var PDO $pdo */
    $pdo = require __DIR__ . '/config/database.php';

    $eventoRepository = new EventoRepository($pdo);

    $eventoService = new EventoService(
        $eventoRepository
    );

    $datos = $eventoService->obtenerEventoPrincipal();

    $evento = $datos['evento'];
    $tipoAcceso = $datos['tipo_acceso'];

    $cupoRepository = new CupoRepository($pdo);

    $cupoService = new CupoService(
        $cupoRepository
    );

    $cupoDisponible = $cupoService->obtenerDisponibilidad(
        $evento
    );
} catch (Throwable $exception) {
    http_response_code(500);

    echo '<h1>Error de configuración</h1>';
    echo '<p>' . htmlspecialchars(
        $exception->getMessage(),
        ENT_QUOTES,
        'UTF-8'
    ) . '</p>';

    exit;
}

$fecha = new DateTimeImmutable($evento['fecha_inicio']);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Plataforma de eventos</title>

    <style>
        body {
            margin: 0;
            padding: 40px;
            font-family: Arial, sans-serif;
            background: #f4f5f8;
            color: #171717;
        }

        .card {
            max-width: 680px;
            margin: 0 auto;
            padding: 32px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
        }

        dl {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 12px;
        }

        dt {
            font-weight: 700;
        }

        dd {
            margin: 0;
        }

        .status {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            background: #e6f7eb;
            color: #176b31;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <main class="card">
        <span class="status">Conexión correcta</span>

        <h1>
            <?= htmlspecialchars(
                $evento['nombre'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </h1>

        <dl>
            <dt>Fecha</dt>
            <dd><?= $fecha->format('d/m/Y H:i') ?></dd>

            <dt>Lugar</dt>
            <dd>
                <?= htmlspecialchars(
                    $evento['lugar'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </dd>

            <dt>Cupo total</dt>
            <dd><?= (int) $evento['cupo_total'] ?></dd>

            <dt>Reserva</dt>
            <dd>
                <?= (int) $evento['minutos_reserva'] ?> minutos
            </dd>

            <dt>Acceso</dt>
            <dd>
                $
                <?= number_format(
                    (float) $tipoAcceso['precio'],
                    0,
                    ',',
                    '.'
                ) ?>
            </dd>
        </dl>
    </main>
</body>
</html>