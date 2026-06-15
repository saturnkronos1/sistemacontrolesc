<?php

namespace App\Livewire\Catalogos;

use App\Models\CicloEscolar;
use App\Support\CicloActivoService;
use Livewire\Component;
use Livewire\WithPagination;

class CiclosEscolares extends Component
{
    use WithPagination;

    public $showModal = false;

    public $editId = null;

    public $nombre = '';

    public $fecha_inicio = '';

    public $fecha_fin = '';

    public string $estatus = 'pendiente';

    public string $sortField = 'fecha_inicio';

    public string $sortDirection = 'desc';

    public string $search = '';

    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'estatus' => 'required|in:pendiente,activo,finalizado',
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
        $query = CicloEscolar::orderBy($this->sortField, $this->sortDirection);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                    ->orWhere('fecha_inicio', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.catalogos.ciclos-escolares', [
            'ciclos' => $query->paginate(10),
        ]);
    }

    public function crear()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editar($id)
    {
        $ciclo = CicloEscolar::findOrFail($id);
        $this->editId = $ciclo->id;
        $this->nombre = $ciclo->nombre;
        $this->fecha_inicio = $ciclo->fecha_inicio->format('Y-m-d');
        $this->fecha_fin = $ciclo->fecha_fin->format('Y-m-d');
        $this->estatus = $ciclo->estatus;
        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        $cicloActivoService = app(CicloActivoService::class);

        if ($this->estatus === 'activo') {
            // Desactivar otros ciclos activos
            CicloEscolar::where('id', '!=', $this->editId)
                ->where('estatus', 'activo')
                ->update(['estatus' => 'pendiente']);
        }

        CicloEscolar::updateOrCreate(
            ['id' => $this->editId],
            [
                'nombre' => $this->nombre,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin,
                'estatus' => $this->estatus,
            ]
        );

        $cicloActivoService->refresh();

        $this->dispatch('toast', message: 'Ciclo escolar guardado exitosamente.', type: 'success');
        $this->resetModal();
    }

    public function confirmar($id)
    {
        $ciclo = CicloEscolar::findOrFail($id);
        $ciclo->update(['estatus' => 'activo']);

        // Finalizar el ciclo activo anterior
        CicloEscolar::where('estatus', 'activo')
            ->where('id', '!=', $ciclo->id)
            ->update(['estatus' => 'finalizado']);

        app(CicloActivoService::class)->refresh();

        $this->dispatch('toast', message: "Ciclo {$ciclo->nombre} confirmado y activado.", type: 'success');
    }

    public function eliminar($id)
    {
        CicloEscolar::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Ciclo escolar eliminado.', type: 'success');
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->nombre = '';
        $this->fecha_inicio = '';
        $this->fecha_fin = '';
        $this->estatus = 'pendiente';
    }

    public function resetModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }
}
