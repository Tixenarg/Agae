<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EventoRepository;
use RuntimeException;

class EventoService
{
    public function __construct(
        private EventoRepository $eventoRepository
    ) {
    }

    public function obtenerEventoPrincipal(): array
    {
        $evento = $this->eventoRepository
            ->obtenerPorSlug('evento-6n-2026');

        if ($evento === null) {
            throw new RuntimeException(
                'No se encontró el evento principal.'
            );
        }

        $tipoAcceso = $this->eventoRepository
            ->obtenerAccesoWeb((int) $evento['id']);

        if ($tipoAcceso === null) {
            throw new RuntimeException(
                'No hay un acceso habilitado para venta web.'
            );
        }

        return [
            'evento' => $evento,
            'tipo_acceso' => $tipoAcceso,
        ];
    }
}