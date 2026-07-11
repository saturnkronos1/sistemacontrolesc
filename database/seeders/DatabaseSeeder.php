<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tablas dependientes antes de poblar (en orden inverso)
        // para arrancar siempre con datos frescos y predecibles.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::disableForeignKeyConstraints();

        DB::table('users')->truncate();
        DB::table('personas')->truncate();
        DB::table('justificantes')->truncate();
        DB::table('asistencias')->truncate();
        DB::table('calificacion_logs')->truncate();
        DB::table('calificaciones')->truncate();
        DB::table('alumno_familia')->truncate();
        DB::table('alumnos')->truncate();
        DB::table('grupos')->truncate();
        DB::table('periodos_evaluacion')->truncate();
        DB::table('materias')->truncate();
        DB::table('ciclos_escolares')->truncate();
        DB::table('grados')->truncate();

        Schema::enableForeignKeyConstraints();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->call([
            // 1. Catálogos base
            RolePermissionSeeder::class,
            GradoSeeder::class,
            CicloEscolarSeeder::class,
            CamposFormativos20242025Seeder::class,
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

            // 8. Datos históricos del ciclo 2024-2025
            Ciclo20242025Seeder::class,
        ]);
    }
}
