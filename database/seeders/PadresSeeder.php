<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\AlumnoFamilia;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Database\Seeder;

class PadresSeeder extends Seeder
{
    /** @var array<string, int> */
    private array $usedParentesco = [];

    public function run(): void
    {
        $alumnos = Alumno::all();

        if ($alumnos->isEmpty()) {
            return;
        }

        $parentescos = ['Padre', 'Madre', 'Tutor'];

        foreach ($alumnos as $alumno) {
            // 30% chance: reuse an existing tutor for this grupo (simulates siblings).
            $existing = AlumnoFamilia::whereHas('alumno', fn ($q) => $q->where('grupo_id', $alumno->grupo_id))
                ->inRandomOrder()
                ->first();

            if ($existing && fake()->boolean(30)) {
                // Reuse the same tutor for a sibling
                AlumnoFamilia::create([
                    'alumno_id' => $alumno->id,
                    'persona_id' => $existing->persona_id,
                    'parentesco' => $existing->parentesco,
                ]);

                continue;
            }

            $persona = Persona::factory()->create();

            AlumnoFamilia::create([
                'alumno_id' => $alumno->id,
                'persona_id' => $persona->id,
                'parentesco' => $parentescos[array_rand($parentescos)],
            ]);
        }

        // Link the Tutor user to the first available tutor Persona
        $tutorUser = User::role('Tutor')->first();
        $firstTutor = AlumnoFamilia::first();

        if ($tutorUser && $firstTutor) {
            $tutorUser->persona_id = $firstTutor->persona_id;
            $tutorUser->save();
        }
    }
}
