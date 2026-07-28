<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Inclusión del Header Admin modularizado
require_once __DIR__ . '/header_admin.php';
?>

<!-- VISTA PRINCIPAL DEL DASHBOARD (CONTENEDOR VUE) -->
<div id="appDashboard" class="container-fluid py-4" v-cloak>

    <!-- 1. ENCABEZADO EJECUTIVO DE BIENVENIDA -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom gap-2">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="bi bi-speedometer2 text-primary me-2"></i>Escritorio Principal
            </h1>
            <p class="text-muted small mb-1">
                 Resumen del estado general del gremio AGAE.
            </p>
        </div>
        <div>
            <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                <i class="bi bi-calendar3 me-1 text-primary"></i> {{ fechaActual }}
            </span>
        </div>
    </div>

    <!-- SPINNER DE CARGA -->
    <div v-if="cargando" class="text-center py-5">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Cargando tablero...</span>
        </div>
        <p class="mt-2 text-muted fw-semibold">Cargando métricas en tiempo real...</p>
    </div>

    <div v-else>
        <!-- 2. TARJETAS DE INDICADORES CLAVE (KPIs EJECUTIVOS) -->
        <div class="row g-3 mb-4">

            <!-- KPI 1: Solicitudes Web Pendientes -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4" :class="metricas.solicitudes_pendientes > 0 ? 'border-danger' : 'border-success'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted fw-semibold small text-uppercase">Solicitudes Web</span>
                                <h2 class="fw-bold my-1" :class="metricas.solicitudes_pendientes > 0 ? 'text-danger' : 'text-success'">
                                    {{ metricas.solicitudes_pendientes }}
                                </h2>
                                <span class="badge rounded-pill" :class="metricas.solicitudes_pendientes > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'">
                                    <i :class="metricas.solicitudes_pendientes > 0 ? 'bi bi-exclamation-circle-fill' : 'bi bi-check-circle-fill'"></i>
                                    {{ metricas.solicitudes_pendientes > 0 ? 'Atención Inmediata' : 'Sin pendientes' }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                                :class="metricas.solicitudes_pendientes > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'"
                                style="width: 52px; height: 52px;">
                                <i class="bi bi-inbox-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 2: Total Padrón Activo -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted fw-semibold small text-uppercase">Afiliados</span>
                                <h2 class="fw-bold my-1 text-primary">{{ metricas.total_afiliados }}</h2>
                                <span class="text-muted small">Afiliados con estado activo</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle flex-shrink-0"
                                style="width: 52px; height: 52px;">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 3: Valor Cuota Vigente -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted fw-semibold small text-uppercase">Cuota Vigente</span>
                                <h2 class="fw-bold my-1 text-info">{{ formatearMoneda(metricas.importe_cuota) }}</h2>
                                <span class="text-muted small">Valor unitario BNA</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-info-subtle text-info rounded-circle flex-shrink-0"
                                style="width: 52px; height: 52px;">
                                <i class="bi bi-tag-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI 4: Proyección Mensual -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted fw-semibold small text-uppercase">Proyección Mensual</span>
                                <h2 class="fw-bold my-1 text-success">{{ formatearMoneda(metricas.proyeccion_recaudacion) }}</h2>
                                <span class="text-muted small">Estimado a recaudar</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle flex-shrink-0"
                                style="width: 52px; height: 52px;">
                                <i class="bi bi-cash-stack fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. DESGLOSE POR FORMA DE PAGO -->
        <div class="row mb-4" v-if="metricas.formas_pago && metricas.formas_pago.length > 0">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title fw-bold text-dark mb-0 fs-6">
                            <i class="bi bi-credit-card-2-front text-primary me-2"></i>Distribución de Afiliados Activos por Forma de Pago
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div v-for="item in metricas.formas_pago" :key="item.id_fpago" class="col-12 col-md-4">
                                <div class="p-3 border rounded-3 bg-light bg-opacity-50 h-100">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="fw-bold text-secondary">{{ item.fpago_nombre }}</span>
                                        <span class="badge bg-primary rounded-pill">
                                            {{ calcularPorcentaje(item.total) }}%
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-baseline justify-content-between">
                                        <h3 class="fw-bold m-0 text-dark">{{ item.total }}</h3>
                                        <span class="text-muted small">afiliados</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. BOTONERA DE ACCIONES RÁPIDAS -->
<!--         <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-body-tertiary">
                    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
                        <div class="fw-bold text-secondary">
                            <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Acciones Rápidas:
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="bandeja.php" class="btn btn-primary btn-sm px-3 shadow-sm">
                                <i class="bi bi-inbox me-1"></i> Ir a Bandeja Web ({{ metricas.solicitudes_pendientes }})
                            </a>
                            <a href="debito_bna.php" class="btn btn-outline-dark btn-sm px-3">
                                <i class="bi bi-file-earmark-code me-1"></i> Generar TXT Débito BNA
                            </a>
                            <a href="padron.php" class="btn btn-outline-secondary btn-sm px-3">
                                <i class="bi bi-person-lines-fill me-1"></i> Ver Padrón Completo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- 5. TABLA OPERATIVA: ÚLTIMAS SOLICITUDES PENDIENTES -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold text-dark mb-0 fs-6">
                    <i class="bi bi-clock-history me-2 text-danger"></i>Últimas Solicitudes Web Pendientes de Afiliación
                </h5>
                <a href="bandeja.php" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold">
                    Ver todas <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">Fecha Solicitud</th>
                                <th scope="col">DNI</th>
                                <th scope="col">Nombre y Apellido</th>
                                <th scope="col">Email</th>
                                <th scope="col">WhatsApp</th>
                                <th scope="col" class="text-end pe-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="solicitudes.length === 0">
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-check2-circle text-success fs-1 d-block mb-2"></i>
                                    ¡Excelente! No hay solicitudes pendientes de procesamiento en este momento.
                                </td>
                            </tr>
                            <tr v-for="item in solicitudes" :key="item.id">
                                <td class="ps-3 fw-semibold text-secondary">
                                    {{ formatearFecha(item.fecha_solicitud) }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        {{ formatearDNI(item.dni) }}
                                    </span>
                                </td>
                                <td class="fw-bold">
                                    {{ capitalizarTexto(item.apellidos) }}, {{ capitalizarTexto(item.nombres) }}
                                </td>
                                <td class="text-muted small">{{ item.email }}</td>
                                <td>
                                    <a v-if="item.whatsapp" :href="'https://wa.me/' + limpiarTelefono(item.whatsapp)" target="_blank" class="btn btn-sm btn-outline-success border-0">
                                        <i class="bi bi-whatsapp me-1"></i> {{ item.whatsapp }}
                                    </a>
                                    <span v-else class="text-muted small">-</span>
                                </td>
                                <td class="text-end pe-3">
                                    <a :href="'bandeja.php?id=' + item.id" class="btn btn-danger btn-sm shadow-sm fw-semibold">
                                        <i class="bi bi-pencil-square me-1"></i> Procesar
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- PREVENCIÓN DE FLICKER DE SINTAXIS MUSTACHE (v-cloak) -->
<style>
    [v-cloak] { display: none !important; }
</style>

<!-- LÓGICA REACTIVA VUE.JS 3 -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Vue === 'undefined') {
            console.error('Error crítico: Vue.js no está cargado correctamente.');
            return;
        }

        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    cargando: true,
                    usuarioNombre: 'Cargando...',
                    fechaActual: new Date().toLocaleDateString('es-AR', {
                        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                    }),
                    metricas: {
                        solicitudes_pendientes: 0,
                        total_afiliados: 0,
                        importe_cuota: 0,
                        proyeccion_recaudacion: 0,
                        formas_pago: []
                    },
                    solicitudes: []
                }
            },
            methods: {
                async cargarDashboard() {
                    this.cargando = true;
                    try {
                        const res = await fetch('../../controladores/admin_dashboard_controlador.php');
                        const response = await res.json();

                        if (response.status === 'success') {
                            this.usuarioNombre = response.data.usuario_nombre;
                            this.metricas = response.data.metricas;
                            this.solicitudes = response.data.solicitudes;
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Error', response.message || 'No se pudieron obtener las métricas.', 'error');
                            }
                        }
                    } catch (error) {
                        console.error('Error al conectar con el servidor:', error);
                    } finally {
                        this.cargando = false;
                    }
                },
                formatearMoneda(valor) {
                    return new Intl.NumberFormat('es-AR', {
                        style: 'currency', currency: 'ARS'
                    }).format(valor || 0);
                },
                formatearFecha(fechaStr) {
                    if (!fechaStr) return '-';
                    const fecha = new Date(fechaStr);
                    return fecha.toLocaleDateString('es-AR', {
                        day: '2-digit', month: '2-digit', year: 'numeric'
                    });
                },
                limpiarTelefono(num) {
                    return num ? num.replace(/\D/g, '') : '';
                },
                formatearDNI(dni) {
                    if (!dni) return '-';
                    const numero = String(dni).replace(/\D/g, '');
                    return new Intl.NumberFormat('es-AR').format(numero);
                },
                capitalizarTexto(str) {
                    if (!str) return '';
                    return str.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
                },
                calcularPorcentaje(cantidad) {
                    if (!this.metricas.total_afiliados || this.metricas.total_afiliados === 0) return 0;
                    return Math.round((parseInt(cantidad) / this.metricas.total_afiliados) * 100);
                }
            },
            mounted() {
                this.cargarDashboard();
            }
        }).mount('#appDashboard');
    });
</script>

<?php
// Inclusión del Footer Admin
require_once __DIR__ . '/footer_admin.php';
?>