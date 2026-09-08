<?php

declare(strict_types=1);

use App\Repositories\AccesoRepository;
use App\Repositories\EmailRepository;
use App\Repositories\OrdenRepository;
use App\Support\AdminGuard;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

$usuario =
    AdminGuard::proteger();

/** @var PDO $pdo */
$pdo =
    require dirname(__DIR__, 2)
        . '/config/database.php';

$ordenRepository =
    new OrdenRepository($pdo);

$accesoRepository =
    new AccesoRepository($pdo);

$emailRepository =
    new EmailRepository($pdo);

$id =
    (int) (
        $_GET['id']
        ?? 0
    );

$orden =
    $ordenRepository
        ->obtenerDetalle($id);

if ($orden === null) {

    http_response_code(404);

    exit('Orden no encontrada.');

}

$accesos =
    $accesoRepository
        ->obtenerPorOrden($id);

$emails =
    $emailRepository
        ->obtenerPorOrden($id);

$timeline = [];

$timeline[] = [
    'titulo' => 'Orden creada',
    'fecha'  => $orden['creado_en'],
];

if (!empty($orden['pagada_en'])) {

    $timeline[] = [
        'titulo' => 'Pago aprobado',
        'fecha'  => $orden['pagada_en'],
    ];

}

foreach ($emails as $email) {

    if (!empty($email['enviado_en'])) {

        $timeline[] = [
            'titulo' => 'Email enviado',
            'fecha'  => $email['enviado_en'],
        ];

    }

}

foreach ($accesos as $acceso) {

    if (!empty($acceso['pdf_generado_en'])) {

        $timeline[] = [
            'titulo' => 'PDF generado',
            'fecha'  => $acceso['pdf_generado_en'],
        ];

    }

    if (!empty($acceso['utilizado_en'])) {

        $timeline[] = [
            'titulo' => 'Check-in realizado',
            'fecha'  => $acceso['utilizado_en'],
        ];

    }

}

usort(
    $timeline,
    static function ($a, $b) {

        return strcmp(
            $a['fecha'],
            $b['fecha']
        );

    }
);

$titulo =
    'Orden ' . $orden['codigo'];

$paginaActiva =
    'ordenes';

ob_start();

?>

<section class="page-header">

    <div>

        <a
            href="/public/admin/ordenes.php"
            class="back-link"
        >
            ← Volver a órdenes
        </a>

        <div class="page-eyebrow">
            <?= htmlspecialchars(
                (string) $orden[
                    'evento_nombre'
                ]
            ) ?>
        </div>

        <h2 class="page-title">
            <?= htmlspecialchars(
                (string) $orden[
                    'codigo'
                ]
            ) ?>
        </h2>

        <div class="page-status">

            <?php

            $estadoClase = match($orden['estado']){

                'pagada' => 'status-success',

                'pendiente_pago' => 'status-warning',

                'cancelada' => 'status-neutral',

                default => 'status-info'

            };

            ?>

            <span class="status-badge <?= $estadoClase ?>">

            <?= ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $orden['estado']
                )
            ) ?>

            </span>

        </div>

        <p class="page-description">
            Detalle completo de la compra,
            sus accesos y comunicaciones.
        </p>

    </div>

</section>

<div class="detail-grid">

    <article class="detail-card">

        <div class="detail-card-label">
            Comprador
        </div>

        <div class="detail-card-value">
            <?= htmlspecialchars(
                trim(
                    (string) $orden['nombre']
                    . ' '
                    . (string) $orden['apellido']
                )
            ) ?>
        </div>

        <div class="detail-card-meta">
            <?= htmlspecialchars(
                (string) $orden['email']
            ) ?>
        </div>

        <?php if (!empty($orden['telefono'])): ?>

            <div class="detail-card-meta">
                <?= htmlspecialchars(
                    (string) $orden['telefono']
                ) ?>
            </div>

        <?php endif; ?>

    </article>

    <article class="detail-card">

        <div class="detail-card-label">
            Compra
        </div>

        <div class="detail-card-value">
            <?= number_format(
                (int) $orden['cantidad_accesos'],
                0,
                ',',
                '.'
            ) ?>
            acceso<?= (int) $orden['cantidad_accesos'] === 1 ? '' : 's' ?>
        </div>

        <div class="detail-card-meta">
            <?= date(
                'd/m/Y H:i',
                strtotime($orden['creado_en'])
            ) ?>
        </div>

    </article>

    <article class="detail-card">

        <div class="detail-card-label">
            Total
        </div>

        <div class="detail-card-value">
            $<?= number_format(
                (float) $orden['total'],
                0,
                ',',
                '.'
            ) ?>
        </div>

        <div class="detail-card-meta">
            <?= htmlspecialchars(
                (string) $orden['moneda']
            ) ?>
        </div>

    </article>

