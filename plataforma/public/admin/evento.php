<?php

declare(strict_types=1);

use App\Support\AdminGuard;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

$usuario =
    AdminGuard::requiereRol([
        'administrador_general',
    ]);

$titulo =
    'Evento';

$paginaActiva =
    'evento';

ob_start();

?>

<section class="page-header">

    <div>

        <div class="page-eyebrow">
            Evento activo
        </div>

        <h2 class="page-title">
            Evento 6N26
        </h2>

        <p class="page-description">
            Centro de administración del evento.
        </p>

    </div>

</section>

<div class="hub-grid">

    <a
        href="/public/admin/configuracion.php"
        class="hub-card"
    >

        <div class="hub-title">
            Información
        </div>

        <div class="hub-description">
            Configuración general
            del evento.
        </div>

    </a>

    <a
        href="/public/admin/centro-impresion.php"
        class="hub-card"
    >

        <div class="hub-title">
            Centro de impresión
        </div>

        <div class="hub-description">
            Listados PDF,
            Excel y herramientas
            para acreditación.
        </div>

    </a>

    <a
        href="#"
        class="hub-card"
    >

        <div class="hub-title">
            Herramientas
        </div>

        <div class="hub-description">
            Reprocesar emails,
            regenerar PDFs
            y tareas internas.
        </div>

    </a>

</div>

<?php

$contenido =
    ob_get_clean();

require __DIR__
    . '/partials/layout.php';