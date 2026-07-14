<?php require_once("include/parte_superior.php"); ?>
<!-- Inicio del contenido Principal -->
<?php require_once('db/conexion.php'); ?>
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <button id="btnNuevo" type="button" class="btn btn-info" data-toggle="modal"><i class="far fa-plus-square fa-1x"> </i> Agregar </i></button>
        </div>
    </div>
</div>
<br>
<div class="container caja">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-table"></i> Padron
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaPadron" class="table table-striped table-bordered table-condensed table-sm" style="width:100%">
                            <thead class="text-center thead-dark ">
                                <tr>
                                    <th>Código</th>
                                    <th>Apellido</th>
                                    <th>Nombre</th>
                                    <th>D.N.I</th>
                                    <th>T</th>
                                    <th>F</th>
                                    <th>Celular</th>
                                    <th>Referente</th>
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

<!--Modal para CRUD-->
<div class="modal fade" id="modalCRUD" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" cabecera="cabecera">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formPadron">
                <div class="modal-body">
                    <div class="form-row">
                        <input type="hidden" id="id" name="id">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fas fa-id-card mr-1"></i> Dni</div>
                            </div>
                            <input type="text" class="form-control" id="dni" name="dni" autocomplete="off" tabindex="0" required>


                        </div>
                        <!-- <small id="dni" class="form-text float-center text-primary">(*) Ingresar el Nro de DNI con puntos</small> -->
                        <div class="input-group mt-3">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fas fa-id-card mr-1"></i> Apellidos</div>
                            </div>
                            <input type="text" class="form-control UpperCase" id="apellido" name="apellido" autocomplete="off">
                        </div>
                        <div class="input-group mt-3">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fas fa-id-card mr-1"></i> Nombres</div>
                            </div>
                            <input type="text" class="form-control UpperCase" id="nombre" name="nombre" autocomplete="off">
                        </div>
                        <div class="input-group mt-3 col-6">
                            <div class="input-group-prepend">
                                <div class="input-group-text "> T</div>
                            </div>
                            <input type="text" class="form-control" id="t" name="t" autocomplete="off">
                        </div>
                        <div class="input-group mt-3 col-6">
                            <div class="input-group-prepend">
                                <div class="input-group-text "> F</div>
                            </div>
                            <input type="text" class="form-control " id="f" name="f" autocomplete="off">
                        </div>
                        <div class="input-group mt-3">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fas fa-mobile-alt mr-1"></i> Cel.</div>
                            </div>
                            <input type="text" class="form-control" id="celular" name="celular" autocomplete="off">
                        </div>
                        <div class="input-group mt-3">
                            <div class="input-group-prepend">
                                <div class="input-group-text "><i class="fas fa-male mr-1"></i>Referente</div>
                            </div>
                            <input type="text" class="form-control UpperCase" id="referente" name="referente" autocomplete="off">
                        </div>

                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnGuardar" class="btn btn-dark">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php require_once("include/parte_inferior.php"); ?>

<script src="js/panel-padron.js"></script>