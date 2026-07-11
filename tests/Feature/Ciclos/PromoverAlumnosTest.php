<?php

use App\Events\CicloActivado;
use App\Listeners\PromoverAlumnos;
use App\Models\Alumno;
use App\Models\AlumnoCiclo;
use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\PromocionLog;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('no hace nada si no hay ciclo anterior', function () {
    $ciclo = CicloEscolar::factory()->create(['estatus' => 'activo']);

    $event = new CicloActivado($ciclo, null);
    app(PromoverAlumnos::class)->handle($event);

    expect(AlumnoCiclo::count())->toBe(0);
    expect(PromocionLog::count())->toBe(0);
});

test('egresa alumnos de 6° grado', function () {
    $anterior = CicloEscolar::factory()->activo()->create();
    $nuevo = CicloEscolar::factory()->create();

    $grado6 = Grado::factory()->create(['id' => 6, 'nombre' => '6°']);

    $grupo = Grupo::factory()
        ->for($anterior)
        ->for($grado6)
        ->create(['nombre' => 'A']);

    $alumno = Alumno::factory()
        ->for($anterior, 'cicloEscolar')
        ->for($grado6, 'grado')
        ->for($grupo, 'grupo')
        ->activo()
        ->create();

    $event = new CicloActivado($nuevo, $anterior);
    app(PromoverAlumnos::class)->handle($event);

    $alumno->refresh();

    // Status changed to egresado
    expect($alumno->estatus)->toBe('egresado');

    // Snapshot saved
    expect(AlumnoCiclo::count())->toBe(1);
    $snapshot = AlumnoCiclo::first();
    expect($snapshot->alumno_id)->toBe($alumno->id);
    expect($snapshot->estatus)->toBe('egresado');
    expect($snapshot->grado_id)->toBe(6);

    // No promocion_log (egresados don't get promoted)
    expect(PromocionLog::count())->toBe(0);
});

test('promueve alumnos 5°→6° con round-robin entre 2 grupos', function () {
    $anterior = CicloEscolar::factory()->activo()->create();
    $nuevo = CicloEscolar::factory()->create();

    $grado5 = Grado::factory()->create(['id' => 5, 'nombre' => '5°']);
    $grado6 = Grado::factory()->create(['id' => 6, 'nombre' => '6°']);

    $grupoOrigen = Grupo::factory()
        ->for($anterior)
        ->for($grado5)
        ->create(['nombre' => 'A']);

    // Two destination groups for round-robin
    $grupoDestinoA = Grupo::factory()
        ->for($nuevo)
        ->for($grado6)
        ->create(['nombre' => 'A']);

    $grupoDestinoB = Grupo::factory()
        ->for($nuevo)
        ->for($grado6)
        ->create(['nombre' => 'B']);

    $alumno1 = Alumno::factory()
        ->for($anterior, 'cicloEscolar')
        ->for($grado5, 'grado')
        ->for($grupoOrigen, 'grupo')
        ->activo()
        ->create();

    $alumno2 = Alumno::factory()
        ->for($anterior, 'cicloEscolar')
        ->for($grado5, 'grado')
        ->for($grupoOrigen, 'grupo')
        ->activo()
        ->create();

    $alumno3 = Alumno::factory()
        ->for($anterior, 'cicloEscolar')
        ->for($grado5, 'grado')
        ->for($grupoOrigen, 'grupo')
        ->activo()
        ->create();

    $event = new CicloActivado($nuevo, $anterior);
    app(PromoverAlumnos::class)->handle($event);

    // Reload
    $alumno1->refresh();
    $alumno2->refresh();
    $alumno3->refresh();

    // All moved to new cycle and grade 6
    expect($alumno1->ciclo_escolar_id)->toBe($nuevo->id);
    expect($alumno1->grado_id)->toBe(6);
    expect($alumno2->ciclo_escolar_id)->toBe($nuevo->id);
    expect($alumno2->grado_id)->toBe(6);
    expect($alumno3->ciclo_escolar_id)->toBe($nuevo->id);
    expect($alumno3->grado_id)->toBe(6);

    // Round-robin: 1→A, 2→B, 3→A
    expect($alumno1->grupo_id)->toBe($grupoDestinoA->id);
    expect($alumno2->grupo_id)->toBe($grupoDestinoB->id);
    expect($alumno3->grupo_id)->toBe($grupoDestinoA->id);

    // Snapshots saved
    expect(AlumnoCiclo::count())->toBe(3);

    // Promocion logs saved
    expect(PromocionLog::count())->toBe(3);
    $log = PromocionLog::where('alumno_id', $alumno1->id)->first();
    expect($log->ciclo_origen_id)->toBe($anterior->id);
    expect($log->ciclo_destino_id)->toBe($nuevo->id);
    expect($log->grado_origen_id)->toBe(5);
    expect($log->grado_destino_id)->toBe(6);
    expect($log->grupo_origen_id)->toBe($grupoOrigen->id);
    expect($log->grupo_destino_id)->toBe($grupoDestinoA->id);
    expect($log->tipo)->toBe('promocion_automatica');
});

