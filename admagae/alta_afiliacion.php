<?php require_once('include/parte_superior.php');?>
<?php print_r($_SESSION);?>

        <link rel="icon" href="assets/icon.png" />
        <?php
        if (!empty($_POST)){

                // require_once('ficha_afiliacion.php');
                require_once("db/conexion_afiliados.php");

                $sqlhoy = "SELECT DATE_FORMAT(NOW(),'%d/%m/%Y') as hoy";
                $resul = mysqli_query($link, $sqlhoy);
                $hoyfila = mysqli_fetch_array($resul);
                $hoy = $hoyfila['hoy'];
                $fecha = str_replace('/', '-', $hoy);
                $fechahoy= date('Y-m-d', strtotime($fecha));

                $apellidos =strtoupper($_POST['apellidos']) ;
                $nombres = strtoupper ($_POST['nombres']);
                $dni = $_POST['dni'];
                $nacionalidad =strtoupper($_POST['nacionalidad']) ;
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
                $declaroyacepto = (isset($_POST["acepto"])) ? '1' : '0';
                $aceptopago = (isset($_POST["aceptopago"])) ? '1' : '0';
                $ctacte = $_POST['ctacte'];
                
                // $usuario= (isset($_SESSION['id_usuario']))? $_SESSION['id_usuario']:0;
               
                $usuario = $_SESSION['id_usuario'];
                $nomcompleto = $apellidos.' '.$nombres;
                
                $sqlexiste="SELECT * FROM afiliados WHERE afi_dni = '".$dni."'";
                $resultado = mysqli_query($link, $sqlexiste);
                if(mysqli_num_rows($resultado) > 0){
                        $mensaje="El Dni "."<strong class='text-danger'>" . $dni . "</strong >"." ya se encuentra registrado como afiliado";
                        include ('bienvenida.php');

                        }else {
                                $sql = "INSERT INTO `afiliados`( `afi_apellidos`, `afi_nombres`, `afi_dni`, `afi_ctacte`, `afi_nacionalidad`, `afi_telefono`, `afi_sexo`, `afi_civil`, `afi_nacimiento`, `afi_domicilio`, `afi_localidad`, `afi_codigopostal`, `afi_provincia`, `afi_email`, `afi_estudio`, `afi_titulo`, `afi_legajo`, `afi_orgliquidahaber`, `afi_cuil`, `afi_orgtrabaja`, `afi_domitrabajo`, `afi_locatrabajo`, `afi_declaroyacepto`, `afi_aceptopago`, `afi_fechasolicitud`)
                                        VALUES ('" . $apellidos . "','" . $nombres . "','" . $dni . "','" .$ctacte ."','". $nacionalidad . "','" . $telefono . "','" . $sexo . "','" . $civil . "','" . $nacimiento . "','" . $domicilio . "','" . $localidad . "','" . $codigopostal . "','" . $provincia . "','" . $email . "','" . $estudio . "','" . $carrera . "','" . $legajo . "','" . $haber . "','" . $cuil . "','" . $trabaja . "','" . $domitrabajo . "','" . $locatrabajo . "','" . $declaroyacepto . "','" . $aceptopago ."','" . $fechahoy . "')";
                                
                                $res = mysqli_query($link, $sql);
                                $ultimoid= mysqli_insert_id($link);
                                
                                $sqlaudi="INSERT INTO `afiliados_auditoria`(`id_afiliado`, `tipo_auditoria`, `usuario`, `fecha`) VALUES ($ultimoid,'A',$usuario ,'" . $fechahoy . "')";
                                $resaudi=mysqli_query($link, $sqlaudi);

                                if ($res) {
                                                ///////////////////enviar mail/////////////////////////
                                                // generaFicha($ultimoid);
                                                // enviarmail($email,$nomcompleto);
                                                $mensaje="Se dío de Alta al Afiliado ".$nomcompleto;
                                                include ('bienvenida.php');

                                        }else{
                                                echo ('por algun motivo no se dio de alta el afiliado');
                                        }

                        }

        }else {
                echo ('no envio datos por post');
                header("refresh:0;url=index.html");
                }
                mysqli_close($link);
        ?>