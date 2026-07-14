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


/* global $link; */
/* $sql = "SELECT id_estado, estado_nombre FROM afiliado_estados ORDER BY estado_nombre";
$resultado = mysqli_query($link, $sql); */

$sql = "SELECT id_estado, estado_nombre FROM afiliado_estados ORDER BY estado_nombre";
$resultado = mysqli_query($link, $sql);

$sqlpago = "SELECT `id_fpago`,`fpago_nombre` FROM `afiliado_forma_de_pago`";
$respago = mysqli_query($link, $sqlpago);



/* $sentenciaestado = mysqli_prepare($link, "SELECT * FROM afiliado_estados ");
mysqli_stmt_execute($sentenciaestado);
$resulestado = mysqli_stmt_get_result($sentenciaestado); */
/* $filaestado = mysqli_fetch_array($resulestado); */






/*   */
//echo $fila["afi_apellidos"];
?>
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header text-white bg-gradient-primary">

                    <h4 style="text-align: center ;">Modificación de ficha de Afiliado </h4>
                </div>
                <div class="card-body">
                    <form id="frmafiliado" action="modifica_afiliado.php" method="post" autocomplete="off" class="row g-3 px-5 needs-validation" novalidate onsubmit="return checkSubmit();">
                        <input id="id" name="id" type="hidden" value="<?php echo $fila["id"]; ?>">

                        <div class="col-12 d-flex justify-content-center m-3">
                            <div class="paragraph h4"><strong>Estado del Afiliado </strong></div>
                        </div>

<!--                         <div class="col-md-4 mt-md-3">
                            <label for="estado" class="form-label">Estado (<span class="text-danger">*</span>)</label>
                            <select name="estado" id="estado" class="form-control">
                                <?php while ($fila2 = mysqli_fetch_assoc($resultado)) { ?>
                                    <option value="<?php echo $fila2['id_estado']; ?>"
                                        <?php echo ($fila2['id_estado'] == $estado) ? 'selected' : ''; ?>>
                                        <?php echo $fila2['estado_nombre']; ?>
                                    </option>
                                <?php } ?>
                            </select>

                        </div> -->
<!--                         <div class="col-md-8">
                        </div> -->

