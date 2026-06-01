<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alumno>
 */
class AlumnoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'persona_id' => Persona::factory(),
            'grado_id' => Grado::factory(),
            'grupo_id' => null,
            'ciclo_escolar_id' => null,
            'matricula' => fake()->unique()->regexify('[0-9]{8}'),
            'estatus' => 'activo',
        ];
    }

    /** Assign a random grupo matching the alumno's grado. */
    public function conGrupo(): static
    {
        return $this->state(function (array $attributes) {
            $gradoId = $attributes['grado_id'] ?? Grado::factory()->create()->id;
            $grupo = Grupo::where('grado_id', $gradoId)->inRandomOrder()->first()
                ?? Grupo::factory()->create(['grado_id' => $gradoId]);

            return [
                'grupo_id' => $grupo->id,
                'ciclo_escolar_id' => $grupo->ciclo_escolar_id,
            ];
        });
    }

    /** Alumno activo (default). */
    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estatus' => 'activo',
        ]);
    }

    /** Alumno dado de baja. */
    public function baja(): static
    {
        return $this->state(fn (array $attributes) => [
            'estatus' => 'baja',
        ]);
    }

    /** Alumno egresado. */
    public function egresado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estatus' => 'egresado',
        ]);
    }
}
