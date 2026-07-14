<?php require_once("include/parte_superior.php"); ?>
<!-- Inicio del contenido Principal -->

<div class="container">

    <div class="container">
        <div class="container d-flex justify-content-center my-3">
            <div class="card" style="width: 35rem;">
                <img src="assets/icon.png" class="card-img-top align-self-center" style="width : 15rem" alt="">
                <div class="card-body">
                    <div class="d-flex justify-content-center">

                        <h5 class="card-title ">BIENVENIDO A AGAE</h5>
                    </div>
                    <p class="card-text col-12"><?php echo $mensaje; ?></p>



                    <div class="d-flex justify-content-center">
                        <a class="btn btn-primary" href="panel-afiliados.php">Volver al Panel de Afiliados</a>
                    </div>





                </div>
            </div>
        </div>


    </div>

</div>
<?php require_once("include/parte_inferior.php"); ?>