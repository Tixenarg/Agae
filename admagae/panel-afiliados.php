<?php require_once("include/parte_superior.php"); ?>
<!-- Inicio del contenido Principal -->
<?php require_once('db/conexion_afiliados.php'); ?>
<div class="container">
    <div class="row">
        <div class="col-lg-12 d-flex justify-content-end">
            <button id="btnNuevo" type="button" class="btn btn-info ml-3" ><i class="far fa-plus-square fa-1x " > </i> Alta de Afiliado </i></button>
            <button id="btnExportar" type="button" class="btn btn-success ml-3" ><i class="far fa-file-excel"></i> Exportar a Excel </i></button>
            <!-- <button id="btnExportar" type="button" class="btn btn-info ml-3" ><i class="far fa-solid fa-file-excel"></i> Exportar a Excel </i></button> -->
            <!-- <button id="btnNuevo" type="button" class="btn btn-info" data-toggle="modal"><i class="far fa-plus-square fa-1x"> </i> Agregar </i></button> -->
        </div>
    </div>
</div>
<br>
<div class="container caja">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header text-white bg-gradient-primary">
                    <h4 style="text-align: center ;">
                    <!-- <i class="fas fa-table"></i> -->
                    AFILIADOS AGAE </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaPadron" class="table table-striped table-bordered table-condensed table-sm" style="width:100%">
                            <thead class="text-center thead-dark ">
                                <tr>
                                    <th>Id</th>
                                    <th>DNI</th>
                                    <th>Apellido/s</th>
                                    <th>Nombre/s</th>
                                    <th>Caja Ahorro</th>
                                    <th>F. Afiliación</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fin del contenido Principal -->

<?php require_once("include/parte_inferior.php"); ?>

<script src="js/panel-afiliado.js"></script>