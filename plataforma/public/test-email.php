<?php

declare(strict_types=1);

use App\Repositories\AccesoRepository;
use App\Services\EmailService;
use App\Services\MailTemplateService;

require_once dirname(__DIR__)
    . '/vendor/autoload.php';

try {
    /** @var PDO $pdo */
    $pdo =
        require dirname(__DIR__)
            . '/config/database.php';

    $accesoRepository =
        new AccesoRepository($pdo);

    $acceso =
        $accesoRepository
            ->obtenerParaPdf(23);

    if ($acceso === null) {
        throw new RuntimeException(
            'No se encontró el acceso de prueba.'
        );
    }

    $rutaPdfRelativa =
        trim(
            (string) (
                $acceso['ruta_pdf']
                ?? ''
            )
        );

    if ($rutaPdfRelativa === '') {
        throw new RuntimeException(
            'El acceso todavía no tiene PDF generado.'
        );
    }

    $rutaPdfAbsoluta =
        dirname(__DIR__)
        . '/'
        . ltrim(
            $rutaPdfRelativa,
            '/'
        );

    if (!is_file($rutaPdfAbsoluta)) {
        throw new RuntimeException(
            'No existe físicamente el PDF del acceso.'
        );
    }

    $templateService =
        new MailTemplateService();

    $datos = [
        'comprador_nombre' =>
            (string) (
                $acceso['comprador_nombre']
                ?? ''
            ),

        'cantidad_accesos' =>
            1,

        'url_mapa' =>
            'https://www.google.com/maps/search/?api=1&query=Palacio+Alsina+Adolfo+Alsina+934+CABA',

        'url_evento' =>
            'https://evento.agae.org.ar',
    ];

    $html =
        $templateService
            ->renderizar(
                'compra_confirmada',
                $datos
            );

    $emailService =
        new EmailService();

    $destinatario =
        (string) (
            $acceso['comprador_email']
            ?? ''
        );

    if ($destinatario === '') {
        throw new RuntimeException(
            'El comprador no tiene email.'
        );
    }

    $nombreDestinatario =
        trim(
            (
                (string) (
                    $acceso['comprador_nombre']
                    ?? ''
                )
            )
            . ' '
            . (
                (string) (
                    $acceso['comprador_apellido']
                    ?? ''
                )
            )
        );

    $emailService->enviar(
        $destinatario,
        $nombreDestinatario,
        '¡Ya sos parte del Evento más grande de la Abogacía!',
        $html,
        [
            $rutaPdfAbsoluta,
        ]
    );

    header(
        'Content-Type: text/plain; charset=utf-8'
    );

    echo "Email enviado correctamente.\n";
    echo "Destinatario: ";
    echo $destinatario;
    echo "\n";
    echo "Adjunto: ";
    echo $rutaPdfRelativa;

} catch (Throwable $exception) {
    http_response_code(500);

    header(
        'Content-Type: text/plain; charset=utf-8'
    );

    echo "ERROR\n\n";
    echo $exception->getMessage();
    echo "\n\n";
    echo $exception->getFile();
    echo ':';
    echo $exception->getLine();
}