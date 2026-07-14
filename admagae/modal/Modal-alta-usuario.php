
<div class="modal" tabindex="-1" role="dialog" id="Modal-alta-usuarios">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form method="post" id="frmUsuario" name="frmUsuario">
                    <div id="resultados_ajax"></div>
                    <div id="loader"></div>
                    <div class="form-group">
                        <label for="nombres" class="control-label">Apellidos y Nombres</label>
                        <input type="text" class="form-control" name="nombres" id="nombres" placeholder="Nombres" required autocomplete="off" />
                    </div>
                    <div class="form-group">
                        <label for="usuario" class="control-label">Usuarios</label>
                        <input type="text" class="form-control" name="usuario" id="usuario" placeholder="Usuario" required autocomplete="off" />
                    </div>
                    <div class="form-group">
                        <label for="Password" class="control-label">Contraseñas</label>
                        <input type="password" class="form-control" name="Password" id="Password" placeholder="Contraseña" required autocomplete="off" />
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