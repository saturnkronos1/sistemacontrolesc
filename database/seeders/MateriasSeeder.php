<?php

namespace Database\Seeders;

use App\Models\Grado;
use App\Models\Materia;
use Illuminate\Database\Seeder;

class MateriasSeeder extends Seeder
{
    /**
     * Materias base del plan de estudios de primaria en México.
     * Se asignan a cada grado con su clave única.
     */
    public function run(): void
    {
        $grados = Grado::all();

        $materiasBase = [
            ['clave' => 'ESP', 'nombre' => 'Español'],
            ['clave' => 'MAT', 'nombre' => 'Matemáticas'],
            ['clave' => 'CN',  'nombre' => 'Ciencias Naturales'],
            ['clave' => 'HIS', 'nombre' => 'Historia'],
            ['clave' => 'GEO', 'nombre' => 'Geografía'],
            ['clave' => 'FCE', 'nombre' => 'Formación Cívica y Ética'],
            ['clave' => 'ART', 'nombre' => 'Artes'],
            ['clave' => 'EF',  'nombre' => 'Educación Física'],
        ];

        foreach ($grados as $grado) {
            $numeroGrado = (int) filter_var($grado->nombre, FILTER_SANITIZE_NUMBER_INT);

            foreach ($materiasBase as $materia) {
                // 1° y 2° no tienen Ciencias Naturales, Historia ni Geografía
                // (tienen Conocimiento del Medio en su lugar)
                if ($numeroGrado <= 2 && in_array($materia['clave'], ['CN', 'HIS', 'GEO'])) {
                    continue;
                }

                $clave = "{$materia['clave']}{$numeroGrado}";

                Materia::firstOrCreate(
                    ['clave_materia' => $clave],
                    [
                        'grado_id' => $grado->id,
                        'nombre' => $materia['nombre'],
                        'clave_materia' => $clave,
                    ]
                );
            }

            // Conocimiento del Medio solo para 1° y 2°
            if ($numeroGrado <= 2) {
                $clave = "CM{$numeroGrado}";
                Materia::firstOrCreate(
                    ['clave_materia' => $clave],
                    [
                        'grado_id' => $grado->id,
                        'nombre' => 'Conocimiento del Medio',
                        'clave_materia' => $clave,
                    ]
                );
            }
        }
    }
}
