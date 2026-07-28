<?php
// 1. Requerimos el header (valida sesión y carga el layout base con CSS)
require_once 'header_admin.php';
?>

<!-- Librerías de cliente: Vue 3 y SheetJS para lectura/escritura de Excel -->
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>

<!-- Contenedor Principal Vue -->
<div id="appCruce" class="container-fluid mt-4" v-cloak>
    <h2 class="mb-4"><i class="bi bi-intersect me-2"></i>Cruce de Padrones con AGAE</h2>

    <!-- 1. CARGA DE ARCHIVO Y PREPARACIÓN -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white fw-bold">
            1. Cargar Archivo Excel para el Cruce
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Seleccioná el listado externo (.xlsx, .xls, .csv):</label>
                    <input 
                        type="file" 
                        class="form-control" 
                        accept=".xlsx, .xls, .csv" 
                        @change="procesarArchivoExcel"
                        :disabled="cargandoExcel || procesandoCruce"
                    >
                </div>

                <!-- Badge de confirmación de archivo cargado -->
                <div class="col-md-4 mt-3 mt-md-0" v-if="nombreArchivo">
                    <div class="p-2 border rounded bg-light d-flex align-items-center">
                        <i class="bi bi-file-earmark-spreadsheet-fill text-success fs-3 me-2"></i>
                        <div class="text-truncate">
                            <small class="text-muted d-block">Archivo cargado:</small>
                            <strong class="text-dark">{{ nombreArchivo }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loader de preparación (Aparece al instante) -->
            <div class="alert alert-info mt-3 d-flex align-items-center mb-0" v-if="cargandoExcel">
                <div class="spinner-border spinner-border-sm text-info me-3" role="status"></div>
                <div>
                    <strong>Preparando archivo...</strong> Leyendo hojas y estructura de columnas. Aguarde un segundo.
                </div>
            </div>
        </div>
    </div>

    <!-- 2. SELECCIÓN DE COLUMNA Y ACCIÓN DE CRUCE -->
    <div class="card shadow-sm mb-4" v-if="columnasDisponibles.length > 0 && !cargandoExcel">
        <div class="card-header bg-secondary text-white fw-bold">
            2. Configurar Columna de DNI
        </div>
        <div class="card-body">
            <div class="row align-items-end g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Seleccioná la columna que contiene los DNIs:</label>
                    <select v-model="columnaDniSeleccionada" class="form-select">
                        <option value="" disabled>-- Seleccionar Columna --</option>
                        <option v-for="col in columnasDisponibles" :key="col" :value="col">
                            {{ col }}
                        </option>
                    </select>
                </div>
                <div class="col-md-6">
                    <button 
                        class="btn btn-success w-100" 
                        @click="ejecutarCruce" 
                        :disabled="!columnaDniSeleccionada || procesandoCruce"
                    >
                        <span v-if="procesandoCruce" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-cpu me-1"></i> 
                        {{ procesandoCruce ? 'Cruzando Datos con la BD...' : 'Ejecutar Cruce de Datos' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. RESUMEN Y RESULTADOS DEL CRUCE -->
    <div v-if="resultadoCruce.length > 0">
        <!-- Tarjetas de métricas rápidas -->
        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <div class="card bg-primary text-white shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="card-title text-uppercase mb-1">Total Procesados</h6>
                        <h2 class="fw-bold mb-0">{{ resumen.total }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="card-title text-uppercase mb-1">Encontrados en AGAE</h6>
                        <h2 class="fw-bold mb-0">{{ resumen.encontrados }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="card-title text-uppercase mb-1">No Encontrados</h6>
                        <h2 class="fw-bold mb-0">{{ resumen.noEncontrados }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla detallada -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-table me-2"></i>Resultado Detallado del Cruce</span>
                <button class="btn btn-sm btn-success" @click="exportarResultadoExcel">
                    <i class="bi bi-file-earmark-excel me-1"></i> Exportar Resultado Completo a Excel
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>DNI Buscado</th>
                                <th>Estado en AGAE</th>
                                <th>Afiliado (AGAE)</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Estado Afiliación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Iteración paginada para mantener el DOM ultra liviano -->
                            <tr v-for="(item, index) in resultadosPaginados" :key="index">
                                <td>{{ (paginaActual - 1) * elementosPorPagina + index + 1 }}</td>
                                <td><strong>{{ item.dni_buscado }}</strong></td>
                                <td>
                                    <span v-if="item.encontrado" class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i> ENCONTRADO
                                    </span>
                                    <span v-else class="badge bg-danger">
                                        <i class="bi bi-x-circle me-1"></i> NO REGISTRADO
                                    </span>
                                </td>
                                <td>
                                    <span v-if="item.encontrado">
                                        {{ item.agae_datos.apellidos }}, {{ item.agae_datos.nombres }}
                                    </span>
                                    <span v-else class="text-muted">-</span>
                                </td>
                                <td>{{ item.agae_datos ? (item.agae_datos.telefono || '-') : '-' }}</td>
                                <td>{{ item.agae_datos ? (item.agae_datos.email || '-') : '-' }}</td>
                                <td>
                                    <span v-if="item.agae_datos" class="badge bg-info text-dark">
                                        {{ item.agae_datos.estado_nombre }}
                                    </span>
                                    <span v-else class="text-muted">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- NAVEGACIÓN Y PAGINACIÓN FLUIDA -->
            <div class="card-footer d-flex justify-content-between align-items-center bg-light">
                <small class="text-muted">
                    Mostrando página <strong>{{ paginaActual }}</strong> de <strong>{{ totalPaginas }}</strong> 
                    ({{ resultadoCruce.length }} registros totales en memoria)
                </small>
                <div class="btn-group btn-group-sm">
                    <button 
                        class="btn btn-outline-secondary" 
                        :disabled="paginaActual === 1" 
                        @click="paginaActual--"
                    >
                        <i class="bi bi-chevron-left"></i> Anterior
                    </button>
                    <button 
                        class="btn btn-outline-secondary" 
                        :disabled="paginaActual >= totalPaginas" 
                        @click="paginaActual++"
                    >
                        Siguiente <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lógica Reactiva de Vue.js -->
<script>
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                nombreArchivo: '',
                cargandoExcel: false,
                procesandoCruce: false,
                filasRawExcel: [],
                columnasDisponibles: [],
                columnaDniSeleccionada: '',
                resultadoCruce: [],
                
                // Control de paginación
                paginaActual: 1,
                elementosPorPagina: 50,

                resumen: {
                    total: 0,
                    encontrados: 0,
                    noEncontrados: 0
                }
            }
        },
        computed: {
            // Recorta los resultados para mostrar únicamente 50 filas por vista
            resultadosPaginados() {
                const inicio = (this.paginaActual - 1) * this.elementosPorPagina;
                const fin = inicio + this.elementosPorPagina;
                return this.resultadoCruce.slice(inicio, fin);
            },
            totalPaginas() {
                return Math.ceil(this.resultadoCruce.length / this.elementosPorPagina) || 1;
            }
        },
        methods: {
            procesarArchivoExcel(event) {
                const file = event.target.files[0];
                if (!file) return;

                this.nombreArchivo = file.name;
                this.cargandoExcel = true;
                this.columnasDisponibles = [];
                this.columnaDniSeleccionada = '';
                this.resultadoCruce = [];
                this.filasRawExcel = [];

                const reader = new FileReader();

                reader.onload = (e) => {
                    const data = new Uint8Array(e.target.result);

                    // Pide un tick al Event Loop para pintar el spinner antes de procesar el binario
                    setTimeout(() => {
                        try {
                            const workbook = XLSX.read(data, { type: 'array' });
                            const primeraHoja = workbook.SheetNames[0];
                            const hoja = workbook.Sheets[primeraHoja];

                            this.filasRawExcel = XLSX.utils.sheet_to_json(hoja, { defval: '' });

                            if (this.filasRawExcel.length > 0) {
                                this.columnasDisponibles = Object.keys(this.filasRawExcel[0]);
                                
                                // Autodetección básica de la columna DNI
                                const probableCol = this.columnasDisponibles.find(col => 
                                    /dni|documento|cedula/i.test(col)
                                );
                                if (probableCol) {
                                    this.columnaDniSeleccionada = probableCol;
                                }
                            } else {
                                alert("El archivo seleccionado está vacío.");
                            }
                        } catch (err) {
                            console.error("Error leyendo Excel:", err);
                            alert("No se pudo procesar la estructura del Excel. Verificá que sea un archivo válido.");
                        } finally {
                            this.cargandoExcel = false;
                        }
                    }, 100);
                };

                reader.readAsArrayBuffer(file);
            },

            limpiarDni(val) {
                if (!val) return '';
                return String(val).replace(/[^0-9]/g, '');
            },

            async ejecutarCruce() {
                if (!this.columnaDniSeleccionada) {
                    alert("Por favor seleccioná la columna que contiene los DNIs.");
                    return;
                }

                this.procesandoCruce = true;
                this.paginaActual = 1;

                const dnisOriginales = this.filasRawExcel.map(fila => fila[this.columnaDniSeleccionada]);
                const dnisLimpios = dnisOriginales
                    .map(dni => this.limpiarDni(dni))
                    .filter(dni => dni.length >= 6 && dni.length <= 9);

                if (dnisLimpios.length === 0) {
                    alert("No se encontraron DNIs válidos (de 6 a 9 dígitos) en la columna seleccionada.");
                    this.procesandoCruce = false;
                    return;
                }

                try {
                    const resp = await fetch('../../controladores/cruce_controlador.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            accion: 'cruzar_padron',
                            dnis: dnisLimpios
                        })
                    });

                    const res = await resp.json();

                    if (res.status === 'success') {
                        // Mapa Hash O(1) para cruce relámpago
                        const mapaAgae = {};
                        res.data.forEach(item => {
                            mapaAgae[String(item.dni).trim()] = item;
                        });

                        let contadorEncontrados = 0;
                        let contadorNoEncontrados = 0;

                        this.resultadoCruce = this.filasRawExcel.map(filaOriginal => {
                            const dniLimpio = this.limpiarDni(filaOriginal[this.columnaDniSeleccionada]);
                            const coincidencia = mapaAgae[dniLimpio] || null;

                            if (coincidencia) {
                                contadorEncontrados++;
                            } else {
                                contadorNoEncontrados++;
                            }

                            return {
                                dni_buscado: dniLimpio || filaOriginal[this.columnaDniSeleccionada],
                                fila_original: filaOriginal,
                                encontrado: !!coincidencia,
                                agae_datos: coincidencia
                            };
                        });

                        this.resumen = {
                            total: this.resultadoCruce.length,
                            encontrados: contadorEncontrados,
                            noEncontrados: contadorNoEncontrados
                        };

                    } else {
                        alert(res.message || "Error al procesar el cruce.");
                    }
                } catch (err) {
                    console.error("Error al ejecutar cruce:", err);
                    alert("Ocurrió un error al conectar con el servidor.");
                } finally {
                    this.procesandoCruce = false;
                }
            },

            exportarResultadoExcel() {
                if (this.resultadoCruce.length === 0) return;

                // Exporta la totalidad de la matriz 'resultadoCruce' independientemente de la página actual
                const datosExportar = this.resultadoCruce.map(item => {
                    const fila = { ...item.fila_original };

                    fila['--- CRUCE AGAE ---'] = item.encontrado ? 'ENCONTRADO' : 'NO REGISTRADO';
                    fila['AGAE - Apellidos'] = item.agae_datos ? item.agae_datos.apellidos : '';
                    fila['AGAE - Nombres'] = item.agae_datos ? item.agae_datos.nombres : '';
                    fila['AGAE - DNI'] = item.agae_datos ? item.agae_datos.dni : '';
                    fila['AGAE - Teléfono'] = item.agae_datos ? item.agae_datos.telefono : '';
                    fila['AGAE - Email'] = item.agae_datos ? item.agae_datos.email : '';
                    fila['AGAE - Estado'] = item.agae_datos ? item.agae_datos.estado_nombre : '';

                    return fila;
                });

                const worksheet = XLSX.utils.json_to_sheet(datosExportar);
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, "Resultado Cruce");
                XLSX.writeFile(workbook, `Cruce_Padron_AGAE_${new Date().toISOString().slice(0,10)}.xlsx`);
            }
        }
    }).mount('#appCruce');
</script>

<?php
// 2. Requerimos el footer (cierra contenedores y carga Bootstrap JS local)
require_once 'footer_admin.php';
?>