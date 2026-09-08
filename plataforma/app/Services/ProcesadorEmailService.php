<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AccesoRepository;
use App\Repositories\EmailRepository;
use RuntimeException;
use Throwable;

final class ProcesadorEmailService
{
    public function __construct(
        private readonly EmailRepository $emails,
        private readonly AccesoRepository $accesos,
        private readonly AccesoPdfService $pdfs,
        private readonly MailTemplateService $templates,
        private readonly EmailService $mailer
    ) {
    }

    public function procesar(
        array $email
    ): void {
        $emailId =
            (int) $email['id'];

        $ordenId =
            (int) $email['orden_id'];

        $this->emails
            ->marcarProcesando(
                $emailId
            );

        try {
            $datos =
                json_decode(
                    (string) $email['datos_plantilla'],
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );

            if (!is_array($datos)) {
                throw new RuntimeException(
                    'Los datos de plantilla no son válidos.'
                );
            }

            $datos['url_mapa'] =
                'https://www.google.com/maps/search/?api=1&query=Palacio+Alsina+Adolfo+Alsina+934+CABA';

            $datos['url_evento'] =
                'https://evento.agae.org.ar';

            $accesos =
                $this->accesos
                    ->obtenerPorOrden(
                        $ordenId
                    );

            if ($accesos === []) {
                throw new RuntimeException(
                    'La orden no tiene accesos asociados.'
                );
            }

            $adjuntos = [];

            foreach ($accesos as $acceso) {
                $rutaRelativa =
                    trim(
                        (string) (
                            $acceso['ruta_pdf']
                            ?? ''
                        )
                    );

                if (
                    $rutaRelativa === ''
                    || !is_file(
                        dirname(__DIR__, 2)
                        . '/'
                        . ltrim(
                            $rutaRelativa,
                            '/'
                        )
                    )
                ) {
                    $rutaRelativa =
                        $this->pdfs
                            ->generar(
                                (int) $acceso['id']
                            );
                }

                $rutaAbsoluta =
                    dirname(__DIR__, 2)
                    . '/'
                    . ltrim(
                        $rutaRelativa,
                        '/'
                    );

                if (!is_file($rutaAbsoluta)) {
                    throw new RuntimeException(
                        sprintf(
                            'No existe el PDF del acceso %s.',
                            (string) $acceso['codigo']
                        )
                    );
                }

                $adjuntos[] =
                    $rutaAbsoluta;
            }

            $html =
                $this->templates
                    ->renderizar(
                        (string) $email['plantilla'],
                        $datos
                    );

            $nombreDestinatario =
                trim(
                    (
                        (string) (
                            $datos['comprador_nombre']
                            ?? ''
                        )
                    )
                    . ' '
                    . (
                        (string) (
                            $datos['comprador_apellido']
                            ?? ''
                        )
                    )
                );

            $this->mailer
                ->enviar(
                    (string) $email['destinatario'],
                    $nombreDestinatario,
                    (string) $email['asunto'],
                    $html,
                    $adjuntos
                );

            $this->emails
                ->marcarEnviado(
                    $emailId
                );

            try {
                $this->accesos
                    ->marcarEmailEnviadoPorOrden(
                        $ordenId
                    );
            } catch (Throwable $exception) {
                error_log(
                    sprintf(
                        '[email-sync] Email %d enviado, pero no se pudo actualizar email_enviado_en de la orden %d: %s',
                        $emailId,
                        $ordenId,
                        $exception->getMessage()
                    )
                );
            }

        } catch (Throwable $exception) {
            $intentos =
                (int) $email['intentos']
                + 1;

            $maxIntentos =
                (int) $email['max_intentos'];

            $this->emails
                ->marcarFallido(
                    $emailId,
                    $exception->getMessage(),
                    $intentos < $maxIntentos
                );

            throw $exception;
        }
    }
}