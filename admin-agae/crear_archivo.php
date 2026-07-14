<?php 
require_once('db/conexion_afiliados.php');
$estadoafiliado=2;
$formadepago=1;
$consulta="SELECT * FROM `afiliados` WHERE `afi_estado`=$estadoafiliado AND `afi_forma_pago`=$formadepago";
$res = mysqli_query($link, $consulta);

// while ($post = each($_POST))
// {
// echo $post[0] . " = " . $post[1];
// }
// echo "<br><br>";
/////////////////////Funciones ////////////////////////////////

function completarConCeros($importe,$Longitud){
    $ceros="";
    $long = strlen($importe);
    $repetir = $Longitud - $long;
    for ($i=1;$i<=$repetir;$i++ ){
        $ceros.="0";
    }
    $valordevuelto=$ceros.$importe;
    return $valordevuelto;
}

function completarConEspaciosAlFinal($cuanto){
    $espacios="";
    for ($i=1;$i<=$cuanto;$i++){
        $espacios .=" "; 
    }
    return $espacios;
}

function convertir_fecha_YYYYMMDD($fecha){
    $ano=substr($fecha,0,4);
    $mes=substr($fecha,5,2);
    $dia=substr($fecha,8,2);
    $fechaok=$ano.$mes.$dia;
    return  $fechaok;
}

function convertirImporteSinPuntos($importe){
    $splitDec = explode(".", $importe); 
    // echo "Entero ".$splitDec[0]. "<br>";  
    // echo "Decimal ".$splitDec[1]."<br>";
    $impo=$splitDec[0].$splitDec[1];
    return $impo;
}
/////////////////////registro 1////////////////////////////////

$tipoDeRegistro = "1";
$sucursal =$_POST['casa'];//"0085";
$monedaCta=$_POST['tipomoneda'];//"10";
$ctacte = $_POST['cuenta'];//"0005555206";
$moneda=$_POST['moneda'];//"P";
$identificador="E";
$secuenciaDeArchivo= $_POST['secuencia'];//"0801"; //mes y nro de archi enviado
$fechaTope= convertir_fecha_YYYYMMDD($_POST['fecha']) ;//"20220815";///AAAAMMDD
$indicador="REE";
$filler=94;
$espacios="";

for ($i = 1; $i <= $filler; $i++) {
    $espacios.=" " ;
}
$saltolinea="\n";

$linea1 = $tipoDeRegistro . $sucursal .$monedaCta. $ctacte .$moneda. $identificador.$secuenciaDeArchivo.$fechaTope.$indicador.$espacios.$saltolinea;
//echo $linea1;

$archivoBNA= fopen("archivo.txt","w");
fwrite($archivoBNA,$linea1);
//fwrite($archivoBNA,$linea1);


/////////////////////registro 2////////////////////////////////

$tipoDeRegistro="2";
$sucCuentaCte="";
$tipoCuentaCte="CA";
$nroCuentaCte="";
$importeoriginal=$_POST['importe'];
$importesinpuntos= convertirImporteSinPuntos($importeoriginal) ;
$importe=completarConCeros($importesinpuntos,15);
//$importe="000000000120000";
$fechaVencimiento="00000000";//"20220815";// AAAAMMDD esta fecha es variable
$estado="0";
$contador=0;

$filler2=86;
$espacios2="";
for ($i = 1; $i <= $filler2; $i++) {
    $espacios2.=" " ;
}

$mensaje="";

while ($row=mysqli_fetch_array($res)) {
    //$sucCuentaCte=substr($row['afi_ctacte'],0,4);
    if(strlen($row['afi_ctacte'])==14){
        $auxCtaCte=substr($row['afi_ctacte'],0,4);
        if ($auxCtaCte=="0002"){
            $sucCuentaCte="0085";        
        }else{
            $sucCuentaCte=substr($row['afi_ctacte'],0,4);
        }
        //$sucCuentaCte=substr($row['afi_ctacte'],0,4);
        $nroCuentaCte="0".substr($row['afi_ctacte'],4,15);
        $registro2 = $tipoDeRegistro.$sucCuentaCte.$tipoCuentaCte.$nroCuentaCte.$importe.$fechaVencimiento.$estado.$espacios2;
        fwrite($archivoBNA,$registro2. $saltolinea);
        // echo $nroCuentaCte."<br>";
        $contador=$contador + 1 ;
    }else{
        $mensaje.= "<div class='text-danger'>ERROR en el numero de caja de ahorro ID: ".$row['id']." "."DNI: ".$row['afi_dni']." "." Apellido y Nombre: ".$row['afi_apellidos']." ".$row['afi_nombres']."</div>";
        
    }
    
    
};
echo $mensaje;

////////////////////registro 3 ///////////////////////////
$tipoDeRegistro="3";
$totalADebitar= convertirImporteSinPuntos($importeoriginal * $contador) ;

$TotalADebitarOk=completarConCeros($totalADebitar,15);

$cantidad = completarConCeros($contador,6) ;
$debitosNoAplicados=completarConCeros(0,15);
$regNoAplicados =completarConCeros(0,6);
$filler3= completarConEspaciosAlFinal(85);


$registro3= $tipoDeRegistro.$TotalADebitarOk.$cantidad.$debitosNoAplicados.$regNoAplicados.$filler3;
fwrite($archivoBNA,$registro3);


?>