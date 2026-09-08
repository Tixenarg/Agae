<?php

declare(strict_types=1);

$nombreCompleto =
    trim(
        (string) $usuario['nombre']
        . ' '
        . (string) $usuario['apellido']
    );

function navActivo(
    string $pagina,
    string $paginaActiva
): string {
    return $pagina === $paginaActiva
        ? ' is-active'
        : '';
}

?>

<aside
    class="admin-sidebar"
    id="admin-sidebar"
>

    <div class="sidebar-top">

        <a
            href="/public/admin/dashboard.php"
            class="sidebar-brand"
        >
            <span class="sidebar-brand-name">
                AGAE
            </span>

            <span class="sidebar-brand-product">
                Platform
            </span>
        </a>

        <div class="sidebar-event">
            <span class="sidebar-event-dot"></span>

            <div>
                <div class="sidebar-event-code">
                    6N26
                </div>

                <div class="sidebar-event-name">
                    Evento 6N
                </div>
            </div>
        </div>

    </div>

    <nav class="sidebar-nav">

        <a
            href="/public/admin/dashboard.php"
            class="sidebar-link<?= navActivo(
                'dashboard',
                $paginaActiva
            ) ?>"
        >
            Dashboard
        </a>

        <div class="sidebar-group-label">
            Evento
        </div>

        <a
            href="/public/admin/ordenes.php"
            class="sidebar-link<?= navActivo(
                'ordenes',
                $paginaActiva
            ) ?>"
        >
            Órdenes
        </a>

        <a
            href="/public/admin/accesos.php"
            class="sidebar-link<?= navActivo(
                'accesos',
                $paginaActiva
            ) ?>"
        >
            Accesos
        </a>

        <a
            href="/public/admin/checkin.php"
            class="sidebar-link<?= navActivo(
                'checkin',
                $paginaActiva
            ) ?>"
        >
            Check-in
        </a>

        <div class="sidebar-group-label">
            Comunicación
        </div>

        <a
            href="/public/admin/emails.php"
            class="sidebar-link<?= navActivo(
                'emails',
                $paginaActiva
            ) ?>"
        >
            Emails
        </a>

        <div class="sidebar-group-label">
            Administración
        </div>

        <a
            href="/public/admin/usuarios.php"
            class="sidebar-link<?= navActivo(
                'usuarios',
                $paginaActiva
            ) ?>"
        >
            Usuarios
        </a>

        <a
            href="/public/admin/evento.php"
            class="sidebar-link<?= navActivo(
                'evento',
                $paginaActiva
            ) ?>"
        >
            Evento
        </a>

    </nav>

    <div class="sidebar-user">

        <div class="sidebar-avatar">
            <?= htmlspecialchars(
                strtoupper(
                    substr(
                        (string) $usuario['nombre'],
                        0,
                        1
                    )
                )
            ) ?>
        </div>

        <div class="sidebar-user-info">

            <div class="sidebar-user-name">
                <?= htmlspecialchars(
                    $nombreCompleto
                ) ?>
            </div>

            <div class="sidebar-user-role">
                <?= htmlspecialchars(
                    (string) $usuario[
                        'rol_nombre'
                    ]
                ) ?>
            </div>

        </div>

        <a
            href="/public/admin/logout.php"
            class="sidebar-logout"
            title="Cerrar sesión"
        >
            Salir
        </a>

    </div>

</aside>