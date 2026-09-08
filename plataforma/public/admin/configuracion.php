<?php

declare(strict_types=1);

use App\Repositories\EventoRepository;
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
    new EventoRepository($pdo);

/*
 * ---------------------------------------------------------
 * EVENTO ACTUAL
 * ---------------------------------------------------------
 *
 * Sigue fijo para la V1 del 6N26.
 * En la evolución multi-evento este ID
 * vendrá del contexto del evento activo.
 */

$eventoId = 1;

$evento =
    $repository->obtenerPorId(
        $eventoId
    );

if ($evento === null) {
    http_response_code(404);

    exit('Evento no encontrado.');
}

$errores = [];

/*
 * ---------------------------------------------------------
 * VALORES
 * ---------------------------------------------------------
 */

$nombre =
    trim(
        (string) (
            $_POST['nombre']
            ?? $evento['nombre']
        )
    );

$codigoPrefijo =
    trim(
        (string) (
            $_POST['codigo_prefijo']
            ?? $evento['codigo_prefijo']
        )
    );

$descripcion =
    trim(
        (string) (
            $_POST['descripcion']
            ?? $evento['descripcion']
            ?? ''
        )
    );

$fechaInicio =
    (string) (
        $_POST['fecha_inicio']
        ?? $evento['fecha_inicio']
        ?? ''
    );

$fechaFin =
    (string) (
        $_POST['fecha_fin']
        ?? $evento['fecha_fin']
        ?? ''
    );

$lugar =
    trim(
        (string) (
            $_POST['lugar']
            ?? $evento['lugar']
            ?? ''
        )
    );

$direccion =
    trim(
        (string) (
            $_POST['direccion']
            ?? $evento['direccion']
            ?? ''
        )
    );

$cupoTotal =
    (int) (
        $_POST['cupo_total']
        ?? $evento['cupo_total']
        ?? 0
    );

$minutosReserva =
    (int) (
        $_POST['minutos_reserva']
        ?? $evento['minutos_reserva']
        ?? 15
    );

$estado =
    (string) (
        $_POST['estado']
        ?? $evento['estado']
        ?? 'borrador'
    );

/*
 * Convertimos MySQL datetime
 * a formato datetime-local.
 */

function fechaParaInput(
    ?string $fecha
): string {
    if (
        $fecha === null
        || trim($fecha) === ''
    ) {
        return '';
    }

    $timestamp =
        strtotime($fecha);

    if ($timestamp === false) {
        return '';
    }

    return date(
        'Y-m-d\TH:i',
        $timestamp
    );
}

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    !== 'POST'
) {
    $fechaInicio =
        fechaParaInput(
            (string) (
                $evento['fecha_inicio']
                ?? ''
            )
        );

    $fechaFin =
        fechaParaInput(
            (string) (
                $evento['fecha_fin']
                ?? ''
            )
        );
}

/*
 * ---------------------------------------------------------
 * GUARDADO
 * ---------------------------------------------------------
 */

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    === 'POST'
) {
    if ($nombre === '') {
        $errores[] =
            'Ingresá el nombre del evento.';
    }

    if ($codigoPrefijo === '') {
        $errores[] =
            'Ingresá el código del evento.';
    }

    if ($fechaInicio === '') {
        $errores[] =
            'Ingresá la fecha de inicio.';
    }

    if ($lugar === '') {
        $errores[] =
            'Ingresá el lugar del evento.';
    }

    if ($direccion === '') {
        $errores[] =
            'Ingresá la dirección.';
    }

    if ($cupoTotal < 1) {
        $errores[] =
            'El cupo total debe ser mayor a cero.';
    }

    if (
        $minutosReserva < 1
        || $minutosReserva > 120
    ) {
        $errores[] =
            'Los minutos de reserva no son válidos.';
    }

    $estadosPermitidos = [
        'borrador',
        'activo',
        'finalizado',
    ];

    if (
        !in_array(
            $estado,
            $estadosPermitidos,
            true
        )
    ) {
        $errores[] =
            'El estado del evento no es válido.';
    }

    if (
        $fechaFin !== ''
        && strtotime($fechaFin)
            < strtotime($fechaInicio)
    ) {
        $errores[] =
            'La fecha de finalización no puede ser anterior al inicio.';
    }

    if ($errores === []) {
        try {
            $repository
                ->actualizarConfiguracion(
                    $eventoId,
                    [
                        'nombre' =>
                            $nombre,

                        'codigo_prefijo' =>
                            $codigoPrefijo,

                        'descripcion' =>
                            $descripcion,

                        'fecha_inicio' =>
                            str_replace(
                                'T',
                                ' ',
                                $fechaInicio
                            ) . ':00',

                        'fecha_fin' =>
                            $fechaFin !== ''
                                ? str_replace(
                                    'T',
                                    ' ',
                                    $fechaFin
                                ) . ':00'
                                : null,

                        'lugar' =>
                            $lugar,

                        'direccion' =>
                            $direccion,

                        'cupo_total' =>
                            $cupoTotal,

                        'minutos_reserva' =>
                            $minutosReserva,

                        'estado' =>
                            $estado,
                    ]
                );

            header(
                'Location: /public/admin/configuracion.php?guardado=1'
            );

            exit;

        } catch (Throwable $exception) {
            error_log(
                '[configuracion-evento] '
                . $exception->getMessage()
            );

            $errores[] =
                'No se pudo guardar la configuración del evento.';
        }
    }
}

/*
 * ---------------------------------------------------------
 * LAYOUT
 * ---------------------------------------------------------
 */

$titulo =
    'Información del evento';

$paginaActiva =
    'evento';

ob_start();

?>

