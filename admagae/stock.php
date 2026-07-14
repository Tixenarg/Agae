<?php require_once("include/parte_superior.php"); ?>
<!-- Inicio del contenido Principal -->
<?php require_once('db/conexion.php'); ?>
<?php require_once('include/funciones.php'); ?>
<div class="container">

    <div class="card card-info">
        <!-- <div class="card-header">
            <h4>Reportes</h4>
        </div> -->
        <div class="card-body">
            <h5 class="card-title">Stock<div class="float-right">
                    <h4><strong><?php echo hoy(); ?></strong></h4>
                </div>
                <br>
                <br>
                <?php
                $sql = "SELECT * FROM inventarios ORDER BY in_nombre";
                $resultado = mysqli_query($link, $sql);
                ?>

                <!-- <div class="form-group "> -->
                <div class="form-row">


                    <div class="form-group col-md-4">
                        <label for="inventario" class="text-info">Inventario:</label>
                        <select name="inventario" size="1" id="inventario" class="form-control">
                            <option value="0">Seleccione Inventario TODOS</option>
                            <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
                                <?php if ($inventario == 0) { ?>
                                    <option value="<?php echo $fila['id_inventario'] ?>"><?php echo $fila['in_nombre'] ?></option>
                                <?php } else { ?>
                                    <option <?php if ($fila["id_inventario"] == $inventario) {
                                                echo "selected ";
                                            } ?>value="<?php echo $fila["id_inventario"]; ?>"><?php echo $fila["in_nombre"]; ?></option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label for="existencia" class="text-info">Existencia:</label>
                        <select name="existencia" size="1" id="existencia" class="form-control">

                            <option value="0">Seleccione Existencia TODOS</option>
                            <option value="1">Con Stock</option>
                            <option value="2">Sin Stock</option>

                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <br>
                        <button class="btn btn-primary col-12 mt-2" onclick="reportear();">Consultar</button>
                    </div>


                </div>
                <div id="loader" class='col-md-12' style="margin-top:5px"></div>
                
                <!-- Carga los datos ajax -->
        </div>
        <!--div card body-->

    </div>

</div>
<!-- Fin del contenido Principal -->
<div class="container">
    <div id="resultados" >
    </div>
</div>

<?php require_once("include/parte_inferior.php"); ?>
<script type="text/javascript" src="js/nuevo_reporte.js"></script>