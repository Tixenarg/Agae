<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <?php

    require_once('db/conexion_afiliados.php');
    require_once('include/funciones.php');

    $estado = $_GET['estado'];
    $pago = $_GET['pago'];

    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Afiliados.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    // como dar formato a la columna en excels (FUNCIONAAAAAAAA)////////////
    // <table>
    //   <tr>
    //         <td style="mso-number-format:'0.00';">12346579.00</td>
    //   </tr>
    // </table>
    // mso-number-format:"0"                  Sin Decimales
    // mso-number-format:"0.00"               02 Decimals
    // mso-number-format:"#,##0.000"            Coma separadora de miles y 03 decimales
    // mso-number-format:"mm/dd/yy"            Formato de Fecha Completa
    // mso-number-format:"mmmm d, yyyy"         Formato de Fecha Literal
    // mso-number-format:"m/d/yy h:mm AM/PM"      Formato de Fecha Corta con Hora y AM/PM
    // mso-number-format:"Short Date"            Formato de Fecha Corta
    // mso-number-format:"Medium Date"            Formato de Fecha Mediana
    // mso-number-format:"d-mmm-yyyy"            Fecha Mediana separada por guiones
    // mso-number-format:"Short Time"            Formato corto de hora
    // mso-number-format:"Medium Time"         Formato mediana de hora
    // mso-number-format:"Long Time"            Formato de Hora Larga
    // mso-number-format:"Percent"            Porcentaje con 02 decimales
    // mso-number-format:"0%"               Porcentaje sin decimale
    // mso-number-format:"0.E+00"               Notación Cientifica
    // mso-number-format:"@"               Texto
    // mso-number-format:"# ???/???"            Fracciones - de 3 dígitos a más (312/943)
    // mso-number-format:"0022£0022#,##0.00"         Formato de Moneda (Libras Esterlinas)
    // mso-number-format:"#,##0.00_ ;[Red]-#,##0.00"   Formato de Número con negativos en rojo y signo -

    ?>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Reportes de Afiliados AGAE </title>
</head>

