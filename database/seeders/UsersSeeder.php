<?php

namespace Database\Seeders;

use App\Actions\Teams\CreateTeam;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Director
            [
                'name' => 'Director Escolar',
                'email' => 'director@sistema.test',
                'role' => 'Director',
            ],
            // Subdirector
            [
                'name' => 'Subdirector Escolar',
                'email' => 'subdirector@sistema.test',
                'role' => 'Subdirector',
            ],
            // 6 Docentes (uno por grado/grupo)
            [
                'name' => 'María García',
                'email' => 'maria@sistema.test',
                'role' => 'Docente',
                'persona' => ['nombre' => 'María', 'apellido_paterno' => 'García', 'apellido_materno' => 'López'],
            ],
            [
                'name' => 'Juan Pérez',
                'email' => 'juan@sistema.test',
                'role' => 'Docente',
                'persona' => ['nombre' => 'Juan', 'apellido_paterno' => 'Pérez', 'apellido_materno' => 'Martínez'],
            ],
            [
                'name' => 'Laura Martínez',
                'email' => 'laura@sistema.test',
                'role' => 'Docente',
                'persona' => ['nombre' => 'Laura', 'apellido_paterno' => 'Martínez', 'apellido_materno' => 'Hernández'],
            ],
            [
                'name' => 'Carlos López',
                'email' => 'carlos@sistema.test',
                'role' => 'Docente',
                'persona' => ['nombre' => 'Carlos', 'apellido_paterno' => 'López', 'apellido_materno' => 'García'],
            ],
            [
                'name' => 'Ana Rodríguez',
                'email' => 'ana@sistema.test',
                'role' => 'Docente',
                'persona' => ['nombre' => 'Ana', 'apellido_paterno' => 'Rodríguez', 'apellido_materno' => 'Pérez'],
            ],
            [
                'name' => 'Pedro Hernández',
                'email' => 'pedro@sistema.test',
                'role' => 'Docente',
                'persona' => ['nombre' => 'Pedro', 'apellido_paterno' => 'Hernández', 'apellido_materno' => null],
            ],
            // Tutor
            [
                'name' => 'Tutor de Prueba',
                'email' => 'tutor@sistema.test',
                'role' => 'Tutor',
            ],
        ];

        $teamCreator = app(CreateTeam::class);

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            // Create Persona for Docentes
            if ($data['role'] === 'Docente' && isset($data['persona']) && ! $user->persona_id) {
                $persona = Persona::create([
                    'nombre' => $data['persona']['nombre'],
                    'apellido_paterno' => $data['persona']['apellido_paterno'],
                    'apellido_materno' => $data['persona']['apellido_materno'],
                    'estatus' => 'activo',
                ]);

                $user->update([
                    'persona_id' => $persona->id,
                    'name' => $persona->nombreCompleto(),
                ]);
            }

            if (! $user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
            }

            if ($user->teams()->doesntExist()) {
                $teamCreator->handle(
                    $user,
                    "{$user->name}'s Team",
                    isPersonal: true,
                );
            }
        }
    }
}
