<?php

declare(strict_types=1);

$nombreCompleto =
    trim(
        (
            (string) (
                $acceso['nombre']
                ?? ''
            )
        )
        . ' '
        . (
            (string) (
                $acceso['apellido']
                ?? ''
            )
        )
    );

$fechaEvento =
    new DateTimeImmutable(
        (string) $acceso['fecha_inicio']
    );

$fechaFormateada =
    $fechaEvento->format('d/m/Y');

$horaFormateada =
    $fechaEvento->format('H:i');

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            color: #111111;
            background: #ffffff;
        }

        .pagina {
            padding: 42px 48px;
        }

        .eyebrow {
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #666666;
            margin-bottom: 14px;
        }

        .titulo {
            font-size: 34px;
            font-weight: 700;
            margin: 0 0 6px 0;
        }

        .subtitulo {
            font-size: 15px;
            color: #555555;
            margin-bottom: 36px;
        }

        .separador {
            border-top: 1px solid #dddddd;
            margin: 28px 0;
        }

        .qr-wrap {
            text-align: center;
            margin: 32px 0 22px 0;
        }

        .qr {
            width: 220px;
            height: 220px;
        }

        .codigo {
            text-align: center;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-top: 12px;
        }

        .bloque {
            margin-bottom: 22px;
        }

        .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #777777;
            margin-bottom: 4px;
        }

        .valor {
            font-size: 19px;
            font-weight: 600;
        }

        .categoria {
            display: inline-block;
            margin-top: 8px;
            padding: 7px 12px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: 1px solid #111111;
        }

        .pie {
            margin-top: 42px;
            padding-top: 18px;
            border-top: 1px solid #dddddd;
            font-size: 11px;
            line-height: 1.5;
            color: #666666;
        }
    </style>
</head>

<body>

<div class="pagina">

    <div class="eyebrow">
        Asociación Gremial de Abogados del Estado
    </div>

    <h1 class="titulo">
        <?= htmlspecialchars(
            (string) $acceso['evento_nombre']
        ) ?>
    </h1>

    <div class="subtitulo">
        <?= htmlspecialchars(
            (string) (
                $acceso['plantilla_titulo']
                ?? 'Acceso'
            )
        ) ?>
    </div>

    <div class="separador"></div>

    <div class="qr-wrap">
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

    <div class="bloque">
        <div class="label">
            Asistente
        </div>

        <div class="valor">
            <?= htmlspecialchars(
                $nombreCompleto !== ''
                    ? $nombreCompleto
                    : 'Sin asignar'
            ) ?>
        </div>

        <div class="categoria">
            <?= htmlspecialchars(
                (string) $acceso[
                    'tipo_acceso_nombre'
                ]
            ) ?>
        </div>
    </div>

    <div class="bloque">
        <div class="label">
            Fecha y hora
        </div>

        <div class="valor">
            <?= htmlspecialchars(
                $fechaFormateada
                . ' · '
                . $horaFormateada
                . ' hs'
            ) ?>
        </div>
    </div>

    <div class="bloque">
        <div class="label">
            Lugar
        </div>

        <div class="valor">
            <?= htmlspecialchars(
                (string) $acceso['lugar']
            ) ?>
        </div>

        <div style="font-size: 12px; color: #777777; margin-top: 4px;">
            <?= htmlspecialchars(
                (string) $acceso['direccion']
            ) ?>
        </div>
    </div>

    <div class="pie">
        Presentá este acceso desde tu celular o impreso.
        Tené tu DNI a mano para agilizar el ingreso.
    </div>

</div>

</body>
</html>