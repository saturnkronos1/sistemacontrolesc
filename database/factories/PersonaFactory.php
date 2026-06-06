<?php

namespace Database\Factories;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Persona>
 */
class PersonaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido_paterno' => fake()->lastName(),
            'apellido_materno' => fake()->lastName(),
            'curp' => fake()->unique()->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}'),
            'telefono' => fake()->regexify('[0-9]{10}'),
            'telefono_2' => fake()->optional()->regexify('[0-9]{10}'),
            'email' => fake()->optional()->safeEmail(),
            'fecha_nacimiento' => fake()->optional()->date('Y-m-d', '2005-01-01'),
            'domicilio' => fake()->optional()->streetAddress(),
        ];
    }

    /** Designar que esta persona es un tutor con cuenta de usuario */
    public function tutor(): static
    {
        return $this->afterCreating(function (Persona $persona) {
            $user = User::factory()->create([
                'name' => "{$persona->nombre} {$persona->apellido_paterno}",
                'email' => $persona->email ?? fake()->unique()->safeEmail(),
                'persona_id' => $persona->id,
            ]);
            $user->assignRole('Tutor');
        });
    }
}
