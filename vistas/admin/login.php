<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrador - AGAE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { 
            background-color: #f0f2f5; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .login-header {
            background-color: #19248B;
            color: white;
            text-align: center;
            padding: 20px;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .btn-primary-agae {
            background-color: #19248B;
            border-color: #19248B;
        }
        .btn-primary-agae:hover {
            background-color: #121a63;
            border-color: #121a63;
        }
    </style>
</head>
<body>

    <div id="appLogin" class="container d-flex justify-content-center">
        <div class="card login-card">
            <div class="login-header">
                <h4 class="mb-0"><i class="bi bi-shield-lock"></i> AGAE Admin</h4>
            </div>
            <div class="card-body p-4">
                <form @submit.prevent="iniciarSesion">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" v-model="form.usuario" placeholder="Ej: ruben" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" v-model="form.clave" placeholder="••••••••" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-agae w-100 fw-bold py-2" :disabled="cargando">
                        <span v-if="cargando" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <span v-else><i class="bi bi-box-arrow-in-right me-2"></i> Ingresar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    form: {
                        usuario: '',
                        clave: ''
                    },
                    cargando: false
                }
            },
            methods: {
                async iniciarSesion() {
                    if (this.form.usuario === '' || this.form.clave === '') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atención',
                            text: 'Completá tu usuario y contraseña.',
                            confirmButtonColor: '#19248B'
                        });
                        return;
                    }

                    this.cargando = true;

                    try {
                        // Ajustá la ruta si tu controlador está en otra carpeta. 
                        // Esta asume que estás en /vistas/admin/ y vas a /controladores/
                        const respuesta = await fetch('../../controladores/admin_auth_controlador.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(this.form)
                        });

                        const resultado = await respuesta.json();

                        if (resultado.status === 'success') {
                            // Login exitoso, pateamos a la bandeja de entrada
                            window.location.href = 'dashboard.php';
                        } else {
                            // Usuario o clave incorrectos
                            Swal.fire({
                                icon: 'error',
                                title: 'Acceso Denegado',
                                text: resultado.message,
                                confirmButtonColor: '#19248B'
                            });
                            this.form.clave = ''; // Limpiamos la clave por seguridad
                        }
                    } catch (error) {
                        console.error("Error en la petición:", error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de red',
                            text: 'No se pudo comunicar con el servidor.',
                            confirmButtonColor: '#19248B'
                        });
                    } finally {
                        this.cargando = false;
                    }
                }
            }
        }).mount('#appLogin');
    </script>
</body>
</html>