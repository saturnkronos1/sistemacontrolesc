<?php

use App\Actions\Ciclos\ClonarPeriodosEvaluacion;
use App\Models\CicloEscolar;
use App\Models\PeriodoEvaluacion;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('clona periodos de evaluacion de un ciclo a otro', function () {
    $source = CicloEscolar::factory()->activo()->create();
    $target = CicloEscolar::factory()->create();

    PeriodoEvaluacion::factory()->for($source, 'cicloEscolar')->count(3)->create();

    $count = app(ClonarPeriodosEvaluacion::class)->execute($source->id, $target->id);

    expect($count)->toBe(3);

    $cloned = PeriodoEvaluacion::where('ciclo_escolar_id', $target->id)->get();
    expect($cloned)->toHaveCount(3);
});

test('retorna 0 si el ciclo origen no tiene periodos', function () {
    $source = CicloEscolar::factory()->create();
    $target = CicloEscolar::factory()->create();

    $count = app(ClonarPeriodosEvaluacion::class)->execute($source->id, $target->id);

    expect($count)->toBe(0);
    expect(PeriodoEvaluacion::where('ciclo_escolar_id', $target->id)->count())->toBe(0);
});
