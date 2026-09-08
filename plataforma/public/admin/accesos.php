<?php

declare(strict_types=1);

use App\Repositories\AdminAccesoRepository;
use App\Support\AdminGuard;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

$usuario =
    AdminGuard::requiereRol([
        'administrador_general',
        'administracion',
        'acreditacion',
    ]);

/** @var PDO $pdo */
$pdo =
    require dirname(__DIR__, 2)
        . '/config/database.php';

$eventoId = 1;

$busqueda =
    trim(
        (string) (
            $_GET['q']
            ?? ''
        )
    );

$estadosPermitidos = [
    'pendiente_emision',
    'emitido',
    'utilizado',
    'anulado',
    'reembolsado',
];

$estado =
    trim(
        (string) (
            $_GET['estado']
            ?? ''
        )
    );

if (
    !in_array(
        $estado,
        $estadosPermitidos,
        true
    )
) {
    $estado = '';
}

$checkin =
    trim(
        (string) (
            $_GET['checkin']
            ?? ''
        )
    );

if (
    !in_array(
        $checkin,
        [
            '',
            'si',
            'no',
        ],
        true
    )
) {
    $checkin = '';
}

$pagina =
    max(
        1,
        (int) (
            $_GET['pagina']
            ?? 1
        )
    );

$porPagina = 50;

$repository =
    new AdminAccesoRepository(
        $pdo
    );

$listado =
    $repository->listar(
        $eventoId,
        $busqueda,
        $estado !== ''
            ? $estado
            : null,
        $checkin !== ''
            ? $checkin
            : null,
        $pagina,
        $porPagina
    );

$accesos =
    $listado['items'];

$total =
    (int) $listado['total'];

$paginas =
    (int) $listado['paginas'];

function etiquetaEstadoAcceso(
    string $estado
): string {
    return match ($estado) {
        'pendiente_emision' =>
            'Pendiente',

        'emitido' =>
            'Emitido',

        'utilizado' =>
            'Utilizado',

        'anulado' =>
            'Anulado',

        'reembolsado' =>
            'Reembolsado',

        default =>
            ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $estado
                )
            ),
    };
}

function claseEstadoAcceso(
    string $estado
): string {
    return match ($estado) {
        'emitido',
        'utilizado' =>
            'success',

        'pendiente_emision' =>
            'warning',

        'anulado',
        'reembolsado' =>
            'neutral',

        default =>
            'neutral',
    };
}

function urlAccesos(
    array $cambios
): string {
    $actual = [
        'q' =>
            $_GET['q']
            ?? '',

        'estado' =>
            $_GET['estado']
            ?? '',

        'checkin' =>
            $_GET['checkin']
            ?? '',

        'pagina' =>
            $_GET['pagina']
            ?? 1,
    ];

    $params =
        array_merge(
            $actual,
            $cambios
        );

    $params =
        array_filter(
            $params,
            static fn ($value) =>
                $value !== ''
                && $value !== null
        );

    return '/public/admin/accesos.php?'
        . http_build_query(
            $params
        );
}

$titulo =
    'Accesos';

$paginaActiva =
    'accesos';

ob_start();

?>

<section class="page-header">

    <div>

        <div class="page-eyebrow">
            Evento 6N26
        </div>

        <h2 class="page-title">
            Accesos
        </h2>

        <p class="page-description">
            Búsqueda y seguimiento de los accesos
            emitidos para el evento.
        </p>

    </div>

    <div class="page-header-meta">

        <strong>
            <?= number_format(
                $total,
                0,
                ',',
                '.'
            ) ?>
        </strong>

        <span>
            resultados
        </span>

    </div>

</section>

<section class="admin-toolbar">

    <form
        method="get"
        class="admin-search"
    >

        <?php if ($estado !== ''): ?>

            <input
                type="hidden"
                name="estado"
                value="<?= htmlspecialchars(
                    $estado
                ) ?>"
            >

        <?php endif; ?>

        <?php if ($checkin !== ''): ?>

            <input
                type="hidden"
                name="checkin"
                value="<?= htmlspecialchars(
                    $checkin
                ) ?>"
            >

        <?php endif; ?>

        <input
            type="search"
            name="q"
            value="<?= htmlspecialchars(
                $busqueda
            ) ?>"
            placeholder="Buscar código, nombre, DNI o email..."
            autocomplete="off"
        >

        <button type="submit">
            Buscar
        </button>

    </form>

</section>

<nav class="filter-tabs">

    <a
        href="<?= htmlspecialchars(
            urlAccesos([
                'estado' => '',
                'checkin' => '',
                'pagina' => 1,
            ])
        ) ?>"
        class="filter-tab<?= (
            $estado === ''
            && $checkin === ''
        )
            ? ' is-active'
            : '' ?>"
    >
        Todos
    </a>

    <a
        href="<?= htmlspecialchars(
            urlAccesos([
                'estado' =>
                    'emitido',
                'pagina' => 1,
            ])
        ) ?>"
        class="filter-tab<?= $estado === 'emitido'
            ? ' is-active'
            : '' ?>"
    >
        Emitidos
    </a>

    <a
        href="<?= htmlspecialchars(
            urlAccesos([
                'estado' =>
                    'utilizado',
                'pagina' => 1,
            ])
        ) ?>"
        class="filter-tab<?= $estado === 'utilizado'
            ? ' is-active'
            : '' ?>"
    >
        Utilizados
    </a>

    <a
        href="<?= htmlspecialchars(
            urlAccesos([
                'checkin' =>
                    'no',
                'estado' => '',
                'pagina' => 1,
            ])
        ) ?>"
        class="filter-tab<?= $checkin === 'no'
            ? ' is-active'
            : '' ?>"
    >
        Sin ingresar
    </a>

    <a
        href="<?= htmlspecialchars(
            urlAccesos([
                'estado' =>
                    'anulado',
                'checkin' => '',
                'pagina' => 1,
            ])
        ) ?>"
        class="filter-tab<?= $estado === 'anulado'
            ? ' is-active'
            : '' ?>"
    >
        Anulados
    </a>

