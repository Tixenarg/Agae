<?php 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/PHPMailer/src/Exception.php';
require 'vendor/PHPMailer/src/PHPMailer.php';
require 'vendor/PHPMailer/src/SMTP.php';

function enviarmail($email, $apellido, $dr) {

    $mail = new PHPMailer(true);

$mensaje = "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
</head>
<body>
    <section style='max-width: 800px; margin: 0 auto; font-family: Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #222;'>

        <p style='text-align: justify;'>Estimado/a Dr./Dra. $apellido:</p>

        <p style='text-align: justify;'>
            Te damos la bienvenida a la AGAE. Nos alegra que hayas decidido sumarte a este espacio que trabaja todos los días para defender los derechos de la abogacía pública, fortalecer la profesión y construir una comunidad cada vez más grande y participativa.
        </p>

        <p style='text-align: justify;'>
            A partir de ahora vas a poder acceder a los distintos beneficios, servicios y espacios que la Asociación pone a disposición de sus afiliadas y afiliados.
        </p>

        <p style='text-align: justify;'>
            Entre ellos, contamos con un espacio de coworking gratuito, oficinas equipadas para uso profesional, sala de reuniones y una sala especialmente preparada para encuentros virtuales.
        </p>

        <p style='text-align: justify;'>
            También vas a poder participar de nuestra oferta académica de formación y estudios de posgrado, acceder a propuestas vinculadas al turismo y la recreación, y contar con descuentos especiales en viajes, alojamiento, espectáculos y otros beneficios.
        </p>

        <p style='text-align: justify;'>
            Pero, sobre todo, vas a formar parte de una organización que trabaja para representar y defender los derechos profesionales de quienes integran la abogacía pública.
        </p>

        <p style='text-align: justify;'>
            Te esperamos en nuestra sede, ubicada en Avda. Julio Argentino Roca —Diagonal Sur— Nro. 620, Piso 6°, CABA.
        </p>

        <p style='text-align: justify;'>
            También podés comunicarte con nosotros a través de los siguientes canales:
        </p>

        <p style='text-align: justify;'>
            Página web: www.agae.org.ar<br>
            Administración: secretaria@agae.org.ar<br>
            Whatsapp: 11-7133-3588
        </p>

        <p>Saludos cordiales.</p>

        <br><br>

        <p>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dr. Rubén Ramos<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Secretario General AGAE
        </p>

    </section>
</body>
</html>";

    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();

        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'afiliaciones@agae.org.ar';
        $mail->Password   = 'Afil$2022';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Encoding correcto
        $mail->CharSet  = 'UTF-8';
        $mail->Encoding = 'base64';

        // Remitente y destinatarios
        $mail->setFrom('afiliaciones@agae.org.ar', 'AGAE');
        $mail->addAddress($email, $apellido);
        $mail->addAddress('afiliaciones@agae.org.ar', 'AGAE');

        // Adjuntos
        $mail->addAttachment('reportes/SOLICITUD_DE_AFILIACION.pdf');
        $mail->addAttachment('reportes/FORMULARIO DEBITO AUTOMATICO CUENTA SUELDO.pdf');

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = 'Solicitud de Afiliación a AGAE';
        $mail->Body    = $mensaje;
        $mail->AltBody = 'Solicitud de Afiliación a AGAE. Bienvenido/a a la AGAE.';

        $mail->send();

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

?>