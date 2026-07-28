<?php
// 1. Blindaje de sesión en la Vista
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit;
}

// 2. Cabecera global del admin (Carga Bootstrap CSS, FontAwesome, etc.)
require_once "header_admin.php";
?>

<!-- Estilo para ocultar las llaves {{ }} hasta que Vue monte la app -->
<style>
    [v-cloak] {
        display: none !important;
    }
</style>

<!-- Contenedor Principal con ID para la Instancia de Vue -->
<div class="container py-4" id="appDebito" v-cloak>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-dark fw-bold">
            <i class="fas fa-file-invoice-dollar text-primary me-2"></i>Archivo Débito Automático BNA
        </h3>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Datos del Banco -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 bg-white">
                    <h5 class="card-title text-center mb-0">
                        Cuenta Bancaria:
                        <span class="text-primary fw-bold">{{ banco.bco_nombre || 'Cargando...' }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Sucursal</span>
                            <span class="fw-bold fs-5">{{ banco.bco_sucursal || '-' }}</span>
                        </li>
                        <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Cuenta Corriente</span>
                            <span class="fw-bold fs-5">{{ banco.bco_ctacte || '-' }}</span>
                        </li>
                        <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Tipo de Cuenta</span>
                            <span class="fw-bold">Cta Cte en $</span>
                        </li>
                        <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Moneda</span>
                            <span class="fw-bold">PESOS</span>
                        </li>
                        <!-- IMPORTE A DEBITAR (Reemplazar desde la línea 48 a la 68) -->
                        <li class="list-group-item px-4 py-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="text-primary fw-bold text-uppercase small">Importe a Debitar</span>

                                <!-- Modo Lectura -->
                                <div v-if="!editandoImporte" class="d-flex align-items-center">
                                    <span class="fw-bold fs-4 me-3 text-success">$ {{ formatearMoneda(banco.bco_importe_debito) }}</span>
                                    <button class="btn btn-outline-primary btn-sm shadow-sm px-3"
                                        @click="activarEdicion"
                                        type="button"
                                        title="Modificar Importe">
                                        <i class="fas fa-pen me-1"></i> Editar
                                    </button>
                                </div>

                                <!-- Modo Edición Visualmente Claro (Iconos + Texto) -->
                                <div v-else class="d-flex align-items-center gap-2">
                                    <!-- Input con Prefijo $ aislado -->
                                    <div class="input-group input-group-sm shadow-sm" style="width: 140px;">
                                        <span class="input-group-text bg-white text-success fw-bold">$</span>
                                        <input type="number"
                                            step="0.01"
                                            min="1"
                                            class="form-control fw-bold text-end"
                                            v-model.number="nuevoImporte"
                                            ref="inputImporte"
                                            @keyup.enter="guardarImporte"
                                            @keyup.esc="editandoImporte = false">
                                    </div>

                                    <!-- Botón Confirmar -->
                                    <button class="btn btn-sm btn-success shadow-sm px-2 fw-semibold"
                                        @click="guardarImporte"
                                        type="button"
                                        title="Confirmar e ingresar importe">
                                        <i class="fas fa-check me-1"></i> Guardar
                                    </button>

                                    <!-- Botón Cancelar -->
                                    <button class="btn btn-sm btn-outline-secondary shadow-sm px-2"
                                        @click="editandoImporte = false"
                                        type="button"
                                        title="Cancelar edición">
                                        <i class="fas fa-times me-1"></i> Cancelar
                                    </button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Parámetros del Archivo -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100 bg-white border-top border-4 border-primary">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4 pb-2 border-bottom fw-bold text-dark">
                        <i class="fas fa-cogs me-2"></i>Parámetros de Generación
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">Secuencia (Mes / Nro)</label>
                            <div class="input-group shadow-sm">
                                <input type="text" class="form-control text-center fw-bold" v-model="secuenciaMes" maxlength="2" title="Mes a rendir">
                                <select class="form-select fw-bold" v-model="secuenciaNro">
                                    <option value="01">01</option>
                                    <option value="02">02</option>
                                    <option value="03">03</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-bold">Fecha Tope Rendición</label>
                            <input type="date" class="form-control shadow-sm fw-bold" v-model="fechaTope">
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-5">
                        <button class="btn btn-primary btn-lg shadow" @click="generarArchivo" :disabled="cargando">
                            <span v-if="cargando" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            <i v-else class="fas fa-file-download me-2"></i>
                            {{ cargando ? 'Procesando archivo...' : 'Generar y Descargar TXT' }}
                        </button>
                    </div>

                    <!-- Alertas de Cuentas Inválidas -->
                    <div v-if="erroresCuentas.length > 0" class="alert alert-danger mt-4 mb-0 shadow-sm" role="alert">
                        <h6 class="alert-heading fw-bold"><i class="fas fa-exclamation-circle me-2"></i>Cuentas Inválidas Omitidas:</h6>
                        <hr>
                        <ul class="mb-2 small ps-3">
                            <li v-for="error in erroresCuentas" :key="error">{{ error }}</li>
                        </ul>
                        <p class="mb-0 small text-muted"><strong>Atención:</strong> Estos afiliados no fueron incluidos en el TXT porque su cuenta no posee exactamente 14 dígitos.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// 3. Footer global (Carga Vue.js, Bootstrap JS, SweetAlert2, </body> y </html>)
require_once "footer_admin.php";
?>

<!-- 4. Lógica reactiva de Vue.js para este módulo -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Control para asegurarse de que Vue se cargó correctamente desde el footer
        if (typeof Vue === 'undefined') {
            console.error('CRÍTICO: Vue.js no está cargado. Asegurate de tener el CDN o script local de Vue en footer_admin.php');
            return;
        }

        const {
            createApp
        } = Vue;

        createApp({
            data() {
                return {
                    banco: {},
                    editandoImporte: false,
                    nuevoImporte: 0,

                    secuenciaMes: '',
                    secuenciaNro: '01',
                    fechaTope: '',

                    cargando: false,
                    erroresCuentas: []
                }
            },
            mounted() {
                this.cargarConfiguracion();
                this.establecerMesActual();
            },
            methods: {
                activarEdicion() {
                    this.nuevoImporte = parseFloat(this.banco.bco_importe_debito || 0);
                    this.editandoImporte = true;
                    this.$nextTick(() => {
                        if (this.$refs.inputImporte) {
                            this.$refs.inputImporte.focus();
                        }
                    });
                },

                cancelarEdicion() {
                    this.editandoImporte = false;
                },

                establecerMesActual() {
                    const mes = new Date().getMonth() + 1;
                    this.secuenciaMes = mes < 10 ? '0' + mes : mes.toString();
                },

                async cargarConfiguracion() {
                    try {
                        const response = await fetch('../../controladores/admin_banco_controlador.php');
                        const resultado = await response.json();

                        if (resultado.status === 'success') {
                            this.banco = resultado.data;
                        }
                    } catch (error) {
                        console.error("Error cargando configuración:", error);
                    }
                },

                async guardarImporte() {
                    if (!this.nuevoImporte || this.nuevoImporte <= 0) {
                        Swal.fire('Atención', 'El importe debe ser mayor a 0', 'warning');
                        return;
                    }

                    try {
                        const response = await fetch('../../controladores/admin_banco_controlador.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                accion: 'actualizar_importe',
                                importe: this.nuevoImporte
                            })
                        });

                        const resultado = await response.json();

                        if (resultado.status === 'success') {
                            this.banco.bco_importe_debito = this.nuevoImporte;
                            this.editandoImporte = false;

                            Swal.fire({
                                icon: 'success',
                                title: 'Guardado',
                                text: resultado.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', resultado.message, 'error');
                        }
                    } catch (error) {
                        Swal.fire('Error', 'Problema de conexión con el servidor', 'error');
                    }
                },

                async generarArchivo() {
                    if (!this.fechaTope) {
                        Swal.fire('Atención', 'Debe seleccionar una Fecha Tope de Rendición', 'warning');
                        return;
                    }
                    if (!this.secuenciaMes || this.secuenciaMes.length !== 2) {
                        Swal.fire('Atención', 'El mes de secuencia debe tener 2 dígitos (Ej: 07)', 'warning');
                        return;
                    }

                    this.cargando = true;
                    this.erroresCuentas = [];

                    try {
                        const response = await fetch('../../controladores/admin_banco_controlador.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                accion: 'generar_archivo',
                                secuencia: this.secuenciaMes + this.secuenciaNro,
                                fecha_tope: this.fechaTope
                            })
                        });

                        const resultado = await response.json();

                        if (resultado.status === 'success') {
                            if (resultado.errores_cuentas && resultado.errores_cuentas.length > 0) {
                                this.erroresCuentas = resultado.errores_cuentas;
                            }

                            this.descargarArchivo(resultado.file_content, resultado.file_name);

                            Swal.fire('¡Éxito!', 'El archivo se generó y descargó correctamente.', 'success');
                        } else {
                            Swal.fire('Error', resultado.message, 'error');
                        }
                    } catch (error) {
                        Swal.fire('Error', 'Fallo al generar el archivo.', 'error');
                        console.error(error);
                    } finally {
                        this.cargando = false;
                    }
                },

                descargarArchivo(base64Data, nombreArchivo) {
                    const link = document.createElement('a');
                    link.href = 'data:text/plain;base64,' + base64Data;
                    link.download = nombreArchivo;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },

                // Formateador eficiente de moneda nativo (Ej: 23490 -> 23.490,00)
                formatearMoneda(valor) {
                    if (valor === null || valor === undefined || valor === '') return '0,00';
                    const numero = parseFloat(valor);
                    if (isNaN(numero)) return '0,00';

                    return new Intl.NumberFormat('es-AR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(numero);
                }
            }
        }).mount('#appDebito');
    });
</script>