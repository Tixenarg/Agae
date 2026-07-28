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
            background-color: #19248B !important;
            color: white !important;
        }

        .btn-agae:hover {
            background-color: #121a63 !important;
            color: white !important;
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

            <!-- CABECERA DEL AFILIADO -->
            <div class="card shadow-sm border-0 mb-4 bg-white">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <!-- Badge Estado Dinámico -->
                            <span class="badge mb-2 fs-6" :class="badgeEstadoClass">
                                {{ (form.estado_nombre || 'DESCONOCIDO').toUpperCase() }}
                            </span>
                            <!-- Nombre con Title Case -->
                            <h3 class="fw-bold mb-1 text-agae">
                                {{ titleCase(form.apellidos) }}, {{ titleCase(form.nombres) }}
                            </h3>
                            <!-- DNI Formateado -->
                            <p class="text-muted mb-0">
                                DNI: <strong>{{ formatearDNI(form.dni) }}</strong> | ID Afiliado: <strong>#{{ form.id_afiliado }}</strong>
                            </p>
                        </div>

                        <!-- Barra de Progreso -->
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
                                Faltan completar {{ 6 - cantidadModulosCompletos }} de 6 módulos.
                            </small>
                            <small class="text-success fw-bold mt-1 d-block text-end" v-else>
                                <i class="bi bi-patch-check-fill"></i> ¡Legajo 100% Completo!
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACORDEÓN DE MÓDULOS -->
            <div class="accordion shadow-sm">

                <!-- 1. DATOS PRINCIPALES DE AFILIACIÓN (MAESTRA) -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button d-flex align-items-center"
                            type="button"
                            :class="{ 'collapsed': !abiertos.maestra }"
                            @click="togglePanel('maestra')">
                            <i class="bi bi-person-badge me-2"></i> 1. Datos Principales de Afiliación
                            <span class="badge ms-auto me-3" :class="moduloMaestraCompleto ? 'bg-success' : 'bg-danger'">
                                {{ moduloMaestraCompleto ? 'Completo' : 'Incompleto' }}
                            </span>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" :class="{ 'show': abiertos.maestra }">
                        <div class="accordion-body">
                            <p class="text-muted small mb-3">Datos de origen de la solicitud web. Puede editarlos si requiere corregir tipeos o actualizar contactos:</p>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Apellidos</label>
                                    <input type="text" class="form-control" v-model="form.apellidos">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Nombres</label>
                                    <input type="text" class="form-control" v-model="form.nombres">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">DNI</label>
                                    <input type="text" class="form-control" v-model="form.dni">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Correo Electrónico</label>
                                    <input type="email" class="form-control" v-model="form.email">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Teléfono de Contacto</label>
                                    <input type="text" class="form-control" v-model="form.telefono">
                                </div>
                            </div>
                            <div class="text-end border-top pt-3">
                                <button class="btn btn-agae" @click="guardarModulo('guardar_maestra')">
                                    <i class="bi bi-floppy me-1"></i> Guardar Datos Principales
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. INFORMACIÓN DE COBRO -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button d-flex align-items-center"
                            type="button"
                            :class="{ 'collapsed': !abiertos.fpago }"
                            @click="togglePanel('fpago')">
                            <i class="bi bi-wallet2 me-2"></i> 2. Información de Cobro
                            <span class="badge ms-auto me-3" :class="moduloFpagoCompleto ? 'bg-success' : 'bg-danger'">
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

                            <!-- Input Caja de Ahorro BNA con validación estricta de 14 dígitos -->
                            <div class="row mb-3" v-if="form.id_fpago == 1">
                                <div class="col-md-8 offset-md-2">
                                    <div class="p-3 border rounded bg-light">
                                        <label class="form-label text-agae fw-bold">
                                            <i class="bi bi-credit-card me-1"></i> Número de Caja de Ahorro BNA *
                                        </label>
                                        <input type="text"
                                            class="form-control form-control-lg text-center fw-bold text-primary"
                                            v-model="form.numero_cuenta"
                                            @input="validarNumeroCuenta"
                                            placeholder="Ingrese los 14 dígitos numéricos"
                                            maxlength="14">
                                        <div class="d-flex justify-content-between mt-2">
                                            <small class="text-muted">Formato exclusivo para débitos automáticos del Banco Nación.</small>
                                            <small class="fw-bold" :class="form.numero_cuenta && form.numero_cuenta.length === 14 ? 'text-success' : 'text-danger'">
                                                {{ form.numero_cuenta ? form.numero_cuenta.length : 0 }} / 14 dígitos
                                            </small>
                                        </div>
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

                <!-- 3. DATOS DE IDENTIDAD -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button d-flex align-items-center"
                            type="button"
                            :class="{ 'collapsed': !abiertos.identidad }"
                            @click="togglePanel('identidad')">
                            <i class="bi bi-person-vcard me-2"></i> 3. Datos de Identidad
                            <span class="badge ms-auto me-3" :class="moduloIdentidadCompleto ? 'bg-success' : 'bg-danger'">
                                {{ moduloIdentidadCompleto ? 'Completo' : 'Incompleto' }}
                            </span>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" :class="{ 'show': abiertos.identidad }">
                        <div class="accordion-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="cuil" class="form-label fw-bold">CUIL</label>
                                    <input
                                        type="text"
                                        id="cuil"
                                        class="form-control"
                                        :value="cuilFormateado"
                                        @input="alEscribirCuil"
                                        placeholder="20-12345678-9"
                                        maxlength="13">
                                    <small class="text-muted">Se guarda automáticamente limpio (11 dígitos).</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Nacionalidad</label>
                                    <input type="text" class="form-control" v-model="form.nacionalidad">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Sexo *</label>
                                    <select v-model="form.sexo" class="form-select">
                                        <option value="">Seleccione...</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                        <option value="X">Otro</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Estado Civil *</label>
                                    <select class="form-select" v-model="form.estado_civil">
                                        <option value="" disabled>Seleccione...</option>
                                        <option v-for="ec in opcionesEstadoCivil" :key="ec" :value="ec">{{ ec }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Fecha Nacimiento</label>
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

                <!-- 4. DOMICILIO -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button d-flex align-items-center"
                            type="button"
                            :class="{ 'collapsed': !abiertos.domicilio }"
                            @click="togglePanel('domicilio')">
                            <i class="bi bi-geo-alt me-2"></i> 4. Domicilio
                            <span class="badge ms-auto me-3" :class="moduloDomicilioCompleto ? 'bg-success' : 'bg-danger'">
                                {{ moduloDomicilioCompleto ? 'Completo' : 'Incompleto' }}
                            </span>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" :class="{ 'show': abiertos.domicilio }">
                        <div class="accordion-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Dirección (Calle y Número)</label>
                                    <input type="text" class="form-control" v-model="form.domicilio">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Localidad</label>
                                    <input type="text" class="form-control" v-model="form.localidad">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Provincia</label>
                                    <input type="text" class="form-control" v-model="form.provincia">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Código Postal</label>
                                    <input type="text" class="form-control" v-model="form.codigo_postal">
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

                <!-- 5. NIVEL ACADÉMICO -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button d-flex align-items-center"
                            type="button"
                            :class="{ 'collapsed': !abiertos.educacion }"
                            @click="togglePanel('educacion')">
                            <i class="bi bi-book me-2"></i> 5. Nivel Académico
                            <span class="badge ms-auto me-3" :class="moduloEducacionCompleto ? 'bg-success' : 'bg-danger'">
                                {{ moduloEducacionCompleto ? 'Completo' : 'Incompleto' }}
                            </span>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" :class="{ 'show': abiertos.educacion }">
                        <div class="accordion-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nivel de Estudio *</label>
                                    <select class="form-select" v-model="form.nivel_estudio">
                                        <option value="" disabled>Seleccione nivel...</option>
                                        <option v-for="n in opcionesEducacion" :key="n" :value="n">{{ n }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Título</label>
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

                <!-- 6. DATOS LABORALES -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button d-flex align-items-center"
                            type="button"
                            :class="{ 'collapsed': !abiertos.laboral }"
                            @click="togglePanel('laboral')">
                            <i class="bi bi-briefcase me-2"></i> 6. Datos Laborales
                            <span class="badge ms-auto me-3" :class="moduloLaboralCompleto ? 'bg-success' : 'bg-danger'">
                                {{ moduloLaboralCompleto ? 'Completo' : 'Incompleto' }}
                            </span>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" :class="{ 'show': abiertos.laboral }">
                        <div class="accordion-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Nro. de Legajo</label>
                                    <input type="text" class="form-control" v-model="form.legajo">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label fw-bold">Organismo que Liquida Haberes</label>
                                    <input type="text" class="form-control" v-model="form.org_liquida_haber">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Organismo / Lugar donde Trabaja</label>
                                    <input type="text" class="form-control" v-model="form.org_trabaja">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Domicilio del Trabajo</label>
                                    <input type="text" class="form-control" v-model="form.domicilio_trabajo">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Localidad del Trabajo</label>
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
        const {
            createApp
        } = Vue;

        createApp({
            data() {
                return {
                    cargando: true,
                    // Estructura unificada plana
                    form: {
                        id_afiliado: null,
                        apellidos: '',
                        nombres: '',
                        dni: '',
                        email: '',
                        telefono: '',
                        id_estado: null,
                        estado_nombre: '',
                        id_fpago: 1,
                        numero_cuenta: '',
                        cuil: '',
                        nacionalidad: '',
                        sexo: '',
                        estado_civil: '',
                        fecha_nacimiento: '',
                        domicilio: '',
                        localidad: '',
                        provincia: '',
                        codigo_postal: '',
                        nivel_estudio: '',
                        titulo: '',
                        legajo: '',
                        org_liquida_haber: '',
                        org_trabaja: '',
                        domicilio_trabajo: '',
                        localidad_trabajo: ''
                    },
                    // Apertura y colapso de paneles
                    abiertos: {
                        maestra: false,
                        fpago: false,
                        identidad: false,
                        domicilio: false,
                        educacion: false,
                        laboral: false
                    },
                    // Combos
                    opcionesSexo: ['Masculino', 'Femenino'],
                    opcionesEstadoCivil: ['Soltero/a', 'Casado/a', 'Divorciado/a', 'Viudo/a'],
                    opcionesEducacion: ['Universitario', 'Posgrado']
                }
            },
            computed: {
                // Estados individuales de los 6 módulos (Corregidos con la totalidad de campos)
                moduloMaestraCompleto() {
                    return Boolean(
                        this.form.apellidos &&
                        this.form.nombres &&
                        this.form.dni &&
                        this.form.email &&
                        this.form.telefono
                    );
                },
                moduloFpagoCompleto() {
                    return Boolean(
                        this.form.id_fpago &&
                        (this.form.id_fpago != 1 || (this.form.numero_cuenta && this.form.numero_cuenta.length === 14))
                    );
                },
                moduloIdentidadCompleto() {
                    return Boolean(
                        this.form.cuil &&
                        this.form.nacionalidad &&
                        this.form.sexo &&
                        this.form.estado_civil &&
                        this.form.fecha_nacimiento
                    );
                },
                moduloDomicilioCompleto() {
                    return Boolean(
                        this.form.domicilio &&
                        this.form.localidad &&
                        this.form.provincia &&
                        this.form.codigo_postal
                    );
                },
                moduloEducacionCompleto() {
                    return Boolean(
                        this.form.nivel_estudio &&
                        this.form.titulo
                    );
                },
                moduloLaboralCompleto() {
                    return Boolean(
                        this.form.legajo &&
                        this.form.org_liquida_haber &&
                        this.form.org_trabaja &&
                        this.form.domicilio_trabajo &&
                        this.form.localidad_trabajo
                    );
                },

                // Métricas
                cantidadModulosCompletos() {
                    const modulos = [
                        this.moduloMaestraCompleto,
                        this.moduloFpagoCompleto,
                        this.moduloIdentidadCompleto,
                        this.moduloDomicilioCompleto,
                        this.moduloEducacionCompleto,
                        this.moduloLaboralCompleto
                    ];
                    return modulos.filter(Boolean).length;
                },
                porcentajeProgreso() {
                    return Math.round((this.cantidadModulosCompletos / 6) * 100);
                },

                // Estilos dinámicos
                badgeEstadoClass() {
                    if (this.form.id_estado == 2) return 'bg-success'; // Afiliado
                    if (this.form.id_estado == 3) return 'bg-danger'; // Desafiliado
                    return 'bg-warning text-dark'; // Solicitud
                },
                colorBarraProgreso() {
                    return this.porcentajeProgreso === 100 ? 'bg-success' : 'bg-primary';
                },
                colorTextoProgreso() {
                    return this.porcentajeProgreso === 100 ? 'text-success' : 'text-primary';
                },
                cuilFormateado() {
                    if (!this.form.cuil) return '';

                    // 1. Solo dígitos (máximo 11)
                    let raw = this.form.cuil.toString().replace(/\D/g, '').slice(0, 11);

                    // 2. Aplicamos la máscara XX-XXXXXXXX-X
                    if (raw.length <= 2) return raw;
                    if (raw.length <= 10) return `${raw.slice(0, 2)}-${raw.slice(2)}`;
                    return `${raw.slice(0, 2)}-${raw.slice(2, 10)}-${raw.slice(10)}`;
                }
            },
            methods: {
                togglePanel(modulo) {
                    this.abiertos[modulo] = !this.abiertos[modulo];
                },

                alEscribirCuil(event) {
                    // Extrae solo números (máximo 11) para guardar limpio en BD
                    let soloNumeros = event.target.value.replace(/\D/g, '').slice(0, 11);
                    this.form.cuil = soloNumeros;
                    event.target.value = this.cuilFormateado;
                },

                titleCase(texto) {
                    if (!texto) return '';
                    return texto.toLowerCase().replace(/(?:^|\s|-)\S/g, match => match.toUpperCase());
                },

                formatearDNI(dni) {
                    if (!dni) return '';
                    return dni.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                },

                validarNumeroCuenta(event) {
                    let valor = event.target.value.replace(/\D/g, '');
                    if (valor.length > 14) {
                        valor = valor.slice(0, 14);
                    }
                    this.form.numero_cuenta = valor;
                },

                evaluarAperturaAcordeones() {
                    if (this.porcentajeProgreso === 100) {
                        // Si está 100% completo, todos cerrados
                        Object.keys(this.abiertos).forEach(k => this.abiertos[k] = false);
                    } else {
                        // Abre el primer módulo incompleto en orden secuencial
                        this.abiertos.maestra = !this.moduloMaestraCompleto;
                        this.abiertos.fpago = this.moduloMaestraCompleto && !this.moduloFpagoCompleto;
                        this.abiertos.identidad = this.moduloMaestraCompleto && this.moduloFpagoCompleto && !this.moduloIdentidadCompleto;
                        this.abiertos.domicilio = this.moduloMaestraCompleto && this.moduloFpagoCompleto && this.moduloIdentidadCompleto && !this.moduloDomicilioCompleto;
                        this.abiertos.educacion = this.moduloMaestraCompleto && this.moduloFpagoCompleto && this.moduloIdentidadCompleto && this.moduloDomicilioCompleto && !this.moduloEducacionCompleto;
                        this.abiertos.laboral = this.moduloMaestraCompleto && this.moduloFpagoCompleto && this.moduloIdentidadCompleto && this.moduloDomicilioCompleto && this.moduloEducacionCompleto && !this.moduloLaboralCompleto;
                    }
                },

                async cargarLegajo(id_afiliado) {
                    this.cargando = true;
                    try {
                        const resp = await fetch(`../../controladores/admin_legajo_controlador.php?id=${id_afiliado}`);
                        const resultado = await resp.json();

                        if (resultado.status === 'success') {
                            const d = resultado.data;

                            // 1. Normalización de Sexo -> Mapea a 'M', 'F' o 'X'
                            let sexoLimpio = '';
                            if (d && d.sexo) {
                                const s = d.sexo.toString().trim().toUpperCase();
                                if (s.startsWith('M') || s === 'MASCULINO') {
                                    sexoLimpio = 'M';
                                } else if (s.startsWith('F') || s === 'FEMENINO') {
                                    sexoLimpio = 'F';
                                } else if (s === 'X' || s === 'OTRO') {
                                    sexoLimpio = 'X';
                                }
                            }

                            // 2. Normalización de Estado Civil
                            let estadoCivilLimpio = '';
                            if (d && d.estado_civil) {
                                const ec = d.estado_civil.toString().trim().toLowerCase();
                                if (ec.includes('casad')) estadoCivilLimpio = 'Casado/a';
                                else if (ec.includes('solter')) estadoCivilLimpio = 'Soltero/a';
                                else if (ec.includes('divorci')) estadoCivilLimpio = 'Divorciado/a';
                                else if (ec.includes('viud')) estadoCivilLimpio = 'Viudo/a';
                            }

                            // 3. Normalización de Nivel Académico
                            let nivelEstudioLimpio = '';
                            if (d && d.nivel_estudio) {
                                const ne = d.nivel_estudio.toString().trim().toLowerCase();
                                if (ne.includes('univ')) nivelEstudioLimpio = 'Universitario';
                                else if (ne.includes('posg') || ne.includes('postg')) nivelEstudioLimpio = 'Posgrado';
                                else if (ne.includes('secund')) nivelEstudioLimpio = 'Secundario';
                                else if (ne.includes('terciar')) nivelEstudioLimpio = 'Terciario';
                            }

                            // Asignación unificada limpia
                            this.form = {
                                ...this.form,
                                ...d,
                                sexo: sexoLimpio,
                                estado_civil: estadoCivilLimpio,
                                nivel_estudio: nivelEstudioLimpio,
                                domicilio: d.domicilio || (d.calle ? `${d.calle} ${d.numero || ''}`.trim() : ''),
                                legajo: d.legajo || d.numero_legajo || '',
                                org_liquida_haber: d.org_liquida_haber || d.organismo_liquidador || '',
                                org_trabaja: d.org_trabaja || d.organismo_trabajo || ''
                            };

                            this.evaluarAperturaAcordeones();
                        } else {
                            Swal.fire('Error', resultado.message, 'error');
                        }
                    } catch (error) {
                        console.error("Error al cargar legajo:", error);
                        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                    } finally {
                        this.cargando = false;
                    }
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
                                id_afiliado: this.form.id_afiliado,
                                datos: this.form
                            })
                        });
                        const resultado = await resp.json();

                        if (resultado.status === 'success') {
                            Swal.fire('¡Guardado!', 'Los datos fueron actualizados correctamente.', 'success');
                            this.cargarLegajo(this.form.id_afiliado);
                        } else {
                            Swal.fire('Error', resultado.message, 'error');
                        }
                    } catch (error) {
                        Swal.fire('Error', 'Ocurrió un error al guardar los cambios.', 'error');
                    }
                }
            },
            mounted() {
                const urlParams = new URLSearchParams(window.location.search);
                const id = urlParams.get('id');
                if (id) {
                    this.cargarLegajo(id);
                }
            }
        }).mount('#appLegajo');
    </script>
</body>

</html>