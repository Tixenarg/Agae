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

$usuarios =
    $repository->listar();

$titulo =
    'Usuarios';

$paginaActiva =
    'usuarios';

ob_start();

?>

<section class="page-header">

    <div>

        <div class="page-eyebrow">
            Administración
        </div>

        <h2 class="page-title">
            Usuarios
        </h2>

        <p class="page-description">
            Gestión de administradores
            y operadores de la plataforma.
        </p>

    </div>

    <a
        href="/public/admin/usuario-nuevo.php"
        class="admin-primary-button access-action-link"
    >
        Nuevo usuario
    </a>

</section>

<?php if (
    ($_GET['creado'] ?? '')
    === '1'
): ?>

    <div class="admin-success-message">
        Usuario creado correctamente.
    </div>

<?php endif; ?>

<?php if (
    ($_GET['actualizado'] ?? '')
    === '1'
): ?>

    <div class="admin-success-message">
        Usuario actualizado correctamente.
    </div>

<?php endif; ?>

<section class="data-panel">

    <?php if ($usuarios === []): ?>

        <div class="data-empty">

            <strong>
                No hay usuarios registrados.
            </strong>

            <span>
                Creá el primer usuario
                desde el botón superior.
            </span>

        </div>

    <?php else: ?>

        <div class="data-table-wrapper">

            <table class="data-table">

                <thead>

                    <tr>

                        <th>
                            Usuario
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Rol
                        </th>

                        <th>
                            Estado
                        </th>

                        <th>
                            Último acceso
                        </th>

                        <th></th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($usuarios as $item): ?>

                        <tr>

                            <td>

                                <div class="table-primary">
                                    <?= htmlspecialchars(
                                        trim(
                                            (string) $item['nombre']
                                            . ' '
                                            . (string) $item['apellido']
                                        )
                                    ) ?>
                                </div>

                                <div class="table-secondary">
                                    ID #<?= (int) $item['id'] ?>
                                </div>

                            </td>

                            <td>

                                <div class="table-primary">
                                    <?= htmlspecialchars(
                                        (string) $item['email']
                                    ) ?>
                                </div>

                            </td>

                            <td>

                                <div class="table-primary">
                                    <?= htmlspecialchars(
                                        (string) $item[
                                            'rol_nombre'
                                        ]
                                    ) ?>
                                </div>

                            </td>

                            <td>

                                <?php if (
                                    (int) $item['activo']
                                    === 1
                                ): ?>

                                    <span
                                        class="
                                            status-badge
                                            status-success
                                        "
                                    >
                                        Activo
                                    </span>

                                <?php else: ?>

                                    <span
                                        class="
                                            status-badge
                                            status-neutral
                                        "
                                    >
                                        Inactivo
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <?php if (
                                    !empty(
                                        $item[
                                            'ultimo_acceso_en'
                                        ]
                                    )
                                ): ?>

                                    <?php

                                    $ultimoAcceso =
                                        new DateTimeImmutable(
                                            (string) $item[
                                                'ultimo_acceso_en'
                                            ]
                                        );

                                    ?>

                                    <div class="table-primary">
                                        <?= $ultimoAcceso
                                            ->format(
                                                'd/m/Y'
                                            ) ?>
                                    </div>

                                    <div class="table-secondary">
                                        <?= $ultimoAcceso
                                            ->format(
                                                'H:i'
                                            ) ?> h
                                    </div>

                                <?php else: ?>

                                    <div class="table-secondary">
                                        Nunca
                                    </div>

                                <?php endif; ?>

                            </td>

                            <td class="table-action-cell">

                                <a
                                    href="/public/admin/usuario-editar.php?id=<?= (int) $item['id'] ?>"
                                    class="table-row-action"
                                    aria-label="Editar usuario"
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