<?php

use App\Console\Commands\AutocrearCiclo;
use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\PeriodoEvaluacion;
use Carbon\Carbon;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('no crea ciclo si el mes es anterior a agosto', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 15));

    $this->artisan(AutocrearCiclo::class)
        ->expectsOutput('No es temporada de auto-creación (mes < agosto).')
        ->assertSuccessful();

    expect(CicloEscolar::where('nombre', '2026-2027')->exists())->toBeFalse();
});

test('crea ciclo 2026-2027 el 1 de agosto', function () {
    Carbon::setTestNow(Carbon::create(2026, 8, 1, 1, 0, 0));

    // Create an active cycle to clone groups from
    $source = CicloEscolar::factory()->activo()->create([
        'nombre' => '2025-2026',
        'fecha_inicio' => '2025-08-01',
        'fecha_fin' => '2026-07-31',
    ]);

    $grado = Grado::factory()->create(['nombre' => '1°']);
    Grupo::factory()->for($source)->for($grado)->conDocente()->create(['nombre' => 'A']);

    PeriodoEvaluacion::factory()->for($source, 'cicloEscolar')->count(3)->create();

    $this->artisan(AutocrearCiclo::class)
        ->assertSuccessful();

    $ciclo = CicloEscolar::where('nombre', '2026-2027')->first();
    expect($ciclo)->not->toBeNull();
    expect($ciclo->estatus)->toBe('pendiente');
    expect($ciclo->autocreado)->toBeTrue();
    expect($ciclo->fecha_inicio->format('Y-m-d'))->toBe('2026-08-01');
    expect($ciclo->fecha_fin->format('Y-m-d'))->toBe('2027-07-31');

    // Groups were cloned
    expect(Grupo::where('ciclo_escolar_id', $ciclo->id)->count())->toBe(1);
    $cloned = Grupo::where('ciclo_escolar_id', $ciclo->id)->first();
    expect($cloned->docente_id)->toBeNull();

    // Evaluation periods were cloned
    expect(PeriodoEvaluacion::where('ciclo_escolar_id', $ciclo->id)->count())->toBe(3);
});

test('no duplica ciclo si ya existe', function () {
    Carbon::setTestNow(Carbon::create(2026, 8, 1, 1, 0, 0));

    CicloEscolar::factory()->create(['nombre' => '2026-2027']);

    $this->artisan(AutocrearCiclo::class)
        ->assertSuccessful();

    expect(CicloEscolar::where('nombre', '2026-2027')->count())->toBe(1);
});

test('auto-activa ciclo pendiente despues de 5 dias', function () {
    // Create a pending auto-created cycle from 6 days ago
    Carbon::setTestNow(Carbon::create(2026, 8, 1, 1, 0, 0));

    $ciclo = CicloEscolar::factory()->create([
        'nombre' => '2026-2027',
        'estatus' => 'pendiente',
        'autocreado' => true,
        'created_at' => Carbon::now()->subDays(6),
    ]);

    // Fast-forward to 6 days later
    Carbon::setTestNow(Carbon::create(2026, 8, 7, 1, 0, 0));

    $this->artisan(AutocrearCiclo::class)
        ->assertSuccessful();

    $ciclo->refresh();
    expect($ciclo->estatus)->toBe('activo');
});

test('no activa ciclo pendiente antes de 5 dias', function () {
    Carbon::setTestNow(Carbon::create(2026, 8, 1, 1, 0, 0));

    $ciclo = CicloEscolar::factory()->create([
        'nombre' => '2026-2027',
        'estatus' => 'pendiente',
        'autocreado' => true,
        'created_at' => Carbon::now()->subDays(2),
    ]);

    Carbon::setTestNow(Carbon::create(2026, 8, 3, 1, 0, 0));

    $this->artisan(AutocrearCiclo::class)
        ->assertSuccessful();

    $ciclo->refresh();
    expect($ciclo->estatus)->toBe('pendiente');
});

test('auto-activacion finaliza el ciclo activo anterior', function () {
    Carbon::setTestNow(Carbon::create(2026, 7, 1, 1, 0, 0));

    $activo = CicloEscolar::factory()->activo()->create([
        'nombre' => '2025-2026',
        'fecha_inicio' => '2025-08-01',
        'fecha_fin' => '2026-07-31',
    ]);

    // Create the auto cycle 6 days ago
    Carbon::setTestNow(Carbon::create(2026, 8, 1, 1, 0, 0));

    $pendiente = CicloEscolar::factory()->create([
        'nombre' => '2026-2027',
        'estatus' => 'pendiente',
        'autocreado' => true,
        'created_at' => Carbon::now()->subDays(6),
    ]);

    Carbon::setTestNow(Carbon::create(2026, 8, 7, 1, 0, 0));

    $this->artisan(AutocrearCiclo::class)
        ->assertSuccessful();

    $activo->refresh();
    expect($activo->estatus)->toBe('finalizado');

    $pendiente->refresh();
    expect($pendiente->estatus)->toBe('activo');
});
