<?php require_once 'header_admin.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de Entrada - Admin AGAE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f4f6f9;
        }

        h2 {
            font-family: 'Montserrat', sans-serif;
            color: #19248B;
            font-weight: 700;
        }

        .navbar-custom {
            background-color: #19248B;
        }

        .table-custom th {
            background-color: #19248B;
            color: white;
        }

        .btn-completar {
            background-color: #19248B;
            color: white;
            border: none;
        }

        .btn-completar:hover {
            background-color: #3a4dbd;
            color: white;
        }

        .badge-pendiente {
            background-color: #ffc107;
            color: #000;
        }
    </style>
</head>

<body>

    <div id="appAdmin">
        <nav class="navbar navbar-dark navbar-custom mb-4 shadow-sm">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">⚙️ AGAE - Panel de Administración</span>
                <span class="navbar-text text-white">
                    👤 Operador Web
                </span>
            </div>
        </nav>

        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Bandeja de Solicitudes Web</h2>
                <button class="btn btn-outline-secondary" @click="cargarPendientes">
                    🔄 Actualizar Bandeja
                </button>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">

                    <div v-if="cargando" class="text-center p-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Buscando nuevas solicitudes...</p>
                    </div>

                    <div class="table-responsive" v-else>
                        <table class="table table-hover table-custom mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>DNI</th>
                                    <th>Afiliado</th>
                                    <th>Contacto</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="solicitudes.length === 0">
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        🎉 ¡Al día! No hay nuevas solicitudes pendientes de revisión.
                                    </td>
                                </tr>

                                <tr v-for="solicitud in solicitudes" :key="solicitud.id">
                                    <td><small>{{ formatearFecha(solicitud.fecha_solicitud) }}</small></td>
                                    <td><strong>{{ solicitud.dni }}</strong></td>
                                    <td style="text-transform: capitalize;">{{ solicitud.apellidos }}, {{ solicitud.nombres }}</td>
                                    <td>
                                        <small>📧 {{ solicitud.email }}</small><br>
                                        <small>📱 {{ solicitud.whatsapp }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-pendiente">Pendiente</span>
                                    </td>
                                    <td class="text-center">
                                        <a :href="'completar_ficha.php?id_solicitud=' + solicitud.id" class="btn btn-sm btn-completar">

                                            ✍️ Completar Ficha
                                        </a>

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <script>
        const {
            createApp
        } = Vue;

        createApp({
            data() {
                return {
                    solicitudes: [],
                    cargando: true
                }
            },
            mounted() {
                // Se ejecuta ni bien el administrador abre la pantalla
                this.cargarPendientes();
            },
            methods: {
                async cargarPendientes() {
                    this.cargando = true;
                    try {
                        // ATENCIÓN ACÁ: Apuntamos al controlador subiendo dos niveles (../../)
                        const respuesta = await fetch('../../controladores/admin_bandeja_controlador.php');
                        const resultado = await respuesta.json();

                        if (resultado.status === 'success') {
                            this.solicitudes = resultado.data;
                        } else {
                            console.error("Mensaje del servidor:", resultado.message);
                        }
                    } catch (error) {
                        console.error("Error de red al obtener datos:", error);
                    } finally {
                        this.cargando = false;
                    }
                },
                formatearFecha(fechaSql) {
                    if (!fechaSql) return '';
                    const fecha = new Date(fechaSql);
                    // Ajustamos el formato para Argentina
                    return fecha.toLocaleDateString('es-AR') + ' ' + fecha.toLocaleTimeString('es-AR', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }
        }).mount('#appAdmin');
    </script>

</body>

</html>