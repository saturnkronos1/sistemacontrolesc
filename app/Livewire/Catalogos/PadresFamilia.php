<?php

namespace App\Livewire\Catalogos;

use App\Livewire\Catalogos\Concerns\PadreFormTrait;
use App\Models\Persona;
use Livewire\Component;
use Livewire\WithPagination;

class PadresFamilia extends Component
{
    use PadreFormTrait;
    use WithPagination;

    public string $sortField = 'apellido_paterno';

    public string $sortDirection = 'asc';

    public string $search = '';

    public function mount(): void
    {
        if ($editarId = request('editar')) {
            $this->editar((int) $editarId);
        }
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
        $query = Persona::whereHas('familiares')
            ->with(['familiares.alumno.persona', 'user']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$this->search}%")
                    ->orWhere('apellido_materno', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('curp', 'like', "%{$this->search}%");
            });
        }

        $padres = $query->orderBy($this->sortField, $this->sortDirection)->paginate(15);

        return view('livewire.catalogos.padres-familia', [
            'padres' => $padres,
        ]);
    }
}
