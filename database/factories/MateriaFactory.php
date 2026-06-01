<?php

namespace Database\Factories;

use App\Models\Grado;
use App\Models\Materia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Materia>
 */
class MateriaFactory extends Factory
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
            'nombre' => fake()->word().' '.fake()->word(),
            'clave_materia' => strtoupper(fake()->bothify('???###')),
        ];
    }
}
