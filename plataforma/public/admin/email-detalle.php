<?php

declare(strict_types=1);

use App\Repositories\EmailRepository;
use App\Support\AdminGuard;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

$usuario =
    AdminGuard::requiereRol([
        'administrador_general',
        'administracion',
    ]);

/** @var PDO $pdo */
$pdo =
    require dirname(__DIR__, 2)
        . '/config/database.php';

$emailRepository =
    new EmailRepository($pdo);

$id =
    (int) (
        $_GET['id']
        ?? 0
    );

$email =
    $emailRepository
        ->obtenerPorId($id);

if ($email === null) {
    http_response_code(404);

    exit('Email no encontrado.');
}

$estado =
    (string) $email['estado'];

$estadoClase =
    match ($estado) {
        'enviado' =>
            'status-success',

        'pendiente',
        'reintentando' =>
            'status-warning',

        'procesando' =>
            'status-info',

        default =>
            'status-neutral',
    };

$estadoEtiqueta =
    match ($estado) {
        'enviado' =>
            'Enviado',

        'pendiente' =>
            'Pendiente',

        'procesando' =>
            'Procesando',

        'reintentando' =>
            'Reintentando',

        'fallido' =>
            'Fallido',

        default =>
            ucfirst($estado),
    };

$titulo =
    'Email #' . $email['id'];

$paginaActiva =
    'emails';

ob_start();

?>

<section class="page-header">

    <div>

        <a
            href="/public/admin/emails.php"
            class="back-link"
        >
            ← Volver a emails
        </a>

        <div class="page-eyebrow">
            Comunicación
        </div>

        <h2 class="page-title">
            <?= htmlspecialchars(
                (string) $email['asunto']
            ) ?>
        </h2>

        <div class="page-status">

            <span
                class="status-badge <?= htmlspecialchars(
                    $estadoClase
                ) ?>"
            >
                <?= htmlspecialchars(
                    $estadoEtiqueta
                ) ?>
            </span>

        </div>

        <p class="page-description">
            Detalle de envío y seguimiento
            de la comunicación.
        </p>

    </div>

</section>

<?php if (
    ($_GET['reenviado'] ?? '')
    === '1'
): ?>

    <div class="admin-success-message">
        Email reenviado a la cola correctamente.
    </div>

<?php endif; ?>

<?php if (
    in_array(
        $estado,
        [
            'enviado',
            'fallido',
        ],
        true
    )
): ?>

    <section class="email-actions">

        <form
            method="post"
            action="/public/admin/email-reenviar.php"
        >

            <input
                type="hidden"
                name="email_id"
                value="<?= (int) $email['id'] ?>"
            >

            <button
                type="submit"
                class="admin-primary-button"
                onclick="return confirm(
                    '¿Querés reenviar este email?'
                );"
            >
                Reenviar email
            </button>

        </form>

    </section>

<?php endif; ?>

<div class="detail-grid">

    <article class="detail-card">

        <div class="detail-card-label">
            Destinatario
        </div>

        <div class="detail-card-value">
            <?= htmlspecialchars(
                (string) $email[
                    'destinatario'
                ]
            ) ?>
        </div>

        <?php if (
            !empty(
                $email['orden_codigo']
            )
        ): ?>

            <div class="detail-card-meta">
                Orden
                <?= htmlspecialchars(
                    (string) $email[
                        'orden_codigo'
                    ]
                ) ?>
            </div>

        <?php endif; ?>

    </article>

    <article class="detail-card">

        <div class="detail-card-label">
            Plantilla
        </div>

        <div class="detail-card-value">
            <?= htmlspecialchars(
                (string) (
                    $email['plantilla']
                    ?? '—'
                )
            ) ?>
        </div>

        <div class="detail-card-meta">
            <?= htmlspecialchars(
                (string) (
                    $email['tipo']
                    ?? ''
                )
            ) ?>
        </div>

    </article>

    <article class="detail-card">

        <div class="detail-card-label">
            Intentos
        </div>

        <div class="detail-card-value">
            <?= (int) $email['intentos'] ?>
            /
            <?= (int) $email[
                'max_intentos'
            ] ?>
        </div>

        <div class="detail-card-meta">
            Máximo permitido
        </div>

    </article>

</div>

<section class="order-section">

    <div class="section-header">

        <div>

            <div class="section-eyebrow">
                Seguimiento
            </div>

            <h3 class="section-title">
                Estado del envío
            </h3>

        </div>

    </div>

    <div class="access-state-grid">

        <article class="access-state-card">

            <div class="access-state-label">
                Programado
            </div>

            <div class="access-state-value">

                <?php if (
                    !empty(
                        $email[
                            'programado_para'
                        ]
                    )
                ): ?>

                    <?= date(
                        'd/m/Y · H:i',
                        strtotime(
                            (string) $email[
                                'programado_para'
                            ]
                        )
                    ) ?>

                <?php else: ?>

                    —

                <?php endif; ?>

            </div>

        </article>

        <article class="access-state-card">

            <div class="access-state-label">
                Enviado
            </div>

            <div
                class="access-state-value <?= !empty(
                    $email['enviado_en']
                )
                    ? 'is-ok'
                    : 'is-pending' ?>"
            >

                <?php if (
                    !empty(
                        $email['enviado_en']
                    )
                ): ?>

                    <?= date(
                        'd/m/Y · H:i',
                        strtotime(
                            (string) $email[
                                'enviado_en'
                            ]
                        )
                    ) ?>

                <?php else: ?>

                    Pendiente

                <?php endif; ?>

            </div>

        </article>

        <article class="access-state-card">

            <div class="access-state-label">
                Último error
            </div>

            <div class="access-state-value">

                <?= !empty(
                    $email['ultimo_error']
                )
                    ? htmlspecialchars(
                        (string) $email[
                            'ultimo_error'
                        ]
                    )
                    : 'Sin errores' ?>

            </div>

        </article>

    </div>

</section>

<?php if (
    !empty(
        $email['orden_id']
    )
): ?>

    <section class="order-section">

        <div class="section-header">

            <div>

                <div class="section-eyebrow">
                    Relación
                </div>

                <h3 class="section-title">
                    Orden asociada
                </h3>

            </div>

        </div>

        <article class="access-card">

            <div class="access-card-main">

                <div class="access-card-title">
                    <?= htmlspecialchars(
                        (string) $email[
                            'orden_codigo'
                        ]
                    ) ?>
                </div>

                <div class="access-card-type">
                    <?= htmlspecialchars(
                        (string) (
                            $email[
                                'orden_estado'
                            ]
                            ?? ''
                        )
                    ) ?>
                </div>

            </div>

            <div class="access-card-action">

                <a
                    href="/public/admin/orden-detalle.php?id=<?= (int) $email['orden_id'] ?>"
                    class="table-row-action"
                    aria-label="Ver orden"
                >
                    →
                </a>

            </div>

        </article>

    </section>

<?php endif; ?>

<?php

$contenido =
    ob_get_clean();

require __DIR__
    . '/partials/layout.php';