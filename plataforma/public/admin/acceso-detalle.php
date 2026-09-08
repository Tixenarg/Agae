<?php

declare(strict_types=1);

use App\Repositories\AccesoRepository;
use App\Support\AdminGuard;
use App\Services\QrCodeService;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

$usuario =
    AdminGuard::proteger();

/** @var PDO $pdo */
$pdo =
    require dirname(__DIR__, 2)
        . '/config/database.php';

$accesoRepository =
    new AccesoRepository($pdo);

$id =
    (int) (
        $_GET['id']
        ?? 0
    );

$acceso =
    $accesoRepository
        ->obtenerPorId($id);

if ($acceso === null) {

    http_response_code(404);

    exit('Acceso no encontrado.');

}

$qrCodeService =
    new QrCodeService();

$rutaQr =
    $qrCodeService->generar(
        (string) $acceso['codigo']
    );

$rutaQrPublica =
    '/' . ltrim(
        str_replace(
            dirname(__DIR__, 2),
            '',
            $rutaQr
        ),
        '/'
    );

$titulo =
    'Acceso ' . $acceso['codigo'];

$paginaActiva =
    'accesos';

ob_start();

?>

<section class="page-header">

    <div>

        <a
            href="/public/admin/orden-detalle.php?id=<?= (int) $acceso['orden_id'] ?>"
            class="back-link"
        >
            ← Volver a la orden
        </a>

        <div class="page-eyebrow">
            <?= htmlspecialchars(
                (string) $acceso[
                    'evento_nombre'
                ]
            ) ?>
        </div>

        <h2 class="page-title">
            <?= htmlspecialchars(
                (string) $acceso[
                    'codigo'
                ]
            ) ?>
        </h2>

        <p class="page-description">
            <?= htmlspecialchars(
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
                )
            ) ?>
        </p>

    </div>

</section>

<div class="detail-grid">

    <article class="detail-card">

        <div class="detail-card-label">
            Titular
        </div>

        <div class="detail-card-value">
            <?= htmlspecialchars(
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
                )
            ) ?>
        </div>

        <?php if (
            !empty($acceso['email_individual'])
        ): ?>

            <div class="detail-card-meta">
                <?= htmlspecialchars(
                    (string) $acceso[
                        'email_individual'
                    ]
                ) ?>
            </div>

        <?php endif; ?>

        <?php if (
            !empty($acceso['dni_individual'])
        ): ?>

            <div class="detail-card-meta">
                DNI
                <?= htmlspecialchars(
                    (string) $acceso[
                        'dni_individual'
                    ]
                ) ?>
            </div>

        <?php endif; ?>

    </article>

    <article class="detail-card">

        <div class="detail-card-label">
            Tipo
        </div>

        <div class="detail-card-value">
            <?= htmlspecialchars(
                (string) $acceso[
                    'tipo_acceso_nombre'
                ]
            ) ?>
        </div>

        <div class="detail-card-meta">
            <?= htmlspecialchars(
                (string) $acceso[
                    'codigo'
                ]
            ) ?>
        </div>

    </article>

    <article class="detail-card">

        <div class="detail-card-label">
            Orden
        </div>

        <div class="detail-card-value">
            <?= htmlspecialchars(
                (string) $acceso[
                    'orden_codigo'
                ]
            ) ?>
        </div>

        <div class="detail-card-meta">
            <?= htmlspecialchars(
                (string) $acceso[
                    'orden_estado'
                ]
            ) ?>
        </div>

    </article>

</div>

<section class="order-section">

    <div class="section-header">

        <div>

            <div class="section-eyebrow">
                Estado
            </div>

            <h3 class="section-title">
                Estado operativo
            </h3>

        </div>

    </div>

    <div class="access-state-grid">

        <article class="access-state-card">

            <div class="access-state-label">
                PDF
            </div>

            <div
                class="access-state-value <?= !empty(
                    $acceso['pdf_generado_en']
                )
                    ? 'is-ok'
                    : 'is-pending' ?>"
            >
                <?= !empty(
                    $acceso['pdf_generado_en']
                )
                    ? 'Generado'
                    : 'Pendiente' ?>
            </div>

            <?php if (
                !empty($acceso['pdf_generado_en'])
            ): ?>

                <div class="access-state-meta">
                    <?= date(
                        'd/m/Y · H:i',
                        strtotime(
                            $acceso[
                                'pdf_generado_en'
                            ]
                        )
                    ) ?>
                </div>

            <?php endif; ?>

        </article>

        <article class="access-state-card">

            <div class="access-state-label">
                Email
            </div>

            <div
                class="access-state-value <?= !empty(
                    $acceso['email_enviado_en']
                )
                    ? 'is-ok'
                    : 'is-pending' ?>"
            >
                <?= !empty(
                    $acceso['email_enviado_en']
                )
                    ? 'Enviado'
                    : 'Pendiente' ?>
            </div>

            <?php if (
                !empty($acceso['email_enviado_en'])
            ): ?>

                <div class="access-state-meta">
                    <?= date(
                        'd/m/Y · H:i',
                        strtotime(
                            $acceso[
                                'email_enviado_en'
                            ]
                        )
                    ) ?>
                </div>

            <?php endif; ?>

        </article>

        <article class="access-state-card">

            <div class="access-state-label">
                Check-in
            </div>

            <div
                class="access-state-value <?= !empty(
                    $acceso['utilizado_en']
                )
                    ? 'is-ok'
                    : 'is-pending' ?>"
            >
                <?= !empty(
                    $acceso['utilizado_en']
                )
                    ? 'Ingresó'
                    : 'Sin ingresar' ?>
            </div>

            <?php if (
                !empty($acceso['utilizado_en'])
            ): ?>

                <div class="access-state-meta">
                    <?= date(
                        'd/m/Y · H:i',
                        strtotime(
                            $acceso[
                                'utilizado_en'
                            ]
                        )
                    ) ?>
                </div>

            <?php endif; ?>

        </article>

    </div>

</section>

<section class="order-section">

    <div class="section-header">

        <div>

            <div class="section-eyebrow">
                Acceso
            </div>

            <h3 class="section-title">
                Código QR y documento
            </h3>

        </div>

    </div>

    <div class="access-tools-grid">

        <article class="qr-card">

            <div class="qr-card-image">

                <img
                    src="<?= htmlspecialchars($rutaQrPublica) ?>"
                    alt="Código QR"
                >

            </div>

            <div class="qr-card-code">
                <?= htmlspecialchars(
                    (string) $acceso['codigo']
                ) ?>
            </div>

        </article>

        <article class="access-actions-card">

            <div class="access-state-label">
                Documento del acceso
            </div>

            <?php if (!empty($acceso['ruta_pdf'])): ?>

                <a
                    class="admin-primary-button access-action-link"
                    href="/<?= htmlspecialchars(
                        ltrim(
                            (string) $acceso['ruta_pdf'],
                            '/'
                        )
                    ) ?>"
                    target="_blank"
                    rel="noopener"
                >
                    Abrir PDF
                </a>

                <a
                    class="admin-secondary-button access-action-link"
                    href="/<?= htmlspecialchars(
                        ltrim(
                            (string) $acceso['ruta_pdf'],
                            '/'
                        )
                    ) ?>"
                    download
                >
                    Descargar PDF
                </a>

            <?php else: ?>

                <div class="access-state-value is-pending">
                    El PDF todavía no fue generado.
                </div>

            <?php endif; ?>

        </article>

    </div>

</section>

<?php

$contenido =
    ob_get_clean();

require __DIR__
    . '/partials/layout.php';