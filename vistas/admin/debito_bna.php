<?php
// Blindaje básico de vista
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php"); 
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Débito BNA | Panel de Administración</title>
    
    <!-- Dependencias de Diseño (CDN temporal para desarrollo) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .card { border: none; border-radius: 0.5rem; }
        .card-header { background-color: #fff; border-bottom: 1px solid rgba(0,0,0,.125); }
    </style>
</head>
<body>

<!-- Navbar Minimalista de Admin -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#"><i class="fas fa-building me-2"></i>TuQ-Sistemas</a>
        <span class="navbar-text text-light">
            <i class="fas fa-user-circle me-1"></i> Panel de Administración
        </span>
    </div>
</nav>

<!-- Contenedor Principal Vue -->
<div class="container" id="appDebito">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-dark fw-bold"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Archivo Débito Automático BNA</h3>
    </div>
    
    <div class="row">
        <!-- Columna Izquierda: Datos del Banco -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3">
                    <h5 class="card-title text-center mb-0">Cuenta Bancaria: <span class="text-primary fw-bold">{{ banco.bco_nombre || 'Cargando...' }}</span></h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Sucursal</span>
                            <span class="fw-bold fs-5">{{ banco.bco_sucursal }}</span>
                        </li>
                        <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Cuenta Corriente</span>
                            <span class="fw-bold fs-5">{{ banco.bco_ctacte }}</span>
                        </li>
                        <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Tipo de Cuenta</span>
                            <span class="fw-bold">Cta Cte en $</span>
                        </li>
                        <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Moneda</span>
                            <span class="fw-bold">PESOS</span>
                        </li>
                        <li class="list-group-item px-4 py-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-primary fw-bold text-uppercase">Importe a Debitar</span>
                                
                                <!-- Modo Lectura -->
                                <div v-if="!editandoImporte" class="d-flex align-items-center">
                                    <span class="fw-bold fs-4 me-3 text-success">$ {{ banco.bco_importe_debito }}</span>
                                    <button class="btn btn-outline-primary shadow-sm" @click="activarEdicion" title="Modificar Importe">
                                        <i class="fas fa-pen"></i> Editar
                                    </button>
                                </div>

                                <!-- Modo Edición (Aparece reactivamente) -->
                                <div v-else class="input-group w-50 shadow-sm">
                                    <span class="input-group-text bg-white text-success fw-bold">$</span>
                                    <input type="number" step="0.01" min="1" class="form-control fw-bold" v-model="nuevoImporte">
                                    <button class="btn btn-success" @click="guardarImporte" title="Guardar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-secondary" @click="editandoImporte = false" title="Cancelar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Configuración de Archivo -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100 bg-white border-top border-4 border-primary">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4 pb-2 border-bottom fw-bold text-dark"><i class="fas fa-cogs me-2"></i>Parámetros de Generación</h5>
                    
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

                    <!-- Alertas de errores -->
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

<!-- ========================================== -->
<!-- DEPENDENCIAS FRONTEND Y LÓGICA VUE         -->
<!-- ========================================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const { createApp } = Vue;

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
                this.nuevoImporte = this.banco.bco_importe_debito;
                this.editandoImporte = true;
            },

            establecerMesActual() {
                const mes = new Date().getMonth() + 1;
                this.secuenciaMes = mes < 10 ? '0' + mes : mes.toString();
            },

            async cargarConfiguracion() {
                try {
                    const response = await fetch('../../controladores/admin_banco_controlador.php');
                    const resultado = await response.json();
                    
                    if(resultado.status === 'success') {
                        this.banco = resultado.data;
                    }
                } catch (error) {
                    console.error("Error cargando configuración:", error);
                }
            },

            async guardarImporte() {
                if (this.nuevoImporte <= 0) {
                    Swal.fire('Error', 'El importe debe ser mayor a 0', 'warning');
                    return;
                }

                try {
                    const response = await fetch('../../controladores/admin_banco_controlador.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
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
                        headers: { 'Content-Type': 'application/json' },
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
            }
        }
    }).mount('#appDebito');
</script>
</body>
</html>