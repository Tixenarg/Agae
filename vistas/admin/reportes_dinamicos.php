<?php
// 1. Requerimos el header (esto valida la sesión y carga el de HTML/CSS)
require_once 'header_admin.php';
?>

<!-- Cargar Vue.js (CDN para prototipado rápido) y SheetJS para exportar a Excel -->
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>

<!-- Contenedor Principal de la App Vue -->
<div id="appReportes" class="container-fluid mt-4" v-cloak>
    <h2 class="mb-4">Generador de Reportes Dinámico</h2>

    <!-- 1. PANEL DE FILTROS MAESTROS -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white fw-bold">
            1. Filtros Principales
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Estado del Afiliado</label>
                    <select v-model="filtros.id_estado" class="form-select">
                        <option value="TODOS">TODOS</option>
                        <option v-for="est in opciones.estados" :key="est.id_estado" :value="est.id_estado">
                            {{ est.estado_nombre }}
                        </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Forma de Pago</label>
                    <select v-model="filtros.id_fpago" class="form-select">
                        <option value="TODOS">TODOS</option>
                        <option v-for="fp in opciones.fpagos" :key="fp.id_fpago" :value="fp.id_fpago">
                            {{ fp.fpago_nombre }}
                        </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-success w-100" @click="generarReporte" :disabled="cargando">
                        <span v-if="cargando" class="spinner-border spinner-border-sm me-2"></span>
                        <i class="bi bi-search me-1"></i> Generar / Actualizar Datos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. SELECTOR DE COLUMNAS (Solo visible si hay datos) -->
    <div class="card shadow-sm mb-4" v-if="datosReporte.length > 0">
        <div class="card-header bg-secondary text-white fw-bold d-flex justify-content-between align-items-center">
            <span>2. Seleccionar Columnas a Visualizar</span>
            <button class="btn btn-sm btn-light" @click="exportarExcel">
                <i class="bi bi-file-earmark-excel text-success"></i> Exportar a Excel
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-2" v-for="(columna, index) in columnasDisponibles" :key="index">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" v-model="columna.visible" :id="'col_'+index">
                        <label class="form-check-label user-select-none" :for="'col_'+index">
                            {{ columna.label }}
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. TABLA DE RESULTADOS -->
    <div class="card shadow-sm" v-if="datosReporte.length > 0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0" id="tablaReporte">
                    <thead class="table-dark">
                        <tr>
                            <!-- Renderizado reactivo de encabezados -->
                            <th v-for="col in columnasVisibles" :key="col.key">
                                {{ col.label }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Renderizado reactivo de filas -->
                        <tr v-for="fila in datosReporte" :key="fila.id_afiliado">
                            <td v-for="col in columnasVisibles" :key="col.key">
                                {{ fila[col.key] || '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted text-end">
            Total registros: <strong>{{ datosReporte.length }}</strong>
        </div>
    </div>

    <!-- Mensaje Estado Vacío -->
    <div class="alert alert-info text-center shadow-sm" v-if="datosReporte.length === 0 && !cargando && primeraBusquedaHecha">
        No se encontraron resultados para los filtros seleccionados.
    </div>
</div>

<!-- Lógica Reactiva de Vue.js -->
<script>
    const {
        createApp
    } = Vue;

    createApp({
        data() {
            return {
                cargando: false,
                primeraBusquedaHecha: false,
                filtros: {
                    id_estado: 'TODOS',
                    id_fpago: 'TODOS'
                },
                opciones: {
                    estados: [],
                    fpagos: []
                },
                datosReporte: [],
                columnasDisponibles: [{
                        key: 'dni',
                        label: 'DNI',
                        visible: true
                    },
                    {
                        key: 'apellidos',
                        label: 'Apellidos',
                        visible: true
                    },
                    {
                        key: 'nombres',
                        label: 'Nombres',
                        visible: true
                    },
                    {
                        key: 'estado_nombre',
                        label: 'Estado',
                        visible: true
                    },
                    {
                        key: 'fpago_nombre',
                        label: 'Forma de Pago',
                        visible: true
                    },
                    {
                        key: 'telefono',
                        label: 'Teléfono',
                        visible: false
                    },
                    {
                        key: 'email',
                        label: 'Email',
                        visible: false
                    },
                    {
                        key: 'cuil',
                        label: 'CUIL',
                        visible: false
                    },
                    {
                        key: 'sexo',
                        label: 'Sexo',
                        visible: false
                    },
                    {
                        key: 'fecha_nacimiento',
                        label: 'Nacimiento',
                        visible: false
                    },
                    {
                        key: 'numero_cuenta',
                        label: 'Nº Cuenta',
                        visible: false
                    },
                    {
                        key: 'direccion',
                        label: 'Domicilio',
                        visible: false
                    },
                    {
                        key: 'localidad',
                        label: 'Localidad',
                        visible: false
                    },
                    {
                        key: 'nivel_estudio',
                        label: 'Estudios',
                        visible: false
                    },
                    {
                        key: 'org_trabaja',
                        label: 'Org. Trabaja',
                        visible: false
                    }
                ]
            }
        },
        computed: {
            // Se recalcula automáticamente cada vez que un checkbox cambia
            columnasVisibles() {
                return this.columnasDisponibles.filter(col => col.visible);
            }
        },
        methods: {
            async cargarFiltrosIniciales() {
                try {
                    const resp = await fetch('../../controladores/reportes_controlador.php?accion=get_parametros');
                    const resultado = await resp.json();

                    if (resultado.status === 'success') {
                        this.opciones.estados = resultado.data.estados;
                        this.opciones.fpagos = resultado.data.fpagos;
                    } else {
                        // Ahora, si el backend manda un error de permisos u otro, lo veremos en pantalla
                        alert("Error del servidor: " + resultado.message);
                    }
                } catch (error) {
                    console.error("Error al cargar filtros:", error);
                    alert("No se pudo conectar con el servidor al cargar los filtros.");
                }
            },

            async generarReporte() {
                this.cargando = true;
                this.primeraBusquedaHecha = true;
                try {
                    const resp = await fetch('../../controladores/reportes_controlador.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            accion: 'generar_reporte',
                            id_estado: this.filtros.id_estado,
                            id_fpago: this.filtros.id_fpago
                        })
                    });
                    const resultado = await resp.json();

                    if (resultado.status === 'success') {
                        this.datosReporte = resultado.data;
                    } else {
                        alert(resultado.message);
                    }
                } catch (error) {
                    console.error("Error al generar reporte:", error);
                    alert("Ocurrió un error al contactar al servidor.");
                } finally {
                    this.cargando = false;
                }
            },

            exportarExcel() {
                // 1. Armamos un array JSON solo con las columnas que el usuario está viendo
                const datosFormateados = this.datosReporte.map(fila => {
                    let obj = {};
                    this.columnasVisibles.forEach(col => {
                        obj[col.label] = fila[col.key];
                    });
                    return obj;
                });

                // 2. Usamos la librería JS para compilar el Excel en el navegador
                const worksheet = XLSX.utils.json_to_sheet(datosFormateados);
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, "Reporte");
                XLSX.writeFile(workbook, "Reporte_Afiliados.xlsx");
            }
        },
        mounted() {
            this.cargarFiltrosIniciales();
        }
    }).mount('#appReportes');
</script>

<?php
// 2. Requerimos el footer (esto cierra los contenedores y carga scripts locales)
require_once 'footer_admin.php';
?>