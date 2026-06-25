<?php

namespace App\Livewire\Catalogos;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Docentes extends Component
{
    use WithPagination;

    public $showModal = false;

    public $editId = null;

    public int $modalKey = 0;

    public $editPersonaId = null;

    // Persona fields
    public $curp = '';

    public $cedula = '';

    public $nombres = '';

    public $apellido_paterno = '';

    public $apellido_materno = '';

    public $telefono = '';

    public $correo = '';

    public $fecha_nacimiento = '';

    public $direccion = '';

    public $estatus = 'activo';

    // User fields
    public $email = '';

    public $password = '';

    public $password_confirmation = '';

    public string $sortField = 'apellido_paterno';

    public string $sortDirection = 'asc';

    public string $search = '';

    public function updated($propertyName): void
    {
        $uppercase = [
            'nombres', 'apellido_paterno', 'apellido_materno', 'curp',
            'direccion',
        ];

        if (in_array($propertyName, $uppercase, true)) {
            $this->$propertyName = mb_strtoupper($this->$propertyName);
        }
    }

    protected function rules()
    {
        $userId = $this->editId;
        $personaId = $this->editPersonaId;

        return [
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'curp' => ['nullable', 'string', 'size:18', Rule::unique('personas', 'curp')->ignore($personaId)],
            'cedula' => 'nullable|string|max:50',
            'telefono' => 'nullable|digits:10',
            'correo' => 'nullable|email|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'direccion' => 'nullable|string|max:500',
            'estatus' => 'required|in:activo,inactivo',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => $userId ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed',
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
        $query = User::role('Docente')
            ->select('users.*')
            ->with('persona')
            ->leftJoin('personas', 'users.persona_id', '=', 'personas.id');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('personas.nombre', 'like', "%{$this->search}%")
                    ->orWhere('personas.apellido_paterno', 'like', "%{$this->search}%")
                    ->orWhere('personas.apellido_materno', 'like', "%{$this->search}%")
                    ->orWhere('personas.curp', 'like', "%{$this->search}%")
                    ->orWhere('users.email', 'like', "%{$this->search}%");
            });
        }

        $sortColumn = match ($this->sortField) {
            'nombre' => 'personas.nombre',
            'curp' => 'personas.curp',
            'estatus' => 'personas.estatus',
            default => 'personas.apellido_paterno',
        };

        $query->orderBy($sortColumn, $this->sortDirection);

        return view('livewire.catalogos.docentes', [
            'docentes' => $query->paginate(15),
        ]);
    }

    public function crear()
    {
        $this->resetForm();
        $this->modalKey++;
        $this->showModal = true;
    }

    public function editar($id)
    {
        $user = User::with('persona')->findOrFail($id);
        $this->editId = $user->id;

        if ($user->persona) {
            $this->editPersonaId = $user->persona->id;
            $this->curp = $user->persona->curp ?? '';
            $this->cedula = $user->persona->cedula ?? '';
            $this->nombres = $user->persona->nombre;
            $this->apellido_paterno = $user->persona->apellido_paterno;
            $this->apellido_materno = $user->persona->apellido_materno ?? '';
            $this->telefono = $user->persona->telefono ?? '';
            $this->correo = $user->persona->email ?? '';
            $this->fecha_nacimiento = $user->persona->fecha_nacimiento?->format('Y-m-d') ?? '';
            $this->direccion = $user->persona->domicilio ?? '';
            $this->estatus = $user->persona->estatus ?? 'activo';
        }

        $this->email = $user->email;
        $this->modalKey++;
        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        $personaData = [
            'nombre' => $this->nombres,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno ?: null,
            'curp' => $this->curp ?: null,
            'cedula' => $this->cedula ?: null,
            'telefono' => $this->telefono ?: null,
            'email' => $this->correo ?: null,
            'fecha_nacimiento' => $this->fecha_nacimiento ?: null,
            'domicilio' => $this->direccion ?: null,
            'estatus' => $this->estatus,
        ];

        if ($this->editPersonaId) {
            $persona = Persona::findOrFail($this->editPersonaId);
            $persona->update($personaData);
        } else {
            $persona = Persona::create($personaData);
        }

        $userData = [
            'name' => $persona->nombreCompleto(),
            'email' => $this->email,
            'persona_id' => $persona->id,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        if ($this->editId) {
            $user = User::findOrFail($this->editId);
            $user->update($userData);
        } else {
            $userData['password'] = Hash::make($this->password);
            $user = User::create($userData);
        }

        $user->syncRoles(['Docente']);

        $this->dispatch('toast', message: 'Docente guardado exitosamente.', type: 'success');
        $this->resetModal();
    }

    public function eliminar($id)
    {
        if ((int) $id === (int) auth()->id()) {
            $this->dispatch('toast', message: 'No puedes eliminar tu propio usuario.', type: 'error');

            return;
        }

        User::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Docente eliminado.', type: 'success');
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->editPersonaId = null;
        $this->curp = '';
        $this->cedula = '';
        $this->nombres = '';
        $this->apellido_paterno = '';
        $this->apellido_materno = '';
        $this->telefono = '';
        $this->correo = '';
        $this->fecha_nacimiento = '';
        $this->direccion = '';
        $this->estatus = 'activo';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function resetModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }
}
