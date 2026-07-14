<?php
include_once '../db/conexion.php';



  



        $consulta="SELECT `APELLIDO Y NOMBRE`, `TOMO`, `FOLIO`, `Numero`, `TELEFONO` FROM `padron2024`";
        $res_art = mysqli_query($link, $consulta);
        $data=array();
            while ($row=mysqli_fetch_array($res_art)) {
                        $data[]=array(
                            'nombres'=> $row['APELLIDO Y NOMBRE'],
                            'dni'=> $row['Numero'],
                            'T'=> $row['TOMO'],
                            'F'=> $row['FOLIO'],
                            'celular'=> $row['TELEFONO']
            
                            
                        
                        );    
            };



print json_encode($data, JSON_UNESCAPED_UNICODE);

?>

