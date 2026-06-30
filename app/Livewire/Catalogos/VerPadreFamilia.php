<?php

namespace App\Livewire\Catalogos;

use App\Livewire\Catalogos\Concerns\PadreFormTrait;
use App\Models\Persona;
use Livewire\Component;

class VerPadreFamilia extends Component
{
    use PadreFormTrait;

    public Persona $padre;

    public function mount(Persona $padre): void
    {
        $this->padre = $padre->load([
            'familiares.alumno.persona',
            'familiares.alumno.grado',
            'familiares.alumno.grupo',
            'user',
        ]);
    }

    public function irAEditar(): void
    {
        $this->editar($this->padre->id);
    }

    protected function onSaved(): void
    {
        $this->redirect(route('padres-familia.index'));
    }

    public function render()
    {
        return view('livewire.catalogos.ver-padre-familia');
    }
}
