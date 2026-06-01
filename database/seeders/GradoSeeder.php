<?php

namespace Database\Seeders;

use App\Models\Grado;
use Illuminate\Database\Seeder;

class GradoSeeder extends Seeder
{
    public function run(): void
    {
        $grados = [
            ['nombre' => '1°', 'nivel' => 'Primaria'],
            ['nombre' => '2°', 'nivel' => 'Primaria'],
            ['nombre' => '3°', 'nivel' => 'Primaria'],
            ['nombre' => '4°', 'nivel' => 'Primaria'],
            ['nombre' => '5°', 'nivel' => 'Primaria'],
            ['nombre' => '6°', 'nivel' => 'Primaria'],
        ];

        foreach ($grados as $grado) {
            Grado::firstOrCreate(
                ['nombre' => $grado['nombre']],
                $grado
            );
        }
    }
}
