<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\PeriodoEvaluacion;
use App\Models\Persona;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Ciclo20242025Seeder extends Seeder
{
    /** @var list<array{nombre: string, apellido_paterno: string, apellido_materno: ?string}> */
    private array $nombresAlumnos = [];

    /** @var array<int, list<int>> IDs de materias (4 campos formativos NEM) por grado_id */
    private array $materiasPorGrado = [];

    /** @var list<string> Días hábiles del ciclo 2024-2025 */
    private array $fechasAsistencia = [];

    public function run(): void
    {
        $ciclo = CicloEscolar::find(1); // 2024-2025

        if (! $ciclo) {
            $this->command?->error('❌ Ciclo 2024-2025 no encontrado. Ejecutá CicloEscolarSeeder primero.');

            return;
        }

        // ── Asegurar campos formativos NEM en todos los grados ──
        $this->call(CamposFormativos20242025Seeder::class);

        // ── Pre-carga de referencias ──
        $this->cargarReferencias($ciclo);

        // ── Datos del ciclo 2024-2025 ──
        $this->crearPeriodos($ciclo);
        $this->crearGrupos($ciclo);
        $this->crearNombresAlumnos();
        $this->crearAlumnos($ciclo);
        $this->crearCalificaciones();
        $this->crearAsistencias();

        $this->command?->info('✅ Ciclo 2024-2025 completado con datos de demostración (MAYÚSCULAS)');
    }

    /**
     * Pre-carga referencias: materias (solo 4 campos NEM) por grado y fechas de asistencia.
     */
    private function cargarReferencias(CicloEscolar $ciclo): void
    {
        $grados = Grado::orderBy('id')->get();

        foreach ($grados as $grado) {
            // Solo los 4 campos formativos NEM por grado
            $materias = Materia::where('grado_id', $grado->id)
                ->where(function ($q) {
                    $q->where('clave_materia', 'like', 'LENG%')
                        ->orWhere('clave_materia', 'like', 'SPC%')
                        ->orWhere('clave_materia', 'like', 'ENS%')
                        ->orWhere('clave_materia', 'like', 'HCOM%');
                })
                ->pluck('id')
                ->toArray();

            $this->materiasPorGrado[$grado->id] = $materias;
        }

        $this->generarFechasAsistencia($ciclo);
    }

    /**
     * Crea 3 periodos de evaluación para 2024-2025 si no existen.
     */
    private function crearPeriodos(CicloEscolar $ciclo): void
    {
        $periodos = [
            ['nombre' => 'TRIMESTRE I',  'orden' => 1, 'fecha_inicio' => '2024-08-15', 'fecha_fin' => '2024-11-30'],
            ['nombre' => 'TRIMESTRE II', 'orden' => 2, 'fecha_inicio' => '2024-12-01', 'fecha_fin' => '2025-03-31'],
            ['nombre' => 'TRIMESTRE III', 'orden' => 3, 'fecha_inicio' => '2025-04-01', 'fecha_fin' => '2025-07-15'],
        ];

        $count = 0;
        foreach ($periodos as $data) {
            PeriodoEvaluacion::firstOrCreate(
                [
                    'ciclo_escolar_id' => $ciclo->id,
                    'nombre' => $data['nombre'],
                ],
                $data + ['ciclo_escolar_id' => $ciclo->id]
            );
            $count++;
        }

        $this->command?->info("✅ {$count} periodos creados para 2024-2025");
    }

    /**
     * Crea 6 grupos (uno por grado, nombre 'A') para 2024-2025.
     * Reusa los 6 docentes existentes.
     */
    private function crearGrupos(CicloEscolar $ciclo): void
    {
        $grados = Grado::orderBy('id')->get();

        // Los docentes ya existen (ids 4-9 del UsersSeeder/DatabaseSeeder)
        $docentes = DB::table('users')
            ->whereIn('id', range(4, 9))
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        if (count($docentes) < 6) {
            // Fallback: tomar cualquier Docente
            $docentes = DB::table('model_has_roles')
                ->where('role_id', function ($q) {
                    $q->select('id')->from('roles')->where('name', 'Docente');
                })
                ->pluck('model_id')
                ->toArray();
        }

        $count = 0;
        foreach ($grados as $i => $grado) {
            $existing = Grupo::where('ciclo_escolar_id', $ciclo->id)
                ->where('grado_id', $grado->id)
                ->exists();

            if ($existing) {
                continue;
            }

            Grupo::create([
                'ciclo_escolar_id' => $ciclo->id,
                'grado_id' => $grado->id,
                'nombre' => 'A',
                'docente_id' => $docentes[$i % count($docentes)] ?? null,
            ]);
            $count++;
        }

        $this->command?->info("✅ {$count} grupos creados para 2024-2025 (1°A a 6°A)");
    }

    /**
     * Genera ~132 nombres de alumnos (22 por grupo) en MAYÚSCULAS.
     */
    private function crearNombresAlumnos(): void
    {
        $nombres = [
            'ANGEL', 'DANIEL', 'DAVID', 'DIEGO', 'EMMANUEL', 'FERNANDO',
            'FRANCISCO', 'GABRIEL', 'HECTOR', 'IVAN', 'JAVIER', 'JESUS',
            'JOSE', 'JUAN', 'LUIS', 'MANUEL', 'MIGUEL', 'PABLO',
            'SANTIAGO', 'SEBASTIAN', 'VICTOR', 'ALEJANDRO',
            'ANDREA', 'CAMILA', 'CAROLINA', 'DANIELA', 'DIANA', 'FERNANDA',
            'GABRIELA', 'GUADALUPE', 'ISABEL', 'JESSICA', 'KAREN',
            'LETICIA', 'LORENA', 'MARIA', 'MARTHA', 'PATRICIA',
            'ROSA', 'SOFIA', 'VALERIA', 'XIMENA',
        ];

        $apellidos = [
            'GARCIA', 'LOPEZ', 'MARTINEZ', 'HERNANDEZ', 'GONZALEZ', 'PEREZ',
            'RODRIGUEZ', 'SANCHEZ', 'RAMIREZ', 'CRUZ', 'FLORES', 'MORALES',
            'VAZQUEZ', 'JIMENEZ', 'REYES', 'TORRES', 'DIAZ', 'GUTIERREZ',
            'MENDOZA', 'AGUILAR', 'CASTILLO', 'ORTIZ', 'MORENO', 'ROMERO',
            'ALVAREZ', 'CHAVEZ', 'RIVERA', 'RAMOS', 'MOLINA', 'DELGADO',
            'CORTES', 'ROJAS', 'SALAZAR', 'NAVARRO', 'LUNA', 'SEPULVEDA',
        ];

        $alumnosPorGrupo = 22;
        $totalAlumnos = $alumnosPorGrupo * 6; // 132
        $this->nombresAlumnos = [];

        shuffle($apellidos);
        $apellidoIdx = 0;

        for ($i = 0; $i < $totalAlumnos; $i++) {
            $paterno = $apellidos[$apellidoIdx % count($apellidos)];
            $apellidoIdx++;

            // Cada ~4 alumnos cambiar de apellido (simula familias de ~4)
            if ($i > 0 && $i % 4 === 0) {
                $apellidoIdx++;
            }

            $materno = $apellidos[array_rand($apellidos)];
            if ($materno === $paterno) {
                $materno = $apellidos[array_rand($apellidos)];
            }

            $this->nombresAlumnos[] = [
                'nombre' => $nombres[array_rand($nombres)],
                'apellido_paterno' => $paterno,
                'apellido_materno' => $materno,
            ];
        }
    }

    /**
     * Crea ~132 alumnos (22 por grupo × 6 grupos) con sus personas en MAYÚSCULAS,
     * asignados al ciclo 2024-2025.
     */
    private function crearAlumnos(CicloEscolar $ciclo): void
    {
        $grupos = Grupo::where('ciclo_escolar_id', $ciclo->id)
            ->with('grado')
            ->orderBy('grado_id')
            ->get();

        if ($grupos->isEmpty()) {
            $this->command?->error('❌ No hay grupos para 2024-2025. Ejecutá crearGrupos primero.');

            return;
        }

        $now = now();

        // ── 1. Insertar personas masivamente ──
        $personasData = [];
        $curps = [];

        foreach ($this->nombresAlumnos as $i => $n) {
            $curp = sprintf('C24A%05d', $i + 1);
            $curps[] = $curp;

            $personasData[] = [
                'nombre' => $n['nombre'],
                'apellido_paterno' => $n['apellido_paterno'],
                'apellido_materno' => $n['apellido_materno'],
                'curp' => $curp,
                'fecha_nacimiento' => fake()->dateTimeBetween('-14 years', '-6 years')->format('Y-m-d'),
                'telefono' => fake()->optional(0.6)->numerify('55########'),
                'email' => 'alumno.'.($i + 1).'@sistema.test',
                'estatus' => 'activo',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('personas')->insert($personasData);

        // ── 2. Recuperar IDs de personas recién creadas ──
        $personasMap = Persona::whereIn('curp', $curps)
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        if (count($personasMap) !== count($this->nombresAlumnos)) {
            $this->command?->warn('⚠️ No se encontraron todas las personas ('.count($personasMap).'/'.count($this->nombresAlumnos).'). Se omiten alumnos.');

            return;
        }

        // ── 3. Insertar alumnos masivamente ──
        $alumnosData = [];
        $seq = 1;

        foreach ($grupos as $grupo) {
            for ($i = 1; $i <= 22; $i++) {
                $personaIdx = $seq - 1;
                $matricula = sprintf('ALU24%04d', $seq);

                $alumnosData[] = [
                    'persona_id' => $personasMap[$personaIdx],
                    'grado_id' => $grupo->grado_id,
                    'grupo_id' => $grupo->id,
                    'ciclo_escolar_id' => $ciclo->id,
                    'matricula' => $matricula,
                    'estatus' => 'activo',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $seq++;
            }
        }

        foreach (array_chunk($alumnosData, 100) as $chunk) {
            DB::table('alumnos')->insert($chunk);
        }

        $totalAlumnos = $seq - 1;
        $this->command?->info("✅ {$totalAlumnos} alumnos creados para 2024-2025 (22 por grupo)");
    }

    /**
     * Crea calificaciones solo para los 4 campos formativos NEM
     * en los 3 periodos de evaluación.
     */
    private function crearCalificaciones(): void
    {
        $alumnos = Alumno::where('ciclo_escolar_id', 1)
            ->where('estatus', 'activo')
            ->get();

        $periodos = PeriodoEvaluacion::where('ciclo_escolar_id', 1)
            ->orderBy('orden')
            ->get();

        if ($alumnos->isEmpty() || $periodos->isEmpty()) {
            $this->command?->warn('⚠️ No hay alumnos o periodos para calificaciones.');

            return;
        }

        $now = now();
        $calificacionesData = [];
        $total = 0;

        foreach ($alumnos as $alumno) {
            $materiaIds = $this->materiasPorGrado[$alumno->grado_id] ?? [];

            if (empty($materiaIds)) {
                $this->command?->warn("⚠️ Grado {$alumno->grado_id} no tiene campos formativos NEM cargados.");

                continue;
            }

            foreach ($materiaIds as $materiaId) {
                foreach ($periodos as $periodo) {
                    $calificacionesData[] = [
                        'alumno_id' => $alumno->id,
                        'grupo_id' => $alumno->grupo_id,
                        'materia_id' => $materiaId,
                        'periodo_evaluacion_id' => $periodo->id,
                        'calificacion' => fake()->randomFloat(2, 6, 10),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $total++;

                    if (count($calificacionesData) >= 500) {
                        DB::table('calificaciones')->insert($calificacionesData);
                        $calificacionesData = [];
                    }
                }
            }
        }

        if (! empty($calificacionesData)) {
            DB::table('calificaciones')->insert($calificacionesData);
        }

        $this->command?->info("✅ {$total} calificaciones creadas (4 campos formativos × 3 periodos)");
    }

    /**
     * Crea asistencias para todos los alumnos de 2024-2025
     * (desde 2024-08-15 hasta 2025-07-15, solo días hábiles).
     */
    private function crearAsistencias(): void
    {
        if (empty($this->fechasAsistencia)) {
            $this->command?->warn('⚠️ No hay fechas de asistencia generadas.');

            return;
        }

        $alumnosList = Alumno::where('ciclo_escolar_id', 1)
            ->where('estatus', 'activo')
            ->get(['id', 'grupo_id']);

        if ($alumnosList->isEmpty()) {
            return;
        }

        $now = now();
        $pesos = ['asistio' => 80, 'falta' => 8, 'retardo' => 7, 'justificado' => 5];
        $totalAlumnos = $alumnosList->count();
        $totalFechas = count($this->fechasAsistencia);

        $this->command?->info("⏳ Generando {$totalAlumnos} alumnos × {$totalFechas} días = ".($totalAlumnos * $totalFechas).' asistencias...');

        $asistenciasData = [];
        $totalAsistencias = 0;

        foreach ($alumnosList as $alumno) {
            foreach ($this->fechasAsistencia as $fecha) {
                $estatus = $this->pesoAleatorio($pesos);

                $asistenciasData[] = [
                    'alumno_id' => $alumno->id,
                    'grupo_id' => $alumno->grupo_id,
                    'fecha' => $fecha,
                    'estatus' => $estatus,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $totalAsistencias++;

                if (count($asistenciasData) >= 1000) {
                    DB::table('asistencias')->insert($asistenciasData);
                    $asistenciasData = [];
                }
            }
        }

        if (! empty($asistenciasData)) {
            DB::table('asistencias')->insert($asistenciasData);
        }

        $this->command?->info("✅ {$totalAsistencias} asistencias creadas (Ago 2024 - Jul 2025)");
    }

    /**
     * Elimina acentos y caracteres no-ASCII para CURP.
     */
    private function sanitizeCurp(string $value): string
    {
        $from = ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü'];
        $to = ['A', 'E', 'I', 'O', 'U', 'U'];

        return str_replace($from, $to, $value);
    }

    /**
     * Genera todas las fechas hábiles (lunes a viernes)
     * desde el 15 de agosto de 2024 hasta el 15 de julio de 2025.
     */
    private function generarFechasAsistencia(CicloEscolar $ciclo): void
    {
        $start = Carbon::parse($ciclo->fecha_inicio->format('Y-m-d'));
        $end = Carbon::parse($ciclo->fecha_fin->format('Y-m-d'));

        $this->fechasAsistencia = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isWeekday()) {
                $this->fechasAsistencia[] = $date->format('Y-m-d');
            }
        }
    }

    /**
     * Selecciona un valor con probabilidades ponderadas.
     *
     * @param  array<string, int>  $weights
     */
    private function pesoAleatorio(array $weights): string
    {
        $rand = fake()->numberBetween(1, 100);
        $cumulative = 0;

        foreach ($weights as $estatus => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $estatus;
            }
        }

        return 'asistio';
    }
}
