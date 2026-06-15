<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\ReinscripcionLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReinscripcionLog>
 */
class ReinscripcionLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fromGrado = Grado::factory()->create();
        $fromCiclo = CicloEscolar::factory()->create();
        $fromGrupo = Grupo::factory()->for($fromGrado)->for($fromCiclo)->create();
        $toGrado = Grado::factory()->create();
        $toCiclo = CicloEscolar::factory()->create();
        $toGrupo = Grupo::factory()->for($toGrado)->for($toCiclo)->create();

        return [
            'alumno_id' => Alumno::factory(),
            'from_grado_id' => $fromGrado->id,
            'from_grupo_id' => $fromGrupo->id,
            'from_ciclo_escolar_id' => $fromCiclo->id,
            'to_grado_id' => $toGrado->id,
            'to_grupo_id' => $toGrupo->id,
            'to_ciclo_escolar_id' => $toCiclo->id,
            'created_by' => User::factory(),
        ];
    }
}
