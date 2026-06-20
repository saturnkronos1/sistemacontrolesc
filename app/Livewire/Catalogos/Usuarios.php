<?php

namespace App\Livewire\Catalogos;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Usuarios extends Component
{
    use WithFileUploads, WithPagination;

    public $showModal = false;

    public $editId = null;

    public int $modalKey = 0;

    public $name = '';

    public $email = '';

    public $password = '';

    public $password_confirmation = '';

    public $rol = '';

    public $foto_perfil = null;

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public string $search = '';

    public string $rolFiltro = '';

    protected function rules()
    {
        $userId = $this->editId;

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => $userId ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed',
            'rol' => 'required|string|exists:roles,name',
            'foto_perfil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
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

    public function updatedRolFiltro(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = User::with('roles');

        // Filtro por rol
        if ($this->rolFiltro) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $this->rolFiltro));
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        // Ordenación especial para el campo 'rol' (requiere join con roles)
        if ($this->sortField === 'rol') {
            $query
                ->leftJoin('model_has_roles', function ($join) {
                    $join->on('users.id', '=', 'model_has_roles.model_id')
                        ->where('model_has_roles.model_type', '=', 'App\Models\User');
                })
                ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->orderBy('roles.name', $this->sortDirection)
                ->select('users.*')
                ->groupBy('users.id');
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return view('livewire.catalogos.usuarios', [
            'usuarios' => $query->paginate(10),
            'roles' => Role::all()->pluck('name'),
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
        $user = User::with('roles')->findOrFail($id);
        $this->editId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->rol = $user->roles->first()?->name ?? '';
        $this->modalKey++;
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
            // For new users, password is required (validated above)
            $data['password'] = Hash::make($this->password);
            $user = User::create($data);

            // Create a personal team for the new user
            $team = Team::factory()->personal()->create([
                'name' => $user->name."'s Team",
            ]);

            $team->members()->attach($user, [
                'role' => TeamRole::Owner->value,
            ]);

            $user->switchTeam($team);
        }

        // Sync role
        $user->syncRoles([$this->rol]);

        // Handle photo upload
        if ($this->foto_perfil) {
            $path = $this->foto_perfil->store('fotos', 'public');
            $user->forceFill(['foto_perfil' => $path])->save();
        }

        $this->dispatch('toast', message: 'Usuario guardado exitosamente.', type: 'success');
        $this->resetModal();
    }

    public function eliminar($id)
    {
        // Prevent deleting yourself
        if ((int) $id === (int) auth()->id()) {
            $this->dispatch('toast', message: 'No puedes eliminar tu propio usuario.', type: 'error');

            return;
        }

        User::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Usuario eliminado.', type: 'success');
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->rol = '';
        $this->foto_perfil = null;
    }

    public function resetModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }
}
