<?php

namespace App\Livewire\Catalogos\Concerns;

use App\Models\Alumno;
use App\Models\AlumnoFamilia;
use App\Models\Grupo;
use App\Models\Persona;
use App\Models\User;
use App\Support\CicloActivoService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

trait PadreFormTrait
{
    public $showModal = false;

    public $editId = null;

    public int $modalKey = 0;

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
    public $grupo_id = '';

    public $alumno_id = '';

    // Alumnos vinculados (para edición)
    public array $vinculos = [];

    // Crear cuenta de tutor
    public $crear_cuenta = false;

    public $password = '';

    public $password_confirmation = '';

    // ─── Reactive: cambiar grupo resetea alumno ───

    public function updatedGrupoId($value): void
    {
        $this->alumno_id = '';
    }

    // ─── Shared computed ───

    #[Computed]
    public function gruposLista(): Collection
    {
        $cicloId = app(CicloActivoService::class)->getId();

        return Grupo::query()
            ->when($cicloId, fn ($q) => $q->where('ciclo_escolar_id', $cicloId))
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();
    }

    #[Computed]
    public function alumnosPorGrupo(): Collection
    {
        if (! $this->grupo_id) {
            return collect();
        }

        return Alumno::with('persona')
            ->where('grupo_id', $this->grupo_id)
            ->join('personas', 'alumnos.persona_id', '=', 'personas.id')
            ->orderBy('personas.apellido_paterno')
            ->orderBy('personas.nombre')
            ->select('alumnos.*')
            ->get();
    }

    // ─── Uppercase hook ───

    public function updated($propertyName): void
    {
        $uppercase = [
            'nombre', 'apellido_paterno', 'apellido_materno', 'curp',
            'domicilio',
        ];

        if (in_array($propertyName, $uppercase, true)) {
            $this->$propertyName = mb_strtoupper($this->$propertyName);
        }
    }

    // ─── Rules ───

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

    // ─── Actions ───

    public function crear()
    {
        $this->resetForm();
        $this->modalKey++;
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

        $this->modalKey++;
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
        $this->onSaved();
    }

    /**
     * Hook called after a successful save. Override in subclasses.
     */
    protected function onSaved(): void
    {
        // no-op by default
    }

    protected function manejarCuentaUsuario(Persona $persona): void
    {
        if ($this->crear_cuenta) {
            $user = $persona->user;

            if ($user) {
                if ($this->password) {
                    $user->update([
                        'password' => Hash::make($this->password),
                    ]);
                }
            } else {
                $userEmail = $persona->email ?? fake()->unique()->safeEmail();
                $user = User::create([
                    'name' => "{$persona->nombre} {$persona->apellido_paterno}",
                    'email' => $userEmail,
                    'password' => Hash::make($this->password ?: fake()->regexify('[A-Za-z0-9]{10}')),
                    'persona_id' => $persona->id,
                ]);
                $user->assignRole('Tutor');
            }
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
            if (! isset($vinculo['id'])) {
                AlumnoFamilia::create([
                    'alumno_id' => $vinculo['alumno_id'],
                    'persona_id' => $persona->id,
                    'parentesco' => $vinculo['parentesco'],
                ]);
            }
        }

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
        $persona->familiares()->delete();

        $this->dispatch('toast', message: 'Padre de familia desvinculado exitosamente.', type: 'success');
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->reset([
            'nombre', 'apellido_paterno', 'apellido_materno', 'curp',
            'telefono', 'telefono_2', 'email', 'fecha_nacimiento', 'domicilio',
            'parentesco', 'grupo_id', 'alumno_id', 'vinculos',
            'crear_cuenta', 'password', 'password_confirmation',
        ]);
        $this->parentesco = 'Padre';
        $this->vinculos = [];
    }

    public function resetModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }
}
