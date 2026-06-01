<?php

namespace App\Livewire\Catalogos;

use App\Models\Alumno;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Persona;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Alumnos extends Component
{
    use WithPagination;

    public $showModal = false;

    public $editId = null;

    // Persona fields
    public $nombre = '';

    public $apellido_paterno = '';

    public $apellido_materno = '';

    public $curp = '';

    public $telefono = '';

    // Alumno fields
    public $grado_id = '';

    public $grupo_id = '';

    public $matricula = '';

    // Filters
    public $filtro_grado = '';

    public $filtro_grupo = '';

    public $filtro_estatus = '';

    public string $sortField = 'matricula';

    public string $sortDirection = 'asc';

    public string $search = '';

    protected function rules()
    {
        $alumnoId = $this->editId;

        return [
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'curp' => 'nullable|string|size:18|unique:personas,curp,'.($alumnoId ? Persona::whereHas('alumnos', fn ($q) => $q->where('id', $alumnoId))->first()?->id : 'NULL').',id',
            'telefono' => 'nullable|string|max:20',
            'grado_id' => 'required|exists:grados,id',
            'matricula' => 'required|string|max:20|unique:alumnos,matricula,'.$alumnoId,
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
                    ->orWhere(DB::raw("CONCAT(personas.apellido_paterno, ' ', personas.apellido_materno, ' ', personas.nombre)"), 'like', "%{$this->search}%");
            });
        }

        // Sort
        $sortField = match ($this->sortField) {
            'nombre_completo' => 'personas.apellido_paterno',
            'grado_id' => 'alumnos.grado_id',
            'grupo_id' => 'alumnos.grupo_id',
            'estatus' => 'alumnos.estatus',
            default => 'alumnos.'.$this->sortField,
        };

        $query->orderBy($sortField, $this->sortDirection);

        $grupos = $this->filtro_grado
            ? Grupo::where('grado_id', $this->filtro_grado)->orderBy('nombre')->get()
            : Grupo::with('grado')->orderBy('grado_id')->orderBy('nombre')->get();

        return view('livewire.catalogos.alumnos', [
            'alumnos' => $query->paginate(15),
            'grados' => Grado::orderBy('orden')->get(),
            'grupos' => $grupos,
        ]);
    }

    public function crear()
    {
        $this->resetModal();
        $this->matricula = 'ALU'.now()->format('y').str_pad((Alumno::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);
        $this->showModal = true;
    }

    public function editar($id)
    {
        $alumno = Alumno::with('persona')->findOrFail($id);
        $persona = $alumno->persona;

        $this->editId = $alumno->id;
        $this->nombre = $persona->nombre;
        $this->apellido_paterno = $persona->apellido_paterno;
        $this->apellido_materno = $persona->apellido_materno;
        $this->curp = $persona->curp;
        $this->telefono = $persona->telefono;
        $this->grado_id = $alumno->grado_id;
        $this->grupo_id = $alumno->grupo_id;
        $this->matricula = $alumno->matricula;
        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        DB::transaction(function () {
            if ($this->editId) {
                $alumno = Alumno::findOrFail($this->editId);
                $persona = $alumno->persona;

                $persona->update([
                    'nombre' => $this->nombre,
                    'apellido_paterno' => $this->apellido_paterno,
                    'apellido_materno' => $this->apellido_materno,
                    'curp' => $this->curp ?: null,
                    'telefono' => $this->telefono ?: null,
                ]);

                $alumno->update([
                    'grado_id' => $this->grado_id,
                    'grupo_id' => $this->grupo_id ?: null,
                    'matricula' => $this->matricula,
                ]);
            } else {
                $grupo = $this->grupo_id ? Grupo::find($this->grupo_id) : null;

                $persona = Persona::create([
                    'nombre' => $this->nombre,
                    'apellido_paterno' => $this->apellido_paterno,
                    'apellido_materno' => $this->apellido_materno ?: null,
                    'curp' => $this->curp ?: null,
                    'telefono' => $this->telefono ?: null,
                ]);

                Alumno::create([
                    'persona_id' => $persona->id,
                    'grado_id' => $this->grado_id,
                    'grupo_id' => $grupo?->id,
                    'ciclo_escolar_id' => $grupo?->ciclo_escolar_id,
                    'matricula' => $this->matricula,
                    'estatus' => 'activo',
                ]);
            }
        });

        $this->dispatch('toast', message: 'Alumno guardado exitosamente.', type: 'success');
        $this->resetModal();
    }

    public function darBaja($id)
    {
        $alumno = Alumno::findOrFail($id);
        $alumno->update(['estatus' => 'baja']);
        $this->dispatch('toast', message: 'Alumno dado de baja.', type: 'success');
    }

    public function darEgreso($id)
    {
        $alumno = Alumno::findOrFail($id);
        $alumno->update(['estatus' => 'egresado']);
        $this->dispatch('toast', message: 'Alumno marcado como egresado.', type: 'success');
    }

    public function reactivar($id)
    {
        $alumno = Alumno::findOrFail($id);
        $alumno->update(['estatus' => 'activo']);
        $this->dispatch('toast', message: 'Alumno reactivado.', type: 'success');
    }

    public function resetModal()
    {
        $this->showModal = false;
        $this->editId = null;
        $this->nombre = '';
        $this->apellido_paterno = '';
        $this->apellido_materno = '';
        $this->curp = '';
        $this->telefono = '';
        $this->grado_id = '';
        $this->grupo_id = '';
        $this->matricula = '';
    }
}
