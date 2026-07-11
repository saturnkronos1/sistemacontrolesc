<?php

namespace Database\Seeders;

use App\Models\Grado;
use App\Models\Materia;
use Illuminate\Database\Seeder;

class CamposFormativos20242025Seeder extends Seeder
{
    /**
     * Campos formativos de la Nueva Escuela Mexicana (NEM) para el ciclo 2024-2025.
     *
     * Se crean los 4 campos formativos para todos los grados (1°–6°):
     *   - LENGUAJES
     *   - SABERES Y PENSAMIENTO CIENTIFICO
     *   - ETICA, NATURALEZA Y SOCIEDADES
     *   - DE LO HUMANO Y LO COMUNITARIO
     */
    public function run(): void
    {
        $grados = Grado::orderBy('id')->get();

        if ($grados->isEmpty()) {
            $this->command->warn('⚠️ No hay grados. Ejecutá GradoSeeder primero.');

            return;
        }

        $campos = [
            ['clave' => 'LENG', 'nombre' => 'LENGUAJES'],
            ['clave' => 'SPC',  'nombre' => 'SABERES Y PENSAMIENTO CIENTIFICO'],
            ['clave' => 'ENS',  'nombre' => 'ETICA, NATURALEZA Y SOCIEDADES'],
            ['clave' => 'HCOM', 'nombre' => 'DE LO HUMANO Y LO COMUNITARIO'],
        ];

        $total = 0;

        foreach ($grados as $grado) {
            $numeroGrado = (int) filter_var($grado->nombre, FILTER_SANITIZE_NUMBER_INT);

            foreach ($campos as $campo) {
                $clave = $campo['clave'].$numeroGrado;

                Materia::firstOrCreate(
                    ['clave_materia' => $clave],
                    [
                        'grado_id' => $grado->id,
                        'nombre' => $campo['nombre'],
                        'clave_materia' => $clave,
                    ]
                );

                $total++;
            }
        }

        $this->command->info("✅ Campos formativos 2024-2025 creados para {$grados->count()} grados ({$total} materias)");
    }
}
