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
        body {
            background-color: #f8f9fa;
        }

        .accordion-button {
            font-weight: 600;
            color: #19248B;
        }

        .accordion-button:not(.collapsed) {
            background-color: #e9ecef;
            color: #19248B;
            box-shadow: none;
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(0, 0, 0, .125);
        }

        .btn-agae {
            background-color: #19248B;
            color: white;
        }

        .btn-agae:hover {
            background-color: #121a63;
            color: white;
        }

        .text-agae {
            color: #19248B !important;
        }

        /* Estilos para las tarjetas de Forma de Pago */
        .pago-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            padding: 20px 15px;
            height: 100%;
        }

        .pago-card:hover {
            border-color: #19248B;
            background-color: #f8f9fa;
        }

        .pago-card.selected {
            border-color: #19248B;
            background-color: rgba(25, 36, 139, 0.05);
            font-weight: 600;
        }

        .pago-icon {
            font-size: 2.5rem;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .pago-card.selected .pago-icon {
            color: #19248B;
        }
    </style>
</head>

<body>
    <div id="appLegajo" class="container py-4">

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

        <div v-if="form.id_afiliado">

            <div class="card shadow-sm border-0 mb-4 bg-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <span class="text-muted text-uppercase small fw-bold">Afiliado Activo</span>
                            <h3 class="fw-bold mb-1 text-agae">{{ form.apellidos }}, {{ form.nombres }}</h3>
                            <p class="text-muted mb-0">DNI: {{ form.dni }} | ID Afiliado: #{{ form.id_afiliado }}</p>
                        </div>

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
                                Faltan completar {{ 5 - cantidadModulosCompletos }} de 5 módulos.
                            </small>
                            <small class="text-success fw-bold mt-1 d-block text-end" v-else>
                                <i class="bi bi-patch-check-fill"></i> ¡Legajo 100% Completo!
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion shadow-sm">
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button d-flex justify-content-between align-items-center" 
                                type="button" 
                                :class="{ 'collapsed': !abiertos.fpago }"
                                @click="togglePanel('fpago')">
                            <div>
                                <i class="bi bi-wallet2 me-2"></i> 1. Información de Cobro
                            </div>
                            <span class="badge ms-auto me-3"
                                :class="moduloFpagoCompleto ? 'bg-success' : 'bg-danger'">
                                {{ moduloFpagoCompleto ? 'Completo' : 'Incompleto' }}
                            </span>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" :class="{ 'show': abiertos.fpago }">
                        <div class="accordion-body">
                            <p class="text-muted small mb-3">Seleccione el método por el cual el afiliado abonará su cuota:</p>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="pago-card" :class="{'selected': form.id_fpago == 1}" @click="form.id_fpago = 1">
                                        <i class="bi bi-bank pago-icon d-block"></i>
                                        <span>Débito automático Bco Nación</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="pago-card" :class="{'selected': form.id_fpago == 2}" @click="form.id_fpago = 2">
                                        <i class="bi bi-phone pago-icon d-block"></i>
                                        <span>Mercado Pago</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="pago-card" :class="{'selected': form.id_fpago == 3}" @click="form.id_fpago = 3">
                                        <i class="bi bi-wallet pago-icon d-block"></i>
                                        <span>Otros</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3" v-if="form.id_fpago == 1">
                                <div class="col-md-6 offset-md-3">
                                    <div class="p-3 border rounded bg-light">
                                        <label class="form-label text-agae fw-bold">Número de Cuenta / CBU</label>
                                        <input type="text" class="form-control border-primary" v-model="form.numero_cuenta" placeholder="Ingrese el número de la caja de ahorro">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end border-top pt-3">
                                <button class="btn btn-agae" @click="guardarModulo('guardar_fpago')">
                                    <i class="bi bi-floppy me-1"></i> Guardar Información de Cobro
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button d-flex align-items-center" 
                                type="button" 
                                :class="{ 'collapsed': !abiertos.identidad }"
                                @click="togglePanel('identidad')">
                            <i class="bi bi-person-vcard me-2"></i> 2. Datos de Identidad
                            <span class="badge ms-auto me-3" :class="moduloIdentidadCompleto ? 'bg-success' : 'bg-danger'">
                                {{ moduloIdentidadCompleto ? 'Completo' : 'Incompleto' }}
                            </span>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" :class="{ 'show': abiertos.identidad }">
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

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button d-flex align-items-center" 
                                type="button" 
                                :class="{ 'collapsed': !abiertos.domicilio }"
                                @click="togglePanel('domicilio')">
                            <i class="bi bi-geo-alt me-2"></i> 3. Domicilio y Contacto
                            <span class="badge ms-auto me-3" :class="moduloDomicilioCompleto ? 'bg-success' : 'bg-danger'">
                                {{ moduloDomicilioCompleto ? 'Completo' : 'Incompleto' }}
                            </span>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" :class="{ 'show': abiertos.domicilio }">
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

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button d-flex align-items-center" 
                                type="button" 
                                :class="{ 'collapsed': !abiertos.educacion }"
                                @click="togglePanel('educacion')">
                            <i class="bi bi-book me-2"></i> 4. Nivel Académico
                            <span class="badge ms-auto me-3" :class="moduloEducacionCompleto ? 'bg-success' : 'bg-danger'">
                                {{ moduloEducacionCompleto ? 'Completo' : 'Incompleto' }}
                            </span>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" :class="{ 'show': abiertos.educacion }">
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

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button d-flex align-items-center" 
                                type="button" 
                                :class="{ 'collapsed': !abiertos.laboral }"
                                @click="togglePanel('laboral')">
                            <i class="bi bi-briefcase me-2"></i> 5. Datos Laborales
                            <span class="badge ms-auto me-3" :class="moduloLaboralCompleto ? 'bg-success' : 'bg-danger'">
                                {{ moduloLaboralCompleto ? 'Completo' : 'Incompleto' }}
                            </span>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" :class="{ 'show': abiertos.laboral }">
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
                    form: {},
                    abiertos: {
                        fpago: false,
                        identidad: false,
                        domicilio: false,
                        educacion: false,
                        laboral: false
                    }
                }
            },
            computed: {
                moduloFpagoCompleto() { return this.isFpagoCompleto(this.form); },
                moduloIdentidadCompleto() { return this.isIdentidadCompleta(this.form); },
                moduloDomicilioCompleto() { return this.isDomicilioCompleto(this.form); },
                moduloEducacionCompleto() { return this.isEducacionCompleta(this.form); },
                moduloLaboralCompleto() { return this.isLaboralCompleto(this.form); },

                cantidadModulosCompletos() {
                    let cant = 0;
                    if (this.moduloFpagoCompleto) cant++;
                    if (this.moduloIdentidadCompleto) cant++;
                    if (this.moduloDomicilioCompleto) cant++;
                    if (this.moduloEducacionCompleto) cant++;
                    if (this.moduloLaboralCompleto) cant++;
                    return cant;
                },

                porcentajeProgreso() {
                    let porcentaje = this.cantidadModulosCompletos * 20;
                    return isNaN(porcentaje) ? 0 : porcentaje;
                },

                colorBarraProgreso() {
                    const pct = this.porcentajeProgreso;
                    if (pct <= 40) return 'bg-danger';
                    if (pct <= 80) return 'bg-warning';
                    return 'bg-success';
                },

                colorTextoProgreso() {
                    const pct = this.porcentajeProgreso;
                    if (pct <= 40) return 'text-danger';
                    if (pct <= 80) return 'text-warning';
                    return 'text-success';
                }
            },
            mounted() {
                const urlParams = new URLSearchParams(window.location.search);
                const id = urlParams.get('id');

                if (id) {
                    this.cargarDatos(id);
                } else {
                    Swal.fire('Error', 'No se especificó un afiliado.', 'error');
                }
            },
            methods: {
                isFpagoCompleto(f) {
                    if (!f) return false;
                    if (f.id_fpago == 1 && f.numero_cuenta && String(f.numero_cuenta).trim() !== '') return true;
                    if (f.id_fpago == 2 || f.id_fpago == 3) return true;
                    return false;
                },

                isIdentidadCompleta(f) {
                    if (!f) return false;
                    return !!(
                        f.cuil && String(f.cuil).trim() !== '' && 
                        f.nacionalidad && String(f.nacionalidad).trim() !== '' && 
                        f.sexo && String(f.sexo).trim() !== '' && 
                        f.estado_civil && String(f.estado_civil).trim() !== '' && 
                        f.fecha_nacimiento
                    );
                },

                isDomicilioCompleto(f) {
                    if (!f) return false;
                    return !!(
                        f.domicilio && String(f.domicilio).trim() !== '' && 
                        f.localidad && String(f.localidad).trim() !== '' && 
                        f.provincia && String(f.provincia).trim() !== '' && 
                        f.telefono && String(f.telefono).trim() !== '' && 
                        f.email && String(f.email).trim() !== ''
                    );
                },

                isEducacionCompleta(f) {
                    if (!f) return false;
                    return !!(
                        f.nivel_estudio && String(f.nivel_estudio).trim() !== '' && 
                        f.titulo && String(f.titulo).trim() !== ''
                    );
                },

                isLaboralCompleto(f) {
                    if (!f) return false;
                    return !!(
                        f.legajo && String(f.legajo).trim() !== '' &&
                        f.org_liquida_haber && String(f.org_liquida_haber).trim() !== '' &&
                        f.org_trabaja && String(f.org_trabaja).trim() !== '' &&
                        f.domicilio_trabajo && String(f.domicilio_trabajo).trim() !== '' &&
                        f.localidad_trabajo && String(f.localidad_trabajo).trim() !== ''
                    );
                },

                async cargarDatos(id) {
                    try {
                        const resp = await fetch(`../../controladores/admin_legajo_controlador.php?id=${id}`);
                        const resultado = await resp.json();

                        if (resultado.status === 'success') {
                            const datosCrudos = resultado.data;
                            this.form = datosCrudos; 

                            const estadosIncompletos = {
                                fpago: !this.isFpagoCompleto(datosCrudos),
                                identidad: !this.isIdentidadCompleta(datosCrudos),
                                domicilio: !this.isDomicilioCompleto(datosCrudos),
                                educacion: !this.isEducacionCompleta(datosCrudos),
                                laboral: !this.isLaboralCompleto(datosCrudos)
                            };

                            // Orden estricto para revisar del 1 al 5 y abrir el primer faltante
                            const ordenModulos = ['fpago', 'identidad', 'domicilio', 'educacion', 'laboral'];
                            let seAbrioAlguno = false;

                            ordenModulos.forEach(modulo => {
                                if (estadosIncompletos[modulo] && !seAbrioAlguno) {
                                    this.abiertos[modulo] = true;
                                    seAbrioAlguno = true;
                                } else {
                                    this.abiertos[modulo] = false;
                                }
                            });

                            // Si todos están completos, abrimos el de pago por defecto
                            if (!seAbrioAlguno) {
                                this.abiertos.fpago = true;
                            }

                        } else {
                            Swal.fire('Error', resultado.message, 'error');
                        }
                    } catch (error) {
                        console.error("Error al cargar datos:", error);
                        Swal.fire('Error', 'Fallo al comunicar con el servidor.', 'error');
                    }
                },

                togglePanel(panelName) {
                    const estadoActual = this.abiertos[panelName];
                    for (let key in this.abiertos) {
                        this.abiertos[key] = false;
                    }
                    this.abiertos[panelName] = !estadoActual;
                },

                async guardarModulo(accion) {
                    try {
                        const resp = await fetch('../../controladores/admin_legajo_controlador.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                accion: accion,
                                datos: this.form
                            })
                        });

                        const resultado = await resp.json();

                        if (resultado.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Guardado!',
                                text: resultado.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            
                            const urlParams = new URLSearchParams(window.location.search);
                            const id = urlParams.get('id');
                            if (id) {
                                this.cargarDatos(id);
                            }
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