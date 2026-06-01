<?php

namespace App\Livewire\Catalogos;

use App\Models\CicloEscolar;
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

    public $activo = false;

    public string $sortField = 'fecha_inicio';

    public string $sortDirection = 'desc';

    public string $search = '';

    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'activo' => 'boolean',
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
        $this->resetModal();
        $this->showModal = true;
    }

    public function editar($id)
    {
        $ciclo = CicloEscolar::findOrFail($id);
        $this->editId = $ciclo->id;
        $this->nombre = $ciclo->nombre;
        $this->fecha_inicio = $ciclo->fecha_inicio->format('Y-m-d');
        $this->fecha_fin = $ciclo->fecha_fin->format('Y-m-d');
        $this->activo = $ciclo->activo;
        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->activo) {
            // Desactivar todos los otros ciclos
            CicloEscolar::where('id', '!=', $this->editId)->update(['activo' => false]);
        }

        CicloEscolar::updateOrCreate(
            ['id' => $this->editId],
            [
                'nombre' => $this->nombre,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin,
                'activo' => $this->activo,
            ]
        );

        $this->dispatch('toast', message: 'Ciclo escolar guardado exitosamente.', type: 'success');
        $this->resetModal();
    }

    public function eliminar($id)
    {
        CicloEscolar::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Ciclo escolar eliminado.', type: 'success');
    }

    public function resetModal()
    {
        $this->showModal = false;
        $this->editId = null;
        $this->nombre = '';
        $this->fecha_inicio = '';
        $this->fecha_fin = '';
        $this->activo = false;
    }
}
