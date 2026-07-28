<?php require_once 'header_admin.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrón de Afiliados - AGAE</title>
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- JS Dependencies (Vue 3 + SweetAlert2) -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .text-agae {
            color: #19248B !important;
        }

        .bg-agae {
            background-color: #19248B !important;
        }

        .border-agae {
            border-color: #19248B !important;
        }

        .btn-outline-agae {
            color: #19248B;
            border-color: #19248B;
        }

        /* ⬇️ PEGAR A CONTINUACIÓN ⬇️ */
        .btn-agae {
            background-color: #19248B !important;
            color: #ffffff !important;
        }

        .btn-agae:hover {
            background-color: #121a68 !important;
            color: #ffffff !important;
        }

        .btn-outline-agae:hover {
            background-color: #19248B;
            color: white;
        }

        /* Estilos para Tarjetas KPI interactivos */
        .kpi-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .kpi-card.active {
            border-left: 5px solid #19248B !important;
        }
    </style>
</head>

<body>

    <div id="appPadron" class="container-fluid px-4 py-4">

        <!-- Encabezado Principal -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0 text-agae fw-bold"><i class="bi bi-people-fill me-2"></i>Padrón General AGAE</h2>
                <p class="text-muted mb-0">Gestión de afiliados, solicitudes y control de legajos</p>
            </div>
            <div>
                <button class="btn btn-agae text-white shadow-sm" @click="cargarPadron">
                    <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
                </button>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- MÓDULO: TARJETAS DASHBOARD (KPIs)                          -->
        <!-- ========================================================== -->
        <div class="row g-3 mb-4">
            <!-- 1. Total Padrón Historico -->
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm border-0 border-start border-4 border-secondary kpi-card h-100 bg-white py-2">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase fw-bold">Total Padrón</span>
                                <h3 class="fw-bold mb-0 text-dark mt-1">{{ stats.total_padron }}</h3>
                            </div>
                            <div class="rounded-circle bg-light p-3 text-secondary">
                                <i class="bi bi-collection-fill fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Afiliados Activos (Estado 2) -->
            <div class="col-xl-3 col-md-6" @click="cambiarEstadoListado(2)">
                <div class="card shadow-sm border-0 border-start border-4 border-success kpi-card h-100 bg-white py-2"
                    :class="{ 'active': estadoListado === 2 }">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-success small text-uppercase fw-bold">Afiliados Activos</span>
                                <h3 class="fw-bold mb-0 text-success mt-1">{{ stats.total_activos }}</h3>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                                <i class="bi bi-person-check-fill fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Desafiliados / Bajas (Estado 3) -->
            <div class="col-xl-3 col-md-6" @click="cambiarEstadoListado(3)">
                <div class="card shadow-sm border-0 border-start border-4 border-danger kpi-card h-100 bg-white py-2"
                    :class="{ 'active': estadoListado === 3 }">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-danger small text-uppercase fw-bold">Desafiliados</span>
                                <h3 class="fw-bold mb-0 text-danger mt-1">{{ stats.total_desafiliados }}</h3>
                            </div>
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger">
                                <i class="bi bi-person-x-fill fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Solicitudes Web Pendientes (Estado 1) -->
            <div class="col-xl-3 col-md-6" @click="cambiarEstadoListado(1)">
                <div class="card shadow-sm border-0 border-start border-4 border-warning kpi-card h-100 bg-white py-2"
                    :class="{ 'active': estadoListado === 1 }">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-warning small text-uppercase fw-bold">Solicitudes Web</span>
                                <h3 class="fw-bold mb-0 text-warning mt-1">{{ stats.total_solicitudes }}</h3>
                            </div>
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                                <i class="bi bi-clock-history fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- BARRA UNIFICADA DE FILTROS Y BÚSQUEDA                      -->
        <!-- ========================================================== -->
        <div class="card shadow-sm border-0 mb-4 bg-white">
            <div class="card-body p-3">
                <div class="row g-3 align-items-end">

                    <!-- Pestaña / Estado del Listado -->
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1">Ver Estado:</label>
                        <select class="form-select" v-model="estadoListado" @change="cargarPadron">
                            <option :value="2">🟢 Afiliados Activos</option>
                            <option :value="3">🔴 Desafiliados / Bajas</option>
                            <option :value="1">🟡 Solicitudes Pendientes</option>
                        </select>
                    </div>

                    <!-- Filtro por Forma de Pago -->
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1">Forma de Pago:</label>
                        <select class="form-select" v-model="filtroFormaPago">
                            <option value="todas">💳 Todas las formas de pago</option>
                            <option v-for="fp in formasPago" :key="fp.id_fpago" :value="fp.id_fpago">
                                {{ fp.fpago_nombre }}
                            </option>
                        </select>
                    </div>

                    <!-- Filtro por Semáforo de Completitud (6/6) -->
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1">Estado Legajo:</label>
                        <select class="form-select" v-model="filtroEstado">
                            <option value="todos">📁 Todos</option>
                            <option value="completo">🟢 Completo (6/6)</option>
                            <option value="incompleto">🔴 Incompleto (&lt;6)</option>
                        </select>
                    </div>

                    <!-- Buscador Inteligente -->
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1">Búsqueda Rápida:</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" v-model="terminoBusqueda" placeholder="Buscar por DNI, Apellido o Nombre...">
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- TABLA PRINCIPAL DEL PADRÓN                                 -->
        <!-- ========================================================== -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted">Apellidos y Nombres</th>
                                <th class="text-muted">DNI</th>
                                <th class="text-muted">Forma de Pago</th>
                                <th class="text-muted">Estado Legajo</th>
                                <th class="text-muted">Fecha Afiliación</th>
                                <th class="text-end px-4 text-muted">Acciones</th>
                            </tr>
                        </thead>

                        <!-- Cargando -->
                        <tbody v-if="cargando">
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="spinner-border text-agae" role="status"></div>
                                    <p class="text-muted mt-2 mb-0">Cargando datos del padrón...</p>
                                </td>
                            </tr>
                        </tbody>

                        <!-- Sin Registros -->
                        <tbody v-else-if="afiliadosPaginados.length === 0">
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-search fs-1 d-block mb-2"></i>
                                    No se encontraron registros que coincidan con los filtros seleccionados.
                                </td>
                            </tr>
                        </tbody>

                        <!-- Lista de Afiliados -->
                        <tbody v-else>
                            <tr v-for="afiliado in afiliadosPaginados" :key="afiliado.id_afiliado">
                                <!-- Nombres en Title Case -->
                                <td class="px-4 fw-bold text-dark">
                                    {{ formatearTitleCase(afiliado.apellidos) }}, {{ formatearTitleCase(afiliado.nombres) }}
                                </td>

                                <!-- REEMPLAZAR POR: -->
                                <td><code class="text-dark fw-bold fs-6">{{ formatearDNI(afiliado.dni) }}</code></td>

                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-credit-card me-1 text-agae"></i>
                                        {{ afiliado.forma_pago_nombre }}
                                    </span>
                                </td>
                                <td>
                                    <!-- Insignia Semáforo 6/6 Completo -->
                                    <span v-if="obtenerPuntaje(afiliado) === 6" class="badge bg-success py-2 px-3">
                                        <i class="bi bi-check-circle-fill me-1"></i> Completo (6/6)
                                    </span>
                                    <!-- Insignia Semáforo Incompleto -->
                                    <span v-else class="badge bg-warning text-dark py-2 px-3">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Incompleto ({{ obtenerPuntaje(afiliado) }}/6)
                                    </span>
                                </td>
                                <td>{{ formatearFecha(afiliado.fecha_afiliacion) }}</td>
                                <td class="text-end px-4">
                                    <!-- Ver Legajo -->
                                    <a :href="'legajo.php?id=' + afiliado.id_afiliado" class="btn btn-sm btn-outline-agae" title="Abrir Expediente">
                                        <i class="bi bi-folder2-open me-1"></i> Legajo
                                    </a>

                                    <!-- Botón Dar de Baja (Si está activo) -->
                                    <button v-if="estadoListado === 2"
                                        class="btn btn-sm btn-outline-danger ms-1"
                                        title="Desafiliar / Dar de Baja"
                                        @click="desafiliarAfiliado(afiliado.id_afiliado, afiliado.nombres + ' ' + afiliado.apellidos)">
                                        <i class="bi bi-person-x-fill"></i>
                                    </button>

                                    <!-- Botón Reactivar / Aprobar (Si está en baja o es solicitud) -->
                                    <button v-else
                                        class="btn btn-sm btn-outline-success ms-1"
                                        title="Aprobar / Reactivar Alta"
                                        @click="reafiliarAfiliado(afiliado.id_afiliado, afiliado.nombres + ' ' + afiliado.apellidos)">
                                        <i class="bi bi-person-check-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ========================================================== -->
            <!-- PIE DE TABLA CON PAGINACIÓN COMPRIMIDA INTELIGENTE          -->
            <!-- ========================================================== -->
            <div class="card-footer bg-white px-4 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="text-muted small">
                    Mostrando del <strong>{{ inicioRegistro }}</strong> al
                    <strong>{{ finRegistro }}</strong> de
                    <strong>{{ afiliadosFiltrados.length }}</strong> afiliados filtrados
                </span>

                <nav v-if="totalPaginas > 1" aria-label="Navegación de páginas">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Anterior -->
                        <li class="page-item" :class="{ disabled: paginaActual === 1 }">
                            <button class="page-link text-agae" @click="cambiarPagina(paginaActual - 1)">&laquo;</button>
                        </li>

                        <!-- Rango Comprimido con Ventana Deslizante -->
                        <li v-for="(pag, idx) in paginasVisibles" :key="idx" class="page-item" :class="{ active: paginaActual === pag, disabled: pag === '...' }">
                            <button v-if="pag !== '...'"
                                class="page-link"
                                :class="paginaActual === pag ? 'bg-agae text-white border-agae' : 'text-agae'"
                                @click="cambiarPagina(pag)">
                                {{ pag }}
                            </button>
                            <span v-else class="page-link text-muted">...</span>
                        </li>

                        <!-- Siguiente -->
                        <li class="page-item" :class="{ disabled: paginaActual === totalPaginas }">
                            <button class="page-link text-agae" @click="cambiarPagina(paginaActual + 1)">&raquo;</button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

    </div>

    <!-- Script Vue 3 (Lógica Reactiva Frontend) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const {
            createApp
        } = Vue;

        createApp({
            data() {
                return {
                    afiliados: [],
                    formasPago: [],
                    stats: {
                        total_padron: 0,
                        total_solicitudes: 0,
                        total_activos: 0,
                        total_desafiliados: 0
                    },
                    terminoBusqueda: '',
                    filtroEstado: 'todos',
                    filtroFormaPago: 'todas',
                    estadoListado: 2, // 2 = Afiliados Activos por defecto
                    cargando: true,
                    paginaActual: 1,
                    elementosPorPagina: 15
                }
            },
            computed: {
                // Filtro combinado en memoria (Busqueda + Forma Pago + Semáforo)
                afiliadosFiltrados() {
                    const busqueda = this.terminoBusqueda.toLowerCase().trim();

                    return this.afiliados.filter(a => {
                        // 1. Filtro por Búsqueda de Texto
                        const apellidos = a.apellidos ? a.apellidos.toLowerCase() : '';
                        const nombres = a.nombres ? a.nombres.toLowerCase() : '';
                        const dni = a.dni ? a.dni.toString() : '';
                        const cumpleTexto = apellidos.includes(busqueda) || nombres.includes(busqueda) || dni.includes(busqueda);

                        // 2. Filtro por Forma de Pago
                        let cumpleFormaPago = true;
                        if (this.filtroFormaPago !== 'todas') {
                            cumpleFormaPago = (parseInt(a.id_fpago) === parseInt(this.filtroFormaPago));
                        }

                        // 3. Filtro por Semáforo Completo (6/6)
                        const puntaje = this.obtenerPuntaje(a);
                        let cumpleEstado = true;
                        if (this.filtroEstado === 'completo') {
                            cumpleEstado = (puntaje === 6);
                        } else if (this.filtroEstado === 'incompleto') {
                            cumpleEstado = (puntaje < 6);
                        }

                        return cumpleTexto && cumpleFormaPago && cumpleEstado;
                    });
                },

                totalPaginas() {
                    return Math.ceil(this.afiliadosFiltrados.length / this.elementosPorPagina) || 1;
                },

                afiliadosPaginados() {
                    const inicio = (this.paginaActual - 1) * this.elementosPorPagina;
                    const fin = inicio + this.elementosPorPagina;
                    return this.afiliadosFiltrados.slice(inicio, fin);
                },

                inicioRegistro() {
                    if (this.afiliadosFiltrados.length === 0) return 0;
                    return (this.paginaActual - 1) * this.elementosPorPagina + 1;
                },

                finRegistro() {
                    return Math.min(this.paginaActual * this.elementosPorPagina, this.afiliadosFiltrados.length);
                },

                // Algoritmo de Paginación Inteligente por Ventana Deslizante (Max 5-7 botones)
                paginasVisibles() {
                    const total = this.totalPaginas;
                    const actual = this.paginaActual;
                    const paginas = [];

                    if (total <= 7) {
                        for (let i = 1; i <= total; i++) paginas.push(i);
                        return paginas;
                    }

                    let izq = actual - 1;
                    let der = actual + 1;

                    if (izq < 2) {
                        izq = 1;
                        der = 4;
                    }
                    if (der > total - 1) {
                        der = total;
                        izq = total - 3;
                    }

                    for (let i = izq; i <= der; i++) {
                        paginas.push(i);
                    }

                    if (izq > 1) {
                        if (izq > 2) paginas.unshift('...');
                        paginas.unshift(1);
                    }

                    if (der < total) {
                        if (der < total - 1) paginas.push('...');
                        paginas.push(total);
                    }

                    return paginas;
                }
            },
            watch: {
                terminoBusqueda() {
                    this.paginaActual = 1;
                },
                filtroEstado() {
                    this.paginaActual = 1;
                },
                filtroFormaPago() {
                    this.paginaActual = 1;
                }
            },
            mounted() {
                this.cargarPadron();
            },
            methods: {
                cambiarPagina(pagina) {
                    if (pagina !== '...' && pagina >= 1 && pagina <= this.totalPaginas) {
                        this.paginaActual = pagina;
                    }
                },

                cambiarEstadoListado(nuevoEstado) {
                    this.estadoListado = nuevoEstado;
                    this.cargarPadron();
                },

                async cargarPadron() {
                    this.cargando = true;
                    try {
                        const resp = await fetch(`../../controladores/admin_padron_controlador.php?estado=${this.estadoListado}`);
                        const resultado = await resp.json();

                        if (resultado.status === 'success') {
                            this.afiliados = resultado.data;
                            this.stats = resultado.stats;
                            this.formasPago = resultado.formas_pago;
                            this.paginaActual = 1;
                        } else {
                            console.error("Error backend:", resultado.message);
                            Swal.fire('Error', resultado.message, 'error');
                        }
                    } catch (error) {
                        console.error("Error de red:", error);
                        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                    } finally {
                        this.cargando = false;
                    }
                },

                // Puntuación del Semáforo (1 punto base por registro + 5 módulos opcionales = 6 pts max)
                obtenerPuntaje(afiliado) {
                    return 1 +
                        parseInt(afiliado.mod_fpago || 0) +
                        parseInt(afiliado.mod_identidad || 0) +
                        parseInt(afiliado.mod_domicilio || 0) +
                        parseInt(afiliado.mod_educacion || 0) +
                        parseInt(afiliado.mod_laboral || 0);
                },

                // Convierte textos en MAYÚSCULAS o minúsculas a Formato Nombre Propio (Title Case)
                formatearTitleCase(texto) {
                    if (!texto) return '';
                    return texto.toLowerCase().replace(/(?:^|\s|-)\S/g, function(a) {
                        return a.toUpperCase();
                    });
                },

                // Formatea la fecha SQL (YYYY-MM-DD) a estilo latino (DD/MM/AAAA)
                formatearFecha(fechaStr) {
                    if (!fechaStr || fechaStr === 'Sin fecha') return 'Sin fecha';
                    const partes = fechaStr.split(' ')[0].split('-');
                    if (partes.length === 3) {
                        return `${partes[2]}/${partes[1]}/${partes[0]}`;
                    }
                    return fechaStr;
                },
                // Formatea un número o string de DNI agregando puntos como separador de miles (Ej: 12345678 -> 12.345.678)
                formatearDNI(dni) {
                    if (!dni) return '';
                    // Limpiamos cualquier carácter no numérico por seguridad y aplicamos Regex de separador de miles
                    return dni.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                },

                async desafiliarAfiliado(id_afiliado, nombreCompleto) {
                    const {
                        value: motivo
                    } = await Swal.fire({
                        title: 'Desafiliar Afiliado',
                        text: `Estás a punto de dar de baja a ${nombreCompleto}. Ingresá el motivo:`,
                        icon: 'warning',
                        input: 'textarea',
                        inputPlaceholder: 'Ej: Renuncia voluntaria, Falta de pago...',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, Desafiliar',
                        cancelButtonText: 'Cancelar',
                        inputValidator: (value) => {
                            if (!value || value.trim() === '') {
                                return '¡Debes escribir un motivo!';
                            }
                        }
                    });

                    if (motivo) {
                        try {
                            const resp = await fetch('../../controladores/admin_padron_controlador.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    accion: 'desafiliar',
                                    id_afiliado: id_afiliado,
                                    motivo: motivo.trim()
                                })
                            });
                            const resultado = await resp.json();
                            if (resultado.status === 'success') {
                                Swal.fire('¡Desafiliado!', 'El afiliado fue dado de baja.', 'success');
                                this.cargarPadron();
                            } else {
                                Swal.fire('Error', resultado.message, 'error');
                            }
                        } catch (error) {
                            Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                        }
                    }
                },

                async reafiliarAfiliado(id_afiliado, nombreCompleto) {
                    const confirmacion = await Swal.fire({
                        title: '¿Aprobar / Reactivar?',
                        text: `¿Estás seguro de reactivar/dar de alta a ${nombreCompleto}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, Reactivar',
                        cancelButtonText: 'Cancelar'
                    });

                    if (confirmacion.isConfirmed) {
                        try {
                            const resp = await fetch('../../controladores/admin_padron_controlador.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    accion: 'reafiliar',
                                    id_afiliado: id_afiliado
                                })
                            });
                            const resultado = await resp.json();

                            if (resultado.status === 'success') {
                                Swal.fire('¡Éxito!', resultado.message, 'success');
                                this.cargarPadron();
                            } else {
                                Swal.fire('Error', resultado.message, 'error');
                            }
                        } catch (error) {
                            Swal.fire('Error', 'No se pudo procesar la solicitud.', 'error');
                        }
                    }
                }
            }
        }).mount('#appPadron');
    </script>
</body>

</html>