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


switch($opcion){
    case 1:
        $consulta ="INSERT INTO `padron`(`apellidos`, `nombres`, `dni`, `T`, `F`, `celular`, `referente`) 
        VALUES ('".$apellido."','".$nombre."','".$dni."',$t,$f,'".$celular."','".$referente."')";
        
        $resultado= mysqli_query($link,$consulta);
      
     
        break;    
    case 2:        
    
        $consulta = "UPDATE padron SET apellidos='".$apellido."',nombres='".$nombre."',dni='".$dni."',t=$t,f=$f,celular='".$celular."',referente='".$referente."' WHERE id_abogado=$id";		
        $resultado= mysqli_query($link,$consulta);
         echo var_dump($consulta);


        break;
    case 3:        
        $consulta = "DELETE FROM padron WHERE id_abogado=$id";	
        $resultado= mysqli_query($link,$consulta);	
        break;
    case 4:
        
        $consulta="SELECT `id`, `afi_apellidos`, `afi_nombres`, `afi_dni`, `afi_ctacte`, `afi_email`, `afi_telefono` , `afi_fechasolicitud` FROM `afiliados`";
         
        $res_art = mysqli_query($link, $consulta);
        $data=array();
            while ($row=mysqli_fetch_array($res_art)) {
                        $data[]=array(
                            'id'=> $row['id'],
                            'apellidos'=> $row['afi_apellidos'],
                            'nombres'=> $row['afi_nombres'],
                            'dni'=> $row['afi_dni'],
                            'ctacte'=> $row['afi_ctacte'],
                            // 'email'=> $row['afi_email'],
                            // 'celular'=> $row['afi_telefono'],
                            'fecha'=> formato_fecha_dd_mm_Y($row['afi_fechasolicitud']) 
                        
                        );    
            };
        break;

}


print json_encode($data, JSON_UNESCAPED_UNICODE);


