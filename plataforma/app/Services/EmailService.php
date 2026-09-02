<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use RuntimeException;

final class EmailService
{
    private array $config;

    public function __construct()
    {
        $config =
            require dirname(__DIR__, 2)
                . '/config/app.php';

        $this->config =
            $config['mail'] ?? [];

        $this->validarConfiguracion();
    }

    public function enviar(
        string $destinatario,
        string $nombreDestinatario,
        string $asunto,
        string $html,
        array $adjuntos = []
    ): void {
        $mail =
            new PHPMailer(true);

        try {
            $mail->isSMTP();

            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'html';

            $mail->Host =
                (string) $this->config['host'];

            $mail->Port =
                (int) $this->config['port'];

            $mail->SMTPAuth =
                true;

            $mail->Username =
                (string) $this->config['username'];

            $mail->Password =
                (string) $this->config['password'];

            $encryption =
                strtolower(
                    trim(
                        (string) $this->config[
                            'encryption'
                        ]
                    )
                );

            if ($encryption !== '') {
                $mail->SMTPSecure =
                    $encryption;
            }

            $mail->CharSet =
                'UTF-8';

            $mail->setFrom(
                (string) $this->config[
                    'from_address'
                ],
                (string) $this->config[
                    'from_name'
                ]
            );

            $mail->addAddress(
                $destinatario,
                $nombreDestinatario
            );

            foreach ($adjuntos as $adjunto) {
                if (
                    !is_string($adjunto)
                    || !is_file($adjunto)
                ) {
                    throw new RuntimeException(
                        'Uno de los archivos adjuntos no existe.'
                    );
                }

                $mail->addAttachment(
                    $adjunto
                );
            }

            $mail->isHTML(true);

            $mail->Subject =
                $asunto;

            $mail->Body =
                $html;

            $mail->AltBody =
                $this->crearTextoPlano(
                    $html
                );

            $mail->send();

        } catch (Exception $exception) {

            throw new RuntimeException(
                $mail->ErrorInfo,
                0,
                $exception
            );

        }
    }

    private function validarConfiguracion(): void
    {
        foreach (
            [
                'host',
                'username',
                'password',
                'from_address',
            ]
            as $campo
        ) {
            if (
                trim(
                    (string) (
                        $this->config[$campo]
                        ?? ''
                    )
                ) === ''
            ) {
                throw new RuntimeException(
                    sprintf(
                        'Falta configurar %s para el correo.',
                        $campo
                    )
                );
            }
        }
    }

    private function crearTextoPlano(
        string $html
    ): string {
        $texto =
            strip_tags(
                str_replace(
                    [
                        '<br>',
                        '<br/>',
                        '<br />',
                        '</p>',
                        '</div>',
                    ],
                    "\n",
                    $html
                )
            );

        return html_entity_decode(
            trim($texto),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
    }
}