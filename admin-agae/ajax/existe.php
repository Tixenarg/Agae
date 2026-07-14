<?php
include_once '../db/conexion.php';

$dni = $_POST['dni'];
$cantidad=0;

$consulta=" SELECT * FROM `padron` WHERE dni='".$dni."'";
$resultado= mysqli_query($link,$consulta);
$cantidad=mysqli_num_rows($resultado);
echo $cantidad;

