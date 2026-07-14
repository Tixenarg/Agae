<?php
require_once '../include/funciones.php';
include_once '../db/conexion_afiliados.php';




$id = (isset($_POST['id'])) ? $_POST['id'] : '';
$opcion = (isset($_POST['opcion'])) ? $_POST['opcion'] : '';
$dni = (isset($_POST['dni'])) ? $_POST['dni'] : '';
$apellido = (isset($_POST['apellido'])) ? $_POST['apellido'] : '';
$nombre = (isset($_POST['nombre'])) ? $_POST['nombre'] : '';
$t = (isset($_POST['t'])) ? $_POST['t'] : 0;
$f = (isset($_POST['f'])) ? $_POST['f'] : 0;
$celular = (isset($_POST['celular'])) ? $_POST['celular'] : '';
$referente = (isset($_POST['referente'])) ? $_POST['referente'] : '';



$consulta="SELECT `id`, `afi_apellidos`, `afi_nombres`, `afi_dni`, `afi_ctacte`, `afi_email`, `afi_telefono` , `afi_fechasolicitud` FROM `afiliados` WHERE`afi_estado`= $opcion";
         
$res_art = mysqli_query($link, $consulta);
$data=array();
    while ($row=mysqli_fetch_array($res_art)) {
                $data[]=array(
                    'id'=> $row['id'],
                    'apellidos'=> $row['afi_apellidos'],
                    'nombres'=> $row['afi_nombres'],
                    'dni'=> $row['afi_dni'],
                    'ctacte'=> $row['afi_ctacte'],
                    'fecha'=> formato_fecha_dd_mm_Y($row['afi_fechasolicitud']) 
                
                );    
    };

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

 mysqli_close($link);

?>