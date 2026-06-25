<?php

use App\Livewire\Catalogos\PadresFamilia;
use App\Models\Alumno;
use App\Models\AlumnoFamilia;
use App\Models\Persona;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// ─── Guest Access ───

test('guest is redirected to login for padres-familia', function () {
    $this->get(route('padres-familia.index'))->assertRedirect(route('login'));
});

// ─── Page Rendering ───

test('padres-familia page loads successfully for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('padres-familia.index'))
        ->assertOk();
});

test('padres-familia shows list of parents', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    // Create a person as parent (linked to a student)
    $persona = Persona::factory()->create([
        'nombre' => 'José',
        'apellido_paterno' => 'Pérez',
        'email' => 'jose@example.com',
    ]);

    $alumno = Alumno::factory()->create();
    AlumnoFamilia::create([
        'alumno_id' => $alumno->id,
        'persona_id' => $persona->id,
        'parentesco' => 'Padre',
    ]);

    // Create a person NOT linked (should not appear)
    Persona::factory()->create([
        'nombre' => 'No',
        'apellido_paterno' => 'Visible',
    ]);

    $this->actingAs($user)
        ->get(route('padres-familia.index'))
        ->assertOk()
        ->assertSee('José Pérez')
        ->assertSee('jose@example.com')
        ->assertDontSee('No Visible');
});

// ─── Create Parent ───

test('creates a parent without linking to student', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    Livewire::actingAs($user)
        ->test(PadresFamilia::class)
        ->call('crear')
        ->set('nombre', 'María')
        ->set('apellido_paterno', 'López')
        ->set('telefono', '5512345678')
        ->set('parentesco', 'Madre')
        ->call('guardar')
        ->assertOk();

    $this->assertDatabaseHas('personas', [
        'nombre' => 'MARÍA',
        'apellido_paterno' => 'LÓPEZ',
    ]);
});

test('creates a parent linked to a student', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $alumno = Alumno::factory()->create();

    Livewire::actingAs($user)
        ->test(PadresFamilia::class)
        ->call('crear')
        ->set('nombre', 'Carlos')
        ->set('apellido_paterno', 'García')
        ->set('parentesco', 'Padre')
        ->set('alumno_id', $alumno->id)
        ->call('guardar')
        ->assertOk();

    // Verify parent was created
    $persona = Persona::where('nombre', 'CARLOS')->first();
    expect($persona)->not->toBeNull();

    // Verify link exists
    $this->assertDatabaseHas('alumno_familia', [
        'alumno_id' => $alumno->id,
        'persona_id' => $persona->id,
        'parentesco' => 'Padre',
    ]);
});

test('creates a parent with tutor account', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    Livewire::actingAs($user)
        ->test(PadresFamilia::class)
        ->call('crear')
        ->set('nombre', 'Ana')
        ->set('apellido_paterno', 'Martínez')
        ->set('email', 'ana@example.com')
        ->set('parentesco', 'Tutor')
        ->set('crear_cuenta', true)
        ->set('password', 'secret123')
        ->set('password_confirmation', 'secret123')
        ->call('guardar')
        ->assertOk();

    // Verify user was created
    $this->assertDatabaseHas('users', [
        'email' => 'ana@example.com',
    ]);

    $tutorUser = User::where('email', 'ana@example.com')->first();
    expect($tutorUser->hasRole('Tutor'))->toBeTrue();
});

// ─── Edit Parent ───

test('edits a parent', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $persona = Persona::factory()->create([
        'nombre' => 'Original',
        'apellido_paterno' => 'Apellido',
    ]);

    // Link to student so it appears in the CRUD
    $alumno = Alumno::factory()->create();
    AlumnoFamilia::create([
        'alumno_id' => $alumno->id,
        'persona_id' => $persona->id,
        'parentesco' => 'Madre',
    ]);

    Livewire::actingAs($user)
        ->test(PadresFamilia::class)
        ->call('editar', $persona->id)
        ->set('nombre', 'Actualizado')
        ->set('telefono', '9999999999')
        ->call('guardar')
        ->assertOk();

    $this->assertDatabaseHas('personas', [
        'id' => $persona->id,
        'nombre' => 'ACTUALIZADO',
        'telefono' => '9999999999',
    ]);
});

// ─── Unlink Parent ───

test('unlinks a parent (keeps persona record)', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $persona = Persona::factory()->create([
        'nombre' => 'Para',
        'apellido_paterno' => 'Desvincular',
    ]);

    $alumno = Alumno::factory()->create();
    AlumnoFamilia::create([
        'alumno_id' => $alumno->id,
        'persona_id' => $persona->id,
        'parentesco' => 'Padre',
    ]);

    Livewire::actingAs($user)
        ->test(PadresFamilia::class)
        ->call('eliminar', $persona->id);

    // Link should be removed
    $this->assertDatabaseMissing('alumno_familia', [
        'persona_id' => $persona->id,
    ]);

    // Persona record still exists
    $this->assertDatabaseHas('personas', [
        'id' => $persona->id,
        'nombre' => 'Para',
    ]);
});

// ─── Validation ───

test('requires nombre and apellido_paterno', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    Livewire::actingAs($user)
        ->test(PadresFamilia::class)
        ->call('crear')
        ->call('guardar')
        ->assertHasErrors(['nombre', 'apellido_paterno']);
});
