<?php

declare(strict_types=1);

use App\Repositories\AdminOrdenRepository;
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

/*
 * ---------------------------------------------------------
 * EVENTO
 * ---------------------------------------------------------
 */

$eventoId = 1;

/*
 * ---------------------------------------------------------
 * FILTROS
 * ---------------------------------------------------------
 */

$estadosPermitidos = [
    'pendiente_pago',
    'pagada',
    'expirada',
    'cancelada',
    'pago_en_revision',
    'reembolsada',
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

$busqueda =
    trim(
        (string) (
            $_GET['q']
            ?? ''
        )
    );

$pagina =
    max(
        1,
        (int) (
            $_GET['pagina']
            ?? 1
        )
    );

$porPagina = 50;

/*
 * ---------------------------------------------------------
 * DATOS
 * ---------------------------------------------------------
 */

$repository =
    new AdminOrdenRepository(
        $pdo
    );

$listado =
    $repository->listar(
        $eventoId,
        $estado !== ''
            ? $estado
            : null,
        $busqueda,
        $pagina,
        $porPagina
    );

$contadores =
    $repository
        ->contarPorEstado(
            $eventoId
        );

$ordenes =
    $listado['items'];

$total =
    (int) $listado['total'];

$paginas =
    (int) $listado['paginas'];

/*
 * ---------------------------------------------------------
 * HELPERS
 * ---------------------------------------------------------
 */

function etiquetaEstadoOrden(
    string $estado
): string {
    return match ($estado) {
        'pagada' =>
            'Pagada',

        'pendiente_pago' =>
            'Pendiente',

        'expirada' =>
            'Expirada',

        'cancelada' =>
            'Cancelada',

        'pago_en_revision' =>
            'En revisión',

        'reembolsada' =>
            'Reembolsada',

        default =>
            ucfirst($estado),
    };
}

function claseEstadoOrden(
    string $estado
): string {
    return match ($estado) {
        'pagada' =>
            'success',

        'pendiente_pago' =>
            'warning',

        'pago_en_revision' =>
            'info',

        'cancelada',
        'expirada',
        'reembolsada' =>
            'neutral',

        default =>
            'neutral',
    };
}

function urlOrdenes(
    array $cambios
): string {
    $actual = [
        'estado' =>
            $_GET['estado']
            ?? '',

        'q' =>
            $_GET['q']
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

    return '/public/admin/ordenes.php?'
        . http_build_query(
            $params
        );
}

/*
 * ---------------------------------------------------------
 * LAYOUT
 * ---------------------------------------------------------
 */

$titulo =
    'Órdenes';

$paginaActiva =
    'ordenes';

ob_start();

?>

<section class="page-header">

    <div>

        <div class="page-eyebrow">
            Evento 6N26
        </div>

        <h2 class="page-title">
            Órdenes
        </h2>

        <p class="page-description">
            Compras y operaciones registradas
            para el evento.
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

<!--
=========================================================
FILTROS
=========================================================
-->

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

        <input
            type="search"
            name="q"
            value="<?= htmlspecialchars(
                $busqueda
            ) ?>"
            placeholder="Buscar código, nombre o email..."
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
            urlOrdenes([
                'estado' => '',
                'pagina' => 1,
            ])
        ) ?>"
        class="filter-tab<?= $estado === ''
            ? ' is-active'
            : '' ?>"
    >
        Todos
    </a>

    <a
        href="<?= htmlspecialchars(
            urlOrdenes([
                'estado' =>
                    'pagada',
                'pagina' => 1,
            ])
        ) ?>"
        class="filter-tab<?= $estado === 'pagada'
            ? ' is-active'
            : '' ?>"
    >
        Pagadas

        <span>
            <?= (int) (
                $contadores['pagada']
                ?? 0
            ) ?>
        </span>
    </a>

    <a
        href="<?= htmlspecialchars(
            urlOrdenes([
                'estado' =>
                    'pendiente_pago',
                'pagina' => 1,
            ])
        ) ?>"
        class="filter-tab<?= $estado === 'pendiente_pago'
            ? ' is-active'
            : '' ?>"
    >
        Pendientes

        <span>
            <?= (int) (
                $contadores[
                    'pendiente_pago'
                ]
                ?? 0
            ) ?>
        </span>
    </a>

    <a
        href="<?= htmlspecialchars(
            urlOrdenes([
                'estado' =>
                    'expirada',
                'pagina' => 1,
            ])
        ) ?>"
        class="filter-tab<?= $estado === 'expirada'
            ? ' is-active'
            : '' ?>"
    >
        Expiradas

        <span>
            <?= (int) (
                $contadores['expirada']
                ?? 0
            ) ?>
        </span>
    </a>

    <a
        href="<?= htmlspecialchars(
            urlOrdenes([
                'estado' =>
                    'cancelada',
                'pagina' => 1,
            ])
        ) ?>"
        class="filter-tab<?= $estado === 'cancelada'
            ? ' is-active'
            : '' ?>"
    >
        Canceladas

        <span>
            <?= (int) (
                $contadores['cancelada']
                ?? 0
            ) ?>
        </span>
    </a>

