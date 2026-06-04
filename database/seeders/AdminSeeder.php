<?php

namespace Database\Seeders;

use App\Actions\Teams\CreateTeam;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@sistema.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('Superadmin');

        // Crear equipo personal si el usuario no tiene ninguno
        if ($admin->teams()->doesntExist()) {
            app(CreateTeam::class)->handle(
                $admin,
                "{$admin->name}'s Team",
                isPersonal: true,
            );
        }
    }
}
