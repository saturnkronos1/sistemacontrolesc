<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Catálogos base
            RolePermissionSeeder::class,
            GradoSeeder::class,
            CicloEscolarSeeder::class,
            MateriasSeeder::class,
            PeriodoEvaluacionSeeder::class,

            // 2. Usuarios
            AdminSeeder::class,
            UsersSeeder::class,

            // 3. Grupos (dependen de grados + docentes)
            GrupoSeeder::class,

            // 4. Alumnos (dependen de grupos)
            AlumnoSeeder::class,

            // 5. Familias (dependen de alumnos)
            PadresSeeder::class,

            // 6. Calificaciones (dependen de alumnos + materias + periodos)
            CalificacionesSeeder::class,

            // 7. Asistencias (dependen de alumnos)
            AsistenciasSeeder::class,
        ]);
    }
}
