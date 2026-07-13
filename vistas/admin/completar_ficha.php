<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Legajo - Admin AGAE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body { font-family: 'Open Sans', sans-serif; background-color: #f4f6f9; }
        h3, h4 { font-family: 'Montserrat', sans-serif; color: #19248B; font-weight: 700; }
        
        /* MAGIA UX: Estilos para las Tarjetas Seleccionables de Pago */
        .radio-card input[type="radio"] { display: none; }
        .radio-card label {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 20px; border: 2px solid #e0e0e0; border-radius: 12px;
            cursor: pointer; transition: all 0.3s ease; background: white; height: 100%;
        }
        .radio-card label:hover { border-color: #b0b5e8; background-color: #f8f9ff; }
        .radio-card label i { font-size: 2rem; color: #6c757d; margin-bottom: 10px; transition: all 0.3s ease; }
        .radio-card label span { font-weight: 600; color: #495057; text-align: center; }
        
        /* Estado Activo (Seleccionado) */
        .radio-card input[type="radio"]:checked + label {
            border-color: #19248B; background-color: #f0f2ff; box-shadow: 0 4px 15px rgba(25,36,139,0.1);
        }
        .radio-card input[type="radio"]:checked + label i,
        .radio-card input[type="radio"]:checked + label span { color: #19248B; }

        /* Animación suave para el input de cuenta BNA */
        .fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease, transform 0.4s ease; }
        .fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-10px); }
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

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <form @submit.prevent="guardarFicha">
                        
                        <h4 class="mb-3 border-bottom pb-2">1. Datos Personales</h4>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Nombre Completo</label>
                                <input type="text" class="form-control bg-light" value="Juan Perez (Ejemplo)" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">DNI</label>
                                <input type="text" class="form-control bg-light" value="35.123.456" readonly>
                            </div>
                        </div>

                        <h4 class="mb-3 border-bottom pb-2 mt-5">2. Información de Cobro</h4>
                        <p class="text-muted small mb-3">Seleccione el método por el cual el afiliado abonará su cuota:</p>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4 radio-card">
                                <input type="radio" id="pago_mp" value="Mercado Pago" v-model="form.forma_pago" required>
                                <label for="pago_mp">
                                    <i class="bi bi-phone"></i>
                                    <span>Mercado Pago</span>
                                </label>
                            </div>

                            <div class="col-md-4 radio-card">
                                <input type="radio" id="pago_bna" value="Debito BNA" v-model="form.forma_pago">
                                <label for="pago_bna">
                                    <i class="bi bi-bank"></i>
                                    <span>Débito Automático BNA</span>
                                </label>
                            </div>

                            <div class="col-md-4 radio-card">
                                <input type="radio" id="pago_otros" value="Otros" v-model="form.forma_pago">
                                <label for="pago_otros">
                                    <i class="bi bi-wallet2"></i>
                                    <span>Otros / Efectivo</span>
                                </label>
                            </div>
                        </div>

                        <transition name="fade">
                            <div class="p-3 bg-light border rounded mb-4" v-if="form.forma_pago === 'Debito BNA'">
                                <label class="form-label fw-bold" style="color: #19248B;">
                                    🏦 Número de Cuenta BNA (CBU) *
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg" 
                                    v-model="form.numero_cuenta" 
                                    placeholder="Ingrese los 22 dígitos del CBU" 
                                    inputmode="numeric"
                                    maxlength="22"
                                    required>
                                <small class="text-muted mt-1 d-block">Asegúrese de verificar este número con el afiliado.</small>
                            </div>
                        </transition>

                        <div class="d-grid gap-2 mt-5">
                            <button type="submit" class="btn btn-lg text-white" style="background-color: #19248B;">
                                💾 Guardar y Aprobar Afiliado
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                form: {
                    forma_pago: '',   // Arranca vacío para obligar a que elijan uno
                    numero_cuenta: '' // Solo se usa si es BNA
                }
            }
        },
        watch: {
            // Un pequeño truco UX: Si cambia de opinión y selecciona otra cosa que no sea BNA,
            // le limpiamos el campo de cuenta por seguridad para no mandar datos sucios a la BD.
            'form.forma_pago'(nuevoValor) {
                if (nuevoValor !== 'Debito BNA') {
                    this.form.numero_cuenta = '';
                }
            },
            // Formateador: obligamos a que el CBU sean solo números
            'form.numero_cuenta'(nuevoValor) {
                this.form.numero_cuenta = nuevoValor.replace(/\D/g, "");
            }
        },
        methods: {
            guardarFicha() {
                // Acá a futuro mandaremos el Fetch a tu nuevo controlador para hacer el UPDATE
                console.log("Datos listos para enviar al backend:", this.form);
                alert("¡Ficha lista! Método: " + this.form.forma_pago + (this.form.numero_cuenta ? " | CBU: " + this.form.numero_cuenta : ""));
            }
        }
    }).mount('#appFicha');
</script>

</body>
</html>