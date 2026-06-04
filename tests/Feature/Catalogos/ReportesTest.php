<?php

use App\Livewire\Catalogos\Reportes;
use App\Models\Alumno;
use App\Models\AlumnoFamilia;
use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\PeriodoEvaluacion;
use App\Models\Persona;
use App\Models\User;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// ─── Guest Access ───

test('guest is redirected to login for reportes', function () {
    $this->get(route('reportes.index'))->assertRedirect(route('login'));
});

// ─── Page Rendering ───

test('reportes page loads successfully for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('reportes.index'))
        ->assertOk();
});

test('reportes page loads successfully for director', function () {
    $user = User::factory()->create();
    $user->assignRole('Director');

    $this->actingAs($user)
        ->get(route('reportes.index'))
        ->assertOk();
});

test('reportes page loads successfully for subdirector', function () {
    $user = User::factory()->create();
    $user->assignRole('Subdirector');

    $this->actingAs($user)
        ->get(route('reportes.index'))
        ->assertOk();
});

test('reportes page shows tabs for all report types', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('reportes.index'))
        ->assertOk()
        ->assertSee('Concentrado')
        ->assertSee('Kardex')
        ->assertSee('Inasistencias')
        ->assertSee('Alumnos por Tutor');
});

// ─── Concentrado ───

test('concentrado loads students for a group', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $materia = Materia::factory()->for($grado)->create(['nombre' => 'Matemáticas']);
    $periodo = PeriodoEvaluacion::factory()->for($ciclo)->create(['orden' => 1, 'nombre' => '1er Trimestre']);
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    Calificacion::factory()->create([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'materia_id' => $materia->id,
        'periodo_evaluacion_id' => $periodo->id,
        'calificacion' => 8.5,
    ]);

    Livewire::actingAs($user)
        ->test(Reportes::class)
        ->set('reporte', 'concentrado')
        ->set('grupo_id', $grupo->id)
        ->call('cargar')
        ->assertSet('cargado', true)
        ->assertSee($alumno->persona->apellido_paterno);
});

test('concentrado shows grades for each student', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $materia = Materia::factory()->for($grado)->create(['nombre' => 'Matemáticas']);
    $periodo = PeriodoEvaluacion::factory()->for($ciclo)->create(['orden' => 1, 'nombre' => '1er Trimestre']);
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    Calificacion::factory()->create([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'materia_id' => $materia->id,
        'periodo_evaluacion_id' => $periodo->id,
        'calificacion' => 8.5,
    ]);

    Livewire::actingAs($user)
        ->test(Reportes::class)
        ->set('reporte', 'concentrado')
        ->set('grupo_id', $grupo->id)
        ->call('cargar')
        ->assertSet('cargado', true)
        ->assertSee('8.5')
        ->assertSee('Matemáticas');
});

// ─── Kardex ───

test('kardex page shows alumnos select when grupo is selected', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    Livewire::actingAs($user)
        ->test(Reportes::class)
        ->set('reporte', 'kardex')
        ->set('grupo_id', $grupo->id)
        ->assertSee($alumno->persona->apellido_paterno);
});

test('kardex loads student grades across ciclos', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $materia = Materia::factory()->for($grado)->create(['nombre' => 'Matemáticas']);
    $periodo = PeriodoEvaluacion::factory()->for($ciclo)->create(['orden' => 1]);
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    Calificacion::factory()->create([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'materia_id' => $materia->id,
        'periodo_evaluacion_id' => $periodo->id,
        'calificacion' => 9.0,
    ]);

    Livewire::actingAs($user)
        ->test(Reportes::class)
        ->set('reporte', 'kardex')
        ->set('grupo_id', $grupo->id)
        ->set('alumno_id', $alumno->id)
        ->call('cargar')
        ->assertSet('cargado', true)
        ->assertSee('9.0')
        ->assertSee('Matemáticas');
});

// ─── Inasistencias ───

