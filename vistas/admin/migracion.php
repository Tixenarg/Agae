<?php
// Carga del encabezado del panel de administración
require_once __DIR__ . '/header_admin.php';
?>

<!-- Contenedor Principal de la App Vue -->
<div class="container-fluid my-4" id="appMigracion">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-database-gear me-2"></i> Migración del Padrón de Afiliados
                    </h5>
                    <span class="badge bg-light text-primary">Arquitectura 3FN</span>
                </div>

                <div class="card-body p-4">
                    <p class="text-secondary">
                        Este módulo procesa la tabla sábana original <code>afiliados</code> y distribuye sus registros en la nueva estructura relacional normada (6 tablas vinculadas por claves foráneas). El proceso se realiza por lotes automáticos para proteger la memoria del servidor.
                    </p>

                    <hr class="my-4">

                    <!-- Estado de Carga / Spinner y Progreso -->
                    <div v-if="cargando" class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-primary">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Migrando datos en segundo plano...
                            </span>
                            <span class="badge bg-primary fs-6">{{ procesados }} Registros Procesados</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                 role="progressbar" 
                                 style="width: 100%;">
                                Procesando lote activo (Offset: {{ offset }})...
                            </div>
                        </div>
                    </div>

                    <!-- Alerta de Éxito al Finalizar -->
                    <div v-if="completado" class="alert alert-success d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1 fw-bold">¡Migración Completada Exitosamente!</h6>
                            <p class="mb-0 small">Se han transformado e insertado <strong>{{ procesados }}</strong> registros en la nueva estructura relacional.</p>
                        </div>
                    </div>

                    <!-- Alerta de Error General de Conexión o Servidor -->
                    <div v-if="errorGeneral" class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1 fw-bold">Se interrumpió el proceso</h6>
                            <p class="mb-0 small">{{ errorGeneral }}</p>
                        </div>
                    </div>

                    <!-- Tabla de Registros Omitidos/Erróneos -->
                    <div v-if="errores.length > 0" class="mt-4">
                        <h6 class="text-danger fw-bold mb-3">
                            <i class="bi bi-bug-fill me-1"></i> Registros omitidos por inconsistencias ({{ errores.length }}):
                        </h6>
                        <div class="table-responsive border rounded" style="max-height: 220px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 120px;">ID / DNI Original</th>
                                        <th>Motivo de la Omisión</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(err, idx) in errores" :key="idx">
                                        <td><code class="text-dark">{{ err.id_afiliado }}</code></td>
                                        <td class="text-danger small">{{ err.error }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Panel de Acciones -->
                    <div class="d-flex justify-content-end align-items-center mt-4 pt-2 border-top">
                        <button class="btn btn-primary px-4 py-2 fw-bold" 
                                @click="iniciarMigracion" 
                                :disabled="cargando">
                            <span v-if="cargando" class="spinner-border spinner-border-sm me-2"></span>
                            <i v-else class="bi bi-play-circle-fill me-2"></i>
                            {{ cargando ? 'Procesando Lotes...' : 'Comenzar Migración' }}
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- CDN de Vue.js 3 (Modo Progresivo) -->
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            cargando: false,
            completado: false,
            procesados: 0,
            offset: 0,
            errores: [],
            errorGeneral: null
        }
    },
    methods: {
        async iniciarMigracion() {
            if (!confirm('¿Confirma que desea iniciar el proceso de migración de la base de datos?')) {
                return;
            }

            // Reinicio de estados del componente
            this.cargando = true;
            this.completado = false;
            this.procesados = 0;
            this.offset = 0;
            this.errores = [];
            this.errorGeneral = null;

            // Iniciar ciclo asíncrono
            await this.procesarLote();
        },
        async procesarLote() {
            try {
                // Petición POST limpia enviando el offset actual como JSON
                const response = await fetch('../../controladores/migracion_controlador.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ offset: this.offset })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    // Si el backend indica que ya no hay más registros por procesar
                    if (data.terminado) {
                        this.cargando = false;
                        this.completado = true;
                        return;
                    }

                    // Acumular cantidad procesada y capturar errores individuales de filas
                    this.procesados += data.procesados;
                    if (data.errores && data.errores.length > 0) {
                        this.errores.push(...data.errores);
                    }

                    // Actualizar puntero y llamar de nuevo en la siguiente iteración del Event Loop
                    this.offset = data.nextOffset;
                    await this.procesarLote();

                } else {
                    this.cargando = false;
                    this.errorGeneral = data.msg || 'Respuesta de error inesperada por parte del servidor.';
                }

            } catch (err) {
                this.cargando = false;
                this.errorGeneral = 'Error de conexión o fallo al interpretar la respuesta JSON del servidor: ' + err.message;
            }
        }
    }
}).mount('#appMigracion');
</script>

<?php
// Carga del pie de página del panel de administración
require_once __DIR__ . '/footer_admin.php';
?>