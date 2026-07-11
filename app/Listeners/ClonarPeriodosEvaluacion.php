<?php

namespace App\Listeners;

use App\Events\CicloActivado;
use App\Models\PeriodoEvaluacion;

class ClonarPeriodosEvaluacion
{
    /**
     * Clone evaluation periods from the previous cycle if the new cycle has none.
     */
    public function handle(CicloActivado $event): void
    {
        if ($event->cicloAnterior === null) {
            return;
        }

        // Only clone if the new cycle has no periods yet (idempotent)
        $periodosExistentes = PeriodoEvaluacion::where('ciclo_escolar_id', $event->ciclo->id)->count();

        if ($periodosExistentes > 0) {
            return;
        }

        $periodosAnteriores = PeriodoEvaluacion::where('ciclo_escolar_id', $event->cicloAnterior->id)->get();

        foreach ($periodosAnteriores as $periodo) {
            PeriodoEvaluacion::create([
                'ciclo_escolar_id' => $event->ciclo->id,
                'nombre' => $periodo->nombre,
                'orden' => $periodo->orden,
                'fecha_inicio' => $periodo->fecha_inicio,
                'fecha_fin' => $periodo->fecha_fin,
            ]);
        }
    }
}
