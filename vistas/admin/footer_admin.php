</main> <!-- Cierre de .admin-wrapper -->

<!-- MODAL DE CONFIRMACIÓN DE CERRAR SESIÓN -->
<div class="modal fade" id="modalLogout" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fs-6"><i class="bi bi-exclamation-triangle me-2"></i>Cerrar Sesión</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-3">
                ¿Estás seguro de que deseas salir del sistema?
            </div>
            <div class="modal-footer justify-content-center bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="logout.php" class="btn btn-sm btn-danger">Salir</a>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- LIBRERÍAS JS Y SCRIPTS DE ESTRUCTURA       -->
<!-- ========================================== -->

<!-- 1. Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- 2. Script de control de Sidebar responsive -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btnToggle = document.getElementById('btnToggleSidebar');
        const sidebar = document.getElementById('sidebarMenu');

        if (btnToggle && sidebar) {
            btnToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });

            document.addEventListener('click', (e) => {
                if (window.innerWidth < 992 &&
                    !sidebar.contains(e.target) &&
                    !btnToggle.contains(e.target) &&
                    sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            });
        }
    });
</script>
</body>
</html>