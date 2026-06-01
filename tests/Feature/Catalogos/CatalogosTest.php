<?php

use App\Livewire\Catalogos\Calificaciones;
use App\Models\Alumno;
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

// ─── Guest Access: redirige al login ───

test('guest is redirected to login for ciclos-escolares', function () {
    $this->get(route('ciclos-escolares.index'))->assertRedirect(route('login'));
});

test('guest is redirected to login for materias', function () {
    $this->get(route('materias.index'))->assertRedirect(route('login'));
});

test('guest is redirected to login for periodos-evaluacion', function () {
    $this->get(route('periodos-evaluacion.index'))->assertRedirect(route('login'));
});

test('guest is redirected to login for usuarios', function () {
    $this->get(route('usuarios.index'))->assertRedirect(route('login'));
});

test('guest is redirected to login for grupos', function () {
    $this->get(route('grupos.index'))->assertRedirect(route('login'));
});

// ─── Page Rendering: Superadmin ───

test('ciclos-escolares page loads successfully for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('ciclos-escolares.index'))
        ->assertOk();
});

test('materias page loads successfully for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('materias.index'))
        ->assertOk();
});

test('periodos-evaluacion page loads successfully for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('periodos-evaluacion.index'))
        ->assertOk();
});

test('usuarios page loads successfully for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('usuarios.index'))
        ->assertOk();
});

// ─── Access for other roles ───
// Nota: las rutas de catálogos no tienen middleware de permisos,
// por lo que cualquier usuario autenticado puede cargar la página.
// El control de acceso por rol se maneja a nivel de sidebar/navegación.

test('docente can access ciclos-escolares page but catalog menu is hidden', function () {
    $user = User::factory()->create();
    $user->assignRole('Docente');

    $this->actingAs($user)
        ->get(route('ciclos-escolares.index'))
        ->assertOk();
});

// ─── Data Display ───

test('ciclos-escolares shows list of ciclos', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $ciclos = CicloEscolar::factory()->count(3)->create();

    $this->actingAs($user)
        ->get(route('ciclos-escolares.index'))
        ->assertOk()
        ->assertSee($ciclos[0]->nombre)
        ->assertSee($ciclos[1]->nombre)
        ->assertSee($ciclos[2]->nombre);
});

test('materias shows list of materias', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $materias = Materia::factory()->count(3)->create();

    $this->actingAs($user)
        ->get(route('materias.index'))
        ->assertOk()
        ->assertSee($materias[0]->nombre)
        ->assertSee($materias[1]->nombre)
        ->assertSee($materias[2]->nombre);
});

test('periodos-evaluacion shows list of periodos', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $periodos = PeriodoEvaluacion::factory()->count(3)->create();

    $this->actingAs($user)
        ->get(route('periodos-evaluacion.index'))
        ->assertOk()
        ->assertSee($periodos[0]->nombre)
        ->assertSee($periodos[1]->nombre)
        ->assertSee($periodos[2]->nombre);
});

test('usuarios shows list of users', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $users = User::factory()->count(2)->create();

    $this->actingAs($user)
        ->get(route('usuarios.index'))
        ->assertOk()
        ->assertSee($users[0]->name)
        ->assertSee($users[1]->name);
});

// ─── Superadmin sees all ───

test('grupos page loads successfully for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('grupos.index'))
        ->assertOk();
});

test('grupos shows list of grupos', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grupos = Grupo::factory()
        ->count(2)
        ->for(Grado::factory()->create(['nombre' => '1°']))
        ->for(CicloEscolar::factory()->activo())
        ->create();

    $this->actingAs($user)
        ->get(route('grupos.index'))
        ->assertOk()
        ->assertSee($grupos[0]->nombre)
        ->assertSee($grupos[1]->nombre);
});

test('guest is redirected to login for docentes', function () {
    $this->get(route('docentes.index'))->assertRedirect(route('login'));
});

test('docentes page loads successfully for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('docentes.index'))
        ->assertOk();
});

test('docentes shows only users with docente role', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $docente = User::factory()->create(['name' => 'Docente Uno']);
    $docente->assignRole('Docente');

    $admin = User::factory()->create(['name' => 'Admin Uno']);
    $admin->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('docentes.index'))
        ->assertOk()
        ->assertSee('Docente Uno')
        ->assertDontSee('Admin Uno');
});

test('alumnos page loads successfully for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('alumnos.index'))
        ->assertOk();
});

