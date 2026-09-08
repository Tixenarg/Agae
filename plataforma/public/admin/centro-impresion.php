<?php

declare(strict_types=1);

use App\Repositories\AdminImpresionRepository;
use App\Support\AdminGuard;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

$usuario =
    AdminGuard::requiereRol([
        'administrador_general',
    ]);

/** @var PDO $pdo */
$pdo =
    require dirname(__DIR__, 2)
        . '/config/database.php';

$repository =
    new AdminImpresionRepository(
        $pdo
    );

/*
 * ---------------------------------------------------------
 * EVENTO
 * ---------------------------------------------------------
 *
 * Por ahora usamos el evento 1.
 * Más adelante esto vendrá del evento activo
 * o del selector multi-evento.
 */

$eventoId = 1;

$categorias =
    $repository
        ->obtenerCategoriasEvento(
            $eventoId
        );

/*
 * ---------------------------------------------------------
 * LAYOUT
 * ---------------------------------------------------------
 */

$titulo =
    'Centro de impresión';

$paginaActiva =
    'evento';

ob_start();

?>

<section class="page-header">

    <div>

        <div class="page-eyebrow">
            Evento 6N26
        </div>

        <h2 class="page-title">
            Centro de impresión
        </h2>

        <p class="page-description">
            Generá listados de asistentes
            para acreditación, contingencias
            y soporte operativo.
        </p>

    </div>

</section>

<form
    method="post"
    action="#"
    class="print-center"
