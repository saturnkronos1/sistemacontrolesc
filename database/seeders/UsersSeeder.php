<?php

namespace Database\Seeders;

use App\Actions\Teams\CreateTeam;
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
            ['name' => 'María García',    'email' => 'maria@sistema.test',     'role' => 'Docente'],
            ['name' => 'Juan Pérez',      'email' => 'juan@sistema.test',      'role' => 'Docente'],
            ['name' => 'Laura Martínez',  'email' => 'laura@sistema.test',     'role' => 'Docente'],
            ['name' => 'Carlos López',    'email' => 'carlos@sistema.test',    'role' => 'Docente'],
            ['name' => 'Ana Rodríguez',   'email' => 'ana@sistema.test',       'role' => 'Docente'],
            ['name' => 'Pedro Hernández', 'email' => 'pedro@sistema.test',     'role' => 'Docente'],
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
