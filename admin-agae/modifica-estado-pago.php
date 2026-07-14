<?php require_once("include/parte_superior.php"); ?>
<?php
require("db/conexion_afiliados.php");

$id = $_GET["id"];

$sentencia = mysqli_prepare($link, "SELECT * FROM afiliados WHERE id = ?");
mysqli_stmt_bind_param($sentencia, "i", $id);
mysqli_stmt_execute($sentencia);
$resultado = mysqli_stmt_get_result($sentencia);
$fila = mysqli_fetch_array($resultado);
mysqli_stmt_close($sentencia);

$estado = $fila['afi_estado'];
$fdepago = $fila['afi_forma_pago'];

$sql = "SELECT id_estado, estado_nombre FROM afiliado_estados ORDER BY estado_nombre";
$resultado = mysqli_query($link, $sql);

$sqlpago = "SELECT id_fpago, fpago_nombre FROM afiliado_forma_de_pago";
$respago = mysqli_query($link, $sqlpago);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-gradient-primary text-white text-center py-3 rounded-top-4">
                    <h4 class="mb-0 fw-bold">Modificación del Estado y Forma de Pago</h4>
                </div>

                <div class="card-body p-4 bg-light">
                    <div class="alert alert-info mb-4 text-center">
                        <h5 class="mb-1 fw-bold">
                            <?php echo $fila["afi_apellidos"] . " " . $fila["afi_nombres"]; ?>
                        </h5>
                        <p class="mb-0">
                            DNI: <strong><?php echo $fila["afi_dni"]; ?></strong> |
                            Tel: <?php echo $fila["afi_telefono"]; ?> |
                            Email: <?php echo $fila["afi_email"]; ?>
                        </p>
                    </div>

                    <form id="frmestadopago" method="post" autocomplete="off" class="needs-validation" novalidate>
                        <input id="id" name="id" type="hidden" value="<?php echo $fila["id"]; ?>">

                        <!-- Estado del Afiliado -->
                        <div class="mb-4">
                            <h5 class="text-primary fw-bold border-bottom pb-2 mb-3">Estado del Afiliado</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="estado" class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                    <select name="estado" id="estado" class="form-control shadow-sm" required>
                                        <?php while ($fila2 = mysqli_fetch_assoc($resultado)) { ?>
                                            <option value="<?php echo $fila2['id_estado']; ?>"
                                                <?php echo ($fila2['id_estado'] == $estado) ? 'selected' : ''; ?>>
                                                <?php echo $fila2['estado_nombre']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Forma de Pago -->
                        <div class="mb-4">
                            <h5 class="text-primary fw-bold border-bottom pb-2 mb-3">Forma de Pago</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="pago" class="form-label fw-semibold">Forma de Pago <span class="text-danger">*</span></label>
                                    <select name="pago" id="pago" class="form-control shadow-sm" required>
                                        <?php while ($fila3 = mysqli_fetch_assoc($respago)) { ?>
                                            <option value="<?php echo $fila3['id_fpago']; ?>"
                                                <?php echo ($fila3['id_fpago'] == $fdepago) ? 'selected' : ''; ?>>
                                                <?php echo $fila3['fpago_nombre']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Caja de Ahorro -->
                        <div class="mb-4" id="bloqueCuenta" style="display: none;">
                            <h5 class="text-primary fw-bold border-bottom pb-2 mb-3">Cuenta Bancaria</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="ctacte" class="form-label fw-semibold">Caja de Ahorro</label>
                                    <input type="text" class="form-control shadow-sm" id="ctacte" name="ctacte" placeholder="00000000000000"
                                        minlength="14" maxlength="14" pattern="[0-9]+" value="<?php echo $fila['afi_ctacte']; ?>">
                                    <div class="form-text">Debe contener 14 dígitos numéricos.</div>
                                    <div class="invalid-feedback">
                                        Por favor ingrese los 14 dígitos numéricos de su Caja de Ahorro.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón -->
                        <div class="text-center">
                            <button type="submit" id="btnGuardar" class="btn btn-primary btn-lg shadow-sm px-5">
                                <i class="bi bi-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-footer text-center text-muted py-2 small">
                    Última modificación: <?php echo date("d/m/Y"); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery (necesario para AJAX y selectores $) -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- SweetAlert2 (si no lo tienes aún) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Tu script actual -->
<!-- <script>
    document.addEventListener("DOMContentLoaded", function() {
        const selectPago = document.getElementById("pago");
        const bloqueCuenta = document.getElementById("bloqueCuenta");
        const ctacteInput = document.getElementById("ctacte");

        // Mostrar u ocultar bloque de cuenta bancaria
        toggleCuenta(selectPago.value);
        selectPago.addEventListener("change", function() {
            toggleCuenta(this.value);
        });

        function toggleCuenta(valor) {
            if (parseInt(valor) === 1) {
                bloqueCuenta.style.display = "block";
                ctacteInput.required = true;
            } else {
                bloqueCuenta.style.display = "none";
                ctacteInput.required = false;
                ctacteInput.value = "";
            }
        }

        // 📤 Enviar por AJAX al presionar "Guardar Cambios"
        $('#frmestadopago').on('submit', function(e) {
            e.preventDefault(); // evitar envío normal

            const id = $('#id').val();
            const estado = $('#estado').val();
            const pago = $('#pago').val();
            const ctacte = $('#ctacte').val();

            if (!id || !estado || !pago) {
                Swal.fire('Atención', 'Faltan completar algunos campos obligatorios.', 'warning');
                return;
            }

            $.ajax({
                url: 'ajax/modifica-estado-pago.php',
                type: 'POST',
                data: {
                    id,
                    estado,
                    pago,
                    ctacte
                },
                beforeSend: function() {
                    $('#btnGuardar').prop('disabled', true).text('Guardando...');
                },
                success: function(resp) {
                    try {
                        const data = JSON.parse(resp);
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Datos actualizados correctamente',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'No se pudieron guardar los cambios'
                            });
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error inesperado',
                            text: resp
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo contactar con el servidor.'
                    });
                },
                complete: function() {
                    $('#btnGuardar').prop('disabled', false).text('Guardar Cambios');
                }
            });
        });
    });
