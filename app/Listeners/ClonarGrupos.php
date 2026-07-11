<?php

namespace App\Listeners;

use App\Events\CicloActivado;
use App\Models\Grupo;

class ClonarGrupos
{
    /**
     * Clone groups from the previous cycle, cleaning names to section-only.
     *
     * "1A" → "A", "2B" → "B". If no numeric prefix is found, the name is kept as-is.
     */
    public function handle(CicloActivado $event): void
    {
        if ($event->cicloAnterior === null) {
            return;
        }

        // Only clone if the new cycle has no groups yet (idempotent)
        $gruposExistentes = Grupo::where('ciclo_escolar_id', $event->ciclo->id)->count();

        if ($gruposExistentes > 0) {
            return;
        }

        $gruposAnteriores = Grupo::where('ciclo_escolar_id', $event->cicloAnterior->id)->get();

        foreach ($gruposAnteriores as $grupo) {
            $seccion = preg_replace('/^\d+/', '', $grupo->nombre);
            $nombre = $seccion !== '' ? $seccion : $grupo->nombre;

            Grupo::create([
                'ciclo_escolar_id' => $event->ciclo->id,
                'grado_id' => $grupo->grado_id,
                'nombre' => $nombre,
                'docente_id' => null,
            ]);
        }
    }
}
