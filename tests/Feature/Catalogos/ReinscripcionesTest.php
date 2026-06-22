<?php

use App\Livewire\Catalogos\Reinscripciones;
use App\Models\Alumno;
use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// ─── Guest Access ───

test('guest is redirected to login for reinscripciones', function () {
    $this->get(route('reinscripciones.index'))->assertRedirect(route('login'));
});

// ─── Page Rendering ───

test('reinscripciones page loads successfully', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('reinscripciones.index'))
        ->assertOk();
});

test('reinscripciones shows ciclo selector', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $ciclo = CicloEscolar::factory()->activo()->create(['nombre' => '2025-2026']);

    Livewire::actingAs($user)
        ->test(Reinscripciones::class)
        ->assertSee('2025-2026');
});

// ─── Mount ───

test('mount sets ciclo activo and detects next ciclo', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $ciclo1 = CicloEscolar::factory()->activo()->create([
        'nombre' => '2025-2026',
        'fecha_inicio' => '2025-08-15',
        'fecha_fin' => '2026-07-15',
    ]);

    $ciclo2 = CicloEscolar::factory()->create([
        'nombre' => '2026-2027',
        'fecha_inicio' => '2026-08-15',
        'fecha_fin' => '2027-07-15',
    ]);

    Livewire::actingAs($user)
        ->test(Reinscripciones::class)
        ->assertSet('ciclo_escolar_id', $ciclo1->id)
        ->assertSet('target_ciclo_escolar_id', $ciclo2->id);
});

test('mount handles no next ciclo gracefully', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $ciclo = CicloEscolar::factory()->activo()->create(['nombre' => '2025-2026']);

    Livewire::actingAs($user)
        ->test(Reinscripciones::class)
        ->assertSet('ciclo_escolar_id', $ciclo->id)
        ->assertSet('target_ciclo_escolar_id', '');
});

// ─── Source & Target Grupos ───

test('sourceGrupos filters by ciclo_escolar_id', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo1 = CicloEscolar::factory()->activo()->create(['nombre' => '2025-2026']);
    $ciclo2 = CicloEscolar::factory()->create(['nombre' => '2024-2025']);

    $grupo1 = Grupo::factory()->for($grado)->for($ciclo1)->create(['nombre' => 'Grupo A']);
    $grupo2 = Grupo::factory()->for($grado)->for($ciclo2)->create(['nombre' => 'Grupo B']);

    Livewire::actingAs($user)
        ->test(Reinscripciones::class)
        ->set('ciclo_escolar_id', $ciclo1->id)
        ->assertSee('Grupo A')
        ->assertDontSee('Grupo B');
});

test('targetGrupos filters by target_ciclo_escolar_id', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo1 = CicloEscolar::factory()->activo()->create(['nombre' => '2025-2026']);
    $ciclo2 = CicloEscolar::factory()->create(['nombre' => '2026-2027']);

    $grupo1 = Grupo::factory()->for($grado)->for($ciclo1)->create(['nombre' => 'Grupo A']);
    $grupo2 = Grupo::factory()->for($grado)->for($ciclo2)->create(['nombre' => 'Grupo B']);

    // Se necesita un alumno y cargarAlumnos para que el bloque de destino se renderice
    $alumno = Alumno::factory()->activo()->for($grado)->create([
        'grupo_id' => $grupo1->id,
        'ciclo_escolar_id' => $ciclo1->id,
    ]);

    Livewire::actingAs($user)
        ->test(Reinscripciones::class)
        ->set('ciclo_escolar_id', $ciclo1->id)
        ->set('target_ciclo_escolar_id', $ciclo2->id)
        ->set('source_grupo_id', $grupo1->id)
        ->call('cargarAlumnos')
        ->assertSee('Grupo A')
        ->assertSee('Grupo B');
});

// ─── Cargar Alumnos ───

test('cargarAlumnos loads students from source group', function () {
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
        ->test(Reinscripciones::class)
        ->set('source_grupo_id', $grupo->id)
        ->call('cargarAlumnos')
        ->assertSet('cargado', true)
        ->assertSet('selected', [])
        ->assertSee($alumno->persona->apellido_paterno);
});

// ─── Toggle All ───

test('toggleAll selects and deselects all students', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    Alumno::factory()
        ->activo()
        ->count(3)
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    Livewire::actingAs($user)
        ->test(Reinscripciones::class)
        ->set('source_grupo_id', $grupo->id)
        ->call('cargarAlumnos')
        ->call('toggleAll')
        ->assertCount('selected', 3);
});

// ─── Reinscribir ───

test('reinscribir moves student to target group', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado1 = Grado::factory()->create(['nombre' => '1°']);
    $grado2 = Grado::factory()->create(['nombre' => '2°']);
    $ciclo1 = CicloEscolar::factory()->activo()->create(['fecha_inicio' => '2025-08-15']);
    $ciclo2 = CicloEscolar::factory()->create(['fecha_inicio' => '2026-08-15']);

    $sourceGrupo = Grupo::factory()->for($grado1)->for($ciclo1)->create(['nombre' => 'A']);
    $targetGrupo = Grupo::factory()->for($grado2)->for($ciclo2)->create(['nombre' => 'A']);

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado1)
        ->create([
            'grupo_id' => $sourceGrupo->id,
            'ciclo_escolar_id' => $ciclo1->id,
        ]);

    Livewire::actingAs($user)
        ->test(Reinscripciones::class)
        ->set('source_grupo_id', $sourceGrupo->id)
        ->call('cargarAlumnos')
        ->call('toggleAll')
        ->set('target_grupo_id', $targetGrupo->id)
        ->call('reinscribir')
        ->assertDispatched('toast');

    $this->assertDatabaseHas('reinscripcion_logs', [
        'alumno_id' => $alumno->id,
        'from_grado_id' => $grado1->id,
        'from_grupo_id' => $sourceGrupo->id,
        'from_ciclo_escolar_id' => $ciclo1->id,
        'to_grado_id' => $grado2->id,
        'to_grupo_id' => $targetGrupo->id,
        'to_ciclo_escolar_id' => $ciclo2->id,
        'created_by' => $user->id,
    ]);

    $alumno->refresh();
    expect($alumno->grado_id)->toBe($grado2->id);
    expect($alumno->grupo_id)->toBe($targetGrupo->id);
    expect($alumno->ciclo_escolar_id)->toBe($ciclo2->id);
});

// ─── Changing ciclo resets───

test('changing ciclo resets source_grupo and carga', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo1 = CicloEscolar::factory()->activo()->create(['fecha_inicio' => '2025-08-15']);
    $ciclo2 = CicloEscolar::factory()->create(['fecha_inicio' => '2026-08-15']);
    $grupo = Grupo::factory()->for($grado)->for($ciclo1)->create();

    Livewire::actingAs($user)
        ->test(Reinscripciones::class)
        ->set('source_grupo_id', $grupo->id)
        ->call('cargarAlumnos')
        ->set('ciclo_escolar_id', $ciclo2->id)
        ->assertSet('source_grupo_id', '')
        ->assertSet('target_grupo_id', '')
        ->assertSet('cargado', false);
});