</nav>

<!--
=========================================================
TABLA
=========================================================
-->

<section class="data-panel">

    <?php if ($ordenes === []): ?>

        <div class="data-empty">

            <strong>
                No encontramos órdenes.
            </strong>

            <span>
                Probá cambiando los filtros
                o la búsqueda.
            </span>

        </div>

    <?php else: ?>

        <div class="data-table-wrapper">

            <table class="data-table">

                <thead>

                    <tr>

                        <th>
                            Orden
                        </th>

                        <th>
                            Comprador
                        </th>

                        <th>
                            Estado
                        </th>

                        <th>
                            Accesos
                        </th>

                        <th>
                            Total
                        </th>

                        <th>
                            Fecha
                        </th>

                        <th></th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($ordenes as $orden): ?>

                        <?php

                        $nombreComprador =
                            trim(
                                (string) (
                                    $orden[
                                        'comprador_nombre'
                                    ] ?? ''
                                )
                                . ' '
                                . (string) (
                                    $orden[
                                        'comprador_apellido'
                                    ] ?? ''
                                )
                            );

                        $estadoOrden =
                            (string) $orden[
                                'estado'
                            ];

                        $fecha =
                            new DateTimeImmutable(
                                (string) $orden[
                                    'creado_en'
                                ]
                            );

                        ?>

                        <tr>

                            <td>

                                <div class="table-primary">
                                    <?= htmlspecialchars(
                                        (string) $orden[
                                            'codigo'
                                        ]
                                    ) ?>
                                </div>

                                <div class="table-secondary">
                                    <?= htmlspecialchars(
                                        (string) $orden[
                                            'origen'
                                        ]
                                    ) ?>
                                </div>

                            </td>

                            <td>

                                <div class="table-primary">
                                    <?= htmlspecialchars(
                                        $nombreComprador !== ''
                                            ? $nombreComprador
                                            : 'Sin comprador'
                                    ) ?>
                                </div>

                                <div class="table-secondary">
                                    <?= htmlspecialchars(
                                        (string) (
                                            $orden[
                                                'comprador_email'
                                            ]
                                            ?? ''
                                        )
                                    ) ?>
                                </div>

                            </td>

                            <td>

                                <span
                                    class="
                                        status-badge
                                        status-<?= htmlspecialchars(
                                            claseEstadoOrden(
                                                $estadoOrden
                                            )
                                        ) ?>
                                    "
                                >
                                    <?= htmlspecialchars(
                                        etiquetaEstadoOrden(
                                            $estadoOrden
                                        )
                                    ) ?>
                                </span>

                            </td>

                            <td>
                                <?= number_format(
                                    (int) $orden[
                                        'cantidad_accesos'
                                    ],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </td>

                            <td>

                                <div class="table-primary">
                                    $<?= number_format(
                                        (float) $orden[
                                            'total'
                                        ],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </div>

                                <div class="table-secondary">
                                    <?= htmlspecialchars(
                                        (string) $orden[
                                            'moneda'
                                        ]
                                    ) ?>
                                </div>

                            </td>

                            <td>

                                <div class="table-primary">
                                    <?= $fecha->format(
                                        'd/m/Y'
                                    ) ?>
                                </div>

                                <div class="table-secondary">
                                    <?= $fecha->format(
                                        'H:i'
                                    ) ?> h
                                </div>

                            </td>

                            <td class="table-action-cell">

                                <a
                                    href="/public/admin/orden-detalle.php?id=<?= (int) $orden['id'] ?>"
                                    class="table-row-action"
                                    aria-label="Ver orden"
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

<!--
=========================================================
PAGINACIÓN
=========================================================
-->

<?php if ($paginas > 1): ?>

    <nav class="pagination">

        <?php if ($pagina > 1): ?>

            <a
                href="<?= htmlspecialchars(
                    urlOrdenes([
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
                    urlOrdenes([
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