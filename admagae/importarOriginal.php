<?php
	# conectare la base de datos
    $con=@mysqli_connect("localhost", "root", "", "agae");
    if(!$con){
        die("imposible conectarse: ".mysqli_error($con));
    }
    if (@mysqli_connect_errno()) {
        die("Connect failed: ".mysqli_connect_errno()." : ". mysqli_connect_error());
    }
	

	$productos = fopen ("padron.csv" , "r" );//leo el archivo que contiene los datos del producto
while (($datos =fgetcsv($productos,1000,";")) !== FALSE )//Leo linea por linea del archivo hasta un maximo de 1000 caracteres por linea leida usando coma(,) como delimitador
{
	
 $linea[]=array('id_abogado'=>$datos[0],'apellidos'=>$datos[1],'nombres'=>$datos[2],'dni'=>$datos[3],'T'=>$datos[4],'F'=>$datos[5],'celular'=>$datos[6],'referente'=>$datos[7]);//Arreglo Bidimensional para guardar los datos de cada linea leida del archivo	
 //$linea[]=array('codigo'=>$datos[0],'descripcion'=>$datos[1],'fabricante'=>$datos[2],'precio'=>$datos[3]);//Arreglo Bidimensional para guardar los datos de cada linea leida del archivo
}
fclose ($productos);//Cierra el archivo
 
	$ingresado=0;//Variable que almacenara los insert exitosos
	$error=0;//Variable que almacenara los errores en almacenamiento
	$duplicado=0;//Variable que almacenara los registros duplicados
    foreach($linea as $indice=>$value) //Iteracion el array para extraer cada uno de los valores almacenados en cada items
	{

			$id=$value["id_abogado"];
			$apellido=$value["apellidos"];
			$nombre=$value["nombres"];
			$dni=$value["dni"];
			$t=$value["T"];
			$f=$value["F"];
			$celular=$value["celular"];
			$referente=utf8_decode($value["referente"]);


 
	$num=0;
	if ($num==0)//Si es == 0 inserto
		{
			$consulta="INSERT INTO padron2 (id_abogado,apellidos,nombres,dni,T,F,celular,referente) VALUES ($id, '$apellido','$nombre','$dni',$t,$f,'$celular','$referente')";
	
			if ($insert=mysqli_query($con,"INSERT INTO padron2 (id_abogado,apellidos,nombres,dni,T,F,celular,referente) VALUES ($id,'$apellido','$nombre','$dni',$t,$f,'$celular','$referente')"))		
		//if ($insert=mysqli_query($con,"insert into articulos (id_articulo,art_codigo,art_proveedor,art_descripcion,art_costo,art_venta) values('NULL', '$codigo','$proveedor','$descripcion',$costook,$ventaok)"))			

			//if ($insert=mysqli_query($con,"insert into productos (codigo, descripcion, fabricante, precio) values('$codigo','$descripcion','$fabricante','$resultado')"))
		{
	echo $msj='<font color=green>Producto <b>'.$id.' '.$referente.'</b> Guardado</font><br/>';
	$ingresado+=1;
	}//fin del if que comprueba que se guarden los datos
	else//sino ingresa el producto
	{
		echo $consulta;
	echo $msj='<font color=red>Producto <b>'.$id.' </b> NO Guardado '.mysqli_error().'</font><br/>';
	$error+=1;
	}
	}//fin de if que comprueba que no haya en registro duplicado
	else
	{
	$duplicado+=1;
	echo $duplicate='<font color=red>El Producto codigo <b>'.$id.'</b> Esta duplicado<br></font>';
	}
	}
	echo "<font color=green>".number_format($ingresado,2)." Productos Almacenados con exito<br/>";

	echo "<font color=red>".number_format($error,2)." Errores de almacenamiento<br/>";
	?>
