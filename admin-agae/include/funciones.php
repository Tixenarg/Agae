<?php 
/////////////////////////Funciones de Fecha///////////////////////////
function mostrarLasVariablesQueViajanPorPost (){
    while ($post = each($_POST))
    {
    echo $post[0] . " = " . $post[1];
    }
    echo "<br><br>";  
}


function formato_fecha_dd_mm_Y($date)
{	
	$fecha = str_replace('/', '-', $date);
    return date('d/m/Y', strtotime($date));
}

function formato_fecha_Y_mm_dd($date)
{	
	$fecha = str_replace('/', '-', $date);
    return date('Y-m-d', strtotime($fecha));
}  

function hoy()
{

    global $link;
    
    $sqlhoy="SELECT DATE_FORMAT(NOW(),'%d/%m/%Y') as hoy";
    $resul=mysqli_query($link, $sqlhoy);
    $hoyfila=mysqli_fetch_array($resul);
    $hoy=$hoyfila['hoy'];
    
    return ($hoy);

}

/////////////////////////Funciones de Fecha///////////////////////////

function hashPassword($password) 
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    return $hash;
}
