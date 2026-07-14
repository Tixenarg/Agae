<?php require_once("include/parte_superior.php");
include_once 'db/conexion_afiliados.php';

$sqlAfiliado = "SELECT COUNT(afi_estado) as cantafiliado FROM `afiliados` WHERE `afi_estado`=2;";
$rowt = mysqli_query($link, $sqlAfiliado);
$regt = mysqli_fetch_assoc($rowt);
$afil = $regt['cantafiliado'];

$sqlSolic = "SELECT COUNT(afi_estado) as cantsolic FROM `afiliados` WHERE `afi_estado`=1;";
$rows = mysqli_query($link, $sqlSolic);
$regs = mysqli_fetch_assoc($rows);
$solic = $regs['cantsolic'];

$sqlDes = "SELECT COUNT(afi_estado) as cantdesaf FROM `afiliados` WHERE `afi_estado`=3;";
$rowd = mysqli_query($link, $sqlDes);
$regd = mysqli_fetch_assoc($rowd);
$desaf = $regd['cantdesaf'];

$sqlafimp = "SELECT COUNT(afi_estado) as afiliadomp FROM `afiliados` WHERE `afi_estado`=2 AND `afi_forma_pago`=2 ";
$rowmp = mysqli_query($link, $sqlafimp);
$regd = mysqli_fetch_assoc($rowmp);
$mp = $regd['afiliadomp'];

$sqldebito = "SELECT COUNT(afi_estado) as afilideb FROM `afiliados` WHERE `afi_estado`=2 AND `afi_forma_pago`=1 ";
$rowdebito = mysqli_query($link, $sqldebito);
$regd = mysqli_fetch_assoc($rowdebito);
$debito = $regd['afilideb'];

$sqlotro = "SELECT COUNT(afi_estado) as afiliotro FROM `afiliados` WHERE `afi_estado`=2 AND `afi_forma_pago`=3 ";
$rowdebito = mysqli_query($link, $sqlotro);
$reotro = mysqli_fetch_assoc($rowdebito);
$otro = $reotro['afiliotro'];

?>
<style>
    .card-stat {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .card-stat:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .card-header-icon {
        font-size: 2.5rem;
        opacity: 0.8;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df, #224abe);
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #1cc88a, #0f9d58);
    }

    .bg-gradient-danger {
        background: linear-gradient(135deg, #e74a3b, #b82e24);
    }

    .card-stat h6 {
        font-weight: 700;
        text-transform: uppercase;
        color: #6c757d;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }

    .card-stat .number {
        font-size: 2rem;
        font-weight: 800;
        color: #343a40;
    }

    .card-footer a {
        color: #adb5bd;
        transition: color 0.2s;
    }

    .card-footer a:hover {
        color: #343a40;
    }
</style>



<div class="container-fluid">
    <!-- <h4 style="text-align: center;" class="m-4">PANEL DE CONTROL DE AFILIADOS AGAE</h4> -->
    <h4 style="text-align: center;" class="m-4">Panel de Control de Afiliaciones  AGAE</h4>
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card card-stat shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="me-2 bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                        <i class="fas fa-users card-header-icon"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 style="text-align: center;">Total de Afiliados</h6>
                        <div class="number" style="text-align: center;"><?php echo $afil; ?></div>
                        <div class="small text-muted mt-1">
                            <div>Transf. Nación: <strong ><?php echo $debito; ?></strong> | Mercado Pago: <strong><?php echo $mp; ?></strong> | Otros: <strong><?php echo $otro; ?></strong></div>
                        </div>
                    </div>
                    <div class="ms-3">
                        <a href="javascript:void(0)" onclick="mostrartabla(2);" title="Ver detalle">
                            <i class="fa fa-angle-right fa-lg text-secondary"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card card-stat shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 bg-gradient-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                        <i class="fas fa-user-plus card-header-icon"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 style="text-align: center;">Solicitud de Afiliación</h6>
                        <div class="number " style="text-align: center;"><?php echo $solic; ?></div>
                        &nbsp
                    </div>
                    <div class="ms-3">
                        <a href="javascript:void(0)" onclick="mostrartabla(1);" title="Ver solicitudes">
                            <i class="fa fa-angle-right fa-lg text-secondary"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card card-stat shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 bg-gradient-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                        <i class="fas fa-user-times card-header-icon"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 style="text-align: center;">Desafiliados</h6>
                        <div class="number" style="text-align: center;"><?php echo $desaf; ?></div>
                        &nbsp
                    </div>
                    <div class="ms-3">
                        <a href="javascript:void(0)" onclick="mostrartabla(3);" title="Ver desafiliados">
                            <i class="fa fa-angle-right fa-lg text-secondary"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- fin de lo bueno -->
 



    </div>
    <div class="container" id="contenedortabla" style="display: none; margin-top: 20px;">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header text-white" id="encabezado">
                        <h4 style="text-align: center ;" id="titulo">

                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaPadron" class="table table-striped table-bordered table-condensed table-sm" style="width:100%">
                                <thead class="text-center thead-dark ">
                                    <tr>
                                        <th>Id</th>
                                        <th>DNI</th>
                                        <th>Apellido/s</th>
                                        <th>Nombre/s</th>
                                        <th>Caja Ahorro</th>
                                        <th>F. Afiliación</th>
                                        <th> </th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
<?php require_once("include/parte_inferior.php"); ?>
<script src="js/panel-principal3.js" defer></script>