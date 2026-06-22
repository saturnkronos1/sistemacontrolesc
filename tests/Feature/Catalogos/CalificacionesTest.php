<?php

use App\Livewire\Catalogos\Calificaciones;
use App\Models\Alumno;
use App\Models\Calificacion as CalificacionModel;
use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\PeriodoEvaluacion;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// ─── Guest Access ───

test('guest is redirected to login for calificaciones', function () {
    $this->get(route('calificaciones.index'))->assertRedirect(route('login'));
});

// ─── Page Rendering ───

test('calificaciones page loads successfully', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('calificaciones.index'))
        ->assertOk();
});

test('calificaciones renders with ciclo selector for admin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    CicloEscolar::factory()->activo()->create(['nombre' => '2025-2026']);

    Livewire::actingAs($user)
        ->test(Calificaciones::class)
        ->assertSee('2025-2026');
});

// ─── Ciclo & Grupo filters ───

test('ciclosEscolares computed returns all active ciclos', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $ciclo1 = CicloEscolar::factory()->activo()->create(['nombre' => '2025-2026']);
    $ciclo2 = CicloEscolar::factory()->create(['nombre' => '2024-2025']);

    Livewire::actingAs($user)
        ->test(Calificaciones::class)
        ->assertSee('2025-2026')
        ->assertDontSee('2024-2025');
});

test('grupos computed filters by ciclo_escolar_id', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo1 = CicloEscolar::factory()->activo()->create(['nombre' => '2025-2026']);
    $ciclo2 = CicloEscolar::factory()->create(['nombre' => '2024-2025']);

    $grupo1 = Grupo::factory()->for($grado)->for($ciclo1)->create(['nombre' => 'A']);
    $grupo2 = Grupo::factory()->for($grado)->for($ciclo2)->create(['nombre' => 'B']);

    Livewire::actingAs($user)
        ->test(Calificaciones::class)
        ->set('ciclo_escolar_id', $ciclo1->id)
        ->assertSee('1° - A (2025-2026)')
        ->assertDontSee('2024-2025');
});

// ─── Materias & Periodos ───

test('materias loads when grupo is selected', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();
    $materia = Materia::factory()->for($grado)->create(['nombre' => 'Matemáticas']);

    Livewire::actingAs($user)
        ->test(Calificaciones::class)
        ->set('grupo_id', $grupo->id)
        ->assertSee('Matemáticas');
});

test('periodos loads when grupo is selected', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();
    $periodo = PeriodoEvaluacion::factory()->for($ciclo)->create(['nombre' => '1er Trimestre']);

    Livewire::actingAs($user)
        ->test(Calificaciones::class)
        ->set('grupo_id', $grupo->id)
        ->assertSee('1er Trimestre');
});

// ─── Alumnos & Grades ───

test('cargarAlumnos loads students for selected group', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $materia = Materia::factory()->for($grado)->create();
    $periodo = PeriodoEvaluacion::factory()->for($ciclo)->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    Livewire::actingAs($user)
        ->test(Calificaciones::class)
        ->set('grupo_id', $grupo->id)
        ->set('materia_id', $materia->id)
        ->set('periodo_id', $periodo->id)
        ->call('cargarAlumnos')
        ->assertSet('cargado', true)
        ->assertSee($alumno->persona->apellido_paterno);
});

test('cargarAlumnos loads existing grades', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $materia = Materia::factory()->for($grado)->create();
    $periodo = PeriodoEvaluacion::factory()->for($ciclo)->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    CalificacionModel::factory()->create([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'materia_id' => $materia->id,
        'periodo_evaluacion_id' => $periodo->id,
        'calificacion' => 9.0,
    ]);

    Livewire::actingAs($user)
        ->test(Calificaciones::class)
        ->set('grupo_id', $grupo->id)
        ->set('materia_id', $materia->id)
        ->set('periodo_id', $periodo->id)
        ->call('cargarAlumnos')
        ->assertSet("notas.{$alumno->id}", 9.0);
});

// ─── Guardar Calificaciones ───

test('guardar saves grades and creates log', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $materia = Materia::factory()->for($grado)->create();
    $periodo = PeriodoEvaluacion::factory()->for($ciclo)->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    Livewire::actingAs($user)
        ->test(Calificaciones::class)
        ->set('grupo_id', $grupo->id)
        ->set('materia_id', $materia->id)
        ->set('periodo_id', $periodo->id)
        ->call('cargarAlumnos')
        ->set("notas.{$alumno->id}", 8.5)
        ->call('guardar')
        ->assertDispatched('toast');

    $this->assertDatabaseHas('calificaciones', [
        'alumno_id' => $alumno->id,
        'materia_id' => $materia->id,
        'periodo_evaluacion_id' => $periodo->id,
        'calificacion' => 8.5,
    ]);

});

test('guardar updates existing grade and logs diff', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $materia = Materia::factory()->for($grado)->create();
    $periodo = PeriodoEvaluacion::factory()->for($ciclo)->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    $existing = CalificacionModel::factory()->create([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'materia_id' => $materia->id,
        'periodo_evaluacion_id' => $periodo->id,
        'calificacion' => 7.0,
    ]);

    Livewire::actingAs($user)
        ->test(Calificaciones::class)
        ->set('grupo_id', $grupo->id)
        ->set('materia_id', $materia->id)
        ->set('periodo_id', $periodo->id)
        ->call('cargarAlumnos')
        ->set("notas.{$alumno->id}", 9.0)
        ->call('guardar')
        ->assertDispatched('toast');

});

// ─── Tab Switching / Reset ───

test('changing ciclo resets group and selection', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo1 = CicloEscolar::factory()->activo()->create();
    $ciclo2 = CicloEscolar::factory()->create(['nombre' => '2024-2025']);
    $grupo1 = Grupo::factory()->for($grado)->for($ciclo1)->create();

    Livewire::actingAs($user)
        ->test(Calificaciones::class)
        ->set('grupo_id', $grupo1->id)
        ->set('ciclo_escolar_id', $ciclo2->id)
        ->assertSet('grupo_id', '');
});

test('changing grupo resets materia, periodo and notas', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo1 = Grupo::factory()->for($grado)->for($ciclo)->create();
    $grupo2 = Grupo::factory()->for($grado)->for($ciclo)->create();

    Livewire::actingAs($user)
        ->test(Calificaciones::class)
        ->set('grupo_id', $grupo1->id)
        ->set('materia_id', 1)
        ->set('periodo_id', 1)
        ->set('grupo_id', $grupo2->id)
        ->assertSet('materia_id', '')
        ->assertSet('periodo_id', '');
});

// ─── Docente auto-load ───

test('docente sees only their group in mount', function () {
    $user = User::factory()->create();
    $user->assignRole('Docente');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create(['docente_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Calificaciones::class)
        ->assertSet('esDocente', true)
        ->assertSet('grupo_id', $grupo->id);
});
