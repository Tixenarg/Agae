<?php require_once("include/parte_superior.php"); ?>
<!-- Inicio del contenido Principal -->
<?php require_once('db/conexion.php'); ?>
<!-- <div class="container">
    <div class="row">
        <div class="col-lg-12">
            <button id="btnNuevo" type="button" class="btn btn-info" data-toggle="modal"><i class="far fa-plus-square fa-1x"> </i> Agregar </i></button>
        </div>
    </div>
</div> -->
<br>
<div class="container caja">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <!-- <i class="fas fa-table"></i> -->
                     <h3>Padron 2024</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaPadron2024" class="table table-striped table-bordered table-condensed table-sm" style="width:100%">
                            <thead class="text-center thead-dark ">
                                <tr>
                                    <th>APELLIDOS Y NOMBRES</th>
                                    <th>D.N.I</th>
                                    <th>TOMO</th>
                                    <th>FOLIO</th>
                                    <th>TELEFONO</th>
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

<script src="js/panel-padron2024.js"></script>