<body>
    <table width="100%" border="1" cellspacing="0" cellpadding="0">
        <tr style="font-style:italic; color:#006; font-size:x-large">
            <h4>
                <th colspan="23" align="left">Listado de Afiliados AGAE <?php echo " " . hoy() . "estado: " . $estado . " forma de pago " . $pago; ?></th>
            </h4>
        </tr>

        <!-- <tr>
    <td colspan="7" bgcolor="skyblue"><CENTER><strong>REPORTE DE INVENTARIO</strong></CENTER></td>-->
        </tr>
        <tr>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>ID</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>APELLIDOS</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>NOMBRES</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>DNI</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>NRO. CAJA DE AHORRO</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>NACIONALIDAD</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>TELEFONO</strong></td>

            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>SEXO</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>ESTADO CIVIL</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>FECHA NACIMIENTO</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>DOMICILIO</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>LOCALIDAD</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>COD.POSTAL</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>PROVINCIA</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>EMAIL</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>ESTUDIOS</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>TITULO</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>NRO. LEGAJO</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>ORGANISMO QUE LIQUIDA HABER</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>CUIL</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>ORGANISMO DONDE TRABAJA</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>DOMICILIO TRABAJO</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>LOCALIDAD TRABAJO</strong></td>

            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>ESTADO</strong></td>
            <td bgcolor="#200C00" style="color:#FFF" align="center"><strong>FORMA DE PAGO</strong></td>
        </tr>

        <?PHP

        $sqlBase = "SELECT 
    a.`id`, a.`afi_apellidos`, a.`afi_nombres`, a.`afi_dni`, a.`afi_ctacte`,
    a.`afi_nacionalidad`, a.`afi_telefono`, a.`afi_sexo`, a.`afi_civil`, a.`afi_nacimiento`,
    a.`afi_domicilio`, a.`afi_localidad`, a.`afi_codigopostal`, a.`afi_provincia`, a.`afi_email`,
    a.`afi_estudio`, a.`afi_titulo`, a.`afi_legajo`, a.`afi_orgliquidahaber`, a.`afi_cuil`,
    a.`afi_orgtrabaja`, a.`afi_domitrabajo`, a.`afi_locatrabajo`, a.`afi_declaroyacepto`,
    a.`afi_aceptopago`, a.`afi_fechasolicitud`, a.`afi_estado`, a.`afi_forma_pago`,
    es.`estado_nombre`, pa.`fpago_nombre`
    FROM afiliados a
    LEFT JOIN afiliado_estados es ON es.id_estado = a.afi_estado
    LEFT JOIN afiliado_forma_de_pago pa ON pa.id_fpago = a.afi_forma_pago";

        // Ahora aplicamos la magia de los filtros
        if ($estado == 0 && $pago == 0) {
            // Caso 1: Todos los estados y todas las formas de pago
            $sql = $sqlBase;
        } elseif ($estado == 0 && $pago != 0) {
            // Caso 2: Todos los estados, pero una forma de pago específica
            $sql = $sqlBase . " WHERE a.afi_forma_pago = $pago";
        } elseif ($estado != 0 && $pago == 0) {
            // Caso 3: Un estado específico, pero todas las formas de pago
            $sql = $sqlBase . " WHERE a.afi_estado = $estado";
        } else {
            // Caso 4: Estado y forma de pago específicos
            $sql = $sqlBase . " WHERE a.afi_estado = $estado AND a.afi_forma_pago = $pago";
        }

        $resultado2 = mysqli_query($link, $sql);

        while ($res = mysqli_fetch_array($resultado2)) {
            $id = $res["id"];
            $apellidos = $res["afi_apellidos"];
            $nombres = $res["afi_nombres"];
            $dni = $res["afi_dni"];
            $ctacte = strval($res["afi_ctacte"]);
            $nacionalidad = $res["afi_nacionalidad"];
            $telefono = $res["afi_telefono"];
        ?>
            <tr>
                <td align="center"><?php echo $id; ?></td>
                <td><?php echo $apellidos; ?></td>
                <td><?php echo $nombres; ?></td>
                <td><?php echo $dni; ?></td>
                <td style="mso-number-format:'@';"><?php echo $ctacte; ?></td>
                <td align="center"><?php echo $nacionalidad; ?></td>
                <td align="center"><?php echo $telefono; ?></td>
                <td align="center"><?php echo  $res["afi_sexo"]; ?></td>
                <td align="center"><?php echo  $res["afi_civil"]; ?></td>
                <td align="center"><?php echo  $res["afi_nacimiento"]; ?></td>
                <td align="center"><?php echo  $res["afi_domicilio"]; ?></td>
                <td align="center"><?php echo  $res["afi_localidad"]; ?></td>
                <td align="center"><?php echo  $res["afi_codigopostal"]; ?></td>
                <td align="center"><?php echo  $res["afi_provincia"]; ?></td>
                <td align="center"><?php echo  $res["afi_email"]; ?></td>
                <td align="center"><?php echo  $res["afi_estudio"]; ?></td>
                <td align="center"><?php echo  $res["afi_titulo"]; ?></td>
                <td align="center"><?php echo  $res["afi_legajo"]; ?></td>
                <td align="center"><?php echo  $res["afi_orgliquidahaber"]; ?></td>
                <td align="center"><?php echo  $res["afi_cuil"]; ?></td>
                <td align="center"><?php echo  $res["afi_orgtrabaja"]; ?></td>
                <td align="center"><?php echo  $res["afi_domitrabajo"]; ?></td>
                <td align="center"><?php echo  $res["afi_locatrabajo"]; ?></td>
                <td align="center"><?php echo  $res["estado_nombre"]; ?></td>
                <td align="center"><?php echo  $res["fpago_nombre"]; ?></td>
            </tr>
        <?php
        }
        ?>
    </table>

</body>
<?php mysqli_close($link); ?>

</html>