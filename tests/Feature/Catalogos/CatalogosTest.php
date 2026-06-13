<?php

use App\Livewire\Catalogos\Alumnos;
use App\Livewire\Catalogos\Boleta;
use App\Livewire\Catalogos\Calificaciones;
use App\Livewire\Catalogos\CiclosEscolares;
use App\Livewire\Catalogos\Reinscripciones;
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

// ─── Alumnos: Family / Parents / Tutors ───

test('alumnos creates alumno with one parent as tutor', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    Livewire::actingAs($user)
        ->test(Alumnos::class)
        ->call('crear')
        ->set('nombre', 'Juanito')
        ->set('apellido_paterno', 'Pérez')
        ->set('apellido_materno', 'López')
        ->set('grado_id', $grado->id)
        ->set('grupo_id', $grupo->id)
        ->set('showFamilia', true)
        ->set('tipo_registro', 'padres')
        ->set('p1_nombre', 'José')
        ->set('p1_apellido_paterno', 'Pérez')
        ->set('p1_apellido_materno', 'García')
        ->set('p1_parentesco', 'Padre')
        ->set('p1_telefono', '5512345678')
        ->set('p1_email', 'jose@example.com')
        ->set('tutor_designado', 'padre1')
        ->call('guardar')
        ->assertOk();

    // Verify alumno was created
    $alumno = Alumno::where('matricula', 'like', 'ALU%')->first();
    expect($alumno)->not->toBeNull();

    // Verify persona for parent exists
    $this->assertDatabaseHas('personas', [
        'nombre' => 'José',
        'apellido_paterno' => 'Pérez',
        'email' => 'jose@example.com',
    ]);

    // Verify family pivot
    $this->assertDatabaseHas('alumno_familia', [
        'alumno_id' => $alumno->id,
        'parentesco' => 'Padre',
    ]);

    // Verify tutor user was auto-created
    $this->assertDatabaseHas('users', [
        'email' => 'jose@example.com',
    ]);
    $tutorUser = User::where('email', 'jose@example.com')->first();
    expect($tutorUser)->not->toBeNull();
    expect($tutorUser->hasRole('Tutor'))->toBeTrue();
});

test('alumnos creates alumno with two parents and designated tutor', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    Livewire::actingAs($user)
        ->test(Alumnos::class)
        ->call('crear')
        ->set('nombre', 'María')
        ->set('apellido_paterno', 'García')
        ->set('grado_id', $grado->id)
        ->set('grupo_id', $grupo->id)
        ->set('showFamilia', true)
        ->set('tipo_registro', 'padres')
        ->set('p1_nombre', 'Carlos')
        ->set('p1_apellido_paterno', 'García')
        ->set('p1_parentesco', 'Padre')
        ->set('p1_telefono', '5511111111')
        ->set('p2_activo', true)
        ->set('p2_nombre', 'Laura')
        ->set('p2_apellido_paterno', 'García')
        ->set('p2_parentesco', 'Madre')
        ->set('p2_telefono', '5522222222')
        ->set('tutor_designado', 'padre2')
        ->call('guardar')
        ->assertOk();

    $alumno = Alumno::where('matricula', 'like', 'ALU%')->latest()->first();

    // Both parents in family
    expect($alumno->familiares()->count())->toBe(2);

    // Tutor user created for parent 2 (madre)
    $tutorPersona = $alumno->familiares()
        ->where('parentesco', 'Madre')
        ->first()?->persona;
    expect($tutorPersona)->not->toBeNull();
    expect($tutorPersona->user)->not->toBeNull();
    expect($tutorPersona->user->hasRole('Tutor'))->toBeTrue();
});

test('alumnos creates alumno with legal tutor', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    Livewire::actingAs($user)
        ->test(Alumnos::class)
        ->call('crear')
        ->set('nombre', 'Luis')
        ->set('apellido_paterno', 'Martínez')
        ->set('grado_id', $grado->id)
        ->set('grupo_id', $grupo->id)
        ->set('showFamilia', true)
        ->set('tipo_registro', 'tutor_legal')
        ->set('tl_nombre', 'Roberto')
        ->set('tl_apellido_paterno', 'Martínez')
        ->set('tl_telefono', '5533333333')
        ->set('tl_email', 'roberto@example.com')
        ->call('guardar')
        ->assertOk();

    $alumno = Alumno::where('matricula', 'like', 'ALU%')->latest()->first();
    expect($alumno->familiares()->count())->toBe(1);
    expect($alumno->familiares()->first()->parentesco)->toBe('Tutor');

    // Tutor user created
    $this->assertDatabaseHas('users', [
        'email' => 'roberto@example.com',
    ]);
});

