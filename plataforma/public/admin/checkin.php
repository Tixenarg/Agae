<?php

declare(strict_types=1);

use App\Support\AdminGuard;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

$usuario =
    AdminGuard::proteger();

$titulo = 'Check-in';
$paginaActiva = 'checkin';

ob_start();

?>

<section class="page-header">

    <div>

        <div class="page-eyebrow">
            Evento 6N26
        </div>

        <h2 class="page-title">
            Check-In
        </h2>

        <p class="page-description">
            Gestión de compras y operaciones
            del evento.
        </p>

    </div>

</section>

<article class="panel">

    <div class="activity-empty">
        Este módulo lo construimos
        en el siguiente paso.
    </div>

</article>

<?php

$contenido =
    ob_get_clean();

require __DIR__
    . '/partials/layout.php';