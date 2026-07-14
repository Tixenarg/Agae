<?php require_once("include/parte_superior.php"); ?>
<!-- Inicio del contenido Principal -->
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

$sqlpago = "SELECT `id_fpago`,`fpago_nombre` FROM `afiliado_forma_de_pago`";
$respago = mysqli_query($link, $sqlpago);




?>
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header text-white bg-gradient-primary">

                    <h4 style="text-align: center ;">Modificación del Estado y Forma de Pago del Afiliado </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                           <a> AFiliado : <?php echo $fila["afi_apellidos"] ." ". $fila["afi_nombres"] ." "."DNI"." " .$fila["afi_dni"]?></a>
                           <a> Teléfono : <?php echo $fila["afi_telefono"]." ". "Mail:" ." ". $fila["afi_email"] ?></a>
                        </div >
                    </div>
                    <form id="frmafiliado" action="modifica_afiliado.php" method="post" autocomplete="off" class="row g-3 px-5 needs-validation" novalidate onsubmit="return checkSubmit();">
                        <input id="id" name="id" type="hidden" value="<?php echo $fila["id"]; ?>">

                        <div class="col-12 d-flex justify-content-center m-3">
                            <div class="paragraph h4"><strong>Estado del Afiliado </strong></div>
                        </div>

                        <div class="col-md-4 mt-md-3">
                            <label for="estado" class="form-label">Estado (<span class="text-danger">*</span>)</label>
                            <select name="estado" id="estado" class="form-control">
                                <?php while ($fila2 = mysqli_fetch_assoc($resultado)) { ?>
                                    <option value="<?php echo $fila2['id_estado']; ?>"
                                        <?php echo ($fila2['id_estado'] == $estado) ? 'selected' : ''; ?>>
                                        <?php echo $fila2['estado_nombre']; ?>
                                    </option>
                                <?php } ?>
                            </select>

                        </div>
                        <div class="col-md-8">
                        </div>

                        <div class="col-12 d-flex justify-content-center m-3">
                            <div class="paragraph h4"><strong>Pago del Afiliado </strong></div>
                        </div>

                        <div class="col-md-4 mt-md-3">
                            <label for="pago" class="form-label">Forma de Pago (<span class="text-danger">*</span>)</label>
                            <select name="pago" id="pago" class="form-control">

                                <?php while ($fila3 = mysqli_fetch_assoc($respago)) { ?>
                                    <option value="<?php echo $fila3['id_fpago']; ?>"
                                        <?php echo ($fila3['id_fpago'] == $fdepago) ? 'selected' : ''; ?>>
                                        <?php echo $fila3['fpago_nombre']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                        </div>


                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-5 mt-md-2">
                                    <label for="ctacte" class="form-label">Caja de Ahorro </label>
                                    <input type="text" class="form-control" id="ctacte" name="ctacte" placeholder="00000000000000" required minlength="14" maxlength="14" pattern="[0-9]+" value="<?php echo $fila["afi_ctacte"]; ?>" />
                                    <small id="emailHelp" class="form-text text-muted">Número de Cuenta (<span class="text-danger">*</span>)</small>
                                    <div class="invalid-feedback">
                                        Por favor ingrese los 14 dígitos numéricos de su Caja de Ahorro.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-center mt-5">
                            <button type="submit" class="btn btn-primary">Modificar datos del Afiliado</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function() {
        "use strict";
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll(".needs-validation");
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener(
                "submit",
                function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        enviando = false;
                    }

                    form.classList.add("was-validated");
                },
                false
            );

        });
    })();
    enviando = false; //Obligaremos a entrar el if en el primer submit
    function checkSubmit() {
        if (!enviando) {
            enviando = true;
            return true;
        } else {
            //Si llega hasta aca significa que pulsaron 2 veces el boton submit
            //alert("El formulario ya se esta enviando");
            return false;
        }
    };
</script> -->

<?php require_once("include/parte_inferior.php"); ?>
<script>
    $(document).ready(function() {
        $(".UpperCase").on("keypress", function() {
            $input = $(this);
            setTimeout(function() {
                $input.val($input.val().toUpperCase());
            }, 50);
        });
        enviando = false; //Obligaremos a entrar el if en el primer submit
    });
</script>