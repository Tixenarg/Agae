<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Legajo - Admin AGAE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f4f6f9;
        }

        h3,
        h4 {
            font-family: 'Montserrat', sans-serif;
            color: #19248B;
            font-weight: 700;
        }

        /* DISEÑO UX: Estilos de Tarjetas Seleccionables para Formas de Pago */
        .radio-card input[type="radio"] {
            display: none;
        }

        .radio-card label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            height: 100%;
        }

        .radio-card label:hover {
            border-color: #b0b5e8;
            background-color: #f8f9ff;
        }

        .radio-card label i {
            font-size: 2rem;
            color: #6c757d;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .radio-card label span {
            font-weight: 600;
            color: #495057;
            text-align: center;
        }

        /* Tarjeta Seleccionada (Estado Activo) */
        .radio-card input[type="radio"]:checked+label {
            border-color: #19248B;
            background-color: #f0f2ff;
            box-shadow: 0 4px 15px rgba(25, 36, 139, 0.1);
        }

        .radio-card input[type="radio"]:checked+label i,
        .radio-card input[type="radio"]:checked+label span {
            color: #19248B;
        }

        /* Animación de transición elegante */
        .fade-enter-active,
        .fade-leave-active {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .fade-enter-from,
        .fade-leave-to {
            opacity: 0;
            transform: translateY(-10px);
        }
    </style>
</head>

<body>

    <div id="appFicha" class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>✍️ Completar Legajo de Afiliado</h3>
                    <a href="bandeja.php" class="btn btn-outline-secondary">⬅ Volver a la Bandeja</a>
                </div>

                <div v-if="cargandoDatos" class="text-center p-5 card shadow-sm border-0">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Buscando ficha del postulante en la base de datos...</p>
                </div>

                <div v-else class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <form @submit.prevent="guardarFicha">

                            <h4 class="mb-3 border-bottom pb-2">1. Datos Personales</h4>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">Nombre Completo</label>
                                    <input type="text" class="form-control bg-light" :value="afiliado.apellidos + ', ' + afiliado.nombres" readonly style="text-transform: capitalize;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">DNI</label>
                                    <input type="text" class="form-control bg-light" :value="afiliado.dni" readonly>
                                </div>
                            </div>

                            <h4 class="mb-3 border-bottom pb-2 mt-5">2. Información de Cobro</h4>
                            <p class="text-muted small mb-3">Seleccione el método por el cual el afiliado abonará su cuota:</p>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4 radio-card" v-for="pago in formasPago" :key="pago.id_fpago">
                                    <input type="radio" :id="'pago_' + pago.id_fpago" :value="pago.id_fpago" v-model="form.id_fpago" required>
                                    <label :for="'pago_' + pago.id_fpago">
                                        <i :class="getIcono(pago.id_fpago)"></i>
                                        <span>{{ pago.fpago_nombre }}</span>
                                    </label>
                                </div>
                            </div>

                            <transition name="fade">
                                <div class="p-3 bg-light border rounded mb-4" v-if="form.id_fpago === 1">
                                    <label class="form-label fw-bold" style="color: #19248B;">
                                        🏦 Número de Caja de Ahorro BNA *
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control form-control-lg text-center"
                                        v-model="form.numero_cuenta"
                                        placeholder="Ingrese los 14 dígitos numéricos"
                                        inputmode="numeric"
                                        maxlength="14"
                                        required
                                        style="letter-spacing: 2px; font-weight: bold;">
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">Formato exclusivo para débitos automáticos del Banco Nación.</small>
                                        <small :class="form.numero_cuenta.length === 14 ? 'text-success fw-bold' : 'text-danger'">
                                            {{ form.numero_cuenta.length }} / 14 dígitos
                                        </small>
                                    </div>
                                </div>
                            </transition>

                            <div class="d-grid gap-2 mt-5">
                                <div class="alert alert-warning">
    <strong>Debug Vue:</strong> El ID seleccionado es: {{ form.forma_pago }}
</div>
                                <button type="submit" class="btn btn-lg text-white" style="background-color: #19248B;" :disabled="guardando || (form.id_fpago === 1 && form.numero_cuenta.length !== 14)">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    {{ guardando ? 'Procesando alta y migración...' : 'Guardar y Aprobar Afiliado' }}
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const {
            createApp
        } = Vue;

        createApp({
            data() {
                return {
                    idSolicitud: null,
                    cargandoDatos: true,
                    guardando: false,
                    afiliado: {},
                    formasPago: [], // Mapeará los registros provenientes de la BD
                    form: {
                        id_fpago: '', // Cambiado a ID numérico relacional
                        numero_cuenta: '' // Caja de ahorro de 14 dígitos
                    }
                }
            },
            mounted() {
                // Captura dinámicamente el id_solicitud presente en la URL del navegador (?id_solicitud=X)
                const urlParams = new URLSearchParams(window.location.search);
                this.idSolicitud = urlParams.get('id_solicitud');

                if (this.idSolicitud) {
                    this.buscarDatosAfiliado();
                } else {
                    Swal.fire('Error', 'Falta el identificador de la solicitud en la URL.', 'error');
                }
            },
            watch: {
                // Si el operador cambia de método de pago y no es BNA (ID 1), limpiamos el input para evitar enviar datos residuales
                'form.id_fpago'(nuevoValor) {
                    if (nuevoValor !== 1) {
                        this.form.numero_cuenta = '';
                    }
                },
                // Sanitizador e indicador UX: solo permite el ingreso de números y corta estrictamente en 14
                'form.numero_cuenta'(nuevoValor) {
                    let limpio = nuevoValor.replace(/\D/g, "");
                    if (limpio.length > 14) limpio = limpio.slice(0, 14);
                    this.form.numero_cuenta = limpio;
                }
            },
            methods: {
                async buscarDatosAfiliado() {
                    try {
                        const res = await fetch(`../../controladores/admin_ficha_controlador.php?id_solicitud=${this.idSolicitud}`);
                        const result = await res.json();
                        if (result.status === 'success') {
                            this.afiliado = result.data;
                            this.formasPago = result.formas_pago; // Obtenemos las filas reales de 'afiliado_forma_pago'
                        } else {
                            Swal.fire('Atención', result.message, 'warning');
                        }
                    } catch (e) {
                        Swal.fire('Error', 'No se pudo establecer comunicación con el backend.', 'error');
                    } finally {
                        this.cargandoDatos = false;
                    }
                },
                getIcono(id) {
                    // Asigna dinámicamente un icono visual según el ID de pago de tu base de datos
                    if (id === 1) return 'bi bi-bank'; // Débito Bco Nación
                    if (id === 2) return 'bi bi-phone'; // Mercado Pago
                    return 'bi bi-wallet2'; // Otros / Efectivo
                },
async guardarFicha() {
                    // 1. Validamos llamando a la variable con su nombre correcto: id_fpago
                    if (!this.form.id_fpago) {
                        alert("Por favor, seleccioná una forma de pago antes de continuar.");
                        return;
                    }

                    // 2. Obtenemos el ID de la solicitud desde la URL (ej: ?id_solicitud=2)
                    const urlParams = new URLSearchParams(window.location.search);
                    const idSolicitudUrl = urlParams.get('id_solicitud');

                    if (!idSolicitudUrl) {
                        alert("Error: No se encontró el ID de la solicitud.");
                        return;
                    }

                    // 3. Armamos el paquete de datos para mandar al backend
                    const datosPost = {
                        id_solicitud: parseInt(idSolicitudUrl),
                        // Usamos la variable correcta también acá
                        id_fpago: parseInt(this.form.id_fpago), 
                        numero_cuenta: this.form.numero_cuenta
                    };

                    try {
                        this.guardando = true; // Activamos el estado de carga visual en el botón

                        // 4. Enviamos la orden al controlador
                        const respuesta = await fetch('../../controladores/admin_procesar_alta_controlador.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(datosPost)
                        });

                        const resultado = await respuesta.json();

                        if (resultado.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Afiliado Activo!',
                                text: 'Ya es oficial y los módulos están listos.',
                                confirmButtonColor: '#19248B'
                            }).then(() => {
                                // Vuelve a la bandeja. Después acá pondremos la ruta al legajo.
                                /* window.location.href = 'bandeja.php'; */
                                window.location.href = 'legajo.php?id=' + resultado.id_afiliado;
                            });
                        } else {
                            Swal.fire('Error de Base de Datos', resultado.message, 'error');
                        }

                    } catch (error) {
                        console.error("Error en la conexión:", error);
                        Swal.fire('Error de Conexión', 'Hubo un problema de red con el servidor.', 'error');
                    } finally {
                        this.guardando = false; // Apagamos el estado de carga
                    }
                }
            }
        }).mount('#appFicha');
    </script>
</body>

</html>