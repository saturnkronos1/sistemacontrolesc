<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\Persona;
use Illuminate\Database\Seeder;

class AlumnoSeeder extends Seeder
{
    public function run(): void
    {
        $grupos = Grupo::with('grado', 'cicloEscolar')->orderBy('grado_id')->get();

        if ($grupos->isEmpty()) {
            return;
        }

        $seq = 1;
        $anio = now()->format('y');

        foreach ($grupos as $grupo) {
            for ($i = 1; $i <= 20; $i++) {
                $persona = Persona::factory()->create();

                Alumno::create([
                    'persona_id' => $persona->id,
                    'grado_id' => $grupo->grado_id,
                    'grupo_id' => $grupo->id,
                    'ciclo_escolar_id' => $grupo->ciclo_escolar_id,
                    'matricula' => sprintf('ALU%s%04d', $anio, $seq),
                    'estatus' => 'activo',
                ]);

                $seq++;
            }
        }
    }
}
