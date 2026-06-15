<?php

namespace App\Livewire\Catalogos;

use App\Models\Alumno;
use App\Models\CicloEscolar;
use App\Models\Grupo;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Reinscripciones extends Component
{
    public $ciclo_escolar_id = '';

    public $target_ciclo_escolar_id = '';

    public $source_grupo_id = '';

    public $target_grupo_id = '';

    public $alumnos = [];

    /** @var array<int, true> */
    public $selected = [];

    public $cargado = false;

    public function mount(): void
    {
        $activo = CicloEscolar::activo()->first();
        if ($activo) {
            $this->ciclo_escolar_id = $activo->id;
            $this->target_ciclo_escolar_id = $this->detectarSiguienteCiclo($activo->id)?->id ?? '';
        }
    }

    public function render()
    {
        $ciclosEscolares = CicloEscolar::orderBy('fecha_inicio')->get();

        // Source grupos — filtrados por ciclo origen
        $sourceGrupos = Grupo::with('grado', 'cicloEscolar')
            ->when($this->ciclo_escolar_id, fn ($q) => $q->where('ciclo_escolar_id', $this->ciclo_escolar_id))
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();

        // Target grupos — filtrados por ciclo destino
        $targetGrupos = Grupo::with('grado', 'cicloEscolar')
            ->when($this->target_ciclo_escolar_id, fn ($q) => $q->where('ciclo_escolar_id', $this->target_ciclo_escolar_id))
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();

        return view('livewire.catalogos.reinscripciones', [
            'ciclosEscolares' => $ciclosEscolares,
            'sourceGrupos' => $sourceGrupos,
            'targetGrupos' => $targetGrupos,
        ]);
    }

    public function updatedCicloEscolarId(): void
    {
        $this->source_grupo_id = '';
        $this->target_grupo_id = '';
        $this->resetCarga();

        // Re-detectar siguiente ciclo cuando cambia el origen
        $this->target_ciclo_escolar_id = $this->detectarSiguienteCiclo($this->ciclo_escolar_id)?->id ?? '';
    }

    public function updatedTargetCicloEscolarId(): void
    {
        $this->target_grupo_id = '';
    }

    public function updatedSourceGrupoId(): void
    {
        $this->resetCarga();
        $this->target_grupo_id = '';
    }

    /**
     * Find the next ciclo after the given one, ordered by fecha_inicio.
     */
    protected function detectarSiguienteCiclo(int $cicloId): ?CicloEscolar
    {
        $actual = CicloEscolar::find($cicloId);
        if (! $actual) {
            return null;
        }

        return CicloEscolar::where('fecha_inicio', '>', $actual->fecha_inicio)
            ->orderBy('fecha_inicio')
            ->first();
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
