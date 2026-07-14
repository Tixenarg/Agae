


<!-- Modal -->
<div class="modal fade" id="myModal-modifica-articulo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Artículos</h4>
      </div>
      <div class="modal-body">
 
   
   
            <form name="form1" action="procesa-modifica-pedidos.php"  method="get"   >	
   

                      <div class="form-group">
                      	<input type="hidden" value="<?php echo $pedido ; ?>" id="mod_pedido" name="mod_pedido"  >    
                        <label for="cod">Código del Artículo</label>          
                     	<input type="text" value="" class="form-control" name="mod_codigo" id="mod_codigo" >
                      </div>
                      <div class="form-group">    
                        <label for="descripcion">Descripción</label>          
                     	<input type="text"  class="form-control" name="mod_nombre" id="mod_nombre"  placeholder="Descripción" disabled="disabled"/>
                      </div>
                      <div class="form-group">    
                        <label for="mod_cantidad">Cantidad</label>          
                     	<input type="text"  class="form-control" name="mod_cantidad" id="mod_cantidad"  placeholder="Cantidad"/>
                      </div>
        
     
      		
   
      </div>
      
      <div class="modal-footer">
<!--    	<input type="button" class="btn btn-primary" name="Volver" id="Volver" value="Volver"  onClick="window.location = 'frm-articulos-compuesto.php';"/>
      	<input type="submit" class="btn btn-primary" name="Agregar" id="Agregar" value="Agregar"  onclick="window.location = 'frm-modifica.php';"/>
-->

    	<input type="button" class="btn btn-primary" name="Volver" id="Volver" value="Volver"  onClick="window.location = 'frm-edita-pedido.php?pedido=<?php echo $pedido ?>';"/>
      	<input type="submit" class="btn btn-primary" name="Modificar" id="Modificar" value="Modificar"  />
         	
			</form>	   


      </div>
    </div>
  </div>
</div>