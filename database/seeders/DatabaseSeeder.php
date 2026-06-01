<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            GradoSeeder::class,
            AdminSeeder::class,
            CicloEscolarSeeder::class,
            MateriasSeeder::class,
            PeriodoEvaluacionSeeder::class,
            GrupoSeeder::class,
            AlumnoSeeder::class,
        ]);
    }
}
