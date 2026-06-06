<?php

namespace Database\Seeders;

use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Database\Seeder;

class GrupoSeeder extends Seeder
{
    public function run(): void
    {
        $ciclo = CicloEscolar::where('activo', true)->first();

        if (! $ciclo) {
            return;
        }

        $docentes = User::role('Docente')->orderBy('id')->get();

        if ($docentes->isEmpty()) {
            return;
        }

        $grados = Grado::orderBy('id')->get();

        foreach ($grados as $i => $grado) {
            Grupo::firstOrCreate(
                [
                    'ciclo_escolar_id' => $ciclo->id,
                    'grado_id' => $grado->id,
                    'nombre' => 'A',
                ],
                [
                    'docente_id' => $docentes[$i % $docentes->count()]->id,
                ]
            );
        }
    }
}