test('inasistencias loads attendance data for a group', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    Asistencia::factory()->create([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'fecha' => '2026-01-15',
        'estatus' => 'asistio',
    ]);

    Asistencia::factory()->create([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'fecha' => '2026-01-16',
        'estatus' => 'falta',
    ]);

    Livewire::actingAs($user)
        ->test(Reportes::class)
        ->set('reporte', 'inasistencias')
        ->set('grupo_id', $grupo->id)
        ->call('cargar')
        ->assertSet('cargado', true)
        ->assertSee($alumno->persona->apellido_paterno)
        ->assertSee('1'); // 1 asistio, 1 falta
});

test('inasistencias filters by date range', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    // Create attendance outside date range
    Asistencia::factory()->create([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'fecha' => '2025-01-10',
        'estatus' => 'asistio',
    ]);

    // Create attendance inside date range
    Asistencia::factory()->create([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'fecha' => '2026-02-15',
        'estatus' => 'falta',
    ]);

    Livewire::actingAs($user)
        ->test(Reportes::class)
        ->set('reporte', 'inasistencias')
        ->set('grupo_id', $grupo->id)
        ->set('fecha_desde', '2026-01-01')
        ->set('fecha_hasta', '2026-12-31')
        ->call('cargar')
        ->assertSet('cargado', true);

    // Only 1 record should be counted within the date range
    $component = Livewire::actingAs($user)
        ->test(Reportes::class)
        ->set('reporte', 'inasistencias')
        ->set('grupo_id', $grupo->id)
        ->set('fecha_desde', '2026-01-01')
        ->set('fecha_hasta', '2026-12-31')
        ->call('cargar');

    $component->assertSet('inasistenciasData.0.total', 1);
});

// ─── Alumnos por Tutor ───

test('alumnos-por-tutor page loads successfully', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('reportes.index'))
        ->assertOk();
});

test('alumnos-por-tutor loads tutor data', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $tutor = Persona::factory()->create([
        'nombre' => 'María',
        'apellido_paterno' => 'López',
        'telefono' => '555-1234',
    ]);

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    AlumnoFamilia::factory()->create([
        'alumno_id' => $alumno->id,
        'persona_id' => $tutor->id,
        'parentesco' => 'Madre',
    ]);

    Livewire::actingAs($user)
        ->test(Reportes::class)
        ->set('reporte', 'alumnos-por-tutor')
        ->call('cargar')
        ->assertSet('cargado', true)
        ->assertSee('López')
        ->assertSee('Madre')
        ->assertSee('1'); // 1 child
});

test('alumnos-por-tutor filters by tutor name', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $tutor1 = Persona::factory()->create([
        'nombre' => 'María',
        'apellido_paterno' => 'López',
    ]);

    $tutor2 = Persona::factory()->create([
        'nombre' => 'Juan',
        'apellido_paterno' => 'Pérez',
    ]);

    $alumno1 = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    $alumno2 = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    AlumnoFamilia::factory()->create([
        'alumno_id' => $alumno1->id,
        'persona_id' => $tutor1->id,
        'parentesco' => 'Madre',
    ]);

    AlumnoFamilia::factory()->create([
        'alumno_id' => $alumno2->id,
        'persona_id' => $tutor2->id,
        'parentesco' => 'Padre',
    ]);

    Livewire::actingAs($user)
        ->test(Reportes::class)
        ->set('reporte', 'alumnos-por-tutor')
        ->set('search', 'López')
        ->call('cargar')
        ->assertSet('cargado', true)
        ->assertSee('López')
        ->assertDontSee('Pérez');
});

// ─── Tab Switching ───

test('reportes resets data when switching tabs', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    Livewire::actingAs($user)
        ->test(Reportes::class)
        ->set('reporte', 'concentrado')
        ->set('grupo_id', $grupo->id)
        ->call('cargar')
        ->assertSet('cargado', true)
        ->set('reporte', 'inasistencias')
        ->assertSet('cargado', false);
});
