<?php

use App\Actions\Ciclos\ClonarGrupos;
use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('clona grupos de un ciclo a otro sin docente', function () {
    $source = CicloEscolar::factory()->activo()->create();
    $target = CicloEscolar::factory()->create();

    $grados = Grado::factory()->count(3)->create();

    foreach ($grados as $grado) {
        Grupo::factory()
            ->for($source)
            ->for($grado)
            ->conDocente()
            ->create(['nombre' => 'A']);
    }

    $count = app(ClonarGrupos::class)->execute($source->id, $target->id);

    expect($count)->toBe(3);

    $cloned = Grupo::where('ciclo_escolar_id', $target->id)->get();
    expect($cloned)->toHaveCount(3);

    foreach ($cloned as $grupo) {
        expect($grupo->docente_id)->toBeNull();
        expect($grupo->nombre)->toBe('A');
    }
});

test('retorna 0 si el ciclo origen no tiene grupos', function () {
    $source = CicloEscolar::factory()->create();
    $target = CicloEscolar::factory()->create();

    $count = app(ClonarGrupos::class)->execute($source->id, $target->id);

    expect($count)->toBe(0);
    expect(Grupo::where('ciclo_escolar_id', $target->id)->count())->toBe(0);
});
