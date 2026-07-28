<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Guardián de Sesión
if (!isset($_SESSION['id_usuario']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Detectamos la página actual para el resaltado del menú
$paginaActual = basename($_SERVER['PHP_SELF']);

// Construimos Apellido y Nombre si existen por separado, o tomamos la clave general de sesión
/* if (!empty($_SESSION['apellido']) && !empty($_SESSION['nombre'])) {
    $nombreUsuario = $_SESSION['apellido'] . ', ' . $_SESSION['nombre'];
} else {
    $nombreUsuario = $_SESSION['nombre_usuario'] 
        ?? $_SESSION['nombre'] 
        ?? $_SESSION['usuario'] 
        ?? 'Usuario';
} */
$nombreUsuario = $_SESSION['usu_nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGAE - Panel de Administración</title>

    <!-- 1. FAVICON (Ubicado en /assets/favicon.png) -->
    <link rel="shortcut icon" href="../../assets/favicon.png" type="image/png">
    <link rel="icon" href="../../assets/favicon.png" type="image/png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- En vistas/admin/header_admin.php dentro de <head> -->
    <!-- CDN Ultra-Estable de Vue 3 -->
    <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js"></script>

    <!-- Script de verificación en consola para detectar bloqueos de red -->
    <script>
        if (typeof Vue === 'undefined') {
            console.error('⚠️ ALERTA: No se pudo cargar Vue.js desde la CDN. Verificá la conexión a internet o guardá la librería en assets/libs/vue/vue.global.js');
        }
    </script>

    <!-- CSS Layout del Panel Admin -->
    <style>
        :root {
            --sb-width: 260px;
            --topbar-height: 60px;
            --bg-agae: #001E82;
            /* Color principal de la empresa */
            --sidebar-hover: rgba(255, 255, 255, 0.12);
            --sidebar-active: rgba(255, 255, 255, 0.22);
        }

        body {
            min-height: 100vh;
            background-color: #f8fafc;
            overflow-x: hidden;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }

        /* Topbar con fondo corporativo */
        .admin-topbar {
            height: var(--topbar-height);
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1030;
            background-color: var(--bg-agae);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }

        /* Sidebar con fondo corporativo unificado */
        .admin-sidebar {
            width: var(--sb-width);
            position: fixed;
            top: var(--topbar-height);
            bottom: 0;
            left: 0;
            z-index: 1020;
            background-color: var(--bg-agae);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease-in-out;
            overflow-y: auto;
        }

        .sidebar-heading {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.55);
            padding: 1.25rem 1.25rem 0.4rem;
            font-weight: 700;
        }

        .nav-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: all 0.2s ease;
        }

        .nav-sidebar .nav-link:hover {
            color: #ffffff;
            background-color: var(--sidebar-hover);
        }

        .nav-sidebar .nav-link.active {
            color: #ffffff;
            background-color: var(--sidebar-active);
            border-left-color: #38bdf8;
            /* Azul cyan brillante para resaltar la sección activa */
        }

        .nav-sidebar .nav-link i {
            font-size: 1.2rem;
            margin-right: 0.75rem;
        }

        .admin-wrapper {
            margin-top: var(--topbar-height);
            margin-left: var(--sb-width);
            padding: 1.5rem;
            min-height: calc(100vh - var(--topbar-height));
            transition: margin-left 0.3s ease-in-out;
        }

        /* REEMPLAZAR EN EL BLOQUE <style>: */
        .brand-logo-img {
            height: 42px;
            /* Le damos 42px para que la tipografía chica sea nítida */
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0px 1px 2px rgba(0, 0, 0, 0.2));
            /* Le da un sutil relieve sobre el azul */
        }

        @media (max-width: 991.98px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-wrapper {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <!-- TOPBAR SUPERIOR -->
    <header class="admin-topbar d-flex align-items-center justify-content-between px-3 shadow-sm">
        <div class="d-flex align-items-center">
            <button class="btn btn-outline-light btn-sm d-lg-none me-2" id="btnToggleSidebar">
                <i class="bi bi-list fs-4"></i>
            </button>

            <!-- LOGO + MARCA -->
            <a class="navbar-brand fw-bold text-white d-flex align-items-center m-0" href="dashboard.php">
                <img src="../../assets/nav-icon.png" alt="AGAE Logo" class="brand-logo-img me-2" onerror="this.style.display='none'; document.getElementById('fallbackIcon').style.display='inline-block';">
                <i class="bi bi-shield-check fs-3 me-2 text-warning" id="fallbackIcon" style="display: none;"></i>
                <span class="fs-5">.. <small class="text-white-50 fs-6 fw-normal d-none d-sm-inline">| Sistema de Gestión Interna</small></span>
            </a>


        </div>

        <!-- USUARIO LOGUEADO + BOTÓN DIRECTO CERRAR SESIÓN -->
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center text-white me-1">
                <i class="bi bi-person-circle fs-5 me-2 text-white-50"></i>
                <span class="fw-semibold small text-uppercase"><?= htmlspecialchars($nombreUsuario) ?></span>
            </div>

            <!-- Botón Directo para salir -->
            <a href="logout.php" class="btn btn-danger btn-sm d-flex align-items-center px-3 shadow-sm fw-medium" title="Cerrar Sesión">
                <i class="bi bi-box-arrow-right me-1"></i>
                <span class="d-none d-sm-inline">Salir</span>
            </a>
        </div>
    </header>

    <!-- SIDEBAR LATERAL -->
    <aside class="admin-sidebar" id="sidebarMenu">
        <nav class="nav-sidebar flex-column my-2">

            <div class="sidebar-heading">Principal</div>
            <a class="nav-link <?= $paginaActual === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                <i class="bi bi-speedometer2"></i> Escritorio
            </a>

            <div class="sidebar-heading">Gestión de Afiliados</div>
            <a class="nav-link <?= $paginaActual === 'bandeja.php' ? 'active' : '' ?>" href="bandeja.php">
                <i class="bi bi-inbox-fill"></i> Bandeja Solicitudes
            </a>
            <a class="nav-link <?= $paginaActual === 'padron.php' ? 'active' : '' ?>" href="padron.php">
                <i class="bi bi-people-fill"></i> Padrón Afiliados
            </a>
<!--             <a class="nav-link <?= in_array($paginaActual, ['legajo.php', 'completar_ficha.php']) ? 'active' : '' ?>" href="legajo.php">
                <i class="bi bi-folder-symlink-fill"></i> Legajos y Fichas
            </a> -->
            <a class="nav-link <?= $paginaActual === 'cruce_padron.php' ? 'active' : '' ?>" href="cruce_padron.php">
                <i class="bi bi-intersect"></i> Cruce de Padrones
            </a>

            <div class="sidebar-heading">Finanzas y Pagos</div>
            <a class="nav-link <?= $paginaActual === 'debito_bna.php' ? 'active' : '' ?>" href="debito_bna.php">
                <i class="bi bi-bank"></i> Débito Automático BNA
            </a>

            <div class="sidebar-heading">Informes y Sistema</div>
            <a class="nav-link <?= $paginaActual === 'reportes_dinamicos.php' ? 'active' : '' ?>" href="reportes_dinamicos.php">
                <i class="bi bi-bar-chart-line-fill"></i> Reportes Dinámicos
            </a>
<!--             <a class="nav-link <?= $paginaActual === 'migracion.php' ? 'active' : '' ?>" href="migracion.php">
                <i class="bi bi-database-fill-gear"></i> Migración de Datos
            </a> -->

        </nav>
    </aside>

    <!-- CONTENEDOR PRINCIPAL -->
    <main class="admin-wrapper">