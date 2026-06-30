<?php

namespace App\Livewire\Catalogos;

use App\Livewire\Catalogos\Concerns\AlumnoFormTrait;
use App\Models\Alumno;
use Livewire\Component;

class VerAlumno extends Component
{
    use AlumnoFormTrait;

    public Alumno $alumno;

    public function mount(Alumno $alumno): void
    {
        $this->alumno = $alumno->load([
            'persona',
            'familiares.persona',
            'grado',
            'grupo',
            'cicloEscolar',
        ]);
    }

    public function irAEditar(): void
    {
        $this->editar($this->alumno->id);
    }

    protected function onSaved(): void
    {
        $this->redirect(route('alumnos.index'));
    }

    public function render()
    {
        return view('livewire.catalogos.ver-alumno');
    }
}
