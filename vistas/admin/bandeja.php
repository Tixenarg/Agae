<?php require_once 'header_admin.php'; ?>

<div id="appAdmin" class="container-fluid px-4 py-4" v-cloak>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="m-0" style="color: #19248B; font-weight: 700;">
            <i class="bi bi-inbox-fill"></i> Bandeja de Solicitudes Web
        </h2>
        <button class="btn btn-outline-secondary shadow-sm" @click="cargarPendientes" :disabled="cargando">
            🔄 Actualizar Bandeja
        </button>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">

            <!-- State: Cargando -->
            <div v-if="cargando" class="text-center p-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted fw-bold">Buscando nuevas solicitudes...</p>
            </div>

            <!-- State: Tabla de Datos -->
            <div class="table-responsive" v-else>
                <table class="table table-hover mb-0 align-middle">
                    <thead style="background-color: #19248B; color: white;">
                        <tr>
                            <th class="ps-3">Fecha</th>
                            <th>DNI</th>
                            <th>Afiliado</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th class="text-center pe-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- State: Sin solicitudes -->
                        <tr v-if="solicitudes.length === 0">
                            <td colspan="6" class="text-center py-5 text-muted">
                                🎉 ¡Al día! No hay nuevas solicitudes pendientes de revisión.
                            </td>
                        </tr>

                        <!-- Lista de Solicitudes -->
                        <tr v-for="solicitud in solicitudes" :key="solicitud.id">
                            <td class="ps-3"><small class="text-muted">{{ formatearFecha(solicitud.fecha_solicitud) }}</small></td>
                            <td><strong>{{ solicitud.dni }}</strong></td>
                            <td class="text-capitalize fw-semibold">{{ solicitud.apellidos }}, {{ solicitud.nombres }}</td>
                            <td>
                                <small class="d-block">📧 {{ solicitud.email }}</small>
                                <small class="d-block text-success">📱 {{ solicitud.whatsapp }}</small>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark px-3 py-2">Pendiente</span>
                            </td>
                            <td class="text-center pe-3">
                                <a :href="'completar_ficha.php?id_solicitud=' + solicitud.id" class="btn btn-sm text-white" style="background-color: #19248B;">
                                    ✍️ Completar Ficha
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>


<!-- 1. Cargamos primero el Footer (que debe incluir Vue.js y Bootstrap JS) -->
<?php require_once 'footer_admin.php'; ?>

<!-- 2. Si footer_admin.php NO tiene el CDN de Vue.js, lo agregamos aquí arriba de bandeja.js -->
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<!-- 3. AL FINAL de todo, cargamos nuestro script reactivo -->
<script src="js/bandeja.js"></script>