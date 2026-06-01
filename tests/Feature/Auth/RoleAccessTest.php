<?php

use App\Models\User;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// ─── Acceso básico por rol ───

test('guest is redirected to login', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('catalogos.index'))->assertRedirect(route('login'));
});

test('authenticated user can access dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

// ─── Sidebar: items visibles según permisos ───

test('sidebar shows dashboard for any authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSee('Dashboard');
});

test('sidebar hides catalogos item from user without permission', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertDontSee('Catálogos');
});

test('sidebar shows catalogos item when user has permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('catalogos.listar');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSee('Catálogos');
});

test('sidebar shows materias only when user has catalogos permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('catalogos.listar');

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSee('Catálogos');
    $response->assertSee('Materias');
});

test('sidebar hides materias from user without catalogos permission', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertDontSee('Materias');
});

// ─── Superadmin: ve todos los módulos ───

test('superadmin sees all menu items', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $response = $this->actingAs($user)->get(route('dashboard'));

    $expected = [
        'Dashboard',
        'Catálogos',
        'Materias',
        'Usuarios',
        'Grupos',
        'Alumnos',
        'Calificaciones',
        'Asistencia',
        'Reinscripciones',
        'Boleta',
        'Reportes',
    ];

    foreach ($expected as $item) {
        $response->assertSee($item);
    }
});

// ─── Docente: ve calificaciones y asistencia ───

test('docente sees teaching-related modules', function () {
    $user = User::factory()->create();
    $user->assignRole('Docente');

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSee('Dashboard');
    $response->assertSee('Calificaciones');
    $response->assertSee('Asistencia');
    $response->assertSee('Boleta');

    $response->assertDontSee('Catálogos');
    $response->assertDontSee('Usuarios');
    $response->assertDontSee('Reinscripciones');
    $response->assertDontSee('Grupos');
});

// ─── Tutor: ve solo su dashboard ───

test('tutor sees only tutor-related modules', function () {
    $user = User::factory()->create();
    $user->assignRole('Tutor');

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSee('Dashboard');
    $response->assertSee('Boleta');

    $response->assertDontSee('Catálogos');
    $response->assertDontSee('Materias');
    $response->assertDontSee('Usuarios');
    $response->assertDontSee('Grupos');
    $response->assertDontSee('Alumnos');
    $response->assertDontSee('Calificaciones');
    $response->assertDontSee('Asistencia');
    $response->assertDontSee('Reinscripciones');

    // Note: "Reportes" group HEADER still shows because "Boleta" is under it
});

// ─── Director / Subdirector: ven reportes ───

test('director sees reports and management modules', function () {
    $user = User::factory()->create();
    $user->assignRole('Director');

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSee('Dashboard');
    $response->assertSee('Reportes');
    $response->assertSee('Grupos');
    $response->assertSee('Alumnos');
    $response->assertSee('Calificaciones');
    $response->assertSee('Asistencia');
    $response->assertSee('Reinscripciones');

    $response->assertDontSee('Catálogos');
    $response->assertDontSee('Usuarios');
});
