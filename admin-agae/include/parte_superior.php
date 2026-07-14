<?php require './validar_session.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Agae </title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Favicom-->
    <!-- <link  rel="icon"   href="assets/img/icon.png" type="image/png" /> -->
    <link rel="icon" href="assets/icon.png" />
    <!-- <link  rel="icon"   href="assets/img/agae2.png" type="image/png" /> -->

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom styles for this select2-->
    <!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> -->
    <link href="vendor/select2/select2.min.css" rel="stylesheet">
    <!-- <link href="vendor/sweetAlert23/select2.min.css" rel="stylesheet" /> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
    <!-- Custom styles for this page -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top" style="color:black">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="panel-principal.php">
                <!-- <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div> -->
                <div class="sidebar-brand-text mx-3">Admin. Agae</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="panel-principal.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Panel Principal</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Interface
            </div>
            <li class="nav-item">
                <a class="nav-link" href="afiliados_panel.php">
                    <i class="fas fa-table"></i>
                    <span>Exportar listados a Excel</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="frm-archivo-bco.php">
                    <i class="fas fa-table"></i>
                    <span>Archivo para Debito BNA</span></a>
            </li>
<!--             <hr class="sidebar-divider">
            <div class="sidebar-heading">
                AGAE
            </div> -->
<!--             <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCajas" aria-expanded="true" aria-controls="collapseIngresos">
                    <i class="fas fa-money-check-alt"></i>
                    <span>Afiliados AGAE </span>
                </a>
                <div id="collapseCajas" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Afiliados</h6>

                        <a class="collapse-item" href="panel-principal.php">Control de Afiliados </a>
                        <a class="collapse-item" href="panel-afiliados.php">Panel de Afiliados </a>
                        <a class="collapse-item" href="frm-archivo-bco.php">Archivo para Debito BNA</a>
                        <a class="collapse-item" href="afiliados_panel.php">Listados...</a>
                    </div>
                </div>
            </li> -->


            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <div class="mt-1">
                        <!-- <img src="img/navbar-logo.svg" class="img-fluid max-width: 100% height: auto" alt="Responsive image"> -->
                        <img src="img/navbar-logo.svg" style="height: 70px; width: auto;">
                    </div>
                    <div class="m-4">
                        <!--  Sede Central, Reconquista 755 1 piso Oficina 9, Ciudad Autonoma de Buenos Aires, Argentina -->
                        ASOCIACIÓN GREMIAL DE ABOGADOS DEL ESTADO
                    </div>
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo " " . $_SESSION['nombre']; ?></span>
                                <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">


                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Salir
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->