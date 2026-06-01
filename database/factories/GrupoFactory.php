<?php

namespace Database\Factories;

use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grupo>
 */
class GrupoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'grado_id' => Grado::factory(),
            'ciclo_escolar_id' => CicloEscolar::factory(),
            'docente_id' => null,
            'nombre' => fake()->randomElement(['A', 'B']),
        ];
    }

    /** Assign a docente from the Docente role. */
    public function conDocente(): static
    {
        return $this->state(fn (array $attributes) => [
            'docente_id' => User::role('Docente')->inRandomOrder()->first()?->id
                ?? User::factory()->create()->assignRole('Docente')->id,
        ]);
    }

    /** Explicitly leave docente unassigned. */
    public function sinDocente(): static
    {
        return $this->state(fn (array $attributes) => [
            'docente_id' => null,
        ]);
    }
}
