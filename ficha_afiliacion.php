<?php 
function generaficha($id){
include('vendor/fpdf/fpdf.php');
include('db/conexion.php');

//$id=$_GET['id'];
$sql="SELECT * FROM afiliados WHERE id=$id";
$res = mysqli_query($link, $sql);
$row=mysqli_fetch_assoc($res);


class PDF extends FPDF
{
// Cabecera de página
    function Header()
        {
            // Logo
            
            $this->Image('assets/icon.png',180,5,20,);
            $this->SetFont('Arial','B',20);
            // $this->Cell(60,15, utf8_decode('Solicitud de Afiliación ') ,0,1,'L');    
            $this->Cell(60,15, mb_convert_encoding('Solicitud de Afiliación a AGAE' ,'ISO-8859-1', 'UTF-8'),0,1,'L');
            $this->SetFont('Arial','',11);
            

            $this->Ln(10);
        }

}

// Creación del objeto dea la clase heredada
$pdf = new PDF('P','mm','A4');
//$pdf->AliasNbPages();
//$pdf->SetMargins(15, 10 , 5); definir margenes

$pdf->AddPage();



$pdf->MultiCell(195,5, mb_convert_encoding('Por la presente solicito se acepte mi afiliación a esta organización gremial, declarando conocer su Estatuto y disposiciones legales vigentes, a las que ajustaré mi actuación. A tal fin, detallo los datos personales y laborales pertinentes: ','ISO-8859-1', 'UTF-8'),0,'J',0);
$pdf->SetFont('Helvetica','B',13);
$pdf->ln(5);
$pdf->Cell(195,10, 'Datos Personales :',0,1,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(195,7, 'Apellido /s.: '.mb_convert_encoding($row['afi_apellidos'],'ISO-8859-1', 'UTF-8' ) ,1,1,'L');
$pdf->Cell(195,7, 'Nombre /s.: '.mb_convert_encoding($row['afi_nombres'],'ISO-8859-1', 'UTF-8'),1,1,'L');
$pdf->Cell(65,7, mb_convert_encoding('Nacionalidad.: ' .$row['afi_nacionalidad'],'ISO-8859-1', 'UTF-8'),1,0,'L');
$pdf->Cell(65,7, mb_convert_encoding('Nº DNI: ','ISO-8859-1', 'UTF-8') .$row['afi_dni'],1,0,'L');
$pdf->Cell(65,7, 'Tel. Contacto.:'.$row['afi_telefono'],1,1,'L');
$pdf->Cell(65,7, 'Sexo: '.$row['afi_sexo'],1,0,'L');
$pdf->Cell(65,7, 'Estado Civil.: ' .$row['afi_civil'],1,0,'L');
$pdf->Cell(65,7, 'Fecha de Nac.: ' .$row['afi_nacimiento'],1,1,'L');
$pdf->Cell(195,7, mb_convert_encoding('Domicilio (Calle y Nº).: '.$row['afi_domicilio'],'ISO-8859-1', 'UTF-8') ,1,1,'L');
$pdf->Cell(65,7, mb_convert_encoding('Código Postal.: ','ISO-8859-1', 'UTF-8') .$row['afi_codigopostal'],1,0,'L');
$pdf->Cell(130,7, mb_convert_encoding('Localidad.: ' .$row['afi_localidad'],'ISO-8859-1', 'UTF-8'),1,1,'L');
$pdf->Cell(95,7, mb_convert_encoding('Provincia.: ' .$row['afi_provincia'],'ISO-8859-1', 'UTF-8'),1,0,'L');
$pdf->Cell(100,7, 'Email.: ' .$row['afi_email'],1,1,'L');
$pdf->Cell(95,7, mb_convert_encoding('Estudios.: ' .$row['afi_estudio'],'ISO-8859-1', 'UTF-8'),1,0,'L');
$pdf->Cell(100,7, mb_convert_encoding('Título o Carrera.: '.$row['afi_titulo'],'ISO-8859-1', 'UTF-8') ,1,1,'L');
$pdf->SetFont('Helvetica','B',13);
$pdf->ln(5);
$pdf->Cell(195,10, 'Datos Laborales :',0,1,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(100,7, mb_convert_encoding('Nº de Legajo: '.$row['afi_legajo'],'ISO-8859-1', 'UTF-8') ,1,0,'L');
$pdf->Cell(95,7, mb_convert_encoding('Nº de CUIL: '.$row['afi_cuil'],'ISO-8859-1', 'UTF-8') ,1,1,'L');
$pdf->Cell(195,7, mb_convert_encoding('Organismo que liquida su haber: '.$row['afi_orgliquidahaber'],'ISO-8859-1', 'UTF-8'),1,1,'L');
$pdf->Cell(195,7, mb_convert_encoding('Organismo donde trabaja: '.$row['afi_orgtrabaja'],'ISO-8859-1', 'UTF-8'),1,1,'L');
$pdf->Cell(195,7, mb_convert_encoding('Domicilio del lugar de trabajo: Calle y Nº: '.$row['afi_domitrabajo'],'ISO-8859-1', 'UTF-8') ,1,1,'L');
$pdf->Cell(195,7, mb_convert_encoding('Localidad: ' .$row['afi_locatrabajo'],'ISO-8859-1', 'UTF-8'),1,1,'L');
$pdf->ln(10);
$pdf->MultiCell(195,5, mb_convert_encoding('Dejo expresa constancia que de solicitar en el futuro mi desafiliación, lo hare mediante nota individual presentada personalmente ante la entidad gremial, o bien, por telegrama o carta documento individual (no colectiva); ello en un todo de acuerdo a lo reglamentado por la Ley Nº23.551, su Decreto Reglamentario y su Estatuto Social a fin de garantizar la expresa voluntad del afiliado.
Declaro conocer y aceptar las normas vigentes para el uso de los servicios al momento de requerir los mismos. ','ISO-8859-1', 'UTF-8'),0,'J',0);
$pdf->ln(7);
/////firmas///////////
$pdf->Cell(100,7, 'Datos del Afiliado',0,0,'L');
$pdf->ln(10);
//$pdf->Cell(95,7, 'Datos del Delegado',0,1,'L');
$pdf->ln(7);
$pdf->Cell(100,7, 'Firma: ............................................................................',0,1,'L');
//$pdf->Cell(95,7, 'Firma:  ............................................................................',0,1,'L');
$pdf->Cell(100,7, mb_convert_encoding('Aclaración:  '.$row['afi_apellidos'].' '.$row['afi_nombres'],'ISO-8859-1', 'UTF-8'),0,1,'L');
$pdf->Cell(100,7, mb_convert_encoding('Nº DNI:      '.$row['afi_dni'],'ISO-8859-1', 'UTF-8'),0,0,'L');
//$pdf->Cell(95,7, utf8_decode('Aclaración:  .....................................................................'),0,1,'L');



////////////////////////////////salida ///////////////////////////
$pdf->Output('F','reportes/SOLICITUD_DE_AFILIACION.pdf');

}








?>
