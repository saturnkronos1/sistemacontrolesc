<?php

use App\Livewire\Catalogos\PasarLista;
use App\Models\Alumno;
use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// ─── Guest Access ───

test('guest is redirected to login for pasar-lista', function () {
    $this->get(route('pasar-lista.index'))->assertRedirect(route('login'));
});

// ─── Page Rendering ───

test('pasar-lista page loads successfully', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('pasar-lista.index'))
        ->assertOk();
});

test('pasar-lista renders ciclo selector', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $ciclo = CicloEscolar::factory()->activo()->create(['nombre' => '2025-2026']);

    Livewire::actingAs($user)
        ->test(PasarLista::class)
        ->assertSee('2025-2026');
});

// ─── Docente auto-load ───

test('docente sees their group auto-loaded', function () {
    $user = User::factory()->create();
    $user->assignRole('Docente');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create(['docente_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(PasarLista::class)
        ->assertSet('esDocente', true)
        ->assertSet('grupo_id', $grupo->id)
        ->assertSet('fecha', now()->format('Y-m-d'));
});

// ─── Grupos filter ───

test('grupos computed filters by ciclo', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo1 = CicloEscolar::factory()->activo()->create(['nombre' => '2025-2026']);
    $ciclo2 = CicloEscolar::factory()->create(['nombre' => '2024-2025']);

    $grupo1 = Grupo::factory()->for($grado)->for($ciclo1)->create(['nombre' => 'Grupo A']);
    $grupo2 = Grupo::factory()->for($grado)->for($ciclo2)->create(['nombre' => 'Grupo B']);

    Livewire::actingAs($user)
        ->test(PasarLista::class)
        ->set('ciclo_escolar_id', $ciclo1->id)
        ->assertSee('Grupo A')
        ->assertDontSee('Grupo B');
});

// ─── Cargar Alumnos ───

test('cargarAlumnos loads students with today attendance', function () {
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
        ->test(PasarLista::class)
        ->set('grupo_id', $grupo->id)
        ->call('cargarAlumnos')
        ->assertSet('cargado', true)
        ->assertSet("estatusList.{$alumno->id}", 'asistio');
});

test('cargarAlumnos loads existing attendance', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    DB::table('asistencias')->insert([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'fecha' => now()->format('Y-m-d'),
        'estatus' => 'falta',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(PasarLista::class)
        ->set('grupo_id', $grupo->id)
        ->call('cargarAlumnos')
        ->assertSet("estatusList.{$alumno->id}", 'falta');
});

// ─── Cambiar estatus ───

test('cambiarEstatus cycles through statuses', function () {
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
        ->test(PasarLista::class)
        ->set('grupo_id', $grupo->id)
        ->call('cargarAlumnos')
        ->call('cambiarEstatus', $alumno->id)
        ->assertSet("estatusList.{$alumno->id}", 'falta')
        ->call('cambiarEstatus', $alumno->id)
        ->assertSet("estatusList.{$alumno->id}", 'retardo')
        ->call('cambiarEstatus', $alumno->id)
        ->assertSet("estatusList.{$alumno->id}", 'pendiente')
        ->call('cambiarEstatus', $alumno->id)
        ->assertSet("estatusList.{$alumno->id}", 'asistio');
});

// ─── Guardar ───

test('guardar saves attendance and shows success', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    $today = now()->format('Y-m-d');

    Livewire::actingAs($user)
        ->test(PasarLista::class)
        ->set('grupo_id', $grupo->id)
        ->call('cargarAlumnos')
        ->call('cambiarEstatus', $alumno->id) // falta
        ->call('guardar')
        ->assertDispatched('toast');

    $this->assertTrue(
        DB::table('asistencias')
            ->where('alumno_id', $alumno->id)
            ->where('grupo_id', $grupo->id)
            ->where('estatus', 'falta')
            ->whereDate('fecha', $today)
            ->exists()
    );
});

// ─── Reset ───

test('changing ciclo resets carga', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo1 = CicloEscolar::factory()->activo()->create();
    $ciclo2 = CicloEscolar::factory()->create(['nombre' => '2024-2025']);
    $grupo = Grupo::factory()->for($grado)->for($ciclo1)->create();

    Livewire::actingAs($user)
        ->test(PasarLista::class)
        ->set('grupo_id', $grupo->id)
        ->call('cargarAlumnos')
        ->assertSet('cargado', true)
        ->set('ciclo_escolar_id', $ciclo2->id)
        ->assertSet('cargado', false)
        ->assertSet('grupo_id', '');
});
