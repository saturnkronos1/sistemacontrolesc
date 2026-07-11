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

test('limpia prefijo numerico del nombre del grupo (1A → A)', function () {
    $source = CicloEscolar::factory()->activo()->create();
    $target = CicloEscolar::factory()->create();

    $grado = Grado::factory()->create(['nombre' => '1°']);

    // Create groups with numeric prefixed names like they exist in production
    Grupo::factory()
        ->for($source)
        ->for($grado)
        ->create(['nombre' => '1A']);

    Grupo::factory()
        ->for($source)
        ->for($grado)
        ->create(['nombre' => '2B']);

    Grupo::factory()
        ->for($source)
        ->for($grado)
        ->create(['nombre' => 'C']); // no prefix — should stay as-is

    app(ClonarGrupos::class)->execute($source->id, $target->id);

    $cloned = Grupo::where('ciclo_escolar_id', $target->id)->orderBy('nombre')->get();

    expect($cloned)->toHaveCount(3);
    expect($cloned[0]->nombre)->toBe('A');  // 1A → A
    expect($cloned[1]->nombre)->toBe('B');  // 2B → B
    expect($cloned[2]->nombre)->toBe('C');  // C → C (unchanged)
});

test('retorna 0 si el ciclo origen no tiene grupos', function () {
    $source = CicloEscolar::factory()->create();
    $target = CicloEscolar::factory()->create();

    $count = app(ClonarGrupos::class)->execute($source->id, $target->id);

    expect($count)->toBe(0);
    expect(Grupo::where('ciclo_escolar_id', $target->id)->count())->toBe(0);
});
