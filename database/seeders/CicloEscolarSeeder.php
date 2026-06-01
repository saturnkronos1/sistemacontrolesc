<?php

namespace Database\Seeders;

use App\Models\CicloEscolar;
use Illuminate\Database\Seeder;

class CicloEscolarSeeder extends Seeder
{
    public function run(): void
    {
        $ciclos = [
            ['nombre' => '2024-2025', 'fecha_inicio' => '2024-08-15', 'fecha_fin' => '2025-07-15', 'activo' => false],
            ['nombre' => '2025-2026', 'fecha_inicio' => '2025-08-15', 'fecha_fin' => '2026-07-15', 'activo' => true],
        ];

        foreach ($ciclos as $ciclo) {
            CicloEscolar::firstOrCreate(
                ['nombre' => $ciclo['nombre']],
                $ciclo
            );
        }
    }
}
