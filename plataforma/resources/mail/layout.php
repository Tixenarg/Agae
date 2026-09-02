<?php

declare(strict_types=1);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($titulo) ?>
    </title>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        background: #f4f4f4;
        font-family:
            -apple-system,
            BlinkMacSystemFont,
            'Segoe UI',
            Arial,
            Helvetica,
            sans-serif;
        color: #111111;
    "
>

<table
    role="presentation"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        width: 100%;
        background: #f4f4f4;
    "
>
    <tr>
        <td
            align="center"
            style="
                padding: 24px 12px;
            "
        >

            <table
                role="presentation"
                width="100%"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    width: 100%;
                    max-width: 600px;
                    background: #ffffff;
                    border-radius: 14px;
                "
            >

                <?php
                require __DIR__
                    . '/partials/header.php';
                ?>

                <?= $contenido ?>

                <?php
                require __DIR__
                    . '/partials/footer.php';
                ?>

            </table>

        </td>
    </tr>
</table>

</body>
</html>