<!--                         <div class="col-12 d-flex justify-content-center m-3">
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
                        </div> -->

                        <div class="col-md-6">
                            <label for="apellidos" class="form-label">Apellido/s (<span class="text-danger">*</span>)</label>
                            <input type="text" class="form-control UpperCase" id="apellidos" name="apellidos" value="<?php echo $fila["afi_apellidos"]; ?>" required />
                            <div class="invalid-feedback">Por favor ingrese su Apellido.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="nombres" class="form-label">Nombre/s (<span class="text-danger">*</span>)</label>
                            <input type="text" class="form-control UpperCase" id="nombres" name="nombres" value="<?php echo $fila["afi_nombres"]; ?>" required />
                            <div class="invalid-feedback">Por favor ingrese su Nombre.</div>
                        </div>
                        <div class="col-md-4 mt-md-3">
                            <label for="nacionalidad" class="form-label">Nacionalidad (<span class="text-danger">*</span>)</label>
                            <input type="text" class="form-control UpperCase" id="nacionalidad" name="nacionalidad" value="<?php echo $fila["afi_nacionalidad"]; ?>" required />
                        </div>
                        <div class="col-md-4 mt-md-3">
                            <label for="dni" class="form-label">Nº DNI (<span class="text-danger">*</span>)</label>
                            <input type="text" class="form-control" id="dni" name="dni" value="<?php echo $fila["afi_dni"]; ?>" required />
                        </div>
                        <div class="col-md-4 mt-md-3">
                            <label for="telefono" class="form-label">Tel. Contacto (<span class="text-danger">*</span>)</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo $fila["afi_telefono"]; ?>" required />
                        </div>
                        <div class="col-md-4 mt-md-3">
                            <label for="sexo" class="form-label">Sexo (<span class="text-danger">*</span>)</label>
                            <select id="sexo" name="sexo" class="form-control" value="<?php echo $fila["afi_sexo"]; ?>" required>
                                <option selected disabled value="">Seleccione Sexo...</option>
                                <option value="FEMENINO" <?php if ($fila['afi_sexo'] == 'FEMENINO') {
                                                                echo 'selected';
                                                            } ?>>FEMENINO</option>
                                <option value="MASCULINO" <?php if ($fila['afi_sexo'] == 'MASCULINO') {
                                                                echo 'selected';
                                                            } ?>>MASCULINO</option>
                                <option value="OTROS" <?php if ($fila['afi_sexo'] == 'OTROS') {
                                                            echo 'selected';
                                                        } ?>>OTROS</option>
                            </select>
                        </div>
                        <div class="col-md-4 mt-md-3">
                            <label for="estadocivil" class="form-label">Estado Civil (<span class="text-danger">*</span>)</label>
                            <select id="estadocivil" name="estadocivil" class="form-control">
                                <option value="CASADA/O" <?php if ($fila['afi_civil'] == 'CASADA/O') {
                                                                echo 'selected';
                                                            } ?>>CASADA/O</option>
                                <option value="SOLTERA/O" <?php if ($fila['afi_civil'] == 'SOLTERA/O') {
                                                                echo 'selected';
                                                            } ?>>SOLTERA/O</option>
                                <option value="DIVORCIADA/O" <?php if ($fila['afi_civil'] == 'DIVORCIADA/O') {
                                                                    echo 'selected';
                                                                } ?>>DIVORCIADA/O</option>
                                <option value="VIUDA/O" <?php if ($fila['afi_civil'] == 'VIUDA/O') {
                                                            echo 'selected';
                                                        } ?>>VIUDA/O</option>
                            </select>
                        </div>
                        <div class="col-md-4 mt-md-3">
                            <label for="fechanacimiento" class="form-label">Fecha de Nacimiento (<span class="text-danger">*</span>)</label>
                            <input type="date" class="form-control" id="fechanacimiento" name="fechanacimiento" required value="<?php echo $fila["afi_nacimiento"]; ?>" />
                        </div>
                        <div class="col-md-6 mt-md-3">
                            <label for="domicilio" class="form-label">Domicilio (Calle y Nº) (<span class="text-danger">*</span>)</label>
                            <input type="text" class="form-control UpperCase" id="domicilio" name="domicilio" required value="<?php echo $fila["afi_domicilio"]; ?>" />
                        </div>
                        <div class="col-md-3 mt-md-3">
                            <label for="localidad" class="form-label">Localidad (<span class="text-danger">*</span>)</label>
                            <input type="text" class="form-control UpperCase" id="localidad" name="localidad" value="<?php echo $fila["afi_localidad"]; ?>" required />
                        </div>
                        <div class="col-md-3 mt-md-3">
                            <label for="codigopostal" class="form-label">Código Postal (<span class="text-danger">*</span>)</label>
                            <input type="text" class="form-control" id="codigopostal" name="codigopostal" value="<?php echo $fila["afi_codigopostal"]; ?>" required />
                        </div>
                        <div class="col-md-5 mt-md-3">
                            <label for="provincia" class="form-label">Provincia (<span class="text-danger">*</span>)</label>
                            <input type="text" class="form-control UpperCase" id="provincia" name="provincia" value="<?php echo $fila["afi_provincia"]; ?>" required />
                        </div>
                        <div class="col-md-7 mt-md-3">
                            <label for="mail" class="form-label">Email (<span class="text-danger">*</span>)</label>
                            <input type="email" class="form-control" id="mail" name="mail" value="<?php echo $fila["afi_email"]; ?>" required />
                        </div>
                        <div class="col-md-6 mt-md-3">
                            <label for="estudios" class="form-label">Estudios </label>
                            <select id="estudios" name="estudios" class="form-control">
                                <option value="UNIVERSITARIO" <?php if ($fila['afi_estudio'] == 'UNIVERSITARIO') {
                                                                    echo 'selected';
                                                                } ?>>UNIVERSITARIO</option>
                                <option value="POSGRADO" <?php if ($fila['afi_estudio'] == 'POSGRADO') {
                                                                echo 'selected';
                                                            } ?>>POSGRADO</option>
                            </select>
                        </div>
                        <div class="col-md-6 mt-md-3">
                            <label for="carrera" class="form-label">Título/Carrera</label>
                            <input type="text" class="form-control UpperCase" id="carrera" name="carrera" value="<?php echo $fila["afi_titulo"]; ?>" />
                        </div>
                        <div class="col-12 d-flex justify-content-center m-3">
                            <div class="paragraph h4"><strong>Datos Laborales </strong></div>
                        </div>
                        <div class="col-md-6 ">
                            <label for="legajo" class="form-label">Nº de Legajo (<span class="text-danger">*</span>)</label>
                            <input type="text" class="form-control" id="legajo" name="legajo" required value="<?php echo $fila["afi_legajo"]; ?>" />
                        </div>
                        <div class="col-md-6">
                            <label for="organismo" class="form-label">Organismo que liquida su haber (<span class="text-danger">*</span>)</label>
                            <input type="text" class="form-control UpperCase" id="organismo" name="organismo" value="<?php echo $fila["afi_orgliquidahaber"]; ?>" required />
                        </div>
                        <div class="col-md-6 mt-md-2">
                            <label for="cuil" class="form-label">Nº de CUIL (<span class="text-danger">*</span>)</label>
                            <input type="text" class="form-control" id="cuil" name="cuil" required value="<?php echo $fila["afi_cuil"]; ?>" />
                        </div>
                        <div class="col-md-6 mt-md-2">
                            <label for="trabajo" class="form-label">Organismo donde trabaja (<span class="text-danger">*</span>)</label>
                            <input type="text" class="form-control UpperCase" id="trabajo" name="trabajo" value="<?php echo $fila["afi_orgtrabaja"]; ?>" required />
                        </div>
                        <div class="col-md-6 mt-md-2">
                            <label for="domiciliotrabajo" class="form-label">Domicilio del lugar de trabajo: (Calle y Nº)(<span class="text-danger">*</span>)
                            </label>
                            <input type="text" class="form-control UpperCase" id="domiciliotrabajo" name="domiciliotrabajo" value="<?php echo $fila["afi_domitrabajo"]; ?>" required />
                        </div>
                        <div class="col-md-6 mt-md-2">
                            <label for="locatrabajo" class="form-label">Localidad (<span class="text-danger">*</span>)</label>
                            <input type="text" class="form-control UpperCase" id="locatrabajo" name="locatrabajo" value="<?php echo $fila["afi_locatrabajo"]; ?>" required />
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

<script>
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
</script>

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