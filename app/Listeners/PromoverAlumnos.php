<?php

namespace App\Listeners;

use App\Events\CicloActivado;
use App\Models\Alumno;
use App\Models\AlumnoCiclo;
use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Models\PromocionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromoverAlumnos
{
    /**
     * Promote students and record history.
     *
     * - 6th grade → egresado (stays in old cycle)
     * - 1st–5th → advance one grade, assigned via round-robin to new groups
     * - New 1st grade groups remain empty
     * - Every cambio is logged in promocion_logs + alumno_ciclos
     */
    public function handle(CicloActivado $event): void
    {
        if ($event->cicloAnterior === null) {
            return;
        }

        DB::transaction(function () use ($event) {
            $cicloAnterior = $event->cicloAnterior;
            $cicloNuevo = $event->ciclo;

            // ── 1. Process 6th grade → egresado ──
            $this->egresarSextoGrado($cicloAnterior, $cicloNuevo);

            // ── 2. Promote 5→6, 4→5, 3→4, 2→3, 1→2 ──
            for ($gradoOrigen = 1; $gradoOrigen <= 5; $gradoOrigen++) {
                $this->promoverGrado($cicloAnterior, $cicloNuevo, $gradoOrigen);
            }
        });
    }

    private function egresarSextoGrado(CicloEscolar $cicloAnterior, CicloEscolar $cicloNuevo): void
    {
        $sextoGradoId = 6;

        $alumnosSexto = Alumno::where('ciclo_escolar_id', $cicloAnterior->id)
            ->where('grado_id', $sextoGradoId)
            ->where('estatus', 'activo')
            ->get();

        foreach ($alumnosSexto as $alumno) {
            // Save snapshot in alumno_ciclos before changing estatus
            AlumnoCiclo::create([
                'alumno_id' => $alumno->id,
                'ciclo_escolar_id' => $alumno->ciclo_escolar_id,
                'grado_id' => $alumno->grado_id,
                'grupo_id' => $alumno->grupo_id,
                'estatus' => 'egresado',
            ]);

            $alumno->update(['estatus' => 'egresado']);
        }
    }

    private function promoverGrado(
        CicloEscolar $cicloAnterior,
        CicloEscolar $cicloNuevo,
        int $gradoOrigen,
    ): void {
        $gradoDestino = $gradoOrigen + 1;

        $alumnos = Alumno::where('ciclo_escolar_id', $cicloAnterior->id)
            ->where('grado_id', $gradoOrigen)
            ->where('estatus', 'activo')
            ->get();

        if ($alumnos->isEmpty()) {
            return;
        }

        $gruposDestino = Grupo::where('ciclo_escolar_id', $cicloNuevo->id)
            ->where('grado_id', $gradoDestino)
            ->get();

        if ($gruposDestino->isEmpty()) {
            // Log warning — director will need to assign manually via Reinscripciones
            Log::warning(
                "Promoción grado {$gradoOrigen}→{$gradoDestino}: sin grupos destino en ciclo {$cicloNuevo->nombre}"
            );

            return;
        }

        $indice = 0;
        $totalGrupos = $gruposDestino->count();

        foreach ($alumnos as $alumno) {
            $grupoDestino = $gruposDestino->get($indice % $totalGrupos);
            $grupoOrigenId = $alumno->grupo_id; // capture BEFORE update

            // 1. Save historical snapshot in alumno_ciclos
            AlumnoCiclo::create([
                'alumno_id' => $alumno->id,
                'ciclo_escolar_id' => $cicloAnterior->id,
                'grado_id' => $gradoOrigen,
                'grupo_id' => $grupoOrigenId,
                'estatus' => 'activo',
            ]);

            // 2. Update current record
            $alumno->update([
                'ciclo_escolar_id' => $cicloNuevo->id,
                'grado_id' => $gradoDestino,
                'grupo_id' => $grupoDestino->id,
            ]);

            // 3. Log promotion event
            PromocionLog::create([
                'alumno_id' => $alumno->id,
                'ciclo_origen_id' => $cicloAnterior->id,
                'ciclo_destino_id' => $cicloNuevo->id,
                'grado_origen_id' => $gradoOrigen,
                'grado_destino_id' => $gradoDestino,
                'grupo_origen_id' => $grupoOrigenId,
                'grupo_destino_id' => $grupoDestino->id,
                'tipo' => 'promocion_automatica',
            ]);

            $indice++;
        }
    }
}
