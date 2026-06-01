<?php

namespace Database\Seeders;

use App\Models\CicloEscolar;
use App\Models\PeriodoEvaluacion;
use Illuminate\Database\Seeder;

class PeriodoEvaluacionSeeder extends Seeder
{
    public function run(): void
    {
        $ciclo = CicloEscolar::where('activo', true)->first();

        if (! $ciclo) {
            return;
        }

        $periodos = [
            ['nombre' => 'Trimestre I',   'orden' => 1, 'fecha_inicio' => "{$ciclo->fecha_inicio->format('Y')}-08-15",  'fecha_fin' => "{$ciclo->fecha_inicio->format('Y')}-11-30"],
            ['nombre' => 'Trimestre II',  'orden' => 2, 'fecha_inicio' => "{$ciclo->fecha_inicio->format('Y')}-12-01",  'fecha_fin' => "{$ciclo->fecha_fin->format('Y')}-03-31"],
            ['nombre' => 'Trimestre III', 'orden' => 3, 'fecha_inicio' => "{$ciclo->fecha_fin->format('Y')}-04-01",   'fecha_fin' => "{$ciclo->fecha_fin->format('Y')}-07-15"],
        ];

        foreach ($periodos as $periodo) {
            PeriodoEvaluacion::firstOrCreate(
                [
                    'ciclo_escolar_id' => $ciclo->id,
                    'nombre' => $periodo['nombre'],
                ],
                $periodo + ['ciclo_escolar_id' => $ciclo->id]
            );
        }
    }
}
