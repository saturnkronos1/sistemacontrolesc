<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Persona;
use Illuminate\Database\Seeder;

class AlumnoSeeder extends Seeder
{
    public function run(): void
    {
        $grados = Grado::all();
        $seq = 1;

        // 3 alumnos por grado (18 total), asignados a grupos existentes
        foreach ($grados as $grado) {
            $grupos = Grupo::where('grado_id', $grado->id)->get();

            for ($i = 1; $i <= 3; $i++) {
                $persona = Persona::factory()->create();
                $grupo = $grupos->random();

                Alumno::factory()->create([
                    'persona_id' => $persona->id,
                    'grado_id' => $grado->id,
                    'grupo_id' => $grupo->id,
                    'ciclo_escolar_id' => $grupo->ciclo_escolar_id,
                    'matricula' => sprintf('ALU%s%04d', now()->format('y'), $seq),
                ]);

                $seq++;
            }
        }
    }
}