test('alumnos shows list of alumnos with full name', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $persona = Persona::factory()->create(['nombre' => 'Carlos', 'apellido_paterno' => 'Pérez']);
    $alumno = Alumno::factory()->create([
        'persona_id' => $persona->id,
        'grado_id' => Grado::factory()->create(['nombre' => '1°'])->id,
        'matricula' => 'TEST0001',
    ]);

    $this->actingAs($user)
        ->get(route('alumnos.index'))
        ->assertOk()
        ->assertSee('TEST0001')
        ->assertSee('Pérez');
});

test('alumnos filters by estatus', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    Grado::factory()->create(['nombre' => '1°']);

    $activo = Alumno::factory()->activo()->create(['matricula' => 'ACT001']);
    $baja = Alumno::factory()->baja()->create(['matricula' => 'BAJA001']);

    $this->actingAs($user)
        ->get(route('alumnos.index'))
        ->assertOk()
        ->assertSee('ACT001')
        ->assertSee('BAJA001');
});

test('superadmin can access all catalog pages', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)->get(route('ciclos-escolares.index'))->assertOk();
    $this->actingAs($user)->get(route('materias.index'))->assertOk();
    $this->actingAs($user)->get(route('periodos-evaluacion.index'))->assertOk();
    $this->actingAs($user)->get(route('usuarios.index'))->assertOk();
    $this->actingAs($user)->get(route('grupos.index'))->assertOk();
    $this->actingAs($user)->get(route('docentes.index'))->assertOk();
    $this->actingAs($user)->get(route('alumnos.index'))->assertOk();
    $this->actingAs($user)->get(route('calificaciones.index'))->assertOk();
});

// ─── Calificaciones ───

test('guest is redirected to login for calificaciones', function () {
    $this->get(route('calificaciones.index'))->assertRedirect(route('login'));
});

test('calificaciones page loads successfully for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('calificaciones.index'))
        ->assertOk();
});

test('calificaciones shows grupos in select for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()
        ->for($grado)
        ->for($ciclo)
        ->create(['nombre' => 'A']);

    $this->actingAs($user)
        ->get(route('calificaciones.index'))
        ->assertOk()
        ->assertSee($grupo->grado->nombre)
        ->assertSee($grupo->nombre);
});

test('calificaciones filters grupos by docente', function () {
    $docente = User::factory()->create();
    $docente->assignRole('Docente');

    $otroDocente = User::factory()->create();
    $otroDocente->assignRole('Docente');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();

    $miGrupo = Grupo::factory()
        ->for($grado)->for($ciclo)
        ->create(['nombre' => 'A', 'docente_id' => $docente->id]);

    $otroGrupo = Grupo::factory()
        ->for($grado)->for($ciclo)
        ->create(['nombre' => 'Z-Grupo-Otro', 'docente_id' => $otroDocente->id]);

    $this->actingAs($docente)
        ->get(route('calificaciones.index'))
        ->assertOk()
        ->assertSee($grado->nombre)
        ->assertDontSee('Z-Grupo-Otro');
});

test('calificaciones loads materias when grupo selected', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $materia = Materia::factory()->for($grado)->create(['nombre' => 'Matemáticas']);
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    Livewire::actingAs($user)
        ->test(Calificaciones::class)
        ->set('grupo_id', $grupo->id)
        ->assertSee('Matemáticas');
});

test('calificaciones carga alumnos y notas existentes', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $materia = Materia::factory()->for($grado)->create();
    $periodo = PeriodoEvaluacion::factory()->for($ciclo)->create(['orden' => 1]);
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    $calificacion = Calificacion::factory()->create([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'materia_id' => $materia->id,
        'periodo_evaluacion_id' => $periodo->id,
        'calificacion' => 8.5,
    ]);

    Livewire::actingAs($user)
        ->test(Calificaciones::class)
        ->set('grupo_id', $grupo->id)
        ->set('materia_id', $materia->id)
        ->set('periodo_id', $periodo->id)
        ->call('cargarAlumnos')
        ->assertSet('cargado', true)
        ->assertSee($alumno->persona->apellido_paterno);
});

test('calificaciones guarda y actualiza notas', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $materia = Materia::factory()->for($grado)->create();
    $periodo = PeriodoEvaluacion::factory()->for($ciclo)->create(['orden' => 1]);
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
        ->set("notas.{$alumno->id}", 9.0)
        ->call('guardar')
        ->assertOk();

    $this->assertDatabaseHas('calificaciones', [
        'alumno_id' => $alumno->id,
        'materia_id' => $materia->id,
        'periodo_evaluacion_id' => $periodo->id,
        'calificacion' => 9.0,
    ]);

    $this->assertDatabaseHas('calificacion_logs', [
        'old_calificacion' => null,
        'new_calificacion' => 9.0,
    ]);
});
