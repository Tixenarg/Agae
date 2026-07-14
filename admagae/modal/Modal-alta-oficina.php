
<div class="modal" tabindex="-1" role="dialog" id="Modal-alta-oficina">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nombre de la Dependencia</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form method="post" id="frmOficina" name="frmOficina">
                    <div id="resultados_ajax"></div>
                    <div id="loader"></div>
                    <div class="form-group">
                        <label for="nombreact" class="control-label">Nombre de la Dependencia u Oficina</label>
                        <input type="text" class="form-control" name="nombreofi" id="nombreofi" placeholder="Nueva Dependencia" required autocomplete="off" />

                    </div>


            </div>
            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary" id="guardar_datos">Grabar</button>

                </form>

            </div>

        </div>
    </div>
</div>