<?php

declare(strict_types=1);

namespace App\Services;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use RuntimeException;

final class QrCodeService
{
    public function generar(
        string $codigo
    ): string {
        $codigo = trim($codigo);

        if ($codigo === '') {
            throw new RuntimeException(
                'El código QR no puede estar vacío.'
            );
        }

        $directorio =
            dirname(__DIR__, 2)
            . '/storage/qr';

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
                'No se pudo crear el directorio de códigos QR.'
            );
        }

        $nombreSeguro =
            preg_replace(
                '/[^A-Za-z0-9_-]/',
                '',
                $codigo
            );

        if (
            !is_string($nombreSeguro)
            || $nombreSeguro === ''
        ) {
            throw new RuntimeException(
                'No se pudo generar un nombre válido para el QR.'
            );
        }

        $rutaAbsoluta =
            $directorio
            . '/'
            . $nombreSeguro
            . '.png';

        /*
         * Si ya existe, reutilizamos exactamente
         * el mismo QR.
         */
        if (is_file($rutaAbsoluta)) {
            return $rutaAbsoluta;
        }

        $qrCode =
            QrCode::create($codigo)
                ->setEncoding(
                    new Encoding('UTF-8')
                )
                ->setErrorCorrectionLevel(
                    ErrorCorrectionLevel::High
                )
                ->setSize(900)
                ->setMargin(30)
                ->setRoundBlockSizeMode(
                    RoundBlockSizeMode::Margin
                );

        $writer =
            new PngWriter();

        $resultado =
            $writer->write(
                $qrCode
            );

        $resultado->saveToFile(
            $rutaAbsoluta
        );

        if (!is_file($rutaAbsoluta)) {
            throw new RuntimeException(
                'El código QR no pudo ser guardado.'
            );
        }

        return $rutaAbsoluta;
    }
}