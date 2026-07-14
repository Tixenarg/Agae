


<!-- Modal -->
<div class="modal fade" id="myModal-proveedor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Alta de Proveedores</h4>
      </div>
      <div class="modal-body">

            <form name="form-alta-proveedor" id="form-alta-proveedor"  method="get"   > 
            <!-- <form name="form1" id="form-alta-proveedor" action="procesa-alta-proveedor.php"  method="get"   >	 -->
              <div id="resultados_ajax"></div>
                      <div class="form-group">    
                        <label for="cod">Razón Social</label>          
                        <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Razón Social"/>
                     	<!--<input type="text" value="" class="form-control" name="cod" id="cod" style="text-transform:uppercase;" placeholder="Nombre de la Oficina" onKeyUp="javascript:this.value=this.value.toUpperCase();">-->
                      </div>
                      <div class="form-group">    
                        <label for="cod">Cuit</label>          
                        <input type="text" class="form-control" name="cuit1" id="cuit1" placeholder="Cuit"/>
                     	<!--<input type="text" value="" class="form-control" name="cod" id="cod" style="text-transform:uppercase;" placeholder="Nombre de la Oficina" onKeyUp="javascript:this.value=this.value.toUpperCase();">-->
                      </div>
                      <div class="form-group">    
                        <label for="cod">Teléfono</label>          
                        <input type="text" class="form-control" name="te" id="te" placeholder="Teléfono"/>
                     	<!--<input type="text" value="" class="form-control" name="cod" id="cod" style="text-transform:uppercase;" placeholder="Nombre de la Oficina" onKeyUp="javascript:this.value=this.value.toUpperCase();">-->
                      </div>
                      <div class="form-group">    
                        <label for="cod">Domicilio</label>          
                        <input type="text" class="form-control" name="domi" id="domi" placeholder="Domicilio"/>
                     	<!--<input type="text" value="" class="form-control" name="cod" id="cod" style="text-transform:uppercase;" placeholder="Nombre de la Oficina" onKeyUp="javascript:this.value=this.value.toUpperCase();">-->
                      </div>
                      
                      
                      
                      
   
      </div>
      <div class="modal-footer">
      
                    <input type="button" class="btn btn-primary" name="Volver" id="Volver" value="Volver"  onClick="window.location = 'oc-arreglado.php';"/>	
                    <input type="submit" class="btn btn-primary" name="Agregar_prov" id="Agregar_prov" value="Agregar" ></td>	
 	     	</form>
      </div>
    </div>
  </div>
</div>