</nav>

<section class="data-panel">

    <?php if ($accesos === []): ?>

        <div class="data-empty">

            <strong>
                No encontramos accesos.
            </strong>

            <span>
                Probá cambiando la búsqueda
                o los filtros.
            </span>

        </div>

    <?php else: ?>

        <div class="data-table-wrapper">

            <table class="data-table">

                <thead>

                    <tr>

                        <th>
                            Código
                        </th>

                        <th>
                            Titular
                        </th>

                        <th>
                            Tipo
                        </th>

                        <th>
                            Estado
                        </th>

                        <th>
                            Check-in
                        </th>

                        <th>
                            Documento
                        </th>

                        <th></th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($accesos as $acceso): ?>

                        <?php

                        $nombre =
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

                        $estadoAcceso =
                            (string) $acceso[
                                'estado'
                            ];

                        ?>

                        <tr>

                            <td>

                                <div class="table-primary">
                                    <?= htmlspecialchars(
                                        (string) $acceso[
                                            'codigo'
                                        ]
                                    ) ?>
                                </div>

                                <div class="table-secondary">
                                    Orden
                                    <?= htmlspecialchars(
                                        (string) $acceso[
                                            'orden_codigo'
                                        ]
                                    ) ?>
                                </div>

                            </td>

                            <td>

                                <div class="table-primary">
                                    <?= htmlspecialchars(
                                        $nombre !== ''
                                            ? $nombre
                                            : 'Sin nombre'
                                    ) ?>
                                </div>

                                <div class="table-secondary">
                                    <?= htmlspecialchars(
                                        (string) (
                                            $acceso[
                                                'comprador_email'
                                            ]
                                            ?? ''
                                        )
                                    ) ?>
                                </div>

                            </td>

                            <td>

                                <div class="table-primary">
                                    <?= htmlspecialchars(
                                        (string) $acceso[
                                            'tipo_acceso_nombre'
                                        ]
                                    ) ?>
                                </div>

                            </td>

                            <td>

                                <span
                                    class="
                                        status-badge
                                        status-<?= htmlspecialchars(
                                            claseEstadoAcceso(
                                                $estadoAcceso
                                            )
                                        ) ?>
                                    "
                                >
                                    <?= htmlspecialchars(
                                        etiquetaEstadoAcceso(
                                            $estadoAcceso
                                        )
                                    ) ?>
                                </span>

                            </td>

                            <td>

                                <?php if (
                                    !empty(
                                        $acceso[
                                            'utilizado_en'
                                        ]
                                    )
                                ): ?>

                                    <?php

                                    $fechaCheckin =
                                        new DateTimeImmutable(
                                            (string) $acceso[
                                                'utilizado_en'
                                            ]
                                        );

                                    ?>

                                    <div class="table-primary">
                                        Ingresó
                                    </div>

                                    <div class="table-secondary">
                                        <?= $fechaCheckin
                                            ->format(
                                                'd/m · H:i'
                                            ) ?>
                                    </div>

                                <?php else: ?>

                                    <div class="table-secondary">
                                        Sin ingresar
                                    </div>

                                <?php endif; ?>

                            </td>

                            <td>

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

                                    PDF

                                </div>

                                <div
                                    class="access-status-item"
                                    style="margin-top:6px;"
                                >

                                    <span
                                        class="access-status-dot <?= !empty(
                                            $acceso[
                                                'email_enviado_en'
                                            ]
                                        )
                                            ? 'is-ok'
                                            : '' ?>"
                                    ></span>

                                    Email

                                </div>

                            </td>

                            <td class="table-action-cell">

                                <a
                                    href="/public/admin/acceso-detalle.php?id=<?= (int) $acceso['id'] ?>"
                                    class="table-row-action"
                                    aria-label="Ver acceso"
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

<?php if ($paginas > 1): ?>

    <nav class="pagination">

        <?php if ($pagina > 1): ?>

            <a
                href="<?= htmlspecialchars(
                    urlAccesos([
                        'pagina' =>
                            $pagina - 1,
                    ])
                ) ?>"
                class="pagination-button"
            >
                ← Anterior
            </a>

        <?php endif; ?>

        <div class="pagination-info">

            Página

            <strong>
                <?= $pagina ?>
            </strong>

            de

            <strong>
                <?= $paginas ?>
            </strong>

        </div>

        <?php if ($pagina < $paginas): ?>

            <a
                href="<?= htmlspecialchars(
                    urlAccesos([
                        'pagina' =>
                            $pagina + 1,
                    ])
                ) ?>"
                class="pagination-button"
            >
                Siguiente →
            </a>

        <?php endif; ?>

    </nav>

<?php endif; ?>

<?php

$contenido =
    ob_get_clean();

require __DIR__
    . '/partials/layout.php';