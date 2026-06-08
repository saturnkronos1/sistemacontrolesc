<?php

namespace App\Actions\Ciclos;

use App\Models\PeriodoEvaluacion;

class ClonarPeriodosEvaluacion
{
    /**
     * Clone all evaluation periods from a source cycle to a target cycle.
     *
     * @return int Number of periods cloned.
     */
    public function execute(int $sourceCicloId, int $targetCicloId): int
    {
        $periodos = PeriodoEvaluacion::where('ciclo_escolar_id', $sourceCicloId)->get();

        foreach ($periodos as $periodo) {
            PeriodoEvaluacion::create([
                'ciclo_escolar_id' => $targetCicloId,
                'nombre' => $periodo->nombre,
                'orden' => $periodo->orden,
                'fecha_inicio' => $periodo->fecha_inicio,
                'fecha_fin' => $periodo->fecha_fin,
            ]);
        }

        return $periodos->count();
    }
}
