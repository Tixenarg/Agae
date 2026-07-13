<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legajo Digital - AGAE (Acordeón)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .accordion-button { font-weight: 600; color: #19248B; }
        .accordion-button:not(.collapsed) { background-color: #e9ecef; color: #19248B; box-shadow: none; }
        .accordion-button:focus { box-shadow: none; border-color: rgba(0,0,0,.125); }
    </style>
</head>
<body>
    <div id="appLegajo" class="container py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-primary mb-0" style="color: #19248B !important;">Legajo #{{ id_afiliado }}</h2>
                <p class="text-muted">Carga y edición de datos del afiliado</p>
            </div>
            <div>
                <a href="bandeja_entrada.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
                <button class="btn btn-success ms-2" @click="guardarLegajo"><i class="bi bi-floppy"></i> Guardar Todo</button>
            </div>
        </div>

        <div class="accordion shadow-sm" id="acordeonLegajo">
            
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelIdentidad" aria-expanded="true">
                        <i class="bi bi-person-vcard me-2"></i> 1. Datos de Identidad
                    </button>
                </h2>
                <div id="panelIdentidad" class="accordion-collapse collapse show" data-bs-parent="#acordeonLegajo">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">DNI</label>
                                <input type="text" class="form-control" v-model="afiliado.dni" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CUIL</label>
                                <input type="text" class="form-control" v-model="afiliado.cuil">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado Civil</label>
                                <select class="form-select" v-model="afiliado.estado_civil">
                                    <option value="Casado">Casado</option>
                                    <option value="Soltero">Soltero</option>
                                    <option value="Divorciado">Divorciado</option>
                                    <option value="Viudo">Viudo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelDomicilio">
                        <i class="bi bi-geo-alt me-2"></i> 2. Domicilio y Contacto
                    </button>
                </h2>
                <div id="panelDomicilio" class="accordion-collapse collapse" data-bs-parent="#acordeonLegajo">
                    <div class="accordion-body">
                        <p class="text-muted">Acá irán los campos de Domicilios (Localidad, Provincia, etc)</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelEducacion">
                        <i class="bi bi-book me-2"></i> 3. Nivel Académico
                    </button>
                </h2>
                <div id="panelEducacion" class="accordion-collapse collapse" data-bs-parent="#acordeonLegajo">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nivel de Estudio</label>
                                <select class="form-select" v-model="educacion.nivel_estudio">
                                    <option value="Universitario">Universitario</option>
                                    <option value="Posgrado">Posgrado</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Título</label>
                                <input type="text" class="form-control" v-model="educacion.titulo" placeholder="Ej: Abogado">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelLaboral">
                        <i class="bi bi-briefcase me-2"></i> 4. Datos Laborales
                    </button>
                </h2>
                <div id="panelLaboral" class="accordion-collapse collapse" data-bs-parent="#acordeonLegajo">
                    <div class="accordion-body">
                        <p class="text-muted">Acá irán los campos de Legajo, Repartición, etc.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    id_afiliado: null,
                    afiliado: { dni: '', cuil: '', estado_civil: 'Casado' },
                    domicilio: {},
                    educacion: { nivel_estudio: 'Universitario', titulo: '' },
                    laboral: {}
                }
            },
            mounted() {
                const urlParams = new URLSearchParams(window.location.search);
                this.id_afiliado = urlParams.get('id');
                
                if(this.id_afiliado) {
                    this.cargarDatosLegajo();
                }
            },
            methods: {
                cargarDatosLegajo() {
                    console.log("Fetch para traer los datos del afiliado " + this.id_afiliado);
                },
                guardarLegajo() {
                    console.log("Fetch para guardar todos los módulos del acordeón juntos");
                }
            }
        }).mount('#appLegajo');
    </script>
</body>
</html>