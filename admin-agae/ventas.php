<?php require_once("include/parte_superior.php"); ?>
<!-- Inicio del contenido Principal -->
<?php require_once("db/conexion.php"); ?>
<?php require_once("include/funciones.php"); ?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-title">
                    <br>
                    <h4 style="text-align: center;">Factura Ventas</h4>

                </div>
                <div class="card-body">
                    <form id="frm-linea" name="frm-linea">
                        <!-- <div class="float-right">
                            <div>
                                <input type="date" class="form-control col-12">
                            </div>
                        </div> -->
                        <div class="form-row">
                            <!-- <div class="form-group col-3">
                                
                                <select class="form-control" name="tipoventa" id="tipoventa">
                                    <option value="0" selected>Venta Contado</option>
                                    <option value="1">Venta Cta. Cte.</option>
                                </select>
                            </div> -->
                            <div class="col-9">
                                    <h5>Factura</h5>
                            </div>


                            <div class="form-group col-3 bg-">

                                <!-- <label for="fecha" class="text-info">Fecha</label> -->
                                
                                <input type="date" name="fecha" id="fecha" class="form-control col-12 bg-gradient-light" value="<?php echo formato_fecha_Y_mm_dd(hoy()); ?>">
                                
                            </div>

                            <div class="row">
                                <div class="form-group col-md-2">
                                    <label for="codigo" class="text-info">Código De Artículo</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <button class="btn btn btn-primary" data-toggle="modal" data-target="#modalCodigo" id="espacio" name="espacio" type="button" tabindex="-1"><i class="fa fa-search"></i></button>
                                        </div>
                                        <input type="text" class="form-control " id="codigo" name="codigo" placeholder="Código" tabindex="0">
                                    </div>
                                </div>
                                <div class="form-group col-5">
                                    <label for="descripcion" class="text-info">Descripción</label>
                                    <input type="text" class="form-control" id="descripcion" name="descripcion" readonly tabindex="-1">
                                </div>
                                <div class="form-group col-2">
                                    <label for="importe" class="text-info">Importe</label>
                                    <input type="text" class="form-control" id="importe" name="importe">
                                </div>
                                <div class="form-group col-2">
                                    <label for="cantidad" class="text-info">Cantidad</label>
                                    <input type="text" class="form-control" id="cantidad" name="cantidad">
                                </div>
                                <div class="form-group col-1 mt-2">
                                    <br>
                                    <button type="submit" class="btn btn-primary" id="carga_codigo" name="carga_codigo">Cargar</button>
                                </div>
                            </div>
                            <!-- <div class="form-group col-3">
                            
                                <select class="form-control" name="tipoventa" id="tipoventa">
                                    <option value="0" selected>Venta Contado</option>
                                    <option value="1">Venta Cta. Cte.</option>
                                </select>
                            </div> -->
                        </div>


                    </form>
                </div>
            </div>

        </div>



    </div>
    <div class="card">
        <div class="row">
            <div class="col-12">
                <div id="detalle_ajax">
                    <table class="table">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">Código</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">Importe</th>
                                <th scope="col">Cantidad</th>
                                <th scope="col">Subtotal</th>
                                <th scope="col">Eliminar</th>
                            </tr>
                        </thead>
                        <tbody id="ajax_lineas">

                        </tbody>

                    </table>
                    <div class="d-flex">
                    <div class="col-8">
                        <div class="form-group col-12 d-inline" style="display:inline-block;">   
                            <div class="custom-control custom-radio custom-control-inline mt-2">
                                <input type="radio" id="contado" name="tipoventa" class="custom-control-input" value="1" checked>
                                <label class="custom-control-label" for="contado">Venta Contado</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline mt-2">
                                <input type="radio" id="ctacte" name="tipoventa" class="custom-control-input" value="2">
                                <label class="custom-control-label" for="ctacte">Venta Cta. Cte.</label>
                            </div>
                        </div> 
                        <div class="form-group col-12 mt-2"  id="cliente" >
                            <?php
                            $sqlper = "SELECT * FROM personas";
                            $resper = mysqli_query($link, $sqlper);
                            // $cantidad=mysqli_num_rows($resOficinas);
                            ?>
                            <!-- <label for="personas" class="text-info">Cliente</label> -->
                            <select class="form-control" name="personas" id="personas" tabindex="0">
                                <option selected value="0">Consumidor Final...</option>
                                <?php
                                while ($reg_per = mysqli_fetch_array($resper)) {
                                ?>
                                    <option value="<?php echo $reg_per['id_persona']; ?>"><?php echo $reg_per['pe_apellido'] . " " . $reg_per['pe_nombre']; ?></option>
                                <?php
                                };
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-4 d-flex ">
                        <input type="button" class="btn btn-primary" value="grabar" id="grabar" name="grabar" class="d-flex align-items-center">
                    </div>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade " id="modalCodigo" role="dialog" aria-labelledby="modalCodigo" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Códigos de Cobranza</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Example DataTables Card-->
                    <div class="card mb-3">

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover table-sm" id="dataTablecodigo" name="dataTablecodigo" width="100%" cellpadding="0">
                                    <!--  <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example"> -->
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Concepto</th>
                                            <th>Importe</th>
                                            <th>Seleccionar</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        //require_once("conexion.php");
                                        global $link;

                                        $sql = "SELECT `id_articulo`, `art_nombre`, `art_precio`  FROM `articulos`";
                                        $res = mysqli_query($link, $sql);
                                        while ($reg = mysqli_fetch_array($res)) {
                                        ?>

                                            <tr class="odd gradeX ">
                                                <td><?php echo $reg["id_articulo"]; ?></td>
                                                <td><?php echo $reg["art_nombre"]; ?></td>
                                                <td style="text-align: right;">
                                                    <?php echo "$ " . number_format($reg["art_precio"], 2, ',', '.'); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <button type="button" class="btn btn-outline-dark btn-sm btnElegir " data-toggle="modal" data-target="#Modal-modif-codigo">
                                                        <i class="fa fa-check-square" aria-hidden="true"></i></button>
                                                </td>
                                            </tr>

                                        <?php
                                        }
                                        ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- <div class="card-footer small text-muted">
                </div> -->
                    </div> <!-- card -->

                </div>
                <!-- <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary">Save changes</button>
            </div> -->
            </div>
        </div>
    </div>

</div>
<!-- Fin del contenido Principal -->


<?php require_once("include/parte_inferior.php"); ?>
<script type="text/javascript" src="js/ventas.js"></script>