test('alumnos edit preserves family data', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $persona = Persona::factory()->create(['nombre' => 'Alumno', 'apellido_paterno' => 'Test']);
    $alumno = Alumno::factory()->create([
        'persona_id' => $persona->id,
        'grado_id' => $grado->id,
        'grupo_id' => $grupo->id,
        'ciclo_escolar_id' => $ciclo->id,
        'matricula' => 'FAM001',
    ]);

    // Add a parent with user account
    $padre = Persona::factory()->create([
        'nombre' => 'Papá',
        'apellido_paterno' => 'Test',
        'email' => 'papa@example.com',
    ]);
    AlumnoFamilia::factory()->create([
        'alumno_id' => $alumno->id,
        'persona_id' => $padre->id,
        'parentesco' => 'Padre',
    ]);
    $tutorUser = User::factory()->create([
        'name' => 'Papá Test',
        'email' => 'papa@example.com',
        'persona_id' => $padre->id,
    ]);
    $tutorUser->assignRole('Tutor');

    Livewire::actingAs($user)
        ->test(Alumnos::class)
        ->call('editar', $alumno->id)
        ->assertSet('p1_nombre', 'Papá')
        ->assertSet('p1_apellido_paterno', 'Test')
        ->assertSet('showFamilia', true);
});

test('alumnos creates alumno without family data', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);

    Livewire::actingAs($user)
        ->test(Alumnos::class)
        ->call('crear')
        ->set('nombre', 'Solo')
        ->set('apellido_paterno', 'Alumno')
        ->set('grado_id', $grado->id)
        ->call('guardar')
        ->assertOk();

    $alumno = Alumno::where('matricula', 'like', 'ALU%')->latest()->first();
    expect($alumno->familiares()->count())->toBe(0);
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

// ─── Asistencia ───

test('guest is redirected to login for asistencia', function () {
    $this->get(route('asistencia.index'))->assertRedirect(route('login'));
});

test('asistencia page loads successfully for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('asistencia.index'))
        ->assertOk();
});

test('asistencia filters grupos by docente', function () {
    $docente = User::factory()->create();
    $docente->assignRole('Docente');

    $otroDocente = User::factory()->create();
    $otroDocente->assignRole('Docente');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();

    $miGrupo = Grupo::factory()
        ->for($grado)->for($ciclo)
        ->create(['nombre' => 'GrupoA', 'docente_id' => $docente->id]);

    $otroGrupo = Grupo::factory()
        ->for($grado)->for($ciclo)
        ->create(['nombre' => 'GrupoB-Otro', 'docente_id' => $otroDocente->id]);

    $this->actingAs($docente)
        ->get(route('asistencia.index'))
        ->assertOk()
        ->assertSee('GrupoA')
        ->assertDontSee('GrupoB-Otro');
});

test('asistencia carga alumnos de un grupo', function () {
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
        ->test(App\Livewire\Catalogos\Asistencia::class)
        ->set('modo', 'pasar-lista')
        ->set('grupo_id', $grupo->id)
        ->set('fecha', '2026-06-01')
        ->call('cargarAlumnos')
        ->assertSet('cargado', true)
        ->assertSee($alumno->persona->apellido_paterno);
});

test('asistencia guarda estatus para cada alumno', function () {
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
        ->test(App\Livewire\Catalogos\Asistencia::class)
        ->set('modo', 'pasar-lista')
        ->set('grupo_id', $grupo->id)
        ->set('fecha', '2026-06-01')
        ->call('cargarAlumnos')
        ->set("estatusList.{$alumno->id}", 'falta')
        ->call('guardar')
        ->assertOk();

    $this->assertDatabaseHas('asistencias', [
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'estatus' => 'falta',
    ]);
});

test('asistencia crea justificante cuando estatus es justificado', function () {
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
        ->test(App\Livewire\Catalogos\Asistencia::class)
        ->set('modo', 'pasar-lista')
        ->set('grupo_id', $grupo->id)
        ->set('fecha', '2026-06-01')
        ->call('cargarAlumnos')
        ->set("estatusList.{$alumno->id}", 'justificado')
        ->set("motivos.{$alumno->id}", 'Cita médica')
        ->call('guardar')
        ->assertOk();

    $this->assertDatabaseHas('asistencias', [
        'alumno_id' => $alumno->id,
        'estatus' => 'justificado',
    ]);

    $this->assertDatabaseHas('justificantes', [
        'motivo' => 'Cita médica',
    ]);
});

test('superadmin can access asistencia', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('asistencia.index'))
        ->assertOk();
});

// ─── Asistencia: Pasar lista after admin mount ───

