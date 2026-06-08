<?php

namespace App\Actions\Ciclos;

use App\Models\Grupo;

class ClonarGrupos
{
    /**
     * Clone all groups from a source cycle to a target cycle.
     * Groups are cloned without a docente assigned.
     *
     * @return int Number of groups cloned.
     */
    public function execute(int $sourceCicloId, int $targetCicloId): int
    {
        $sourceGrupos = Grupo::where('ciclo_escolar_id', $sourceCicloId)->get();

        foreach ($sourceGrupos as $grupo) {
            Grupo::create([
                'ciclo_escolar_id' => $targetCicloId,
                'grado_id' => $grupo->grado_id,
                'nombre' => $grupo->nombre,
                'docente_id' => null,
            ]);
        }

        return $sourceGrupos->count();
    }
}
