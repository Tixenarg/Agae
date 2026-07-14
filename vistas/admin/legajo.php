<?php require_once 'header_admin.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legajo Digital - AGAE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f8f9fa; }
        .accordion-button { font-weight: 600; color: #19248B; }
        .accordion-button:not(.collapsed) { background-color: #e9ecef; color: #19248B; box-shadow: none; }
        .accordion-button:focus { box-shadow: none; border-color: rgba(0,0,0,.125); }
        .btn-agae { background-color: #19248B; color: white; }
        .btn-agae:hover { background-color: #121a63; color: white; }
        .text-agae { color: #19248B !important; }
    </style>
</head>
<body>
    <div id="appLegajo" class="container py-4">
        
        <!-- CABECERA PRINCIPAL -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0 text-agae">
                    <i class="bi bi-person-lines-fill me-2"></i>Legajo del Afiliado
                </h2>
                <p class="text-muted mb-0">Carga y edición por módulos</p>
            </div>
            <div>
                <a href="padron.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Padrón
                </a>
            </div>
        </div>

        <!-- CONTENEDOR REACTIVO (Se muestra solo si ya cargó el afiliado) -->
        <div v-if="form.id_afiliado">
            
            <!-- TARJETA DEL TERMÓMETRO DE INTEGRIDAD -->
            <div class="card shadow-sm border-0 mb-4 bg-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <span class="text-muted text-uppercase small fw-bold">Afiliado Activo</span>
                            <h3 class="fw-bold mb-1 text-agae">{{ form.apellidos }}, {{ form.nombres }}</h3>
                            <p class="text-muted mb-0">DNI: {{ form.dni }} | ID Afiliado: #{{ form.id_afiliado }}</p>
                        </div>
                        
                        <!-- Barra de Progreso Reactiva -->
                        <div class="col-md-5 mt-3 mt-md-0">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold text-muted small">Integridad del Legajo</span>
                                <span class="fw-bold small" :class="colorTextoProgreso">{{ porcentajeProgreso }}%</span>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     :class="colorBarraProgreso"
                                     :style="{ width: porcentajeProgreso + '%' }" 
                                     :aria-valuenow="porcentajeProgreso" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted mt-1 d-block text-end" v-if="porcentajeProgreso < 100">
                                Faltan completar {{ 4 - cantidadModulosCompletos }} de 4 módulos.
                            </small>
                            <small class="text-success fw-bold mt-1 d-block text-end" v-else>
                                <i class="bi bi-patch-check-fill"></i> ¡Legajo 100% Completo!
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACORDEÓN DE MÓDULOS -->
            <div class="accordion shadow-sm" id="acordeonLegajo">
                
                <!-- MÓDULO 1: IDENTIDAD -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#panelIdentidad">
                            <i class="bi bi-person-vcard me-2"></i> 1. Datos de Identidad
                            <span class="badge ms-auto me-3" :class="moduloIdentidadCompleto ? 'bg-success' : 'bg-danger'">
                                {{ moduloIdentidadCompleto ? 'Completo' : 'Incompleto' }}
                            </span>
                        </button>
                    </h2>
                    <div id="panelIdentidad" class="accordion-collapse collapse show" data-bs-parent="#acordeonLegajo">
                        <div class="accordion-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label text-muted">Apellidos</label>
                                    <input type="text" class="form-control" v-model="form.apellidos" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted">Nombres</label>
                                    <input type="text" class="form-control" v-model="form.nombres" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted">DNI</label>
                                    <input type="text" class="form-control" v-model="form.dni" disabled>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">CUIL</label>
                                    <input type="text" class="form-control" v-model="form.cuil" placeholder="Sin guiones">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Nacionalidad</label>
                                    <input type="text" class="form-control" v-model="form.nacionalidad">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Sexo</label>
                                    <select class="form-select" v-model="form.sexo">
                                        <option value=""></option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                        <option value="X">Otro</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Estado Civil</label>
                                    <select class="form-select" v-model="form.estado_civil">
                                        <option value=""></option>
                                        <option value="Soltero/a">Soltero/a</option>
                                        <option value="Casado/a">Casado/a</option>
                                        <option value="Divorciado/a">Divorciado/a</option>
                                        <option value="Viudo/a">Viudo/a</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Fecha Nacimiento</label>
                                    <input type="date" class="form-control" v-model="form.fecha_nacimiento">
                                </div>
                            </div>
                            <div class="text-end border-top pt-3">
                                <button class="btn btn-agae" @click="guardarModulo('guardar_identidad')">
                                    <i class="bi bi-floppy me-1"></i> Guardar Identidad
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MÓDULO 2: DOMICILIO -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#panelDomicilio">
                            <i class="bi bi-geo-alt me-2"></i> 2. Domicilio y Contacto
                            <span class="badge ms-auto me-3" :class="moduloDomicilioCompleto ? 'bg-success' : 'bg-danger'">
                                {{ moduloDomicilioCompleto ? 'Completo' : 'Incompleto' }}
                            </span>
                        </button>
                    </h2>
                    <div id="panelDomicilio" class="accordion-collapse collapse" data-bs-parent="#acordeonLegajo">
                        <div class="accordion-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Dirección (Calle y Número)</label>
                                    <input type="text" class="form-control" v-model="form.domicilio">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Localidad</label>
                                    <input type="text" class="form-control" v-model="form.localidad">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Provincia</label>
                                    <input type="text" class="form-control" v-model="form.provincia">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">C.P.</label>
                                    <input type="text" class="form-control" v-model="form.codigo_postal">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" v-model="form.telefono">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" v-model="form.email">
                                </div>
                            </div>
                            <div class="text-end border-top pt-3">
                                <button class="btn btn-agae" @click="guardarModulo('guardar_domicilio')">
                                    <i class="bi bi-floppy me-1"></i> Guardar Domicilio
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MÓDULO 3: EDUCACIÓN -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#panelEducacion">
                            <i class="bi bi-book me-2"></i> 3. Nivel Académico
                            <span class="badge ms-auto me-3" :class="moduloEducacionCompleto ? 'bg-success' : 'bg-danger'">
                                {{ moduloEducacionCompleto ? 'Completo' : 'Incompleto' }}
                            </span>
                        </button>
                    </h2>
                    <div id="panelEducacion" class="accordion-collapse collapse" data-bs-parent="#acordeonLegajo">
                        <div class="accordion-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nivel de Estudio</label>
                                    <select class="form-select" v-model="form.nivel_estudio">
                                        <option value=""></option>
                                        <option value="Secundario">Secundario</option>
                                        <option value="Terciario">Terciario</option>
                                        <option value="Universitario">Universitario</option>
                                        <option value="Posgrado">Posgrado</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Título</label>
                                    <input type="text" class="form-control" v-model="form.titulo" placeholder="Ej: Abogado">
                                </div>
                            </div>
                            <div class="text-end border-top pt-3">
                                <button class="btn btn-agae" @click="guardarModulo('guardar_educacion')">
                                    <i class="bi bi-floppy me-1"></i> Guardar Educación
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MÓDULO 4: LABORAL -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#panelLaboral">
                            <i class="bi bi-briefcase me-2"></i> 4. Datos Laborales
                            <span class="badge ms-auto me-3" :class="moduloLaboralCompleto ? 'bg-success' : 'bg-danger'">
                                {{ moduloLaboralCompleto ? 'Completo' : 'Incompleto' }}
                            </span>
                        </button>
                    </h2>
                    <div id="panelLaboral" class="accordion-collapse collapse" data-bs-parent="#acordeonLegajo">
                        <div class="accordion-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Nro. de Legajo</label>
                                    <input type="text" class="form-control" v-model="form.legajo">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label">Organismo que Liquida Haberes</label>
                                    <input type="text" class="form-control" v-model="form.org_liquida_haber">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Organismo / Lugar donde Trabaja</label>
                                    <input type="text" class="form-control" v-model="form.org_trabaja">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Domicilio del Trabajo</label>
                                    <input type="text" class="form-control" v-model="form.domicilio_trabajo">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Localidad del Trabajo</label>
                                    <input type="text" class="form-control" v-model="form.localidad_trabajo">
                                </div>
                            </div>
                            <div class="text-end border-top pt-3">
                                <button class="btn btn-agae" @click="guardarModulo('guardar_laboral')">
                                    <i class="bi bi-floppy me-1"></i> Guardar Laboral
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        
        <!-- PANTALLA DE CARGA (Aparece mientras fetch hace su trabajo) -->
        <div v-else class="text-center mt-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Cargando legajo del afiliado...</p>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    form: {} // Los datos se cargan dinámicamente desde el controlador
                }
            },
            computed: {
                // Evaluamos si el Módulo de Identidad tiene los datos clave cargados
                moduloIdentidadCompleto() {
                    const f = this.form;
                    return !!(f.cuil && f.nacionalidad && f.sexo && f.estado_civil && f.fecha_nacimiento);
                },

                // Evaluamos si el Módulo de Domicilio está completo
                moduloDomicilioCompleto() {
                    const f = this.form;
                    return !!(f.domicilio && f.localidad && f.provincia && f.telefono && f.email);
                },

                // Evaluamos si el Módulo de Educación está completo
                moduloEducacionCompleto() {
                    const f = this.form;
                    return !!(f.nivel_estudio && f.titulo);
                },

                // Evaluamos si el Módulo Laboral está completo
                moduloLaboralCompleto() {
                    const f = this.form;
                    return !!(f.legajo && f.org_trabaja);
                },

                // Cuenta cuántos de los 4 módulos están completamente listos
                cantidadModulosCompletos() {
                    let total = 0;
                    if (this.moduloIdentidadCompleto) total++;
                    if (this.moduloDomicilioCompleto) total++;
                    if (this.moduloEducacionCompleto) total++;
                    if (this.moduloLaboralCompleto) total++;
                    return total;
                },

                // Convierte la cantidad de módulos listos en porcentaje (0%, 25%, 50%, 75%, 100%)
                porcentajeProgreso() {
                    return this.cantidadModulosCompletos * 25;
                },

                // Controla el color de la barra (Rojo -> Amarillo -> Verde)
                colorBarraProgreso() {
                    const pct = this.porcentajeProgreso;
                    if (pct <= 25) return 'bg-danger';
                    if (pct <= 75) return 'bg-warning';
                    return 'bg-success';
                },

                // Controla el color del texto del porcentaje
                colorTextoProgreso() {
                    const pct = this.porcentajeProgreso;
                    if (pct <= 25) return 'text-danger';
                    if (pct <= 75) return 'text-warning';
                    return 'text-success';
                }
            },
            mounted() {
                const urlParams = new URLSearchParams(window.location.search);
                const id = urlParams.get('id');
                
                if(id) {
                    this.cargarDatos(id);
                } else {
                    Swal.fire('Error', 'No se especificó un afiliado.', 'error');
                }
            },
            methods: {
                async cargarDatos(id) {
                    try {
                        const resp = await fetch(`../../controladores/admin_legajo_controlador.php?id=${id}`);
                        const resultado = await resp.json();
                        
                        if(resultado.status === 'success') {
                            this.form = resultado.data;
                        } else {
                            Swal.fire('Error', resultado.message, 'error');
                        }
                    } catch (error) {
                        console.error(error);
                        Swal.fire('Error', 'Fallo al comunicar con el servidor.', 'error');
                    }
                },

                async guardarModulo(accion) {
                    try {
                        const resp = await fetch('../../controladores/admin_legajo_controlador.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                accion: accion,
                                datos: this.form
                            })
                        });
                        
                        const resultado = await resp.json();
                        
                        if(resultado.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Guardado!',
                                text: resultado.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', resultado.message, 'error');
                        }
                    } catch (error) {
                        console.error(error);
                        Swal.fire('Error', 'Fallo al guardar los datos.', 'error');
                    }
                }
            }
        }).mount('#appLegajo');
    </script>
</body>
</html>