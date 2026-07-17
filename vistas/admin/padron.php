<?php require_once 'header_admin.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrón de Afiliados - AGAE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .text-agae {
            color: #19248B !important;
        }

        .btn-outline-agae {
            color: #19248B;
            border-color: #19248B;
        }

        .btn-outline-agae:hover {
            background-color: #19248B;
            color: white;
        }
    </style>
</head>

<body>

    <div id="appPadron" class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0 text-agae"><i class="bi bi-people-fill me-2"></i>Padrón de AGAE</h2>
                <p class="text-muted mb-0">Gestión general y estado de legajos</p>
            </div>
            <div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4 bg-white">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <!-- 1. Filtrar Padrón (Botones de Alta/Baja) -->
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-2">Filtrar Padrón:</label>
                        <div class="btn-group w-100" role="group" style="height: 38px;">
                            <!-- Botón Activos -->
                            <input type="radio" class="btn-check" name="filtroEstadoListado" id="btn-activos" :value="1" v-model="estadoListado" @change="cargarPadron" autocomplete="off">
                            <label class="btn btn-outline-success d-flex align-items-center justify-content-center gap-2 h-100" for="btn-activos">
                                <i class="bi bi-person-check-fill fs-5"></i>
                                <span>Afiliados</span>
                            </label>

                            <!-- Botón Bajas -->
                            <input type="radio" class="btn-check" name="filtroEstadoListado" id="btn-bajas" :value="2" v-model="estadoListado" @change="cargarPadron" autocomplete="off">
                            <label class="btn btn-outline-danger d-flex align-items-center justify-content-center gap-2 h-100" for="btn-bajas">
                                <i class="bi bi-person-x-fill fs-5"></i>
                                <span>Desafiliados</span>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Filtrar por Estado (Semáforo) -->
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-2">Filtrar por Estado:</label>
                        <select class="form-select" v-model="filtroEstado" style="height: 38px;">
                            <option value="todos">🟢🔴 Todos los Legajos</option>
                            <option value="completo">🟢 Completos</option>
                            <option value="incompleto">🔴 Incompletos</option>
                        </select>
                    </div>

                    <!-- 3. Buscador Inteligente -->
                    <div class="col-md-5">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-2">Buscador Inteligente:</label>
                        <div class="input-group" style="height: 38px;">
                            <span class="input-group-text bg-white text-muted border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" v-model="terminoBusqueda" placeholder="Escribí un DNI, Apellido o Nombre...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted">Apellidos y Nombres</th>
                                <th class="text-muted">DNI</th>
                                <th class="text-muted">Estado del Legajo</th>
                                <th class="text-muted">Fecha de Alta</th>
                                <th class="text-end px-4 text-muted">Acciones</th>
                            </tr>
                        </thead>

                        <tbody v-if="cargando">
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="text-muted mt-2">Cargando padrón...</p>
                                </td>
                            </tr>
                        </tbody>

                        <tbody v-else-if="afiliadosFiltrados.length === 0">
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-search fs-1 d-block mb-2"></i>
                                    No se encontraron afiliados que coincidan con la búsqueda.
                                </td>
                            </tr>
                        </tbody>

                        <tbody v-else>
                            <tr v-for="afiliado in afiliadosFiltrados" :key="afiliado.id_afiliado">
                                <td class="px-4 fw-bold text-dark">{{ afiliado.apellidos }}, {{ afiliado.nombres }}</td>
                                <td>{{ afiliado.dni }}</td>
                                <td>
                                    <!-- Insignia de Completo -->
                                    <span v-if="obtenerPuntaje(afiliado) === 5" class="badge bg-success py-2 px-3">
                                        <i class="bi bi-check-circle-fill me-1"></i> Activo (5/5)
                                    </span>
                                    <!-- Insignia de Incompleto -->
                                    <span v-else class="badge bg-danger py-2 px-3">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Incompleto ({{ obtenerPuntaje(afiliado) }}/5)
                                    </span>
                                </td>
                                <td>{{ afiliado.fecha_alta }}</td>
                                <td class="text-end px-4">
                                    <a :href="'legajo.php?id=' + afiliado.id_afiliado" class="btn btn-sm btn-outline-agae">
                                        <i class="bi bi-folder2-open me-1"></i> Abrir Legajo
                                    </a>



                                    <!-- Si estamos viendo Activos (1), mostramos botón rojo de desafiliación -->
                                    <button v-if="estadoListado === 1"
                                        class="btn btn-sm btn-outline-danger ms-1"
                                        title="Desafiliar"
                                        @click="desafiliarAfiliado(afiliado.id_afiliado, afiliado.nombres + ' ' + afiliado.apellidos)">
                                        <i class="bi bi-person-x-fill"></i>
                                    </button>

                                    <!-- Si estamos viendo Bajas (2), mostramos botón verde de reactivación -->
                                    <button v-else
                                        class="btn btn-sm btn-outline-success ms-1"
                                        title="Reafiliar / Dar de Alta"
                                        @click="reafiliarAfiliado(afiliado.id_afiliado, afiliado.nombres + ' ' + afiliado.apellidos)">
                                        <i class="bi bi-person-check-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-muted small px-4 py-3">
                Mostrando {{ afiliadosFiltrados.length }} afiliados en pantalla.
            </div>
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
                    afiliados: [],
                    terminoBusqueda: '',
                    filtroEstado: 'todos',
                    estadoListado: 1, // 1 = Ver Activos, 2 = Ver Dados de Baja
                    cargando: true
                }
            },
            computed: {
                afiliadosFiltrados() {
                    const busqueda = this.terminoBusqueda.toLowerCase();
                    return this.afiliados.filter(a => {
                        const apellidos = a.apellidos ? a.apellidos.toLowerCase() : '';
                        const nombres = a.nombres ? a.nombres.toLowerCase() : '';
                        const dni = a.dni ? a.dni.toString() : '';

                        const cumpleTexto = apellidos.includes(busqueda) ||
                            nombres.includes(busqueda) ||
                            dni.includes(busqueda);

                        const puntaje = this.obtenerPuntaje(a);
                        let cumpleEstado = true;
                        if (this.filtroEstado === 'completo') {
                            cumpleEstado = (puntaje === 5);
                        } else if (this.filtroEstado === 'incompleto') {
                            cumpleEstado = (puntaje < 5);
                        }

                        return cumpleTexto && cumpleEstado;
                    });
                }
            },
            mounted() {
                this.cargarPadron();
            },
            methods: {
                async cargarPadron() {
                    this.cargando = true;
                    try {
                        // Enviamos el estado seleccionado en la URL (?estado=1 o ?estado=2)
                        const resp = await fetch(`../../controladores/admin_padron_controlador.php?estado=${this.estadoListado}`);
                        const resultado = await resp.json();

                        if (resultado.status === 'success') {
                            this.afiliados = resultado.data;
                        } else {
                            console.error("Error:", resultado.message);
                            alert("Error al cargar el padrón.");
                        }
                    } catch (error) {
                        console.error("Error de red:", error);
                    } finally {
                        this.cargando = false;
                    }
                },

                obtenerPuntaje(afiliado) {
                    return parseInt(afiliado.mod_fpago || 0) +
                        parseInt(afiliado.mod_identidad || 0) +
                        parseInt(afiliado.mod_domicilio || 0) +
                        parseInt(afiliado.mod_educacion || 0) +
                        parseInt(afiliado.mod_laboral || 0);
                },

                async desafiliarAfiliado(id_afiliado, nombreCompleto) {
                    const {
                        value: motivo
                    } = await Swal.fire({
                        title: 'Desafiliar Afiliado',
                        text: `Estás a punto de dar de baja a ${nombreCompleto}. Por favor, ingresa el motivo:`,
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
                                return '¡Necesitas escribir un motivo para continuar!';
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

                // NUEVO MÉTODO: Reafiliación reactiva
                async reafiliarAfiliado(id_afiliado, nombreCompleto) {
                    const confirmacion = await Swal.fire({
                        title: '¿Reafiliar Afiliado?',
                        text: `¿Estás seguro de que querés volver a dar de alta a ${nombreCompleto}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, reactivar alta',
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
                                Swal.fire('¡Reafiliado!', resultado.message, 'success');
                                this.cargarPadron(); // Recarga automáticamente removiéndolo de la lista de bajas
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