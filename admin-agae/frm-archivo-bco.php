<?php require_once("include/parte_superior.php"); ?>
<!-- Inicio del contenido Principal -->
<?php
require_once('db/conexion_afiliados.php');
$consulta = "SELECT * FROM bancos";
$res = mysqli_query($link, $consulta);
$row = mysqli_fetch_array($res);
mysqli_close($link);

?>

<div class="container">
    <h3 class="text-center">Archivo para debito automático BNA</h3>
    <br>
    <div class="container">
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <div class="h5 text-center">Cuenta Bancaria : <b><?php echo $row['bco_nombre']; ?></b></div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7">
                                <p class="mb-0 text-primary font-weight-bold">Sucursal</p>
                            </div>
                            <div class="col-5">
                                <!-- <p class="mb-0 font-weight-bold">0085</p> -->
                                <p class="mb-0 font-weight-bold"><?php echo $row['bco_sucursal'] ?></p>
                            </div>
                            <br>
                        </div>
                        <div class="row">
                            <div class="col-7">
                                <p class="mb-0 text-primary font-weight-bold">Cuenta Corriente</p>
                            </div>
                            <div class="col-5 ">
                                <!-- <p class="mb-0 font-weight-bold">0005555206</p> -->
                                <p class="mb-0 font-weight-bold"><?php echo $row['bco_ctacte'] ?></p>
                            </div>
                            <br>
                        </div>
                        <div class="row">
                            <div class="col-7">
                                <p class="mb-0 text-primary font-weight-bold">Tipo de Cuenta Corriente</p>
                            </div>
                            <div class="col-5">
                                <p class="mb-0 font-weight-bold"> Cta Cte en $</p>
                            </div>
                            <br>
                        </div>
                        <div class="row">
                            <div class="col-7">
                                <p class="mb-0 text-primary font-weight-bold">Moneda del Movimiento</p>
                            </div>
                            <div class="col-5">
                                <p class="mb-0 font-weight-bold">PESOS</p>
                            </div>
                            <br>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-7">
                                <p class="mb-0 text-primary font-weight-bold">Importe a Debitar</p>
                            </div>
                            <div class="col-5 d-flex align-items-center">
                                <p id="importeDebito" class="mb-0 font-weight-bold me-2">
                                    $ <?php echo $row['bco_importe_debito']; ?>
                                </p>
                                <button type="button" class="btn btn-sm btn-outline-primary ml-2" id="btnEditarImporte" title="Modificar Importe">
                                    <i class="fas fa-pen"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Formulario oculto para editar importe -->
                        <div id="formEditarImporte" class="mt-2" style="display: none;">
                            <div class="input-group input-group-prepend">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="nuevoImporte" placeholder="Nuevo importe">
                                <button class="btn btn-success" id="btnGuardarImporte" title="Guardar cambio">
                                    <i class="fas fa-save"></i>
                                </button>
                                <button class="btn btn-secondary" id="btnCancelarImporte" title="Cancelar">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card-body">
                    <div class="row bg-dark text-white">
                        <div class="form-group col-md-6">
                            <label for="mes">Secuencia del Archivo</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="mes" mes="mes" title="El mes que le corresponde a la fecha tope (Numérico de 2 digitos)">
                                </div>
                                <div class="col-md-6">
                                    <select class="form-control" aria-label="Default select example" id="nroArchivo" name="nroArchivo">
                                        <option value="01" selected>01</option>
                                        <option value="02">02</option>
                                        <option value="03">03</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="fechaTope">Fecha tope Rendición</label>
                            <input type="date" class="form-control" id="fechaTope" name="fechaTope" title="La fecha que desee la rendición y acreditación en la cuenta ">
                        </div>
                    </div>
                    <div class="row">
                        <hr class="col-12" style="border: 0; height: 2px; background-color:#85A1E5">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <button type="button" class="btn btn-primary" id="crear" name="crear">Generar Archivo</button>
                            <button type="button" class="btn btn-success" id="nuevocrear" name="nuevocrear">Generar Nuevo Archivo</button>
                        </div>
                        <div class="col-6">
                            <a href="descarga.php" id="btndescarga">
                                <i class="fas fa-download"> Descargar Archivo</i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
    <input type="hidden" class="form-control" id="casaSucursal" name=" casaSucursal" value="<?php echo $row['bco_sucursal']; ?>">
    <input type="hidden" class="form-control" id="TipoMoneda" name=" TipoMoneda" value="<?php echo $row['bco_tipo_moneda']; ?>">
    <input type="hidden" class="form-control" id="cuenta" name="cuenta" value="<?php echo $row['bco_ctacte']; ?>">
    <input type="hidden" class="form-control" id="moneda" name="moneda" value="<?php echo $row['bco_moneda']; ?>">
    <input type="hidden" value="<?php echo $row['bco_importe_debito']; ?>" class="form-control" id="importe" name="importe">

    <br>
    <div class="container" id="resultado">
        <div class="card">
            <div class="card-footer">
                Se creo el archivo
            </div>
            <div class="card-body">
                <div class="alert alert-danger" role="alert">
                    <div id="resultados_ajax"></div>
                </div>

            </div>
        </div>
    </div>



</div>
<!-- Fin del contenido Principal -->

<?php require_once("include/parte_inferior.php"); ?>
<script src="js/archivo-bco.js"></script>