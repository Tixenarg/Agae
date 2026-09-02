<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

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
            viewport-fit=cover,
            user-scalable=no
        "
    >

    <meta
        name="theme-color"
        content="#0b0b0b"
    >

    <title>6N26 · Check-in</title>

    <link
        rel="stylesheet"
        href="/public/assets/css/checkin.css"
    >
</head>

<body>

<main class="checkin-app">

    <header class="checkin-header">

        <div>
            <div class="checkin-brand">
                6N26
            </div>

            <div class="checkin-label">
                CHECK-IN
            </div>
        </div>

        <div class="checkin-counter">
            <span>✓</span>
            <span id="contador-ingresos">—</span>
        </div>

    </header>

    <section class="scanner-area">

        <div
            id="reader"
            class="scanner-reader"
        ></div>

        <div class="scanner-frame">
            <span class="corner corner-tl"></span>
            <span class="corner corner-tr"></span>
            <span class="corner corner-bl"></span>
            <span class="corner corner-br"></span>
        </div>

        <div
            class="scanner-status"
            id="scanner-status"
        >
            Apuntá al código QR
        </div>

    </section>

    <footer class="checkin-footer">

        <button
            type="button"
            class="manual-button"
            id="manual-button"
        >
            Buscar acceso
        </button>

    </footer>

</main>

<section
    class="result-screen"
    id="result-screen"
    aria-hidden="true"
>

    <div class="result-content">

        <div
            class="result-icon"
            id="result-icon"
        >
            ✓
        </div>

        <div
            class="result-title"
            id="result-title"
        ></div>

        <div
            class="result-name"
            id="result-name"
        ></div>

        <div
            class="result-detail"
            id="result-detail"
        ></div>

        <button
            type="button"
            class="result-button"
            id="result-button"
            hidden
        >
            Continuar
        </button>

    </div>

</section>

<section
    class="manual-panel"
    id="manual-panel"
    aria-hidden="true"
>

    <div class="manual-card">

        <div class="manual-header">

            <div>
                <div class="manual-title">
                    Buscar acceso
                </div>

                <div class="manual-subtitle">
                    Ingresá el código manualmente.
                </div>
            </div>

            <button
                type="button"
                class="manual-close"
                id="manual-close"
                aria-label="Cerrar"
            >
                ×
            </button>

        </div>

        <form id="manual-form">

            <label
                for="manual-code"
                class="manual-label"
            >
                Código de acceso
            </label>

            <input
                type="text"
                id="manual-code"
                name="codigo"
                class="manual-input"
                placeholder="6N26-XXXX-XXXX-A1"
                autocomplete="off"
                autocapitalize="characters"
                spellcheck="false"
            >

            <button
                type="submit"
                class="manual-submit"
            >
                Validar acceso
            </button>

        </form>

    </div>

</section>

<script
    src="https://unpkg.com/html5-qrcode"
    type="text/javascript"
></script>

<script
    src="/public/assets/js/checkin.js"
    defer
></script>

</body>
</html>