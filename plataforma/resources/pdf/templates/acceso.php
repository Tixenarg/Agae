<?php

declare(strict_types=1);

/*
 * ---------------------------------------------------------
 * DATOS PRINCIPALES
 * ---------------------------------------------------------
 */

$nombreCompleto =
    trim(
        (string) ($acceso['nombre'] ?? '')
        . ' '
        . (string) ($acceso['apellido'] ?? '')
    );

if ($nombreCompleto === '') {
    $nombreCompleto = 'Sin asignar';
}

$fechaEvento =
    new DateTimeImmutable(
        (string) $acceso['fecha_inicio'],
        new DateTimeZone(
            'America/Argentina/Buenos_Aires'
        )
    );

$meses = [
    1 => 'enero',
    2 => 'febrero',
    3 => 'marzo',
    4 => 'abril',
    5 => 'mayo',
    6 => 'junio',
    7 => 'julio',
    8 => 'agosto',
    9 => 'septiembre',
    10 => 'octubre',
    11 => 'noviembre',
    12 => 'diciembre',
];

$fechaFormateada =
    $fechaEvento->format('j')
    . ' de '
    . $meses[(int) $fechaEvento->format('n')]
    . ' de '
    . $fechaEvento->format('Y');

$horaFormateada =
    $fechaEvento->format('H:i')
    . ' hs';

/*
 * ---------------------------------------------------------
 * IDENTIDAD DEL ACCESO
 * ---------------------------------------------------------
 */

$tipoCodigo =
    strtolower(
        trim(
            (string) (
                $acceso['tipo_acceso_codigo']
                ?? ''
            )
        )
    );

$etiquetas = [
    'general' =>
        'ACCESO GENERAL',

    'protocolo' =>
        'INVITACIÓN DE PROTOCOLO',

    'sponsor' =>
        'ACCESO SPONSOR',

    'staff' =>
        'STAFF',

    'prensa' =>
        'PRENSA',

    'cortesia' =>
        'CORTESÍA',

    'invitado' =>
        'INVITADO',

    'organizacion' =>
        'ORGANIZACIÓN',
];

$etiquetaAcceso =
    $etiquetas[$tipoCodigo]
    ?? strtoupper(
        (string) (
            $acceso['tipo_acceso_nombre']
            ?? 'ACCESO'
        )
    );

$marcaEvento =
    strtoupper(
        trim(
            (string) (
                $acceso['codigo_prefijo']
                ?? '6N26'
            )
        )
    );

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <style>

        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #111111;
            font-family: sans-serif;
        }

        .pagina {
            padding: 9mm 12mm 7mm 12mm;
        }

        .claim {
            text-align: center;
            font-size: 6pt;
            letter-spacing: 1.5pt;
            color: #888888;
            text-transform: uppercase;
        }

        .marca {
            margin-top: 4mm;
            text-align: center;
            font-size: 29pt;
            font-weight: bold;
            line-height: 1;
        }

        .tipo {
            margin-top: 2.8mm;
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 1.3pt;
            text-transform: uppercase;
        }

        .separador {
            margin: 5mm 0;
            border-top: 0.25mm solid #dedede;
        }

        .qr-contenedor {
            text-align: center;
        }

        .qr {
            width: 44mm;
            height: 44mm;
        }

        .codigo {
            margin-top: 2.5mm;
            text-align: center;
            font-size: 9.5pt;
            font-weight: bold;
            letter-spacing: 0.7pt;
            color: #444444;
        }

        .datos {
            margin-top: 0;
        }

        .grupo {
            margin-bottom: 4.5mm;
        }

        .label {
            margin-bottom: 1.2mm;
            font-size: 6.5pt;
            color: #888888;
            letter-spacing: 1pt;
            text-transform: uppercase;
        }

        .valor {
            font-size: 11.5pt;
            font-weight: bold;
            line-height: 1.2;
        }

        .valor-secundario {
            margin-top: 0.8mm;
            font-size: 8.3pt;
            color: #555555;
            line-height: 1.3;
        }

        .fecha {
            font-size: 10.5pt;
            font-weight: bold;
            line-height: 1.2;
        }

        .hora {
            margin-top: 0.8mm;
            font-size: 8.5pt;
            color: #555555;
        }

        .footer {
            margin-top: 4mm;
            padding-top: 3mm;
            border-top: 0.25mm solid #dedede;
            text-align: center;
        }

        .footer-principal {
            font-size: 7.2pt;
            font-weight: bold;
        }

        .footer-secundario {
            margin-top: 1.2mm;
            font-size: 7pt;
            color: #666666;
        }

    </style>
</head>

<body>

<div class="pagina">

    <div class="claim">
        EL EVENTO MÁS GRANDE DE LA ABOGACÍA
    </div>

    <div class="marca">
        <?= htmlspecialchars($marcaEvento) ?>
    </div>

    <div class="tipo">
        <?= htmlspecialchars($etiquetaAcceso) ?>
    </div>

    <div class="separador"></div>

    <div class="qr-contenedor">

        <img
            class="qr"
            src="<?= htmlspecialchars(
                (string) $acceso['ruta_qr']
            ) ?>"
            alt="QR"
        >

        <div class="codigo">
            <?= htmlspecialchars(
                (string) $acceso['codigo']
            ) ?>
        </div>

    </div>

    <div class="separador"></div>

    <div class="datos">

        <div class="grupo">

            <div class="label">
                Asistente
            </div>

            <div class="valor">
                <?= htmlspecialchars(
                    $nombreCompleto
                ) ?>
            </div>

        </div>

        <div class="grupo">

            <div class="label">
                Fecha
            </div>

            <div class="fecha">
                <?= htmlspecialchars(
                    $fechaFormateada
                ) ?>
            </div>

            <div class="hora">
                <?= htmlspecialchars(
                    $horaFormateada
                ) ?>
            </div>

        </div>

        <div class="grupo">

            <div class="label">
                Lugar
            </div>

            <div class="valor">
                <?= htmlspecialchars(
                    (string) (
                        $acceso['lugar']
                        ?? ''
                    )
                ) ?>
            </div>

            <div class="valor-secundario">
                <?= htmlspecialchars(
                    (string) (
                        $acceso['direccion']
                        ?? ''
                    )
                ) ?>
            </div>

        </div>

    </div>

    <div class="footer">

        <div class="footer-principal">
            Este acceso identifica de manera única
            a su titular.
        </div>

        <div class="footer-secundario">
            Presentalo junto con tu DNI para agilizar el ingreso.
        </div>

    </div>

</div>

</body>
</html>