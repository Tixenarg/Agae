<?php require_once("include/parte_superior.php"); ?>
<!-- Inicio del contenido Principal -->

<div class="container">
  
<?php
    require_once("db/conexion.php");
	
	$borrarTabla="TRUNCATE TABLE padron";
	$ejecuto=mysqli_query($link,$borrarTabla);

	
	
	$productos = fopen ("importar/padron.csv" , "r" );//leo el archivo que contiene los datos del producto
while (($datos =fgetcsv($productos,1000,";")) !== FALSE )//Leo linea por linea del archivo hasta un maximo de 1000 caracteres por linea leida usando coma(,) como delimitador
{
	
// $linea[]=array('id_abogado'=>$datos[0],'apellidos'=>$datos[1],'nombres'=>$datos[2],'dni'=>$datos[3],'T'=>$datos[4],'F'=>$datos[5],'celular'=>$datos[6],'referente'=>$datos[7]);//Arreglo Bidimensional para guardar los datos de cada linea leida del archivo	
 $linea[]=array('apellidos'=>$datos[0],'nombres'=>$datos[1],'dni'=>$datos[2],'T'=>$datos[3],'F'=>$datos[4],'celular'=>$datos[5],'referente'=>$datos[6]);//Arreglo Bidimensional para guardar los datos de cada linea leida del archivo	
 //$linea[]=array('codigo'=>$datos[0],'descripcion'=>$datos[1],'fabricante'=>$datos[2],'precio'=>$datos[3]);//Arreglo Bidimensional para guardar los datos de cada linea leida del archivo
}
fclose ($productos);//Cierra el archivo
 
	$ingresado=0;//Variable que almacenara los insert exitosos
	$error=0;//Variable que almacenara los errores en almacenamiento
	$duplicado=0;//Variable que almacenara los registros duplicados
    foreach($linea as $indice=>$value) //Iteracion el array para extraer cada uno de los valores almacenados en cada items
	{

			//$id=$value["id_abogado"];
			$apellido=utf8_encode($value["apellidos"]);
			$nombre=utf8_encode($value["nombres"]);
			$dni=$value["dni"];
			$t=$value["T"];
			$f=$value["F"];
			$celular=$value["celular"];
			$referente=utf8_encode($value["referente"]);


 
	$num=0;
	if ($num==0)//Si es == 0 inserto
		{
			//$consulta="INSERT INTO padron (id_abogado,apellidos,nombres,dni,T,F,celular,referente) VALUES ($id, '$apellido','$nombre','$dni',$t,$f,'$celular','$referente')";
	
			if ($insert=mysqli_query($link,"INSERT INTO padron (apellidos,nombres,dni,T,F,celular,referente) VALUES ('$apellido','$nombre','$dni',$t,$f,'$celular','$referente')"))		

            {
                echo $msj='<font color=green>DNI <b>'.$dni.' '.$apellido.'</b> Guardado</font><br/>';
                $ingresado+=1;
            }//fin del if que comprueba que se guarden los datos
            else//sino ingresa el producto
            {
                //echo $consulta;
                echo $msj='<font color=red>DNI <b>'.$dni.' </b> NO Guardado </font><br/>';
                $error+=1;
            }
	}//fin de if que comprueba que no haya en registro duplicado
	else
	{
	$duplicado+=1;
	echo $duplicate='<font color=red>El Producto codigo <b>'.$id.'</b> Esta duplicado<br></font>';
	}
	}
	echo "<font color=green>".number_format($ingresado,2)." Registros almacenados con exito<br/>";

	echo "<font color=red>".number_format($error,2)." Errores de almacenamiento<br/>";
?>

</div>
<!-- Fin del contenido Principal -->

<?php require_once("include/parte_inferior.php"); ?>