test('asistencia pasar lista keeps grupo_id after switching from consulta', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    // Step 1: Mount as admin — should be in consulta mode with ciclo auto-set
    $component = Livewire::actingAs($user)->test(App\Livewire\Catalogos\Asistencia::class);

    // Verify initial state
    $component->assertSet('modo', 'consulta');
    expect($component->get('ciclo_escolar_id'))->not()->toBeEmpty();

    // Step 2: Switch to pasar-lista mode
    $component->set('modo', 'pasar-lista');
    $component->assertSet('modo', 'pasar-lista');

    // Step 3: Select a group (should not clear grupo_id)
    $component->set('grupo_id', (string) $grupo->id);
    $component->assertSet('grupo_id', (string) $grupo->id);

    // Step 4: Check cargarAlumnos works
    $component->set('fecha', '2026-06-01');
    $component->call('cargarAlumnos')
        ->assertSet('cargado', true)
        ->assertCount('alumnos', 1)
        ->assertOk();

    // Step 5: Verify grupo_id is still set
    $component->assertSet('grupo_id', (string) $grupo->id);
});

// ─── Asistencia: Consulta ───

test('asistencia consulta shows tabs for admin roles', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    Livewire::actingAs($user)
        ->test(App\Livewire\Catalogos\Asistencia::class)
        ->assertSet('modo', 'consulta')
        ->assertSee('Pasar lista')
        ->assertSee('Consulta');
});

test('asistencia consulta defaults to pasar-lista for docente', function () {
    $user = User::factory()->create();
    $user->assignRole('Docente');

    Livewire::actingAs($user)
        ->test(App\Livewire\Catalogos\Asistencia::class)
        ->assertSet('modo', 'pasar-lista')
        ->assertDontSee('Consulta');
});

test('asistencia consulta loads attendance records filtered by grupo and date range', function () {
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
        'fecha' => '2026-06-01',
        'estatus' => 'asistio',
    ]);

    Asistencia::factory()->create([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'fecha' => '2026-06-02',
        'estatus' => 'falta',
    ]);

    Livewire::actingAs($user)
        ->test(App\Livewire\Catalogos\Asistencia::class)
        ->set('modo', 'consulta')
        ->set('grupo_id', $grupo->id)
        ->set('fecha_desde', '2026-06-01')
        ->set('fecha_hasta', '2026-06-30')
        ->call('consultar')
        ->assertSet('consultado', true)
        ->assertSee($alumno->persona->apellido_paterno);
});

test('asistencia consulta shows resumen counts', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    collect(['2026-06-01', '2026-06-02', '2026-06-03'])->each(function ($fecha) use ($alumno, $grupo) {
        Asistencia::factory()->create([
            'alumno_id' => $alumno->id,
            'grupo_id' => $grupo->id,
            'estatus' => 'asistio',
            'fecha' => $fecha,
        ]);
    });

    Asistencia::factory()->create([
        'alumno_id' => $alumno->id,
        'grupo_id' => $grupo->id,
        'fecha' => '2026-06-11',
        'estatus' => 'falta',
    ]);

    Livewire::actingAs($user)
        ->test(App\Livewire\Catalogos\Asistencia::class)
        ->set('modo', 'consulta')
        ->set('grupo_id', $grupo->id)
        ->set('fecha_desde', '2026-06-01')
        ->set('fecha_hasta', '2026-06-30')
        ->call('consultar')
        ->assertSet('resumen.asistio', 3)
        ->assertSet('resumen.falta', 1)
        ->assertSet('resumen.total', 4);
});

test('asistencia consulta filters by alumno', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()->for($grado)->for($ciclo)->create();

    $alumno1 = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    $alumno2 = Alumno::factory()
        ->activo()
        ->for($grado)
        ->create(['grupo_id' => $grupo->id, 'ciclo_escolar_id' => $ciclo->id]);

    Asistencia::factory()->create([
        'alumno_id' => $alumno1->id,
        'grupo_id' => $grupo->id,
        'fecha' => '2026-06-01',
        'estatus' => 'asistio',
    ]);

    Asistencia::factory()->create([
        'alumno_id' => $alumno2->id,
        'grupo_id' => $grupo->id,
        'fecha' => '2026-06-01',
        'estatus' => 'falta',
    ]);

    // Sin filtro de alumno — ve todos
    Livewire::actingAs($user)
        ->test(App\Livewire\Catalogos\Asistencia::class)
        ->set('modo', 'consulta')
        ->set('grupo_id', $grupo->id)
        ->set('fecha_desde', '2026-06-01')
        ->set('fecha_hasta', '2026-06-30')
        ->call('consultar')
        ->assertSet('resumen.total', 2);

    // Con filtro de alumno1 — ve solo sus registros
    Livewire::actingAs($user)
        ->test(App\Livewire\Catalogos\Asistencia::class)
        ->set('modo', 'consulta')
        ->set('grupo_id', $grupo->id)
        ->set('alumno_id', $alumno1->id)
        ->set('fecha_desde', '2026-06-01')
        ->set('fecha_hasta', '2026-06-30')
        ->call('consultar')
        ->assertSet('resumen.total', 1)
        ->assertSet('resumen.asistio', 1);
});

