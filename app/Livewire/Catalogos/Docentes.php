<?php

namespace App\Livewire\Catalogos;

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

    public $name = '';

    public $email = '';

    public $password = '';

    public $password_confirmation = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public string $search = '';

    protected function rules()
    {
        $userId = $this->editId;

        return [
            'name' => 'required|string|max:255',
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
        $query = User::role('Docente')->with('roles');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.catalogos.docentes', [
            'docentes' => $query->orderBy($this->sortField, $this->sortDirection)->paginate(15),
        ]);
    }

    public function crear()
    {
        $this->resetModal();
        $this->showModal = true;
    }

    public function editar($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $this->editId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editId) {
            $user = User::findOrFail($this->editId);
            $user->update($data);
        } else {
            $data['password'] = Hash::make($this->password);
            $user = User::create($data);
        }

        // Always assign/reassign the Docente role
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

    public function resetModal()
    {
        $this->showModal = false;
        $this->editId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
    }
}
