<?php
if (!empty($_POST)) {
    // Eliminamos los requires de HTML (modalprocesando y bienvenida)
    require_once('ficha_afiliacion.php');
    require_once('enviar_mail.php');
    require_once("db/conexion.php");

    $fechahoy = date('Y-m-d');

    $apellidos   = strtoupper($_POST['apellidos']);
    $nombres     = strtoupper($_POST['nombres']);
    $dni         = $_POST['dni'];
    $nacionalidad= strtoupper($_POST['nacionalidad']);
    $telefono    = $_POST['telefono'];
    $sexo        = $_POST['sexo'];
    $civil       = $_POST['estadocivil'];
    $nacimiento  = $_POST['fechanacimiento'];
    $domicilio   = strtoupper($_POST['domicilio']);
    $localidad   = strtoupper($_POST['localidad']);
    $codigopostal= $_POST['codigopostal'];
    $provincia   = strtoupper($_POST['provincia']);
    $email       = $_POST['mail'];
    $estudio     = strtoupper($_POST['estudios']);
    $carrera     = strtoupper($_POST['carrera']);
    $legajo      = $_POST['legajo'];
    $haber       = strtoupper($_POST['organismo']);
    $cuil        = $_POST['cuil'];
    $trabaja     = strtoupper($_POST['trabajo']);
    $domitrabajo = strtoupper($_POST['domiciliotrabajo']);
    $locatrabajo = strtoupper($_POST['locatrabajo']);
    $declaroyacepto = (isset($_POST["acepto"])) ? '1' : '0';
    $aceptopago     = (isset($_POST["aceptopago"])) ? '1' : '0';
    $ctacte         = isset($_POST["ctacte"]) ? $_POST["ctacte"] : "00000000000000";

    $dr = ($sexo == 'FEMENINO') ? 'Dra' : 'Dr';
    $nomcompleto = $apellidos . ' ' . $nombres;

    // Verificar si existe
    $stmt_existe = mysqli_prepare($link, "SELECT afi_dni FROM afiliados WHERE afi_dni = ?");
    mysqli_stmt_bind_param($stmt_existe, "s", $dni);
    mysqli_stmt_execute($stmt_existe);
    $resultado = mysqli_stmt_get_result($stmt_existe);

    if (mysqli_num_rows($resultado) > 0) {
        echo 'duplicado'; // Respuesta silenciosa para JS
    } else {
        $sql = "INSERT INTO `afiliados` (
            `afi_apellidos`, `afi_nombres`, `afi_dni`, `afi_ctacte`, `afi_nacionalidad`, 
            `afi_telefono`, `afi_sexo`, `afi_civil`, `afi_nacimiento`, `afi_domicilio`, 
            `afi_localidad`, `afi_codigopostal`, `afi_provincia`, `afi_email`, `afi_estudio`, 
            `afi_titulo`, `afi_legajo`, `afi_orgliquidahaber`, `afi_cuil`, `afi_orgtrabaja`, 
            `afi_domitrabajo`, `afi_locatrabajo`, `afi_declaroyacepto`, `afi_aceptopago`, `afi_fechasolicitud`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssssssssssssssssssss", 
            $apellidos, $nombres, $dni, $ctacte, $nacionalidad, 
            $telefono, $sexo, $civil, $nacimiento, $domicilio, 
            $localidad, $codigopostal, $provincia, $email, $estudio, 
            $carrera, $legajo, $haber, $cuil, $trabaja, 
            $domitrabajo, $locatrabajo, $declaroyacepto, $aceptopago, $fechahoy
        );

        $res = mysqli_stmt_execute($stmt);

        if ($res) {
            $ultimoid = mysqli_insert_id($link);

            $sql_audi = "INSERT INTO `afiliados_auditoria`(`id_afiliado`, `tipo_auditoria`, `usuario`, `fecha`) VALUES (?, 'A', 10, ?)";
            $stmt_audi = mysqli_prepare($link, $sql_audi);
            mysqli_stmt_bind_param($stmt_audi, "is", $ultimoid, $fechahoy);
            mysqli_stmt_execute($stmt_audi);

            generaficha($ultimoid);
            enviarmail($email, $nomcompleto, $dr);
            
            echo 'ok'; // Respuesta silenciosa de éxito
        } else {
            echo 'error_db';
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_stmt_close($stmt_existe);

} else {
    header("Location: index.html");
    exit();
}
mysqli_close($link);
?>