</script> -->


<script>
    document.addEventListener("DOMContentLoaded", function() {
        const selectPago = document.getElementById("pago");
        const bloqueCuenta = document.getElementById("bloqueCuenta");
        const ctacteInput = document.getElementById("ctacte");

        // Mostrar u ocultar bloque de cuenta bancaria según forma de pago
        toggleCuenta(selectPago.value);
        selectPago.addEventListener("change", function() {
            toggleCuenta(this.value);
        });

        function toggleCuenta(valor) {
            if (parseInt(valor) === 1) {
                bloqueCuenta.style.display = "block";
                ctacteInput.required = true;
            } else {
                bloqueCuenta.style.display = "none";
                ctacteInput.required = false;
                ctacteInput.value = "";
            }
        }

        // 📤 Enviar por AJAX al presionar "Guardar Cambios"
        $('#frmestadopago').on('submit', function(e) {
            e.preventDefault(); // Evitar envío tradicional del formulario

            const id = $('#id').val();
            const estado = $('#estado').val();
            const pago = $('#pago').val();
            const ctacte = $('#ctacte').val();

            if (!id || !estado || !pago) {
                Swal.fire('Atención', 'Faltan completar algunos campos obligatorios.', 'warning');
                return;
            }

            $.ajax({
                url: 'ajax/modifica-estado-pago.php',
                type: 'POST',
                data: {
                    id,
                    estado,
                    pago,
                    ctacte
                },
                beforeSend: function() {
                    $('#btnGuardar').prop('disabled', true).text('Guardando...');
                },
                success: function(resp) {
                    try {
                        const data = JSON.parse(resp);
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Datos actualizados correctamente',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                // 🔁 Redirige después del mensaje
                                window.location.href = 'panel-principal.php';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'No se pudieron guardar los cambios'
                            });
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error inesperado',
                            text: resp
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo contactar con el servidor.'
                    });
                },
                complete: function() {
                    $('#btnGuardar').prop('disabled', false).text('Guardar Cambios');
                }
            });
        });
    });
</script>