<?php
//////////conexion a la base de datos//////////////////////
$server="localhost";
$usuario_db="root";
$clave_db="";
$base="agaeweb";
$link=mysqli_connect($server,$usuario_db,$clave_db);

if (mysqli_connect_errno()){
    echo "Fallo al conectar con la BBDD";
    exit();
}
mysqli_select_db($link,$base) or die ("No se Encuentra la BBDD"." ".$base);

mysqli_set_charset($link, "utf8");

echo "conexion correcta a la base de datos";
//////////fin de la conexion////////////////////////////////////////////////



/////////////////////consultas Preparadas ////////////////////////////////
/* Pasos a seguir son 6
1.- crear la consulta sql 
        $sql = "SELECT * from PRODUCTOS WHERE paisorigen =?";
2.- preparar la consulta con . devuelve el objeto stmt
        $resultado =mysqli_prepare($link,$sql);
3.- Unir los parametros.  devuelve true o false        
        $ok=mysqli_stmt_bind_param($resultado,"s");
4.- Ejecuta la consulta. devuelve true o false         
        $ok=mysqli_stmt_execute($resultado);
5.- Asociar las variables a la consulta.  devuelve true o false        
        mysqli_stmt_bind_result($resultado);
6.- Lectura de valores para ello usaremos la funcion 
        mysqli_stmt_fetch($resultado);
 */

////////////////////////////Consulta tipo SELECT/////////////////////////////////////
$dnivar='26066493';

$sql="SELECT afi_apellidos,afi_nombres,afi_ctacte,afi_dni FROM afiliados WHERE afi_dni=?";
$resultado = mysqli_prepare($link,$sql);
$ok=mysqli_stmt_bind_param($resultado,'s',$dnivar);
$ok=mysqli_stmt_execute($resultado);
if($ok==false){
    echo "Error al ejecutar la consulta";
}else {
    $ok=mysqli_stmt_bind_result($resultado,$apellido,$nombre,$ctacte,$dni);
    echo "Afiliados encontrados"."<br><br>";
    while (mysqli_stmt_fetch($resultado)){
        echo $apellido ." ".$nombre." ".$ctacte." ".$dni."<br><br>";
    }
}


mysqli_stmt_close($resultado);

/////////////////////////Consulta tipo Insert///////////////////////////////////////
$ape="Correa";
$nom="Ruben antonio";
$dni="22805302";
$cta="12345678901234";
$fecha="1972-07-23";

$sql="INSERT INTO afiliados (afi_apellidos,afi_nombres,afi_dni,afi_ctacte,afi_nacimiento) VALUES (?,?,?,?,?) ";
$resultado = mysqli_prepare($link,$sql);
$ok=mysqli_stmt_bind_param($resultado,"sssss",$ape,$nom,$dni,$cta,$fecha);
$ok=mysqli_stmt_execute($resultado);
if($ok==false){
    echo "Error al ejecutar la consulta";
}else {
    //$ok=mysqli_stmt_bind_result($resultado,$apellido,$nombre,$ctacte,$dni);
    echo "Se a Agragado nuevo registro"."<br><br>";
    //while (mysqli_stmt_fetch($resultado)){
    //    echo $apellido ." ".$nombre." ".$ctacte." ".$dni."<br><br>";
    //}
}

mysqli_stmt_close($resultado);

/////////////////////////Consulta tipo actualizar///////////////////////////////////////
$ape="Correa";
$nom="Actualizado";
$dni="22805302";
$cta="12345678901234";
$fecha="1972-07-23";

$sql="UPDATE afiliados SET afi_apellidos=?,afi_nombres =?,
                            afi_dni=?,afi_ctacte=?,afi_nacimiento=? WHERE id=20";
//$sql="INSERT INTO afiliados (afi_apellidos,afi_nombres,afi_dni,afi_ctacte,afi_nacimiento) VALUES (?,?,?,?,?) ";
$resultado = mysqli_prepare($link,$sql);
$ok=mysqli_stmt_bind_param($resultado,"sssss",$ape,$nom,$dni,$cta,$fecha);
$ok=mysqli_stmt_execute($resultado);
if($ok==false){
    echo "Error al ejecutar la consulta";
}else {
    //$ok=mysqli_stmt_bind_result($resultado,$apellido,$nombre,$ctacte,$dni);
    echo "Se a Agragado nuevo registro"."<br><br>";
    //while (mysqli_stmt_fetch($resultado)){
    //    echo $apellido ." ".$nombre." ".$ctacte." ".$dni."<br><br>";
    //}
}

mysqli_stmt_close($resultado);

