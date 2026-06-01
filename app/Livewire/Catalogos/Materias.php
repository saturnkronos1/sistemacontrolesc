<?php

namespace App\Livewire\Catalogos;

use App\Models\Grado;
use App\Models\Materia;
use Livewire\Component;
use Livewire\WithPagination;

class Materias extends Component
{
    use WithPagination;

    public $showModal = false;

    public $editId = null;

    public $grado_id = '';

    public $nombre = '';

    public $clave_materia = '';

    public $filtro_grado = '';

    public string $sortField = 'grado_id';

    public string $sortDirection = 'asc';

    protected function rules()
    {
        return [
            'grado_id' => 'required|exists:grados,id',
            'nombre' => 'required|string|max:255',
            'clave_materia' => 'required|string|max:20|unique:materias,clave_materia,'.($this->editId ?? 'NULL'),
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
        $query = Materia::with('grado');

        if ($this->filtro_grado) {
            $query->where('grado_id', $this->filtro_grado);
        }

        return view('livewire.catalogos.materias', [
            'materias' => $query->orderBy($this->sortField, $this->sortDirection)->paginate(15),
            'grados' => Grado::orderBy('nombre')->get(),
        ]);
    }

    public function crear()
    {
        $this->resetModal();
        $this->showModal = true;
    }

    public function editar($id)
    {
        $materia = Materia::findOrFail($id);
        $this->editId = $materia->id;
        $this->grado_id = $materia->grado_id;
        $this->nombre = $materia->nombre;
        $this->clave_materia = $materia->clave_materia;
        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        Materia::updateOrCreate(
            ['id' => $this->editId],
            [
                'grado_id' => $this->grado_id,
                'nombre' => $this->nombre,
                'clave_materia' => $this->clave_materia,
            ]
        );

        $this->dispatch('toast', message: 'Materia guardada exitosamente.', type: 'success');
        $this->resetModal();
    }

    public function eliminar($id)
    {
        Materia::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Materia eliminada.', type: 'success');
    }

    public function resetModal()
    {
        $this->showModal = false;
        $this->editId = null;
        $this->grado_id = '';
        $this->nombre = '';
        $this->clave_materia = '';
    }
}
