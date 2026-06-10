<?php

namespace App\Livewire\Catalogos;

use App\Models\Alumno;
use App\Models\AlumnoFamilia;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class PadresFamilia extends Component
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

    public $telefono_2 = '';

    public $email = '';

    public $fecha_nacimiento = '';

    public $domicilio = '';

    // Parentesco predeterminado
    public $parentesco = 'Padre';

    // Vincular a alumno
    public $alumno_id = '';

    // Alumnos vinculados (para edición)
    public array $vinculos = [];

    // Crear cuenta de tutor
    public $crear_cuenta = false;

    public $password = '';

    public $password_confirmation = '';

    public string $sortField = 'apellido_paterno';

    public string $sortDirection = 'asc';

    public string $search = '';

    protected function rules()
    {
        $personaId = $this->editId;

        return [
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'curp' => ['nullable', 'string', 'max:18', Rule::unique('personas', 'curp')->ignore($personaId)],
            'telefono' => 'nullable|digits:10',
            'telefono_2' => 'nullable|digits:10',
            'email' => 'nullable|email|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'domicilio' => 'nullable|string|max:500',
            'parentesco' => 'required|in:Padre,Madre,Tutor',
            'alumno_id' => 'nullable|exists:alumnos,id',
            'crear_cuenta' => 'boolean',
            'password' => $this->crear_cuenta && ! $this->editId ? 'required|min:8|confirmed' : 'nullable|min:8|confirmed',
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

        $alumnos = Alumno::with('persona')
            ->join('personas', 'alumnos.persona_id', '=', 'personas.id')
            ->orderBy('personas.apellido_paterno')
            ->orderBy('personas.nombre')
            ->select('alumnos.*')
            ->get();

        return view('livewire.catalogos.padres-familia', [
            'padres' => $padres,
            'alumnos' => $alumnos,
        ]);
    }

    public function crear()
    {
        $this->resetModal();
        $this->showModal = true;
    }

    public function editar($id)
    {
        $persona = Persona::with(['familiares.alumno.persona', 'user'])->findOrFail($id);

        $this->editId = $persona->id;
        $this->nombre = $persona->nombre;
        $this->apellido_paterno = $persona->apellido_paterno;
        $this->apellido_materno = $persona->apellido_materno ?? '';
        $this->curp = $persona->curp ?? '';
        $this->telefono = $persona->telefono ?? '';
        $this->telefono_2 = $persona->telefono_2 ?? '';
        $this->email = $persona->email ?? '';
        $this->fecha_nacimiento = $persona->fecha_nacimiento?->format('Y-m-d') ?? '';
        $this->domicilio = $persona->domicilio ?? '';

        // Cargar vínculos actuales
        $this->vinculos = $persona->familiares->map(function ($f) {
            return [
                'id' => $f->id,
                'alumno_id' => $f->alumno_id,
                'alumno_nombre' => $f->alumno->persona->nombreCompleto(),
                'parentesco' => $f->parentesco,
            ];
        })->toArray();

        // Primer parentesco como predeterminado
        $this->parentesco = $persona->familiares->first()->parentesco ?? 'Padre';

        // Cuenta de usuario existente
        $this->crear_cuenta = $persona->user !== null;

        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        DB::transaction(function () {
            $personaData = [
                'nombre' => $this->nombre,
                'apellido_paterno' => $this->apellido_paterno,
                'apellido_materno' => $this->apellido_materno ?: null,
                'curp' => $this->curp ?: null,
                'telefono' => $this->telefono ?: null,
                'telefono_2' => $this->telefono_2 ?: null,
                'email' => $this->email ?: null,
                'fecha_nacimiento' => $this->fecha_nacimiento ?: null,
                'domicilio' => $this->domicilio ?: null,
            ];

            if ($this->editId) {
                $persona = Persona::findOrFail($this->editId);
                $persona->update($personaData);
            } else {
                $persona = Persona::create($personaData);
            }

            // Vincular alumno si se seleccionó (creación únicamente)
            if (! $this->editId && $this->alumno_id) {
                AlumnoFamilia::create([
                    'alumno_id' => $this->alumno_id,
                    'persona_id' => $persona->id,
                    'parentesco' => $this->parentesco,
                ]);
            }

            // Manejar cuenta de usuario
            $this->manejarCuentaUsuario($persona);
        });

        $this->dispatch('toast', message: $this->editId ? 'Padre de familia actualizado exitosamente.' : 'Padre de familia guardado exitosamente.', type: 'success');
        $this->resetModal();
    }

    protected function manejarCuentaUsuario(Persona $persona): void
    {
        if ($this->crear_cuenta) {
            $user = $persona->user;

            if ($user) {
                // Actualizar contraseña si se proporcionó
                if ($this->password) {
                    $user->update([
                        'password' => Hash::make($this->password),
                    ]);
                }
            } else {
                // Crear nueva cuenta
                $userEmail = $persona->email ?? fake()->unique()->safeEmail();
                $user = User::create([
                    'name' => "{$persona->nombre} {$persona->apellido_paterno}",
                    'email' => $userEmail,
                    'password' => Hash::make($this->password ?: fake()->regexify('[A-Za-z0-9]{10}')),
                    'persona_id' => $persona->id,
                ]);
                $user->assignRole('Tutor');
            }
        } elseif (! $this->crear_cuenta && $persona->user && ! $this->editId) {
            // Solo en creación: si no se marcó cuenta, no se crea (nada que hacer)
            // En edición: si se desmarcó, no se elimina la cuenta existente
        }
    }

    public function agregarVinculo()
    {
        $this->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'parentesco' => 'required|in:Padre,Madre,Tutor',
        ]);

        $alumno = Alumno::with('persona')->find($this->alumno_id);

        // Verificar si ya está vinculado
        $yaVinculado = collect($this->vinculos)->contains('alumno_id', (int) $this->alumno_id);

        if ($yaVinculado) {
            $this->dispatch('toast', message: 'El alumno ya está vinculado a este padre/tutor.', type: 'warning');

            return;
        }

        $this->vinculos[] = [
            'alumno_id' => (int) $this->alumno_id,
            'alumno_nombre' => $alumno->persona->nombreCompleto(),
            'parentesco' => $this->parentesco,
        ];

        $this->alumno_id = '';
    }

    public function quitarVinculo($index)
    {
        // Si tiene ID persistido, eliminar de BD
        $vinculo = $this->vinculos[$index] ?? null;

        if ($vinculo && isset($vinculo['id'])) {
            AlumnoFamilia::find($vinculo['id'])?->delete();
        }

        unset($this->vinculos[$index]);
        $this->vinculos = array_values($this->vinculos);
    }

    public function guardarVinculos()
    {
        if (! $this->editId) {
            return;
        }

        $persona = Persona::findOrFail($this->editId);

        foreach ($this->vinculos as $vinculo) {
            // Si no tiene ID, es nuevo — crear
            if (! isset($vinculo['id'])) {
                AlumnoFamilia::create([
                    'alumno_id' => $vinculo['alumno_id'],
                    'persona_id' => $persona->id,
                    'parentesco' => $vinculo['parentesco'],
                ]);
            }
        }

        // Recargar vínculos actualizados
        $persona->load('familiares.alumno.persona');
        $this->vinculos = $persona->familiares->map(function ($f) {
            return [
                'id' => $f->id,
                'alumno_id' => $f->alumno_id,
                'alumno_nombre' => $f->alumno->persona->nombreCompleto(),
                'parentesco' => $f->parentesco,
            ];
        })->toArray();

        $this->dispatch('toast', message: 'Vínculos guardados exitosamente.', type: 'success');
    }

    public function eliminar($id)
    {
        $persona = Persona::with('familiares')->findOrFail($id);

        // Solo desvincular: eliminar registros de alumno_familia
        $persona->familiares()->delete();

        // Si tiene cuenta de usuario con rol Tutor, la conservamos
        // (no se elimina la persona ni el usuario)

        $this->dispatch('toast', message: 'Padre de familia desvinculado exitosamente.', type: 'success');
    }

    public function resetModal()
    {
        $this->showModal = false;
        $this->editId = null;
        $this->reset([
            'nombre', 'apellido_paterno', 'apellido_materno', 'curp',
            'telefono', 'telefono_2', 'email', 'fecha_nacimiento', 'domicilio',
            'parentesco', 'alumno_id', 'vinculos',
            'crear_cuenta', 'password', 'password_confirmation',
        ]);
        $this->parentesco = 'Padre';
        $this->vinculos = [];
    }
}
