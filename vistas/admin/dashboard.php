<?php 
require_once 'header_admin.php'; 
// Recuperamos el nombre del usuario logueado desde la sesión
$nombre_operador = $_SESSION['usu_nombre'] ?? 'Operador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - AGAE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <style>
        body { background-color: #f4f6f9; }
        .text-agae { color: #19248B !important; }
        .bg-agae { background-color: #19248B !important; }
        .card-menu {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            border: none;
        }
        .card-menu:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .icon-box {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div id="appDashboard" class="container py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0 text-agae">¡Hola, <?= htmlspecialchars($nombre_operador) ?>!</h2>
                <p class="text-muted mb-0">Bienvenido al Sistema de Gestión Interna de AGAE.</p>
            </div>
            <div>
                <a href="logout.php" class="btn btn-outline-danger">
                    <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                </a>
            </div>
        </div>

        <div class="row g-3 mb-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                            <i class="bi bi-hourglass-split fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Solicitudes Pendientes</h6>
                            <h3 class="fw-bold mb-0" v-text="metricas.pendientes">0</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-people fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Afiliados Activos</h6>
                            <h3 class="fw-bold mb-0" v-text="metricas.afiliados">0</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 d-none d-lg-block">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box bg-info bg-opacity-10 text-info me-3">
                            <i class="bi bi-shield-check fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Estado del Sistema</h6>
                            <h3 class="fw-bold mb-0 text-success fs-5">En Línea</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="text-agae mb-3 fw-semibold">Procesos Disponibles</h4>
        <div class="row g-4">
            
            <div class="col-md-6 col-lg-4">
                <div class="card card-menu h-100 shadow-sm" onclick="location.href='bandeja.php'">
                    <div class="card-body text-center py-4">
                        <div class="icon-box bg-agae text-white mx-auto mb-3 shadow">
                            <i class="bi bi-envelope-paper fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-dark">Bandeja de Entrada</h5>
                        <p class="text-muted small mb-0">Revisar, evaluar y aprobar las postulaciones de nuevos afiliados recibidas desde la web.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card card-menu h-100 shadow-sm" onclick="location.href='padron.php'">
                    <div class="card-body text-center py-4">
                        <div class="icon-box bg-primary text-white mx-auto mb-3 shadow" style="background-color: #0d6efd !important;">
                            <i class="bi bi-person-badge fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-dark">Padrón de Afiliados</h5>
                        <p class="text-muted small mb-0">Buscar afiliados, ver listados generales y acceder a la edición modular de sus legajos digitales.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card card-menu h-100 shadow-sm" style="border: 2px dashed #ccc; background-color: transparent;" onclick="alert('Aquí podés enlazar tus otros scripts armados.')">
                    <div class="card-body text-center py-4 d-flex flex-column justify-content-center align-items-center">
                        <div class="icon-box bg-secondary bg-opacity-10 text-secondary mx-auto mb-3">
                            <i class="bi bi-plus-circle fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-secondary">Agregar Proceso</h5>
                        <p class="text-muted small mb-0">Módulo libre para integrar los scripts que ya tenés armados.</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    metricas: {
                        pendientes: 0,
                        afiliados: 0
                    }
                }
            },
            mounted() {
                this.cargarMetricas();
            },
            methods: {
                async cargarMetricas() {
                    try {
                        const resp = await fetch('../../controladores/admin_dashboard_controlador.php');
                        const resultado = await resp.json();
                        if (resultado.status === 'success') {
                            this.metricas = resultado.metricas;
                        }
                    } catch (error) {
                        console.error("Error al cargar las métricas:", error);
                    }
                }
            }
        }).mount('#appDashboard');
    </script>
</body>
</html>