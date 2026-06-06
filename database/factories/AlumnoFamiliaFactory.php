<?php

namespace Database\Factories;

use App\Models\AlumnoFamilia;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlumnoFamilia>
 */
class AlumnoFamiliaFactory extends Factory
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
            'parentesco' => fake()->randomElement(['Padre', 'Madre', 'Tutor']),
        ];
    }
}
