<?php

declare(strict_types=1);

$cantidadAccesos =
    (int) (
        $datos['cantidad_accesos']
        ?? 0
    );

$nombreComprador =
    trim(
        (string) (
            $datos['comprador_nombre']
            ?? ''
        )
    );

$textoAdjuntos =
    $cantidadAccesos === 1
        ? 'Adjuntamos tu acceso en formato PDF.'
        : sprintf(
            'Adjuntamos tus %d accesos en formato PDF.',
            $cantidadAccesos
        );

$titulo =
    '¡Ya sos parte del Evento más grande de la Abogacía!';

ob_start();

?>

<tr>
    <td
        style="
            padding: 12px 32px 32px 32px;
        "
    >

        <h1
            style="
                margin: 0;
                font-size: 34px;
                line-height: 1.1;
                letter-spacing: -1px;
                color: #111111;
            "
        >
            ¡Ya sos parte del Evento más grande de la Abogacía!
        </h1>

        <p
            style="
                margin: 28px 0 0 0;
                font-size: 17px;
                line-height: 1.6;
                color: #111111;
            "
        >
            Hola,
            <strong>
                <?= htmlspecialchars(
                    $nombreComprador
                ) ?>
            </strong>.
        </p>

        <p
            style="
                margin: 12px 0 0 0;
                font-size: 16px;
                line-height: 1.6;
                color: #444444;
            "
        >
            Tu compra fue confirmada correctamente.
        </p>

        <p
            style="
                margin: 8px 0 0 0;
                font-size: 16px;
                line-height: 1.6;
                color: #444444;
            "
        >
            <?= htmlspecialchars(
                $textoAdjuntos
            ) ?>
        </p>

    </td>
</tr>

<?php

$tituloSeccion = 'Lugar';

ob_start();

?>

<div
    style="
        font-size: 22px;
        line-height: 1.3;
        font-weight: 700;
        color: #111111;
    "
>
    Palacio Alsina
</div>

<div
    style="
        margin-top: 6px;
        font-size: 14px;
        line-height: 1.5;
        color: #666666;
    "
>
    Adolfo Alsina 934<br>
    Ciudad Autónoma de Buenos Aires
</div>

<div style="margin-top: 18px;">

    <?php

    $textoBoton =
        'Cómo llegar';

    $urlBoton =
        (string) (
            $datos['url_mapa']
            ?? '#'
        );

    require __DIR__
        . '/partials/button.php';

    ?>

</div>

<?php

$contenidoSeccion =
    ob_get_clean();

require __DIR__
    . '/partials/section.php';

$tituloSeccion =
    'Inicio del evento';

ob_start();

?>

<div
    style="
        font-size: 28px;
        line-height: 1;
        font-weight: 700;
        color: #111111;
    "
>
    20:30 h
</div>

<div
    style="
        margin-top: 8px;
        font-size: 14px;
        line-height: 1.5;
        color: #666666;
    "
>
    6 de noviembre de 2026
</div>

<?php

$contenidoSeccion =
    ob_get_clean();

require __DIR__
    . '/partials/section.php';

$tituloSeccion =
    'Sitio oficial';

ob_start();

?>

<div>
    <?php

    $textoBoton =
        'Conocer el evento';

    $urlBoton =
        (string) (
            $datos['url_evento']
            ?? '#'
        );

    require __DIR__
        . '/partials/button.php';

    ?>
</div>

<?php

$contenidoSeccion =
    ob_get_clean();

require __DIR__
    . '/partials/section.php';

$tituloSeccion =
    'Antes de venir';

ob_start();

?>

<table
    role="presentation"
    cellpadding="0"
    cellspacing="0"
    border="0"
    width="100%"
>

    <tr>
        <td
            width="24"
            valign="top"
            style="
                padding: 0 0 12px 0;
                font-size: 16px;
                color: #111111;
            "
        >
            ✓
        </td>

        <td
            style="
                padding: 0 0 12px 0;
                font-size: 15px;
                line-height: 1.5;
                color: #444444;
            "
        >
            Presentá tu acceso desde el celular
            o impreso.
        </td>
    </tr>

    <tr>
        <td
            width="24"
            valign="top"
            style="
                padding: 0 0 12px 0;
                font-size: 16px;
                color: #111111;
            "
        >
            ✓
        </td>

        <td
            style="
                padding: 0 0 12px 0;
                font-size: 15px;
                line-height: 1.5;
                color: #444444;
            "
        >
            Tené tu DNI a mano para agilizar
            el ingreso.
        </td>
    </tr>

    <tr>
        <td
            width="24"
            valign="top"
            style="
                padding: 0 0 12px 0;
                font-size: 16px;
                color: #111111;
            "
        >
            ✓
        </td>

        <td
            style="
                padding: 0 0 12px 0;
                font-size: 15px;
                line-height: 1.5;
                color: #444444;
            "
        >
            Llegá 30 minutos antes del inicio.
        </td>
    </tr>

    <?php if ($cantidadAccesos > 1): ?>

        <tr>
            <td
                width="24"
                valign="top"
                style="
                    font-size: 16px;
                    color: #111111;
                "
            >
                ✓
            </td>

            <td
                style="
                    font-size: 15px;
                    line-height: 1.5;
                    color: #444444;
                "
            >
                Reenviá cada PDF al asistente
                correspondiente.
            </td>
        </tr>

    <?php endif; ?>

</table>

<?php

$contenidoSeccion =
    ob_get_clean();

require __DIR__
    . '/partials/section.php';

$contenido =
    ob_get_clean();

require __DIR__
    . '/layout.php';