<?php

namespace Database\Factories;

use App\Models\CicloEscolar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CicloEscolar>
 */
class CicloEscolarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2024, 2027);

        $nextYear = $year + 1;

        return [
            'nombre' => "{$year}-{$nextYear}",
            'fecha_inicio' => "{$year}-08-15",
            'fecha_fin' => "{$nextYear}-07-15",
            'activo' => false,
        ];
    }

    /** Indicate that the ciclo is active. */
    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => true,
        ]);
    }
}
