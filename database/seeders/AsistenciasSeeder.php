<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsistenciasSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = Alumno::with('grupo')->where('estatus', 'activo')->get();

        if ($alumnos->isEmpty()) {
            return;
        }

        $estatusPool = [
            'asistio' => 70,
            'falta' => 15,
            'retardo' => 10,
            'justificado' => 5,
        ];

        // Generar 20 días hábiles hacia atrás
        $fechas = [];
        $date = now()->subWeekday(1); // arranca desde ayer

        while (count($fechas) < 20) {
            if ($date->isWeekday()) {
                $fechas[] = $date->copy()->format('Y-m-d');
            }
            // subDay() retorna una nueva instancia si Carbon es inmutable
            $date = $date->subDay();
        }

        sort($fechas);
        $now = now();

        DB::transaction(function () use ($alumnos, $fechas, $estatusPool, $now) {
            $inserts = [];
            $justificanteInsert = [];

            foreach ($alumnos as $alumno) {
                foreach ($fechas as $fecha) {
                    $estatus = $this->weightedRandom($estatusPool);
                    $inserts[] = [
                        'alumno_id' => $alumno->id,
                        'grupo_id' => $alumno->grupo_id,
                        'fecha' => $fecha,
                        'estatus' => $estatus,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if ($estatus === 'justificado') {
                        $justificanteInsert[] = [
                            'alumno_id' => $alumno->id,
                            'fecha' => $fecha,
                            'motivo' => fake()->randomElement([
                                'Cita médica',
                                'Enfermedad',
                                'Trámite familiar',
                                'Motivo personal',
                                'Fallecimiento de familiar',
                            ]),
                        ];
                    }
                }
            }

            // Insert masivo ignorando duplicados por si se re-ejecuta
            foreach (array_chunk($inserts, 500) as $chunk) {
                DB::table('asistencias')->insertOrIgnore($chunk);
            }

            if (empty($justificanteInsert)) {
                return;
            }

            // Vincular justificantes a las asistencias insertadas
            $motivosByKey = [];
            foreach ($justificanteInsert as $j) {
                $motivosByKey[$j['alumno_id'].'-'.$j['fecha']] = $j['motivo'];
            }

            $asistenciasJustificadas = DB::table('asistencias')
                ->where('estatus', 'justificado')
                ->whereNotIn('id', function ($q) {
                    $q->select('asistencia_id')->from('justificantes');
                })
                ->get();

            $justificantes = [];
            foreach ($asistenciasJustificadas as $a) {
                $key = $a->alumno_id.'-'.$a->fecha;
                $justificantes[] = [
                    'asistencia_id' => $a->id,
                    'motivo' => $motivosByKey[$key] ?? 'Motivo personal',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($justificantes, 500) as $chunk) {
                DB::table('justificantes')->insertOrIgnore($chunk);
            }
        });
    }

    private function weightedRandom(array $weights): string
    {
        $rand = fake()->numberBetween(1, 100);
        $cumulative = 0;

        foreach ($weights as $estatus => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $estatus;
            }
        }

        return 'asistio';
    }
}
