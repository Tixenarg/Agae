<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use Throwable;

final class MailTemplateService
{
    public function renderizar(
        string $template,
        array $datos
    ): string {
        $template =
            basename($template);

        $ruta =
            dirname(__DIR__, 2)
            . '/resources/mail/'
            . $template
            . '.php';

        if (!is_file($ruta)) {
            throw new RuntimeException(
                'La plantilla de correo no existe.'
            );
        }

        ob_start();

        try {
            require $ruta;

            $html =
                ob_get_clean();

            if (!is_string($html)) {
                throw new RuntimeException(
                    'No se pudo renderizar el correo.'
                );
            }

            return $html;

        } catch (Throwable $exception) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }

            throw $exception;
        }
    }
}