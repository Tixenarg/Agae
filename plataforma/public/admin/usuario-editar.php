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
    new AdminUsuarioRepository($pdo);

$id =
    (int) (
        $_GET['id']
        ?? 0
    );

$usuarioEditar =
    $repository->obtenerPorId($id);

if ($usuarioEditar === null) {
    http_response_code(404);

    exit('Usuario no encontrado.');
}

$roles =
    $repository->obtenerRoles();

$errores = [];

$nombre =
    trim(
        (string) (
            $_POST['nombre']
            ?? $usuarioEditar['nombre']
        )
    );

$apellido =
    trim(
        (string) (
            $_POST['apellido']
            ?? $usuarioEditar['apellido']
        )
    );

$email =
    trim(
        (string) (
            $_POST['email']
            ?? $usuarioEditar['email']
        )
    );

$rolId =
    (int) (
        $_POST['rol_id']
        ?? $usuarioEditar['rol_id']
    );

$activo =
    (int) (
        $_POST['activo']
        ?? $usuarioEditar['activo']
    );

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    === 'POST'
) {
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
        !in_array(
            $activo,
            [0, 1],
            true
        )
    ) {
        $errores[] =
            'El estado del usuario no es válido.';
    }

    $emailActual =
        mb_strtolower(
            trim(
                (string) $usuarioEditar['email']
            )
        );

    $emailNuevo =
        mb_strtolower(
            trim($email)
        );

    if (
        $emailNuevo !== $emailActual
        && $repository
            ->existeEmail($emailNuevo)
    ) {
        $errores[] =
            'Ya existe otro usuario con ese email.';
    }

    $usuarioActualId =
        (int) (
            $usuario['id']
            ?? 0
        );

    if (
        $id === $usuarioActualId
        && $activo === 0
    ) {
        $errores[] =
            'No podés desactivar tu propio usuario.';
    }

    if ($errores === []) {
        try {
            $repository->actualizar(
                $id,
                [
                    'nombre' =>
                        $nombre,

                    'apellido' =>
                        $apellido,

                    'email' =>
                        $email,

                    'rol_id' =>
                        $rolId,

                    'activo' =>
                        $activo,
                ]
            );

            header(
                'Location: /public/admin/usuarios.php?actualizado=1'
            );

            exit;

        } catch (Throwable $exception) {
            error_log(
                '[usuario-editar] '
                . $exception->getMessage()
            );

            $errores[] =
                'No se pudo actualizar el usuario.';
        }
    }
}

$titulo =
    'Editar usuario';

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
            Editar usuario
        </h2>

        <p class="page-description">
            Modificá los datos y permisos
            de este usuario.
        </p>

    </div>

</section>

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

        <div class="admin-form-grid">

            <div class="admin-field">

                <label for="rol_id">
                    Rol
                </label>

                <select
                    id="rol_id"
                    name="rol_id"
                    required
                >

                    <?php foreach ($roles as $rol): ?>

                        <option
                            value="<?= (int) $rol['id'] ?>"
                            <?= $rolId === (int) $rol['id']
                                ? 'selected'
                                : '' ?>
                        >
                            <?= htmlspecialchars(
                                (string) $rol['nombre']
                            ) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="admin-field">

                <label for="activo">
                    Estado
                </label>

                <select
                    id="activo"
                    name="activo"
                    required
                >

                    <option
                        value="1"
                        <?= $activo === 1
                            ? 'selected'
                            : '' ?>
                    >
                        Activo
                    </option>

                    <option
                        value="0"
                        <?= $activo === 0
                            ? 'selected'
                            : '' ?>
                    >
                        Inactivo
                    </option>

                </select>

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
                Guardar cambios
            </button>

        </div>

    </form>

</section>

<?php

$contenido =
    ob_get_clean();

require __DIR__
    . '/partials/layout.php';