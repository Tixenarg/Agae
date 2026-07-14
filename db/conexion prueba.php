
<?php

//////////conexion a la base de datos//////////////////////
$server="localhost";
$usuario_db="u736192581_rcorreaprueba";
$clave_db="9c^=Rwy!C";
$base="u736192581_agaewebprueba";
$link=mysqli_connect($server,$usuario_db,$clave_db,$base)  or die("Connection Failed");
mysqli_set_charset($link, "utf8");
//////////fin de la conexion////////////////////////////////////////////////
?>