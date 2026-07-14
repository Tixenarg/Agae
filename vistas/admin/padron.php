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
    <style>
        body { background-color: #f8f9fa; }
        .text-agae { color: #19248B !important; }
        .btn-outline-agae { color: #19248B; border-color: #19248B; }
        .btn-outline-agae:hover { background-color: #19248B; color: white; }
    </style>
</head>
<body>

    <div id="appPadron" class="container py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0 text-agae"><i class="bi bi-people-fill me-2"></i>Padrón de Afiliados</h2>
                <p class="text-muted mb-0">Gestión general y estado de legajos</p>
            </div>
            <div>
                </div>
        </div>

        <div class="card shadow-sm border-0 mb-4 bg-white">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Filtrar por Estado</label>
                        <select class="form-select" v-model="filtroEstado">
                            <option value="todos">🟢🔴 Todos los Legajos</option>
                            <option value="completo">🟢 Solo Completos (4/4)</option>
                            <option value="incompleto">🔴 Solo Incompletos</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label text-muted small fw-bold text-uppercase">Buscador Inteligente</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control" v-model="terminoBusqueda" placeholder="Escribí un DNI, Apellido o Nombre para buscar al instante...">
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
                                    <span v-if="obtenerPuntaje(afiliado) === 4" class="badge bg-success py-2 px-3">
                                        <i class="bi bi-check-circle-fill me-1"></i> Completo (4/4)
                                    </span>
                                    <span v-else class="badge bg-danger py-2 px-3">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Incompleto ({{ obtenerPuntaje(afiliado) }}/4)
                                    </span>
                                </td>
                                <td>{{ afiliado.fecha_alta }}</td>
                                <td class="text-end px-4">
                                    <a :href="'legajo.php?id=' + afiliado.id_afiliado" class="btn btn-sm btn-outline-agae">
                                        <i class="bi bi-folder2-open me-1"></i> Abrir Legajo
                                    </a>
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
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    afiliados: [],
                    terminoBusqueda: '',
                    filtroEstado: 'todos',
                    cargando: true
                }
            },
            computed: {
                // Propiedad computada que filtra la tabla AL VUELO sin consultar a la BD de nuevo
                afiliadosFiltrados() {
                    const busqueda = this.terminoBusqueda.toLowerCase();
                    
                    return this.afiliados.filter(a => {
                        // 1. Filtrado por texto (Maneja nulos por si algún campo viene vacío)
                        const apellidos = a.apellidos ? a.apellidos.toLowerCase() : '';
                        const nombres = a.nombres ? a.nombres.toLowerCase() : '';
                        const dni = a.dni ? a.dni.toString() : '';
                        
                        const cumpleTexto = apellidos.includes(busqueda) || 
                                            nombres.includes(busqueda) || 
                                            dni.includes(busqueda);
                        
                        // 2. Filtrado por estado del semáforo
                        const puntaje = this.obtenerPuntaje(a);
                        const estaCompleto = puntaje === 4;
                        
                        let cumpleEstado = true;
                        if (this.filtroEstado === 'completo') {
                            cumpleEstado = estaCompleto;
                        } else if (this.filtroEstado === 'incompleto') {
                            cumpleEstado = !estaCompleto;
                        }
                        
                        // Retorna true solo si cumple AMBAS condiciones
                        return cumpleTexto && cumpleEstado;
                    });
                }
            },
            mounted() {
                // Al cargar la pantalla, vamos a buscar los datos
                this.cargarPadron();
            },
            methods: {
                async cargarPadron() {
                    try {
                        const resp = await fetch('../../controladores/admin_padron_controlador.php');
                        const resultado = await resp.json();
                        
                        if(resultado.status === 'success') {
                            this.afiliados = resultado.data;
                        } else {
                            console.error("Error del servidor:", resultado.message);
                            alert("Hubo un error al cargar el padrón.");
                        }
                    } catch (error) {
                        console.error("Error de red:", error);
                    } finally {
                        this.cargando = false;
                    }
                },
                
                // Suma los valores (0 o 1) que nos manda el SQL para saber cuántos módulos están cargados
                obtenerPuntaje(afiliado) {
                    return parseInt(afiliado.mod_identidad || 0) + 
                           parseInt(afiliado.mod_domicilio || 0) + 
                           parseInt(afiliado.mod_educacion || 0) + 
                           parseInt(afiliado.mod_laboral || 0);
                }
            }
        }).mount('#appPadron');
    </script>
</body>
</html>