

<?php 

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

$fecha ="2022-08-23";
$importe ="1200.00";

$ano=substr($fecha,0,4);
$mes=substr($fecha,5,2);
$dia=substr($fecha,8,2);
$fechaok=$ano.$mes.$dia;
echo $fechaok."<br><br><br>";



echo "PRUEBA DE IMPORTES <br><br><br>";
$splitDec = explode(".", $importe); 
echo "Entero ".$splitDec[0]. "<br>";  
echo "Decimal ".$splitDec[1]."<br>";

$impo=$splitDec[0].$splitDec[1];

echo "total ". completarConCeros($impo,15);

?>