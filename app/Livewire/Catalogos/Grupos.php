<?php

namespace App\Livewire\Catalogos;

use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\User;
use App\Support\CicloActivoService;
use Livewire\Component;
use Livewire\WithPagination;

class Grupos extends Component
{
    use WithPagination;

    public $showModal = false;

    public $editId = null;

    public $grado_id = '';

    public $ciclo_escolar_id = '';

    public $docente_id = '';

    public $nombre = '';

    public $filtro_ciclo = '';

    public $filtro_grado = '';

    public string $sortField = 'grado_id';

    public string $sortDirection = 'asc';

    public string $search = '';

    public function mount(): void
    {
        $this->filtro_ciclo = (string) app(CicloActivoService::class)->getId() ?: '';
    }

    protected function rules()
    {
        return [
            'grado_id' => 'required|exists:grados,id',
            'ciclo_escolar_id' => 'required|exists:ciclos_escolares,id',
            'docente_id' => 'nullable|exists:users,id',
            'nombre' => 'required|string|max:50',
        ];
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
        $query = Grupo::with('grado', 'cicloEscolar', 'docente');

        if ($this->filtro_ciclo) {
            $query->where('ciclo_escolar_id', $this->filtro_ciclo);
        }

        if ($this->filtro_grado) {
            $query->where('grado_id', $this->filtro_grado);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                    ->orWhereHas('docente', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
            });
        }

        return view('livewire.catalogos.grupos', [
            'grupos' => $query->orderBy($this->sortField, $this->sortDirection)->paginate(15),
            'ciclos' => CicloEscolar::orderBy('fecha_inicio', 'desc')->get(),
            'grados' => Grado::orderBy('nombre')->get(),
            'docentes' => User::role('Docente')->orderBy('name')->get(),
        ]);
    }

    public function crear()
    {
        $this->resetModal();
        $cicloActivoId = app(CicloActivoService::class)->getId();
        if ($cicloActivoId) {
            $this->ciclo_escolar_id = $cicloActivoId;
        }
        $this->showModal = true;
    }

    public function editar($id)
    {
        $grupo = Grupo::findOrFail($id);
        $this->editId = $grupo->id;
        $this->grado_id = $grupo->grado_id;
        $this->ciclo_escolar_id = $grupo->ciclo_escolar_id;
        $this->docente_id = $grupo->docente_id;
        $this->nombre = $grupo->nombre;
        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        Grupo::updateOrCreate(
            ['id' => $this->editId],
            [
                'grado_id' => $this->grado_id,
                'ciclo_escolar_id' => $this->ciclo_escolar_id,
                'docente_id' => $this->docente_id ?: null,
                'nombre' => $this->nombre,
            ]
        );

        $this->dispatch('toast', message: 'Grupo guardado exitosamente.', type: 'success');
        $this->resetModal();
    }

    public function eliminar($id)
    {
        Grupo::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Grupo eliminado.', type: 'success');
    }

    public function resetModal()
    {
        $this->showModal = false;
        $this->editId = null;
        $this->grado_id = '';
        $this->ciclo_escolar_id = '';
        $this->docente_id = '';
        $this->nombre = '';
    }
}
