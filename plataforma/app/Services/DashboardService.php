<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DashboardRepository;
use DomainException;

final class DashboardService
{
    public function __construct(
        private readonly DashboardRepository $dashboard
    ) {
    }

    public function obtener(
        int $eventoId
    ): array {
        if ($eventoId < 1) {
            throw new DomainException(
                'El evento solicitado no es válido.'
            );
        }

        $resumen =
            $this->dashboard
                ->obtenerResumenEvento(
                    $eventoId
                );

        if ($resumen === []) {
            throw new DomainException(
                'No se encontró el evento.'
            );
        }

        $cupo =
            (int) $resumen['cupo_total'];

        $ingresos =
            (int) $resumen['ingresos'];

        $porcentajeIngreso =
            $cupo > 0
                ? round(
                    ($ingresos / $cupo) * 100,
                    1
                )
                : 0;

        return [
            'evento' => [
                'id' =>
                    (int) $resumen['id'],

                'nombre' =>
                    (string) $resumen['nombre'],

                'codigo' =>
                    (string) $resumen[
                        'codigo_prefijo'
                    ],

                'cupo_total' =>
                    $cupo,
            ],

            'resumen' => [
                'accesos_emitidos' =>
                    (int) $resumen[
                        'accesos_emitidos'
                    ],

                'ingresos' =>
                    $ingresos,

                'porcentaje_ingreso' =>
                    $porcentajeIngreso,

                'ordenes_pagadas' =>
                    (int) $resumen[
                        'ordenes_pagadas'
                    ],

                'ordenes_pendientes' =>
                    (int) $resumen[
                        'ordenes_pendientes'
                    ],

                'ingresos_ultimos_10_min' =>
                    $this->dashboard
                        ->obtenerIngresosUltimosMinutos(
                            $eventoId,
                            10
                        ),
            ],

            'tipos_acceso' =>
                $this->dashboard
                    ->obtenerIngresosPorTipo(
                        $eventoId
                    ),

            'ultimo_ingreso' =>
                $this->dashboard
                    ->obtenerUltimoIngreso(
                        $eventoId
                    ),
        ];
    }
}