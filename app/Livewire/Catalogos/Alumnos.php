<?php

namespace App\Livewire\Catalogos;

use App\Livewire\Catalogos\Concerns\AlumnoFormTrait;
use App\Models\Alumno;
use App\Models\Grupo;
use App\Support\CicloActivoService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Alumnos extends Component
{
    use AlumnoFormTrait;
    use WithPagination;

    // ─── Filters ───

    public $filtro_ciclo = '';

    public $filtro_grado = '';

    public $filtro_grupo = '';

    public $filtro_estatus = '';

    public string $sortField = 'matricula';

    public string $sortDirection = 'asc';

    public string $search = '';

    public function mount(): void
    {
        $this->filtro_ciclo = (string) app(CicloActivoService::class)->getId() ?: '';

        if ($editarId = request('editar')) {
            $this->editar((int) $editarId);
        }
    }

    // Override trait's grupos() to support grado filter

    #[Computed]
    public function grupos(): Collection
    {
        $cicloActivoId = app(CicloActivoService::class)->getId();

        return $this->filtro_grado
            ? Grupo::where('grado_id', $this->filtro_grado)
                ->where('ciclo_escolar_id', $cicloActivoId)
                ->orderBy('nombre')->get()
            : Grupo::with('grado')
                ->where('ciclo_escolar_id', $cicloActivoId)
                ->orderBy('grado_id')->orderBy('nombre')->get();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = Alumno::with('persona', 'grado', 'grupo')
            ->join('personas', 'alumnos.persona_id', '=', 'personas.id')
            ->select('alumnos.*');

        if ($this->filtro_grado) {
            $query->where('alumnos.grado_id', $this->filtro_grado);
        }

        if ($this->filtro_grupo) {
            $query->where('alumnos.grupo_id', $this->filtro_grupo);
        }

        if ($this->filtro_estatus) {
            $query->where('alumnos.estatus', $this->filtro_estatus);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('alumnos.matricula', 'like', "%{$this->search}%")
                    ->orWhere('personas.nombre', 'like', "%{$this->search}%")
                    ->orWhere('personas.apellido_paterno', 'like', "%{$this->search}%")
                    ->orWhere('personas.curp', 'like', "%{$this->search}%")
                    ->orWhere(DB::raw("CONCAT(personas.apellido_paterno, ' ', personas.apellido_materno, ' ', personas.nombre)"), 'like', "%{$this->search}%");
            });
        }

        $sortField = match ($this->sortField) {
            'nombre_completo' => 'personas.apellido_paterno',
            'curp' => 'personas.curp',
            'grado_id' => 'alumnos.grado_id',
            'grupo_id' => 'alumnos.grupo_id',
            'estatus' => 'alumnos.estatus',
            default => 'alumnos.'.$this->sortField,
        };

        $query->orderBy($sortField, $this->sortDirection);

        return view('livewire.catalogos.alumnos', [
            'alumnos' => $query->paginate(15),
            'grados' => $this->grados,
            'grupos' => $this->grupos,
        ]);
    }
}
