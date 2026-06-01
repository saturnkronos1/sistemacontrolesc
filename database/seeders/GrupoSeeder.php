<?php

namespace Database\Seeders;

use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GrupoSeeder extends Seeder
{
    public function run(): void
    {
        $ciclo = CicloEscolar::where('activo', true)->first();

        if (! $ciclo) {
            return;
        }

        // Crear algunos docentes de prueba si no existen
        $docentes = [];
        $nombresDocentes = [
            ['name' => 'María García', 'email' => 'maria@sistema.test'],
            ['name' => 'Juan Pérez', 'email' => 'juan@sistema.test'],
            ['name' => 'Laura Martínez', 'email' => 'laura@sistema.test'],
        ];

        foreach ($nombresDocentes as $data) {
            $docente = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            if (! $docente->hasRole('Docente')) {
                $docente->assignRole('Docente');
            }

            $docentes[] = $docente;
        }

        $grados = Grado::all();

        foreach ($grados as $i => $grado) {
            Grupo::firstOrCreate(
                [
                    'ciclo_escolar_id' => $ciclo->id,
                    'grado_id' => $grado->id,
                    'nombre' => 'A',
                ],
                [
                    'docente_id' => $docentes[$i % count($docentes)]->id,
                ]
            );
        }
    }
}
