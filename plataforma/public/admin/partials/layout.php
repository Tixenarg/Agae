<?php

declare(strict_types=1);

if (!isset($usuario)) {
    throw new RuntimeException(
        'El layout requiere un usuario autenticado.'
    );
}

$titulo =
    $titulo
    ?? 'AGAE Platform';

$paginaActiva =
    $paginaActiva
    ?? '';

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="
            width=device-width,
            initial-scale=1,
            viewport-fit=cover
        "
    >

    <meta
        name="theme-color"
        content="#0b0b0b"
    >

    <title>
        <?= htmlspecialchars($titulo) ?>
        · AGAE Platform
    </title>

    <link
        rel="stylesheet"
        href="/public/admin/assets/css/admin.css"
    >

</head>

<body>

    <?php

    $flash =
        $_SESSION['admin_flash']
        ?? null;

    unset(
        $_SESSION['admin_flash']
    );

    ?>

    <?php if (
        is_array($flash)
        && !empty($flash['mensaje'])
    ): ?>

        <div
            class="admin-toast admin-toast-<?= htmlspecialchars(
                (string) (
                    $flash['tipo']
                    ?? 'info'
                )
            ) ?>"
            id="admin-toast"
        >
            <?= htmlspecialchars(
                (string) $flash['mensaje']
            ) ?>
        </div>

    <?php endif; ?>

<div class="admin-shell">

    <?php
    require __DIR__
        . '/sidebar.php';
    ?>

    <div
        class="admin-overlay"
        id="admin-overlay"
    ></div>

    <div class="admin-main">

        <?php
        require __DIR__
            . '/topbar.php';
        ?>

        <main class="admin-content">

            <div class="admin-container">

                <?= $contenido ?>

            </div>

        </main>

    </div>

</div>

<script
    src="/public/admin/assets/js/admin.js"
    defer
></script>

</body>
</html>