<section class="page-header">

    <div>

        <a
            href="/public/admin/evento.php"
            class="back-link"
        >
            ← Evento
        </a>

        <div class="page-eyebrow">
            Evento 6N26
        </div>

        <h2 class="page-title">
            Información
        </h2>

        <p class="page-description">
            Configuración general y operativa
            del evento.
        </p>

    </div>

</section>

<?php if (
    ($_GET['guardado'] ?? '')
    === '1'
): ?>

    <div class="admin-success-message">
        Configuración actualizada correctamente.
    </div>

<?php endif; ?>

<?php if ($errores !== []): ?>

    <div class="admin-error-message">

        <?php foreach ($errores as $error): ?>

            <div>
                <?= htmlspecialchars(
                    $error
                ) ?>
            </div>

        <?php endforeach; ?>

    </div>

<?php endif; ?>

<form
    method="post"
    class="event-settings"
    novalidate
>

    <!--
    =========================================================
    IDENTIDAD
    =========================================================
    -->

    <section class="admin-form-panel">

        <div class="section-eyebrow">
            Identidad
        </div>

        <h3 class="section-title">
            Información general
        </h3>

        <div class="event-settings-fields">

            <div class="admin-form-grid">

                <div class="admin-field">

                    <label for="nombre">
                        Nombre
                    </label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        value="<?= htmlspecialchars(
                            $nombre
                        ) ?>"
                        required
                    >

                </div>

                <div class="admin-field">

                    <label for="codigo_prefijo">
                        Código
                    </label>

                    <input
                        type="text"
                        id="codigo_prefijo"
                        value="<?= htmlspecialchars(
                            $codigoPrefijo
                        ) ?>"
                        readonly
                        disabled
                    >

                    <div class="admin-field-help">
                        Identificador técnico del evento.
                        No puede modificarse una vez creado.
                    </div>

                </div>

            </div>

            <div class="admin-field">

                <label for="descripcion">
                    Descripción
                </label>

                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="4"
                ><?= htmlspecialchars(
                    $descripcion
                ) ?></textarea>

            </div>

        </div>

    </section>

    <!--
    =========================================================
    FECHA Y LUGAR
    =========================================================
    -->

    <section class="admin-form-panel">

        <div class="section-eyebrow">
            Evento
        </div>

        <h3 class="section-title">
            Fecha y ubicación
        </h3>

        <div class="event-settings-fields">

            <div class="admin-form-grid">

                <div class="admin-field">

                    <label for="fecha_inicio">
                        Inicio
                    </label>

                    <input
                        type="datetime-local"
                        id="fecha_inicio"
                        name="fecha_inicio"
                        value="<?= htmlspecialchars(
                            $fechaInicio
                        ) ?>"
                        required
                    >

                </div>

                <div class="admin-field">

                    <label for="fecha_fin">
                        Finalización
                    </label>

                    <input
                        type="datetime-local"
                        id="fecha_fin"
                        name="fecha_fin"
                        value="<?= htmlspecialchars(
                            $fechaFin
                        ) ?>"
                    >

                </div>

            </div>

            <div class="admin-form-grid">

                <div class="admin-field">

                    <label for="lugar">
                        Lugar
                    </label>

                    <input
                        type="text"
                        id="lugar"
                        name="lugar"
                        value="<?= htmlspecialchars(
                            $lugar
                        ) ?>"
                        required
                    >

                </div>

                <div class="admin-field">

                    <label for="direccion">
                        Dirección
                    </label>

                    <input
                        type="text"
                        id="direccion"
                        name="direccion"
                        value="<?= htmlspecialchars(
                            $direccion
                        ) ?>"
                        required
                    >

                </div>

            </div>

        </div>

    </section>

    <!--
    =========================================================
    OPERACIÓN
    =========================================================
    -->

    <section class="admin-form-panel">

        <div class="section-eyebrow">
            Operación
        </div>

        <h3 class="section-title">
            Capacidad y reservas
        </h3>

        <div class="event-settings-fields">

            <div class="admin-form-grid">

                <div class="admin-field">

                    <label for="cupo_total">
                        Cupo total
                    </label>

                    <input
                        type="number"
                        id="cupo_total"
                        name="cupo_total"
                        min="1"
                        value="<?= $cupoTotal ?>"
                        required
                    >

                </div>

                <div class="admin-field">

                    <label for="minutos_reserva">
                        Minutos de reserva
                    </label>

                    <input
                        type="number"
                        id="minutos_reserva"
                        name="minutos_reserva"
                        min="1"
                        max="120"
                        value="<?= $minutosReserva ?>"
                        required
                    >

                </div>

            </div>

            <div class="admin-field">

                <label for="estado">
                    Estado del evento
                </label>

                <select
                    id="estado"
                    name="estado"
                    required
                >

                    <option
                        value="borrador"
                        <?= $estado === 'borrador'
                            ? 'selected'
                            : '' ?>
                    >
                        Borrador
                    </option>

                    <option
                        value="activo"
                        <?= $estado === 'activo'
                            ? 'selected'
                            : '' ?>
                    >
                        Activo
                    </option>

                    <option
                        value="finalizado"
                        <?= $estado === 'finalizado'
                            ? 'selected'
                            : '' ?>
                    >
                        Finalizado
                    </option>

                </select>

            </div>

        </div>

    </section>

    <!--
    =========================================================
    ACCIONES
    =========================================================
    -->

    <div class="admin-form-actions">

        <a
            href="/public/admin/evento.php"
            class="admin-secondary-button form-button"
        >
            Cancelar
        </a>

        <button
            type="submit"
            class="admin-primary-button form-button"
        >
            Guardar cambios
        </button>

    </div>

</form>

<?php

$contenido =
    ob_get_clean();

require __DIR__
    . '/partials/layout.php';