<?php

namespace App\Livewire\Catalogos;

use App\Models\Alumno;
use App\Models\Grupo;
use App\Support\CicloActivoService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Reinscripciones extends Component
{
    public $source_grupo_id = '';

    public $target_grupo_id = '';

    public $alumnos = [];

    /** @var array<int, true> */
    public $selected = [];

    public $cargado = false;

    public function render()
    {
        $grupos = Grupo::with('grado', 'cicloEscolar')
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();

        // Mostrar solo grupos del ciclo activo como fuente, y otros como destino
        $cicloActivo = app(CicloActivoService::class)->get();

        return view('livewire.catalogos.reinscripciones', [
            'grupos' => $grupos,
            'cicloActivo' => $cicloActivo,
        ]);
    }

    public function updatedSourceGrupoId(): void
    {
        $this->resetCarga();
        $this->target_grupo_id = '';
    }

    public function cargarAlumnos()
    {
        $this->validate([
            'source_grupo_id' => 'required|exists:grupos,id',
        ]);

        $grupo = Grupo::with('grado', 'cicloEscolar')->findOrFail($this->source_grupo_id);

        $this->alumnos = $grupo->alumnos()
            ->where('alumnos.estatus', 'activo')
            ->with('persona', 'grado')
            ->join('personas', 'alumnos.persona_id', '=', 'personas.id')
            ->orderBy('personas.apellido_paterno')
            ->orderBy('personas.apellido_materno')
            ->orderBy('personas.nombre')
            ->select('alumnos.*')
            ->get()
            ->toArray();

        $this->selected = [];
        $this->cargado = true;
    }

    public function reinscribir()
    {
        $this->validate([
            'target_grupo_id' => 'required|exists:grupos,id',
            'selected' => 'required|array|min:1',
            'selected.*' => 'exists:alumnos,id',
        ]);

        $target = Grupo::with('grado', 'cicloEscolar')->findOrFail($this->target_grupo_id);

        $count = 0;

        DB::transaction(function () use ($target, &$count) {
            foreach ($this->selected as $alumnoId) {
                $alumno = Alumno::find($alumnoId);
                if ($alumno) {
                    $alumno->update([
                        'grado_id' => $target->grado_id,
                        'grupo_id' => $target->id,
                        'ciclo_escolar_id' => $target->ciclo_escolar_id,
                    ]);
                    $count++;
                }
            }
        });

        $this->dispatch('toast', message: "{$count} alumno(s) reinscrito(s) a {$target->grado->nombre} - {$target->nombre} ({$target->cicloEscolar->nombre}).", type: 'success');

        $this->cargarAlumnos();
    }

    public function toggleAll(): void
    {
        if (count($this->selected) === count($this->alumnos)) {
            $this->selected = [];
        } else {
            $this->selected = collect($this->alumnos)->pluck('id')->mapWithKeys(fn ($id) => [$id => true])->toArray();
        }
    }

    public function resetCarga(): void
    {
        $this->alumnos = [];
        $this->selected = [];
        $this->cargado = false;
    }
}
