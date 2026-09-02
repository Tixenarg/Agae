<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CupoRepository;

class CupoService
{
    public function __construct(
        private CupoRepository $cupoRepository
    ) {
    }

    public function obtenerDisponibilidad(array $evento): int
    {
        $eventoId = (int) $evento['id'];
        $cupoTotal = (int) $evento['cupo_total'];

        $accesosConfirmados = $this->cupoRepository
            ->contarAccesosConfirmados($eventoId);

        $reservasVigentes = $this->cupoRepository
            ->contarReservasVigentes($eventoId);

        $disponibilidad = $cupoTotal
            - $accesosConfirmados
            - $reservasVigentes;

        return max(0, $disponibilidad);
    }
}