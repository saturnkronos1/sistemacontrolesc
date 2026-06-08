<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Materia;
use App\Models\PeriodoEvaluacion;
use Illuminate\Database\Seeder;

class CalificacionesSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = Alumno::with('grupo')->where('estatus', 'activo')->get();

        if ($alumnos->isEmpty()) {
            return;
        }

        // Tomar solo los primeros 2 periodos para agilizar
        $periodos = PeriodoEvaluacion::orderBy('orden')->limit(2)->get();

        if ($periodos->isEmpty()) {
            return;
        }

        foreach ($alumnos as $alumno) {
            $materias = Materia::where('grado_id', $alumno->grado_id)->get();

            foreach ($materias as $materia) {
                foreach ($periodos as $periodo) {
                    Calificacion::firstOrCreate(
                        [
                            'alumno_id' => $alumno->id,
                            'grupo_id' => $alumno->grupo_id,
                            'materia_id' => $materia->id,
                            'periodo_evaluacion_id' => $periodo->id,
                        ],
                        [
                            'calificacion' => fake()->randomFloat(2, 6, 10),
                        ]
                    );
                }
            }
        }
    }
}
