<?php

declare(strict_types=1);

use App\Repositories\AdminUsuarioRepository;
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
    new AdminUsuarioRepository(
        $pdo
    );

$roles =
    $repository->obtenerRoles();

$errores = [];

$nombre =
    trim(
        (string) (
            $_POST['nombre']
            ?? ''
        )
    );

$apellido =
    trim(
        (string) (
            $_POST['apellido']
            ?? ''
        )
    );

$email =
    trim(
        (string) (
            $_POST['email']
            ?? ''
        )
    );

$rolId =
    (int) (
        $_POST['rol_id']
        ?? 0
    );

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    === 'POST'
) {
    $password =
        (string) (
            $_POST['password']
            ?? ''
        );

    $passwordConfirmacion =
        (string) (
            $_POST['password_confirmacion']
            ?? ''
        );

    if ($nombre === '') {
        $errores[] =
            'Ingresá el nombre.';
    }

    if ($apellido === '') {
        $errores[] =
            'Ingresá el apellido.';
    }

    if (
        $email === ''
        || !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        $errores[] =
            'Ingresá un email válido.';
    }

    if ($rolId < 1) {
        $errores[] =
            'Seleccioná un rol.';
    }

    if (
        mb_strlen($password)
        < 8
    ) {
        $errores[] =
            'La contraseña debe tener al menos 8 caracteres.';
    }

    if (
        $password
        !== $passwordConfirmacion
    ) {
        $errores[] =
            'Las contraseñas no coinciden.';
    }

    if (
        $email !== ''
        && $repository
            ->existeEmail($email)
    ) {
        $errores[] =
            'Ya existe un usuario con ese email.';
    }

    if ($errores === []) {
        try {
            $repository->crear([
                'nombre' =>
                    $nombre,

                'apellido' =>
                    $apellido,

                'email' =>
                    $email,

                'rol_id' =>
                    $rolId,

                'password' =>
                    $password,
            ]);

            header(
                'Location: /public/admin/usuarios.php?creado=1'
            );

            exit;

        } catch (Throwable $exception) {
            error_log(
                '[usuario-nuevo] '
                . $exception->getMessage()
            );

            $errores[] =
                'No se pudo crear el usuario.';
        }
    }
}

$titulo =
    'Nuevo usuario';

$paginaActiva =
    'usuarios';

ob_start();

?>

<section class="page-header">

    <div>

        <a
            href="/public/admin/usuarios.php"
            class="back-link"
        >
            ← Usuarios
        </a>

        <div class="page-eyebrow">
            Administración
        </div>

        <h2 class="page-title">
            Nuevo usuario
        </h2>

        <p class="page-description">
            Creá un administrador u operador
            de la plataforma.
        </p>

    </div>

</section>

<?php if (
    ($_GET['creado'] ?? '')
    === '1'
): ?>

    <div class="admin-success-message">
        Usuario creado correctamente.
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

<section class="admin-form-panel">

    <form
        method="post"
        class="admin-form"
        novalidate
    >

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
                    autocomplete="given-name"
                >

            </div>

            <div class="admin-field">

                <label for="apellido">
                    Apellido
                </label>

                <input
                    type="text"
                    id="apellido"
                    name="apellido"
                    value="<?= htmlspecialchars(
                        $apellido
                    ) ?>"
                    required
                    autocomplete="family-name"
                >

            </div>

        </div>

        <div class="admin-field">

            <label for="email">
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars(
                    $email
                ) ?>"
                required
                autocomplete="email"
            >

        </div>

        <div class="admin-field">

            <label for="rol_id">
                Rol
            </label>

            <select
                id="rol_id"
                name="rol_id"
                required
            >

                <option value="">
                    Seleccionar rol
                </option>

                <?php foreach ($roles as $rol): ?>

                    <option
                        value="<?= (int) $rol['id'] ?>"
                        <?= $rolId === (int) $rol['id']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= htmlspecialchars(
                            (string) $rol[
                                'nombre'
                            ]
                        ) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <div class="admin-form-grid">

            <div class="admin-field">

                <label for="password">
                    Contraseña
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                >

                <div class="admin-field-help">
                    Mínimo 8 caracteres.
                </div>

            </div>

            <div class="admin-field">

                <label for="password_confirmacion">
                    Confirmar contraseña
                </label>

                <input
                    type="password"
                    id="password_confirmacion"
                    name="password_confirmacion"
                    required
                    autocomplete="new-password"
                >

            </div>

        </div>

        <div class="admin-form-actions">

            <a
                href="/public/admin/usuarios.php"
                class="admin-secondary-button form-button"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="admin-primary-button form-button"
            >
                Crear usuario
            </button>

        </div>

    </form>

</section>

<?php

$contenido =
    ob_get_clean();

require __DIR__
    . '/partials/layout.php';