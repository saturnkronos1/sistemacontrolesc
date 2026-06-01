<?php

namespace Database\Factories;

use App\Models\CicloEscolar;
use App\Models\PeriodoEvaluacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeriodoEvaluacion>
 */
class PeriodoEvaluacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ciclo_escolar_id' => CicloEscolar::factory(),
            'nombre' => fake()->randomElement(['Trimestre I', 'Trimestre II', 'Trimestre III']),
            'orden' => fake()->numberBetween(1, 3),
            'fecha_inicio' => fake()->date(),
            'fecha_fin' => fake()->date(),
        ];
    }
}