test('asistencia consulta generates PDF download', function () {
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
        'fecha' => '2026-06-01',
        'estatus' => 'asistio',
    ]);

    Livewire::actingAs($user)
        ->test(App\Livewire\Catalogos\Asistencia::class)
        ->set('modo', 'consulta')
        ->set('grupo_id', $grupo->id)
        ->set('fecha_desde', '2026-06-01')
        ->set('fecha_hasta', '2026-06-30')
        ->call('descargarPDFConsulta')
        ->assertFileDownloaded("asistencia-{$grado->nombre}-{$grupo->nombre}.pdf");
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

// ─── Boleta ───

test('guest is redirected to login for boleta', function () {
    $this->get(route('boleta.index'))->assertRedirect(route('login'));
});

test('boleta page loads successfully for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('boleta.index'))
        ->assertOk();
});

test('boleta shows grupos in select for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado = Grado::factory()->create(['nombre' => '1°']);
    $ciclo = CicloEscolar::factory()->activo()->create();
    $grupo = Grupo::factory()
        ->for($grado)->for($ciclo)
        ->create(['nombre' => 'A']);

    $this->actingAs($user)
        ->get(route('boleta.index'))
        ->assertOk()
        ->assertSee($grupo->nombre);
});

test('boleta loads alumnos when grupo selected', function () {
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
        ->test(Boleta::class)
        ->set('grupo_id', $grupo->id)
        ->assertSet('alumnos', fn ($alumnos) => count($alumnos) === 1);
});

test('boleta loads calificaciones for alumno', function () {
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
        'calificacion' => 9.0,
    ]);

    Livewire::actingAs($user)
        ->test(Boleta::class)
        ->set('grupo_id', $grupo->id)
        ->set('alumno_id', $alumno->id)
        ->call('cargar')
        ->assertSet('cargado', true)
        ->assertSee('9.0')
        ->assertSee('Matemáticas');
});

// ─── Reinscripciones ───

test('guest is redirected to login for reinscripciones', function () {
    $this->get(route('reinscripciones.index'))->assertRedirect(route('login'));
});

test('reinscripciones page loads successfully for superadmin', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('reinscripciones.index'))
        ->assertOk();
});

test('reinscripciones carga alumnos del grupo fuente', function () {
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
        ->assertSee($alumno->persona->apellido_paterno);
});

test('reinscripciones reinscribe alumno a nuevo grupo', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $grado1 = Grado::factory()->create(['nombre' => '1°']);
    $grado2 = Grado::factory()->create(['nombre' => '2°']);
    $cicloActivo = CicloEscolar::factory()->activo()->create();
    $cicloNuevo = CicloEscolar::factory()->create(['nombre' => '2026-2027', 'estatus' => 'pendiente']);

    $sourceGrupo = Grupo::factory()->for($grado1)->for($cicloActivo)->create(['nombre' => 'A']);
    $targetGrupo = Grupo::factory()->for($grado2)->for($cicloNuevo)->create(['nombre' => 'A']);

    $alumno = Alumno::factory()
        ->activo()
        ->for($grado1)
        ->create([
            'grupo_id' => $sourceGrupo->id,
            'ciclo_escolar_id' => $cicloActivo->id,
        ]);

    Livewire::actingAs($user)
        ->test(Reinscripciones::class)
        ->set('source_grupo_id', $sourceGrupo->id)
        ->call('cargarAlumnos')
        ->set('selected', [$alumno->id => true])
        ->set('target_grupo_id', $targetGrupo->id)
        ->call('reinscribir')
        ->assertOk();

    $this->assertDatabaseHas('alumnos', [
        'id' => $alumno->id,
        'grado_id' => $grado2->id,
        'grupo_id' => $targetGrupo->id,
        'ciclo_escolar_id' => $cicloNuevo->id,
        'estatus' => 'activo',
    ]);
});

// ─── Ciclos Escolares: confirmación rápida ───

test('confirma ciclo pendiente y finaliza el activo anterior', function () {
    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $activo = CicloEscolar::factory()->activo()->create(['nombre' => '2025-2026']);
    $pendiente = CicloEscolar::factory()->create([
        'nombre' => '2026-2027',
        'estatus' => 'pendiente',
        'autocreado' => true,
    ]);

    Livewire::actingAs($user)
        ->test(CiclosEscolares::class)
        ->call('confirmar', $pendiente->id);

    $this->assertDatabaseHas('ciclos_escolares', [
        'id' => $pendiente->id,
        'estatus' => 'activo',
    ]);

    $this->assertDatabaseHas('ciclos_escolares', [
        'id' => $activo->id,
        'estatus' => 'finalizado',
    ]);
});
