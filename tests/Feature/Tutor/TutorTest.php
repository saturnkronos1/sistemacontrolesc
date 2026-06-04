<?php

use App\Livewire\Catalogos\TutorDashboard;
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

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// ─── Guest Access ───

test('guest is redirected to login for tutor dashboard', function () {
    $this->get(route('tutor.dashboard'))->assertRedirect(route('login'));
});

// ─── Page Rendering ───

test('tutor dashboard loads successfully', function () {
    $persona = Persona::factory()->create();
    $user = User::factory()->create(['persona_id' => $persona->id]);
    $user->assignRole('Tutor');

    actingAs($user)
        ->get(route('tutor.dashboard'))
        ->assertOk();
});

test('tutor without linked persona sees no children message', function () {
    $user = User::factory()->create(['persona_id' => null]);
    $user->assignRole('Tutor');

    Livewire::actingAs($user)
        ->test(TutorDashboard::class)
        ->assertSet('vista', 'dashboard')
        ->assertCount('hijos', 0);
});

test('tutor sees their children in dashboard', function () {
    $tutor = Persona::factory()->create();
    $user = User::factory()->create(['persona_id' => $tutor->id]);
    $user->assignRole('Tutor');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create(['nombre' => 'A']);

    $alumno1 = Alumno::factory()->activo()->for($grado)->create([
        'grupo_id' => $grupo->id,
        'ciclo_escolar_id' => $ciclo->id,
    ]);

    $alumno2 = Alumno::factory()->activo()->for($grado)->create([
        'grupo_id' => $grupo->id,
        'ciclo_escolar_id' => $ciclo->id,
    ]);

    AlumnoFamilia::factory()->create([
        'alumno_id' => $alumno1->id,
        'persona_id' => $tutor->id,
        'parentesco' => 'Padre',
    ]);

    AlumnoFamilia::factory()->create([
        'alumno_id' => $alumno2->id,
        'persona_id' => $tutor->id,
        'parentesco' => 'Padre',
    ]);

    Livewire::actingAs($user)
        ->test(TutorDashboard::class)
        ->assertCount('hijos', 2)
        ->assertSee($alumno1->persona->nombre)
        ->assertSee($alumno2->persona->nombre);
});

// ─── Calificaciones ───

test('tutor can view calificaciones of a child', function () {
    $tutor = Persona::factory()->create();
    $user = User::factory()->create(['persona_id' => $tutor->id]);
    $user->assignRole('Tutor');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create(['nombre' => 'A']);

    $alumno = Alumno::factory()->activo()->for($grado)->create([
        'grupo_id' => $grupo->id,
        'ciclo_escolar_id' => $ciclo->id,
    ]);

    AlumnoFamilia::factory()->create([
        'alumno_id' => $alumno->id,
        'persona_id' => $tutor->id,
        'parentesco' => 'Padre',
    ]);

    $materia = Materia::factory()->for($grado)->create(['nombre' => 'Matemáticas']);
    $periodo = PeriodoEvaluacion::factory()->for($ciclo)->create(['nombre' => '1er Trimestre', 'orden' => 1]);

    Calificacion::factory()->create([
        'alumno_id' => $alumno->id,
        'materia_id' => $materia->id,
        'grupo_id' => $grupo->id,
        'periodo_evaluacion_id' => $periodo->id,
        'calificacion' => 8.5,
    ]);

    Livewire::actingAs($user)
        ->test(TutorDashboard::class)
        ->call('verCalificaciones', $alumno->id)
        ->assertSet('vista', 'calificaciones')
        ->assertSet('alumnoId', $alumno->id)
        ->assertCount('materias', 1)
        ->assertCount('periodos', 1);
});

test('tutor can view asistencias of a child', function () {
    $tutor = Persona::factory()->create();
    $user = User::factory()->create(['persona_id' => $tutor->id]);
    $user->assignRole('Tutor');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create(['nombre' => 'A']);

    $alumno = Alumno::factory()->activo()->for($grado)->create([
        'grupo_id' => $grupo->id,
        'ciclo_escolar_id' => $ciclo->id,
    ]);

    AlumnoFamilia::factory()->create([
        'alumno_id' => $alumno->id,
        'persona_id' => $tutor->id,
        'parentesco' => 'Madre',
    ]);

    Asistencia::factory()->create([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'fecha' => now(),
        'estatus' => 'asistio',
    ]);

    Asistencia::factory()->create([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'fecha' => now()->subDay(),
        'estatus' => 'falta',
    ]);

    Livewire::actingAs($user)
        ->test(TutorDashboard::class)
        ->call('verAsistencias', $alumno->id)
        ->assertSet('vista', 'asistencias')
        ->assertSet('alumnoId', $alumno->id)
        ->assertCount('asistencias', 2);
});

test('tutor can return to dashboard from child view', function () {
    $tutor = Persona::factory()->create();
    $user = User::factory()->create(['persona_id' => $tutor->id]);
    $user->assignRole('Tutor');

    $grado = Grado::factory()->create();
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()->activo()->for($grado)->create([
        'grupo_id' => $grupo->id,
        'ciclo_escolar_id' => $ciclo->id,
    ]);

    AlumnoFamilia::factory()->create([
        'alumno_id' => $alumno->id,
        'persona_id' => $tutor->id,
        'parentesco' => 'Padre',
    ]);

    Livewire::actingAs($user)
        ->test(TutorDashboard::class)
        ->call('verCalificaciones', $alumno->id)
        ->assertSet('vista', 'calificaciones')
        ->call('volver')
        ->assertSet('vista', 'dashboard')
        ->assertSet('alumnoId', null);
});
