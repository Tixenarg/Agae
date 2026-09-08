<?php

declare(strict_types=1);

use App\Repositories\EstadisticasRepository;
use App\Support\AdminGuard;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

/*
 * ---------------------------------------------------------
 * AUTENTICACIÓN
 * ---------------------------------------------------------
 */

$usuario =
    AdminGuard::proteger();

/*
 * ---------------------------------------------------------
 * BASE DE DATOS
 * ---------------------------------------------------------
 */

/** @var PDO $pdo */
$pdo =
    require dirname(__DIR__, 2)
        . '/config/database.php';

/*
 * ---------------------------------------------------------
 * ESTADÍSTICAS
 * ---------------------------------------------------------
 */

$estadisticasRepository =
    new EstadisticasRepository(
        $pdo
    );

$resumen =
    $estadisticasRepository
        ->obtenerResumen();

$actividad =
    $estadisticasRepository
        ->obtenerActividadReciente(
            12
        );

$accesosEmitidos =
    (int) (
        $resumen[
            'accesos_emitidos'
        ] ?? 0
    );

$capacidad =
    (int) (
        $resumen[
            'capacidad'
        ] ?? 0
    );

$recaudacion =
    (float) (
        $resumen[
            'recaudacion'
        ] ?? 0
    );

$checkins =
    (int) (
        $resumen[
            'checkins'
        ] ?? 0
    );

$emailsEnviados =
    (int) (
        $resumen[
            'emails'
        ] ?? 0
    );

$porcentajeCapacidad =
    $capacidad > 0
        ? round(
            (
                $accesosEmitidos
                / $capacidad
            ) * 100,
            1
        )
        : 0;

/*
 * ---------------------------------------------------------
 * LAYOUT
 * ---------------------------------------------------------
 */

$titulo =
    'Dashboard';

$paginaActiva =
    'dashboard';

ob_start();

?>

<section class="page-header">

    <div>

        <div class="page-eyebrow">
            Evento 6N26
        </div>

        <h2 class="page-title">
            Estado del evento
        </h2>

        <p class="page-description">
            Una vista general de ventas, accesos,
            comunicaciones e ingresos.
        </p>

    </div>

</section>

<section class="kpi-grid">

    <article class="kpi-card">

        <div class="kpi-label">
            Accesos emitidos
        </div>

        <div>

            <div class="kpi-value">
                <?= number_format(
                    $accesosEmitidos,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

            <div class="kpi-meta">

                <?= number_format(
                    $porcentajeCapacidad,
                    1,
                    ',',
                    '.'
                ) ?>%

                ·

                <?= number_format(
                    $capacidad,
                    0,
                    ',',
                    '.'
                ) ?>

                de capacidad

            </div>

        </div>

    </article>

    <article class="kpi-card">

        <div class="kpi-label">
            Recaudación
        </div>

        <div>

            <div class="kpi-value">
                $<?= number_format(
                    $recaudacion,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

            <div class="kpi-meta">
                Pagos confirmados
            </div>

        </div>

    </article>

    <article class="kpi-card">

        <div class="kpi-label">
            Check-ins
        </div>

        <div>

            <div class="kpi-value">
                <?= number_format(
                    $checkins,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

            <div class="kpi-meta">

                <?php if ($accesosEmitidos > 0): ?>

                    <?= number_format(
                        (
                            $checkins
                            / $accesosEmitidos
                        ) * 100,
                        1,
                        ',',
                        '.'
                    ) ?>%

                    de los accesos emitidos

                <?php else: ?>

                    Personas ingresadas

                <?php endif; ?>

            </div>

        </div>

    </article>

    <article class="kpi-card">

        <div class="kpi-label">
            Emails
        </div>

        <div>

            <div class="kpi-value">
                <?= number_format(
                    $emailsEnviados,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

            <div class="kpi-meta">
                Confirmaciones enviadas
            </div>

        </div>

    </article>

</section>

<section class="dashboard-grid">

    <article class="panel">

        <div class="panel-header">

            <h3 class="panel-title">
                Actividad reciente
            </h3>

            <span class="panel-caption">
                Últimos movimientos
            </span>

        </div>

        <?php if ($actividad === []): ?>

            <div class="activity-empty">
                Todavía no hay actividad registrada.
            </div>

        <?php else: ?>

            <div class="activity-list">

                <?php foreach ($actividad as $item): ?>

                    <?php

                    $tipo =
                        (string) (
                            $item['tipo']
                            ?? ''
                        );

                    $fecha =
                        new DateTimeImmutable(
                            (string) $item['fecha']
                        );

                    $fechaFormateada =
                        $fecha->format(
                            'd/m'
                        );

                    $horaFormateada =
                        $fecha->format(
                            'H:i'
                        );

                    ?>

                    <div class="activity-item">

                        <div
                            class="
                                activity-icon
                                activity-icon-<?= htmlspecialchars(
                                    $tipo
                                ) ?>
                            "
                        >

                            <?php if ($tipo === 'pago'): ?>

                                $

                            <?php elseif ($tipo === 'email'): ?>

                                @

                            <?php elseif ($tipo === 'checkin'): ?>

                                ✓

                            <?php else: ?>

                                •

                            <?php endif; ?>

                        </div>

                        <div class="activity-main">

                            <div class="activity-line">

                                <span class="activity-title">
                                    <?= htmlspecialchars(
                                        (string) $item['titulo']
                                    ) ?>
                                </span>

                                <span class="activity-separator">
                                    ·
                                </span>

                                <span class="activity-detail">
                                    <?= htmlspecialchars(
                                        (string) $item['detalle']
                                    ) ?>
                                </span>

                            </div>

                        </div>

                        <time class="activity-time">

                            <span>
                                <?= htmlspecialchars(
                                    $fechaFormateada
                                ) ?>
                            </span>

                            <span class="activity-time-separator">
                                ·
                            </span>

                            <span>
                                <?= htmlspecialchars(
                                    $horaFormateada
                                ) ?>
                            </span>

                        </time>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </article>

    <article class="panel">

        <div class="panel-header">

            <h3 class="panel-title">
                Acciones rápidas
            </h3>

        </div>

        <div class="quick-actions">

            <a
                href="/public/checkin/"
                class="quick-action"
                target="_blank"
                rel="noopener"
            >
                <span>
                    Abrir check-in
                </span>

                <span class="quick-action-arrow">
                    ↗
                </span>
            </a>

            <a
                href="/public/admin/accesos.php"
                class="quick-action"
            >
                <span>
                    Buscar acceso
                </span>

                <span class="quick-action-arrow">
                    →
                </span>
            </a>

            <a
                href="/public/admin/ordenes.php"
                class="quick-action"
            >
                <span>
                    Ver órdenes
                </span>

                <span class="quick-action-arrow">
                    →
                </span>
            </a>

            <a
                href="/public/admin/emails.php"
                class="quick-action"
            >
                <span>
                    Ver emails
                </span>

                <span class="quick-action-arrow">
                    →
                </span>
            </a>

        </div>

    </article>

</section>

<?php

$contenido =
    ob_get_clean();

require __DIR__
    . '/partials/layout.php';