test('no procesa grado sin alumnos', function () {
    $anterior = CicloEscolar::factory()->activo()->create();
    $nuevo = CicloEscolar::factory()->create();

    $grado3 = Grado::factory()->create(['id' => 3, 'nombre' => '3°']);
    $grado4 = Grado::factory()->create(['id' => 4, 'nombre' => '4°']);

    // Groups exist but no students
    Grupo::factory()
        ->for($anterior)
        ->for($grado3)
        ->create(['nombre' => 'A']);

    Grupo::factory()
        ->for($nuevo)
        ->for($grado4)
        ->create(['nombre' => 'A']);

    $event = new CicloActivado($nuevo, $anterior);
    app(PromoverAlumnos::class)->handle($event);

    expect(PromocionLog::count())->toBe(0);
});

test('logged warning si grado origen no tiene grupos destino', function () {
    $anterior = CicloEscolar::factory()->activo()->create();
    $nuevo = CicloEscolar::factory()->create();

    $grado2 = Grado::factory()->create(['id' => 2, 'nombre' => '2°']);

    $grupoOrigen = Grupo::factory()
        ->for($anterior)
        ->for($grado2)
        ->create(['nombre' => 'A']);

    Alumno::factory()
        ->for($anterior, 'cicloEscolar')
        ->for($grado2, 'grado')
        ->for($grupoOrigen, 'grupo')
        ->activo()
        ->create();

    // No destination groups for grado 3 in the new cycle

    Log::shouldReceive('warning')
        ->once()
        ->with(Mockery::pattern('/sin grupos destino/'));

    $event = new CicloActivado($nuevo, $anterior);
    app(PromoverAlumnos::class)->handle($event);

    // Student remains in old cycle
    expect(PromocionLog::count())->toBe(0);
});

test('integra egresados y promocion en un solo evento', function () {
    $anterior = CicloEscolar::factory()->activo()->create();
    $nuevo = CicloEscolar::factory()->create();

    $grado6 = Grado::factory()->create(['id' => 6, 'nombre' => '6°']);
    $grado1 = Grado::factory()->create(['id' => 1, 'nombre' => '1°']);
    $grado2 = Grado::factory()->create(['id' => 2, 'nombre' => '2°']);

    $grupo6 = Grupo::factory()->for($anterior)->for($grado6)->create(['nombre' => 'A']);
    $grupo1 = Grupo::factory()->for($anterior)->for($grado1)->create(['nombre' => 'A']);
    $grupo2Destino = Grupo::factory()->for($nuevo)->for($grado2)->create(['nombre' => 'A']);

    // 6th grade student → egresado
    $sexto = Alumno::factory()
        ->for($anterior, 'cicloEscolar')
        ->for($grado6, 'grado')
        ->for($grupo6, 'grupo')
        ->activo()
        ->create();

    // 1st grade student → promoted to 2nd
    $primero = Alumno::factory()
        ->for($anterior, 'cicloEscolar')
        ->for($grado1, 'grado')
        ->for($grupo1, 'grupo')
        ->activo()
        ->create();

    $event = new CicloActivado($nuevo, $anterior);
    app(PromoverAlumnos::class)->handle($event);

    // 6th grader now egresado
    $sexto->refresh();
    expect($sexto->estatus)->toBe('egresado');
    expect($sexto->ciclo_escolar_id)->toBe($anterior->id); // stayed in old cycle

    // 1st grader promoted
    $primero->refresh();
    expect($primero->ciclo_escolar_id)->toBe($nuevo->id);
    expect($primero->grado_id)->toBe(2);

    // Totals: 1 snapshot (6th) + 1 snapshot (1st) + 1 log = 2 snapshots, 1 log
    expect(AlumnoCiclo::count())->toBe(2);
    expect(PromocionLog::count())->toBe(1);
});
