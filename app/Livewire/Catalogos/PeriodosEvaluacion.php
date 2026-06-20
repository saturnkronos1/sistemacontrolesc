<?php

namespace App\Livewire\Catalogos;

use App\Models\CicloEscolar;
use App\Models\PeriodoEvaluacion;
use App\Support\CicloActivoService;
use Livewire\Component;
use Livewire\WithPagination;

class PeriodosEvaluacion extends Component
{
    use WithPagination;

    public $showModal = false;

    public $editId = null;

    public int $modalKey = 0;

    public $ciclo_escolar_id = '';

    public $nombre = '';

    public $orden = '';

    public $fecha_inicio = '';

    public $fecha_fin = '';

    public $filtro_ciclo = '';

    public string $sortField = 'orden';

    public string $sortDirection = 'asc';

    public string $search = '';

    public function mount(): void
    {
        $this->filtro_ciclo = (string) app(CicloActivoService::class)->getId() ?: '';
    }

    protected function rules()
    {
        return [
            'ciclo_escolar_id' => 'required|exists:ciclos_escolares,id',
            'nombre' => 'required|string|max:255',
            'orden' => 'required|integer|min:1|max:10',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
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
        $query = PeriodoEvaluacion::with('cicloEscolar');

        if ($this->filtro_ciclo) {
            $query->where('ciclo_escolar_id', $this->filtro_ciclo);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                    ->orWhere('orden', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.catalogos.periodos-evaluacion', [
            'periodos' => $query->orderBy($this->sortField, $this->sortDirection)->paginate(10),
            'ciclos' => CicloEscolar::orderBy('fecha_inicio', 'desc')->get(),
        ]);
    }

    public function crear()
    {
        $this->resetForm();
        $cicloActivoId = app(CicloActivoService::class)->getId();
        if ($cicloActivoId) {
            $this->ciclo_escolar_id = $cicloActivoId;
        }
        $this->modalKey++;
        $this->showModal = true;
    }

    public function editar($id)
    {
        $periodo = PeriodoEvaluacion::findOrFail($id);
        $this->editId = $periodo->id;
        $this->ciclo_escolar_id = $periodo->ciclo_escolar_id;
        $this->nombre = $periodo->nombre;
        $this->orden = $periodo->orden;
        $this->fecha_inicio = $periodo->fecha_inicio->format('Y-m-d');
        $this->fecha_fin = $periodo->fecha_fin->format('Y-m-d');
        $this->modalKey++;
        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        PeriodoEvaluacion::updateOrCreate(
            ['id' => $this->editId],
            [
                'ciclo_escolar_id' => $this->ciclo_escolar_id,
                'nombre' => $this->nombre,
                'orden' => $this->orden,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin,
            ]
        );

        $this->dispatch('toast', message: 'Periodo guardado exitosamente.', type: 'success');
        $this->resetModal();
    }

    public function eliminar($id)
    {
        PeriodoEvaluacion::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Periodo eliminado.', type: 'success');
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->ciclo_escolar_id = '';
        $this->nombre = '';
        $this->orden = '';
        $this->fecha_inicio = '';
        $this->fecha_fin = '';
    }

    public function resetModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }
}
