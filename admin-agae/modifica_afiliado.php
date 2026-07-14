<?php require_once('include/parte_superior.php');?>        
        <?php
        if (!empty($_POST)) {

                //require_once('ficha_afiliacion.php');
                require_once("db/conexion_afiliados.php");

                $sqlhoy = "SELECT DATE_FORMAT(NOW(),'%d/%m/%Y') as hoy";
                $resul = mysqli_query($link, $sqlhoy);
                $hoyfila = mysqli_fetch_array($resul);
                $hoy = $hoyfila['hoy'];
                $fecha = str_replace('/', '-', $hoy);
                $fechahoy = date('Y-m-d', strtotime($fecha));

                $id = $_POST['id'];
                $apellidos = strtoupper($_POST['apellidos']);
                $nombres = strtoupper($_POST['nombres']);
                $dni = $_POST['dni'];
                $nacionalidad = strtoupper($_POST['nacionalidad']);
                $telefono = $_POST['telefono'];
                $sexo = $_POST['sexo'];
                $civil = $_POST['estadocivil'];
                $nacimiento = $_POST['fechanacimiento'];
                $domicilio = strtoupper($_POST['domicilio']);
                $localidad = strtoupper($_POST['localidad']);
                $codigopostal = $_POST['codigopostal'];
                $provincia = strtoupper($_POST['provincia']);
                $email = $_POST['mail'];
                $estudio = strtoupper($_POST['estudios']);
                $carrera = strtoupper($_POST['carrera']);
                $legajo = $_POST['legajo'];
                $haber = strtoupper($_POST['organismo']);
                $cuil = $_POST['cuil'];
                $trabaja = strtoupper($_POST['trabajo']);
                $domitrabajo = strtoupper($_POST['domiciliotrabajo']);
                $locatrabajo = strtoupper($_POST['locatrabajo']);
                $declaroyacepto = '1';
                $aceptopago = '1';
                $ctacte = $_POST['ctacte'];
                
                $usuario = $_SESSION['id_usuario'];

                $nomcompleto = $apellidos.' '.$nombres;
                        $sql = "UPDATE `afiliados` SET `afi_apellidos`=?,`afi_nombres`=?,`afi_dni`=?,`afi_ctacte`=?,`afi_nacionalidad`=?,`afi_telefono`=?,`afi_sexo`=? 
                                ,`afi_civil`=?,`afi_nacimiento`=?,`afi_domicilio`=?,`afi_localidad`=?,`afi_codigopostal`=?,`afi_provincia`=?,`afi_email`=?,`afi_estudio`=?
                                ,`afi_titulo`=?,`afi_legajo`=?,`afi_orgliquidahaber`=?,`afi_cuil`=?,`afi_orgtrabaja`=?,`afi_domitrabajo`=?,`afi_locatrabajo`=?
                                ,`afi_declaroyacepto`=?,`afi_aceptopago`=?,`afi_fechasolicitud`=? WHERE id=$id";

                        $resultado = mysqli_prepare($link, $sql);
                        $ok = mysqli_stmt_bind_param($resultado, "sssssssssssssssssssssssss", $apellidos, $nombres, $dni, $ctacte, $nacionalidad, $telefono, $sexo, $civil, $nacimiento, $domicilio, $localidad, $codigopostal, $provincia, $email, $estudio, $carrera, $legajo, $haber, $cuil, $trabaja, $domitrabajo, $locatrabajo, $declaroyacepto, $aceptopago, $fechahoy);
                        $ok = mysqli_stmt_execute($resultado);

                        $tipo="M";//agrega registtro en auditoria 
                        $sqlaudi="INSERT INTO `afiliados_auditoria`(`id_afiliado`, `tipo_auditoria`, `usuario`, `fecha`) VALUES (?,?,?,?)";
                        $res=mysqli_prepare($link,$sqlaudi);
                        $ok2= mysqli_stmt_bind_param($res, "isis", $id, $tipo, $usuario, $fechahoy);
                        $ok2=mysqli_stmt_execute($res);

                        //$resaudi=mysqli_query($link, $sqlaudi);

                        if ($ok == false) {
                                echo "Error al Actualizar La Ficha  del Afiliado";
                        } else {
                                $mensaje="Se Modifico con éxito la ficha del Afiliado <strong > ".$nomcompleto. "</strong >"." DNI <strong >".$dni."</strong >" ;
                                include ('bienvenida.php');
                        }
                
                mysqli_stmt_close($resultado);
        }
        ?>