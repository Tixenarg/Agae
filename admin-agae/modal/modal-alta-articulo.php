<!-- Modal -->
<div class="modal fade" id="ModalArticulo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title" id="myModalLabel">Artículos</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

      </div>

      <div class="modal-body">
        <!-- <form name="form1" action="procesa-alta-insumos.php" method="get" onSubmit="return valida()"> -->
        <form id="form1" name="form1">
          <?php
          $sql = "SELECT * FROM inventarios ORDER BY in_nombre";
          $resultado = mysqli_query($link, $sql);
          ?>
          <div class="form-row">
            <div class="col-7 form-group">
              <label for="inventario" class="text-primary">Inventario</label>
              <select name="inventario" id="inventario" class="form-control" required>
                <option value="0">Seleccione Inventario</option>
                <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
                  <option value="<?php echo $fila["id_inventario"]; ?>"><?php echo $fila["in_nombre"]; ?></option>
                <?php } ?>
              </select>
            </div>

            <div class="col-5 form-group">
              <label for="codigo" class="label-control text-primary">Cod. Artículo</label>
              <input type="text" id="articulo" name="articulo" class="form-control" style="text-align: center;" disabled>
            </div>

            <div class="col-12 form-group">
              <label for="descripcion" class="text-primary">Descripción</label>
              <input type="text" name="descripcion" id="descripcion" class="form-control" placeholder="Descripción" required />
            </div>
            <?php

            $sql1 = "SELECT * FROM unidades ORDER BY uni_nombre";
            $resultado1 = mysqli_query($link, $sql1);
            ?>

            <div class="col-7 form-group">
              <label for="unidades" class="text-primary">Unidades</label>
              <select name="unidades" id="unidades" class="form-control">
                <option value="0">Seleccione Unidades </option>
                <?php while ($fila1 = mysqli_fetch_assoc($resultado1)) { ?>
                  <option value="<?php echo $fila1["id_unidad"]; ?>"><?php echo $fila1["uni_nombre"]; ?></option>
                <?php } ?>
              </select>
            </div>

            <div class="col-5 form-group">
              <label for="stock" class="text-primary">Stock Inicial</label>
              <input type="text" name="stock" id="stock" class="form-control" value="0" style="text-align:right;"/>
            </div>

            <div class="col-6 form-group">
              <label for="precio" class="text-primary">Precio Unitario</label>
              <input type="number" value="0.00" name="precio" id="precio" class="form-control" step="0.01" style="text-align: right;"  />
            </div>

            <?php
            $sqlbienes = "SELECT * FROM tipo_de_bienes ";
            $resbienes = mysqli_query($link, $sqlbienes);
            ?>
            <div class="col-6 form-group">
              <label for="bien" class="text-primary">Tipo de Bienes</label>
              <select name="bien" id="bien" class="form-control">
                <!-- <option value="0">Seleccione Unidades </option> -->
                <?php while ($filabien = mysqli_fetch_assoc($resbienes)) { ?>
                  <option value="<?php echo $filabien["id_tipo"]; ?>"><?php echo $filabien["nombre"]; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>





      </div>
      <div class="modal-footer">
        <input type="button" class="btn btn-primary" name="Volver" id="Volver" value="Volver" onClick="window.location = 'panel-articulos.php';" />
        <input type="submit" class="btn btn-primary" name="Agregar" id="Agregar" value="Agregar" />
        </form>


      </div>
    </div>
  </div>
</div>