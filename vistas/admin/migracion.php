<?php
// Validamos sesión antes de siquiera pintar el HTML
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../publico/index.php");
    exit;
}

// 1. Uso obligatorio de modularización

?>

<!-- Contenedor principal anclado a Vue -->
<div id="app-migracion" class="container mt-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2>Migración y Normalización de Afiliados</h2>
            <p class="text-muted">Procesando lotes de 50 registros para optimización de memoria.</p>

            <!-- Botón reactivo: se deshabilita mientras migra o si ya terminó -->
            <button
                class="btn btn-primary btn-lg mt-3"
                @click="iniciarMigracion"
                :disabled="migrando || terminado">
                {{ migrando ? 'Migrando...' : (terminado ? 'Migración Finalizada' : 'Iniciar Migración') }}
            </button>
        </div>
    </div>

    <!-- Panel de Progreso Reactivo (Solo se muestra si arranca el proceso) -->
    <div class="row" v-if="migrando || terminado">
        <div class="col-md-8 mx-auto">
            <div class="alert" :class="terminado && errores.length === 0 ? 'alert-success' : 'alert-info'">
                <h4 class="alert-heading">{{ terminado ? '¡Proceso Terminado!' : 'Procesando...' }}</h4>
                <p class="mb-0"><strong>Registros insertados correctamente:</strong> {{ totalProcesados }}</p>
            </div>
        </div>
    </div>

    <!-- Tabla de Errores o "Frenos" (Se llena si el catch de PHP captura algo) -->
    <div class="row mt-4" v-if="errores.length > 0">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Errores Detectados ({{ errores.length }})</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID Afiliado</th>
                                <th>Detalle del Error SQL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="err in errores" :key="err.id_afiliado">
                                <td><strong>{{ err.id_afiliado }}</strong></td>
                                <td class="text-danger">{{ err.error }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Usamos Vue 3 por CDN (Modo progresivo) -->
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<script>
    const {
        createApp
    } = Vue;

    createApp({
        data() {
            return {
                migrando: false, // Controla el estado del botón y loaders
                terminado: false, // Indica si el backend devolvió count == 0
                totalProcesados: 0, // Contador en tiempo real
                errores: [] // Acumulador de registros fallidos
            }
        },
        methods: {
            iniciarMigracion() {
                // Reseteamos estados por si le da click 2 veces (improbable por el :disabled, pero seguro)
                this.migrando = true;
                this.terminado = false;
                this.totalProcesados = 0;
                this.errores = [];

                // Disparamos el primer lote
                this.procesarLote(0);
            },

            // Método recursivo: Se llama a sí mismo hasta que el backend avisa que terminó
            async procesarLote(offsetActual) {
                try {

                    const respuesta = await fetch('../../controladores/migracion_controlador.php', {
                        method: 'POST',
                        credentials: 'same-origin', // Mantenemos la cookie de sesión de tu $_SESSION['id_usuario']
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            offset: offsetActual
                        })
                    });

                    const data = await respuesta.json();

                    // Caso 1: Error de sesión o seguridad aborta todo
                    if (data.status === 'error') {
                        alert(data.msg);
                        this.migrando = false;
                        return;
                    }

                    // Caso 2: El backend avisa que ya no hay más registros
                    if (data.terminado) {
                        this.migrando = false;
                        this.terminado = true;
                        return;
                    }

                    // Caso 3: Lote exitoso. Actualizamos la Vista reactivamente.
                    this.totalProcesados += data.procesados;

                    if (data.errores && data.errores.length > 0) {
                        // El spread operator (...) empuja múltiples errores de golpe al array
                        this.errores.push(...data.errores);
                    }

                    // Recursividad: Llamamos al mismo método pasando el nuevo offset calculado por PHP
                    this.procesarLote(data.nextOffset);

                } catch (error) {
                    console.error("Falla de red o JSON inválido:", error);
                    alert("Se cortó la conexión con el servidor en el offset: " + offsetActual);
                    this.migrando = false;
                }
            }
        }
    }).mount('#app-migracion');
</script>

<?php
// Cerramos la estructura

?>