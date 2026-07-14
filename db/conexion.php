
<?php

//////////conexion a la base de datos//////////////////////
$server="localhost";
/* $usuario_db="u736192581_mpc"; */
$usuario_db="root";
/* $clave_db=":ZensHo5Zh"; */
$clave_db="";
$base="agaeweb";
/* $base="u736192581_agaeweb"; */

$link=mysqli_connect($server,$usuario_db,$clave_db,$base)  or die("Connection Failed");
mysqli_set_charset($link, "utf8");
//////////fin de la conexion////////////////////////////////////////////////
?>