<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EmailRepository;
use App\Repositories\OrdenRepository;
use RuntimeException;

final class EmailQueueService
{
    public function __construct(
        private readonly EmailRepository $emails,
        private readonly OrdenRepository $ordenes
    ) {
    }

    public function encolarCompraConfirmada(
        int $ordenId
    ): void {
        if (
            $this->emails
                ->compraConfirmadaExiste(
                    $ordenId
                )
        ) {
            return;
        }

        $orden =
            $this->ordenes
                ->obtenerDatosParaEmail(
                    $ordenId
                );

        if ($orden === null) {
            throw new RuntimeException(
                'No se encontraron los datos de la orden para enviar el email.'
            );
        }

        $this->emails
            ->crearCompraConfirmada(
                $ordenId,
                (string) $orden[
                    'comprador_email'
                ],
                '¡Ya sos parte del Evento más grande de la Abogacía!',
                [
                    'comprador_nombre' =>
                        (string) $orden[
                            'comprador_nombre'
                        ],

                    'comprador_apellido' =>
                        (string) $orden[
                            'comprador_apellido'
                        ],

                    'cantidad_accesos' =>
                        (int) $orden[
                            'cantidad_accesos'
                        ],
                ]
            );
    }
}