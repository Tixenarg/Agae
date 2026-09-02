<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AccesoRepository;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Mpdf\Output\Destination;
use RuntimeException;

final class AccesoPdfService
{
    public function __construct(
        private readonly AccesoRepository $accesos,
        private readonly QrCodeService $qrCode
    ) {
    }

    public function generar(
        int $accesoId
    ): string {
        if ($accesoId < 1) {
            throw new DomainException(
                'El acceso solicitado no es válido.'
            );
        }

        $acceso =
            $this->accesos
                ->obtenerParaPdf(
                    $accesoId
                );

        if ($acceso === null) {
            throw new DomainException(
                'No se encontró el acceso.'
            );
        }

        if (
            !in_array(
                $acceso['estado'],
                [
                    'emitido',
                    'utilizado',
                ],
                true
            )
        ) {
            throw new DomainException(
                'El acceso todavía no está habilitado para emitir PDF.'
            );
        }

        $acceso['ruta_qr'] =
            $this->qrCode->generar(
                (string) $acceso['codigo']
            );

        $archivoTemplate =
            trim(
                (string) (
                    $acceso['archivo_plantilla']
                    ?? ''
                )
            );

        if ($archivoTemplate === '') {
            throw new DomainException(
                'El tipo de acceso no tiene una plantilla PDF configurada.'
            );
        }

        $templatePath =
            dirname(__DIR__, 2)
            . '/resources/pdf/templates/'
            . basename($archivoTemplate);

        if (!is_file($templatePath)) {
            throw new RuntimeException(
                'No existe el archivo de plantilla PDF configurado.'
            );
        }

        $html =
            $this->renderizarTemplate(
                $templatePath,
                $acceso
            );

        $directorio =
            $this->obtenerDirectorioSalida(
                $acceso
            );

        if (
            !is_dir($directorio)
            && !mkdir(
                $directorio,
                0775,
                true
            )
            && !is_dir($directorio)
        ) {
            throw new RuntimeException(
                'No se pudo crear el directorio de PDFs.'
            );
        }

        $nombreArchivo =
            $acceso['codigo']
            . '.pdf';

        $rutaAbsoluta =
            $directorio
            . '/'
            . $nombreArchivo;

        try {
            $mpdf =
                new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A5',
                    'margin_left' => 0,
                    'margin_right' => 0,
                    'margin_top' => 0,
                    'margin_bottom' => 0,
                ]);

            $mpdf->WriteHTML(
                $html
            );

            $mpdf->Output(
                $rutaAbsoluta,
                Destination::FILE
            );
        } catch (MpdfException $exception) {
            throw new RuntimeException(
                'No se pudo generar el PDF del acceso.',
                0,
                $exception
            );
        }

        $rutaRelativa =
            $this->obtenerRutaRelativa(
                $rutaAbsoluta
            );

        $this->accesos
            ->marcarPdfGenerado(
                $accesoId,
                $rutaRelativa
            );

        return $rutaRelativa;
    }

    private function renderizarTemplate(
        string $templatePath,
        array $acceso
    ): string {
        ob_start();

        try {
            require $templatePath;

            $html =
                ob_get_clean();

            if (!is_string($html)) {
                throw new RuntimeException(
                    'No se pudo renderizar la plantilla PDF.'
                );
            }

            return $html;
        } catch (\Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }
    }

    private function obtenerDirectorioSalida(
        array $acceso
    ): string {
        $fechaEvento =
            new DateTimeImmutable(
                (string) $acceso[
                    'fecha_inicio'
                ],
                new DateTimeZone(
                    'America/Argentina/Buenos_Aires'
                )
            );

        $anio =
            $fechaEvento->format('Y');

        $prefijo =
            preg_replace(
                '/[^A-Za-z0-9_-]/',
                '',
                (string) $acceso[
                    'codigo_prefijo'
                ]
            );

        return dirname(__DIR__, 2)
            . '/storage/pdfs/'
            . $anio
            . '/'
            . $prefijo;
    }

    private function obtenerRutaRelativa(
        string $rutaAbsoluta
    ): string {
        $root =
            dirname(__DIR__, 2)
            . '/';

        if (
            !str_starts_with(
                $rutaAbsoluta,
                $root
            )
        ) {
            throw new RuntimeException(
                'La ruta generada del PDF no pertenece al proyecto.'
            );
        }

        return substr(
            $rutaAbsoluta,
            strlen($root)
        );
    }
}