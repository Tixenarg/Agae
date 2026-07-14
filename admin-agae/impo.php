<?php require_once("include/parte_superior.php"); ?>
<!-- Inicio del contenido Principal -->

<div class="container">
    

</div>
<?php require_once("include/parte_inferior.php"); ?>
<script>
    $(document).ready(function () {

        Swal.fire({
        title: 'Esta Seguro que desea Importar datos?',
        text: "Se borraran todos los datos ",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, Importar Archivo!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "importar.php";

        }
    })
        
    });

</script>