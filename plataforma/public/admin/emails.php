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

$emails =
    $emailRepository
        ->obtenerListado();

function etiquetaEstadoEmail(
    string $estado
): string {
    return match ($estado) {
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
}

function claseEstadoEmail(
    string $estado
): string {
    return match ($estado) {
        'enviado' =>
            'success',

        'pendiente',
        'reintentando' =>
            'warning',

        'procesando' =>
            'info',

        'fallido' =>
            'neutral',

        default =>
            'neutral',
    };
}

$titulo =
    'Emails';

$paginaActiva =
    'emails';

ob_start();

?>

<section class="page-header">

    <div>

        <div class="page-eyebrow">
            Evento 6N26
        </div>

        <h2 class="page-title">
            Emails
        </h2>

        <p class="page-description">
            Seguimiento de comunicaciones,
            confirmaciones y reintentos.
        </p>

    </div>

    <div class="page-header-meta">

        <strong>
            <?= number_format(
                count($emails),
                0,
                ',',
                '.'
            ) ?>
        </strong>

        <span>
            registros
        </span>

    </div>

</section>

<section class="data-panel">

    <?php if ($emails === []): ?>

        <div class="data-empty">

            <strong>
                No hay emails registrados.
            </strong>

            <span>
                Las comunicaciones del evento
                aparecerán acá.
            </span>

        </div>

    <?php else: ?>

        <div class="data-table-wrapper">

            <table class="data-table">

                <thead>

                    <tr>

                        <th>
                            Destinatario
                        </th>

                        <th>
                            Asunto
                        </th>

                        <th>
                            Plantilla
                        </th>

                        <th>
                            Estado
                        </th>

                        <th>
                            Intentos
                        </th>

                        <th>
                            Fecha
                        </th>

                        <th></th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($emails as $email): ?>

                        <?php

                        $estado =
                            (string) (
                                $email['estado']
                                ?? ''
                            );

                        $fecha =
                            $email['enviado_en']
                            ?? $email['programado_para']
                            ?? null;

                        ?>

                        <tr>

                            <td>

                                <div class="table-primary">
                                    <?= htmlspecialchars(
                                        (string) $email[
                                            'destinatario'
                                        ]
                                    ) ?>
                                </div>

                                <?php if (
                                    !empty(
                                        $email[
                                            'orden_codigo'
                                        ]
                                    )
                                ): ?>

                                    <div class="table-secondary">
                                        Orden
                                        <?= htmlspecialchars(
                                            (string) $email[
                                                'orden_codigo'
                                            ]
                                        ) ?>
                                    </div>

                                <?php endif; ?>

                            </td>

                            <td>

                                <div class="table-primary">
                                    <?= htmlspecialchars(
                                        (string) $email[
                                            'asunto'
                                        ]
                                    ) ?>
                                </div>

                            </td>

                            <td>

                                <div class="table-primary">
                                    <?= htmlspecialchars(
                                        (string) $email[
                                            'plantilla'
                                        ]
                                    ) ?>
                                </div>

                            </td>

                            <td>

                                <span
                                    class="
                                        status-badge
                                        status-<?= htmlspecialchars(
                                            claseEstadoEmail(
                                                $estado
                                            )
                                        ) ?>
                                    "
                                >
                                    <?= htmlspecialchars(
                                        etiquetaEstadoEmail(
                                            $estado
                                        )
                                    ) ?>
                                </span>

                            </td>

                            <td>

                                <div class="table-primary">
                                    <?= (int) $email[
                                        'intentos'
                                    ] ?>
                                    /
                                    <?= (int) $email[
                                        'max_intentos'
                                    ] ?>
                                </div>

                            </td>

                            <td>

                                <?php if ($fecha !== null): ?>

                                    <?php

                                    $fechaEmail =
                                        new DateTimeImmutable(
                                            (string) $fecha
                                        );

                                    ?>

                                    <div class="table-primary">
                                        <?= $fechaEmail
                                            ->format(
                                                'd/m/Y'
                                            ) ?>
                                    </div>

                                    <div class="table-secondary">
                                        <?= $fechaEmail
                                            ->format(
                                                'H:i'
                                            ) ?> h
                                    </div>

                                <?php else: ?>

                                    <div class="table-secondary">
                                        Sin fecha
                                    </div>

                                <?php endif; ?>

                            </td>

                            <td class="table-action-cell">

                                <a
                                    href="/public/admin/email-detalle.php?id=<?= (int) $email['id'] ?>"
                                    class="table-row-action"
                                    aria-label="Ver email"
                                >
                                    →
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>

<?php

$contenido =
    ob_get_clean();

require __DIR__
    . '/partials/layout.php';