</div>

<section class="order-section">

    <div class="section-header">

        <div>

            <div class="section-eyebrow">
                Accesos
            </div>

            <h3 class="section-title">
                Accesos asociados
            </h3>

        </div>

        <div class="section-count">
            <?= count($accesos) ?>
        </div>

    </div>

    <div class="access-list">

        <?php foreach ($accesos as $acceso): ?>

            <?php

            $nombreAcceso =
                trim(
                    (string) (
                        $acceso['nombre']
                        ?? ''
                    )
                    . ' '
                    . (string) (
                        $acceso['apellido']
                        ?? ''
                    )
                );

            ?>

            <article class="access-card">

                <div class="access-card-main">

                    <div class="access-card-title">
                        <?= htmlspecialchars(
                            $nombreAcceso !== ''
                                ? $nombreAcceso
                                : 'Sin nombre'
                        ) ?>
                    </div>

                    <div class="access-card-type">
                        <?= htmlspecialchars(
                            (string) (
                                $acceso[
                                    'tipo_acceso_nombre'
                                ]
                                ?? 'Acceso'
                            )
                        ) ?>
                    </div>

                    <div class="access-card-code">
                        <?= htmlspecialchars(
                            (string) $acceso[
                                'codigo'
                            ]
                        ) ?>
                    </div>

                </div>

                <div class="access-card-status">

                    <div class="access-status-item">

                        <span
                            class="access-status-dot <?= !empty(
                                $acceso[
                                    'pdf_generado_en'
                                ]
                            )
                                ? 'is-ok'
                                : '' ?>"
                        ></span>

                        <span>
                            PDF
                        </span>

                    </div>

                    <div class="access-status-item">

                        <span
                            class="access-status-dot <?= !empty(
                                $acceso[
                                    'email_enviado_en'
                                ]
                            )
                                ? 'is-ok'
                                : '' ?>"
                        ></span>

                        <span>
                            Email
                        </span>

                    </div>

                    <div class="access-status-item">

                        <span
                            class="access-status-dot <?= !empty(
                                $acceso[
                                    'utilizado_en'
                                ]
                            )
                                ? 'is-ok'
                                : '' ?>"
                        ></span>

                        <span>
                            Check-in
                        </span>

                    </div>

                </div>

                <div class="access-card-action">

                    <a
                        href="/public/admin/acceso-detalle.php?id=<?= (int) $acceso['id'] ?>"
                        class="table-row-action"
                        aria-label="Ver acceso"
                    >
                        →
                    </a>

                </div>

            </article>

        <?php endforeach; ?>

    </div>

</section>

<section class="order-section">

    <div class="section-header">

        <div>

            <div class="section-eyebrow">
                Actividad
            </div>

            <h3 class="section-title">
                Historial de la orden
            </h3>

        </div>

    </div>

    <div class="timeline">

        <?php foreach ($timeline as $evento): ?>

            <div class="timeline-item">

                <div class="timeline-dot"></div>

                <div class="timeline-content">

                    <div class="timeline-title">
                        <?= htmlspecialchars(
                            $evento['titulo']
                        ) ?>
                    </div>

                    <div class="timeline-date">
                        <?= date(
                            'd/m/Y · H:i',
                            strtotime(
                                $evento['fecha']
                            )
                        ) ?>
                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</section>

<?php

$contenido =
    ob_get_clean();

require __DIR__
    . '/partials/layout.php';