>

    <!--
    =========================================================
    CATEGORÍAS
    =========================================================
    -->

    <section class="print-panel">

        <div class="print-panel-header">

            <div>

                <div class="section-eyebrow">
                    Selección
                </div>

                <h3 class="section-title">
                    Categorías
                </h3>

                <p class="print-panel-description">
                    Elegí qué tipos de acceso
                    querés incluir en la exportación.
                </p>

            </div>

            <div class="print-selection-actions">

                <button
                    type="button"
                    class="print-link-button"
                    id="select-all-categories"
                >
                    Seleccionar todas
                </button>

                <button
                    type="button"
                    class="print-link-button"
                    id="clear-all-categories"
                >
                    Quitar todas
                </button>

            </div>

        </div>

        <?php if ($categorias === []): ?>

            <div class="data-empty">

                <strong>
                    No hay categorías disponibles.
                </strong>

                <span>
                    El evento todavía no tiene
                    tipos de acceso configurados.
                </span>

            </div>

        <?php else: ?>

            <div class="print-category-grid">

                <?php foreach ($categorias as $categoria): ?>

                    <?php

                    $cantidadTotal =
                        (int) (
                            $categoria[
                                'cantidad_total'
                            ]
                            ?? 0
                        );

                    $cantidadIngresados =
                        (int) (
                            $categoria[
                                'cantidad_ingresados'
                            ]
                            ?? 0
                        );

                    $cantidadPendientes =
                        (int) (
                            $categoria[
                                'cantidad_pendientes'
                            ]
                            ?? 0
                        );

                    ?>

                    <label class="print-category-card">

                        <input
                            type="checkbox"
                            name="categorias[]"
                            value="<?= (int) $categoria['id'] ?>"
                            checked
                        >

                        <div class="print-category-check">
                            ✓
                        </div>

                        <div class="print-category-content">

                            <div class="print-category-header">

                                <div class="print-category-name">
                                    <?= htmlspecialchars(
                                        (string) $categoria[
                                            'nombre'
                                        ]
                                    ) ?>
                                </div>

                                <div class="print-category-count">
                                    <?= number_format(
                                        $cantidadTotal,
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </div>

                            </div>

                            <div class="print-category-stats">

                                <span>
                                    <?= number_format(
                                        $cantidadIngresados,
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                    ingresados
                                </span>

                                <span class="print-category-dot">
                                    ·
                                </span>

                                <span>
                                    <?= number_format(
                                        $cantidadPendientes,
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                    pendientes
                                </span>

                            </div>

                        </div>

                    </label>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

    <!--
    =========================================================
    OPCIONES
    =========================================================
    -->

    <section class="print-panel">

        <div class="print-panel-header">

            <div>

                <div class="section-eyebrow">
                    Contenido
                </div>

                <h3 class="section-title">
                    Opciones del listado
                </h3>

                <p class="print-panel-description">
                    Definí qué información debe
                    aparecer en el PDF.
                </p>

            </div>

        </div>

        <div class="print-options-grid">

            <label class="print-option">

                <input
                    type="checkbox"
                    name="incluir_codigo"
                    value="1"
                    checked
                >

                <div>

                    <div class="print-option-title">
                        Código de acceso
                    </div>

                    <div class="print-option-description">
                        Incluye el código individual
                        de cada acceso.
                    </div>

                </div>

            </label>

            <label class="print-option">

                <input
                    type="checkbox"
                    name="incluir_dni"
                    value="1"
                    checked
                >

                <div>

                    <div class="print-option-title">
                        DNI
                    </div>

                    <div class="print-option-description">
                        Muestra el documento cuando
                        esté disponible.
                    </div>

                </div>

            </label>

            <label class="print-option">

                <input
                    type="checkbox"
                    name="check_manual"
                    value="1"
                    checked
                >

                <div>

                    <div class="print-option-title">
                        Casilla manual
                    </div>

                    <div class="print-option-description">
                        Agrega una casilla para marcar
                        ingresos sobre papel.
                    </div>

                </div>

            </label>

            <label class="print-option">

                <input
                    type="checkbox"
                    name="excluir_anulados"
                    value="1"
                    checked
                >

                <div>

                    <div class="print-option-title">
                        Excluir anulados
                    </div>

                    <div class="print-option-description">
                        No incluye accesos anulados
                        ni reembolsados.
                    </div>

                </div>

            </label>

            <label class="print-option">

                <input
                    type="checkbox"
                    name="solo_pendientes"
                    value="1"
                >

                <div>

                    <div class="print-option-title">
                        Solo sin check-in
                    </div>

                    <div class="print-option-description">
                        Genera únicamente los accesos
                        que todavía no ingresaron.
                    </div>

                </div>

            </label>

        </div>

    </section>

    <!--
    =========================================================
    REGLA DE ORDEN
    =========================================================
    -->

    <section class="print-panel">

        <div class="print-panel-header">

            <div>

                <div class="section-eyebrow">
                    Ordenamiento
                </div>

                <h3 class="section-title">
                    Orden alfabético
                </h3>

                <p class="print-panel-description">
                    Los accesos con nombre se ordenan
                    por apellido de A a Z.
                    Los accesos sin asignar se ubican
                    debajo del titular de la compra.
                </p>

            </div>

        </div>

        <div class="print-rule-card">

            <div class="print-rule-example">

                <div class="print-rule-person">
                    García, Ana
                </div>

                <div class="print-rule-person">
                    López, Martín
                </div>

                <div class="print-rule-person">
                    Pérez, Juan
                </div>

                <div class="print-rule-unassigned">
                    ↳ Acceso sin asignar
                </div>

                <div class="print-rule-unassigned">
                    ↳ Acceso sin asignar
                </div>

            </div>

        </div>

    </section>

    <!--
    =========================================================
    ACCIONES
    =========================================================
    -->

    <section class="print-panel print-actions-panel">

        <div>

            <div class="section-eyebrow">
                Exportación
            </div>

            <h3 class="section-title">
                Generar listado
            </h3>

            <p class="print-panel-description">
                Primero podés revisar una vista previa
                y después descargar el PDF definitivo.
            </p>

        </div>

        <div class="print-actions">

            <button
                type="button"
                class="admin-secondary-button print-action-button"
                disabled
            >
                Vista previa
            </button>

            <button
                type="button"
                class="admin-primary-button print-action-button"
                disabled
            >
                Descargar PDF
            </button>

        </div>

    </section>

</form>

<?php

$contenido =
    ob_get_clean();

require __DIR__
    . '/partials/layout.php';