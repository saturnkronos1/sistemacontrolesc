<?php

namespace Database\Seeders;

use App\Models\Grado;
use App\Models\Materia;
use Illuminate\Database\Seeder;

class CamposFormativosSeeder extends Seeder
{
    /**
     * Campos formativos de la Nueva Escuela Mexicana (NEM) para 5° grado.
     *
     * Reemplazan las materias tradicionales del plan anterior.
     */
    public function run(): void
    {
        $grado = Grado::where('nombre', '5°')->first();

        if (! $grado) {
            $this->command->warn('⚠️ No se encontró el grado 5°. Ejecutá GradoSeeder primero.');

            return;
        }

        $campos = [
            ['clave' => 'LENG5',  'nombre' => 'LENGUAJES'],
            ['clave' => 'SPC5',   'nombre' => 'SABERES Y PENSAMIENTO CIENTIFICO'],
            ['clave' => 'ENS5',   'nombre' => 'ETICA, NATURALEZA Y SOCIEDADES'],
            ['clave' => 'HCOM5',  'nombre' => 'DE LO HUMANO Y LO COMUNITARIO'],
        ];

        foreach ($campos as $campo) {
            Materia::firstOrCreate(
                ['clave_materia' => $campo['clave']],
                [
                    'grado_id' => $grado->id,
                    'nombre' => $campo['nombre'],
                    'clave_materia' => $campo['clave'],
                ]
            );
        }

        $this->command->info('✅ Campos formativos de 5° creados: '.implode(', ', array_column($campos, 'nombre')));
    }
}
