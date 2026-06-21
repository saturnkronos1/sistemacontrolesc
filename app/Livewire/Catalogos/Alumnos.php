<?php

namespace App\Livewire\Catalogos;

use App\Models\Alumno;
use App\Models\AlumnoFamilia;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Persona;
use App\Models\User;
use App\Support\CicloActivoService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Alumnos extends Component
{
    use WithPagination;

    public $showModal = false;

    public $editId = null;

    public int $modalKey = 0;

    // ─── Persona fields (alumno) ───

    public $nombre = '';

    public $apellido_paterno = '';

    public $apellido_materno = '';

    public $curp = '';

    public $telefono = '';

    // ─── Alumno fields ───

    public $grado_id = '';

    public $grupo_id = '';

    public $matricula = '';

    // ─── Tutor ───

    public $showFamilia = false;

    public $tutor_nombre = '';

    public $tutor_apellido_paterno = '';

    public $tutor_apellido_materno = '';

    public $tutor_parentesco = 'Padre';

    public $tutor_telefono = '';

    public $tutor_telefono_2 = '';

    public $tutor_email = '';

    public $tutor_fecha_nacimiento = '';

    public $tutor_domicilio = '';

    // Tutor user credentials (set by user in the form)

    public $tutor_user_email = '';

    public $tutor_user_password = '';

    // ─── Filters ───

    public $filtro_grado = '';

    public $filtro_grupo = '';

    public $filtro_estatus = '';

    public string $sortField = 'matricula';

    public string $sortDirection = 'asc';

    public string $search = '';

    protected function rules()
    {
        $alumnoId = $this->editId;

        $personaId = $alumnoId
            ? Persona::whereHas('alumnos', fn ($q) => $q->where('id', $alumnoId))->first()?->id
            : 'NULL';

        $rules = [
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'curp' => 'nullable|string|size:18|unique:personas,curp,'.$personaId.',id',
            'telefono' => 'nullable|digits:10',
            'grado_id' => 'required|exists:grados,id',
            'matricula' => 'required|string|max:20|unique:alumnos,matricula,'.$alumnoId,
        ];

        if ($this->showFamilia) {
            $passwordRules = ['nullable', 'string', 'min:8'];
            $userEmailUnique = '|unique:users,email';

            if ($alumnoId) {
                $alumno = Alumno::with('familiares.persona.user')->find($alumnoId);
                $existingUser = $alumno?->familiares->first()?->persona?->user;

                if ($existingUser) {
                    $userEmailUnique = '|unique:users,email,'.$existingUser->id;
                } else {
                    $passwordRules = ['required', 'string', 'min:8'];
                }
            } else {
                $passwordRules = ['required', 'string', 'min:8'];
            }

            $rules = array_merge($rules, [
                'tutor_nombre' => 'required|string|max:100',
                'tutor_apellido_paterno' => 'required|string|max:100',
                'tutor_apellido_materno' => 'nullable|string|max:100',
                'tutor_parentesco' => 'required|in:Padre,Madre,Abuelo/a,Hermana/o,Tutor Legal',
                'tutor_telefono' => 'nullable|digits:10',
                'tutor_telefono_2' => 'nullable|digits:10',
                'tutor_email' => 'nullable|email|max:100',
                'tutor_fecha_nacimiento' => 'nullable|date',
                'tutor_user_email' => 'required|email|max:255'.$userEmailUnique,
                'tutor_user_password' => $passwordRules,
            ]);
        }

        return $rules;
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

    #[Computed]
    public function grados(): Collection
    {
        return Grado::orderBy('nombre')->get();
    }

    #[Computed]
    public function grupos(): Collection
    {
        $cicloActivoId = app(CicloActivoService::class)->getId();

        return $this->filtro_grado
            ? Grupo::where('grado_id', $this->filtro_grado)
                ->where('ciclo_escolar_id', $cicloActivoId)
                ->orderBy('nombre')->get()
            : Grupo::with('grado')
                ->where('ciclo_escolar_id', $cicloActivoId)
                ->orderBy('grado_id')->orderBy('nombre')->get();
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
                    ->orWhere('personas.curp', 'like', "%{$this->search}%")
                    ->orWhere(DB::raw("CONCAT(personas.apellido_paterno, ' ', personas.apellido_materno, ' ', personas.nombre)"), 'like', "%{$this->search}%");
            });
        }

        $sortField = match ($this->sortField) {
            'nombre_completo' => 'personas.apellido_paterno',
            'curp' => 'personas.curp',
            'grado_id' => 'alumnos.grado_id',
            'grupo_id' => 'alumnos.grupo_id',
            'estatus' => 'alumnos.estatus',
            default => 'alumnos.'.$this->sortField,
        };

        $query->orderBy($sortField, $this->sortDirection);

        return view('livewire.catalogos.alumnos', [
            'alumnos' => $query->paginate(15),
            'grados' => $this->grados,
            'grupos' => $this->grupos,
        ]);
    }

    public function crear()
    {
        $this->resetForm();
        $this->matricula = 'ALU'.now()->format('y').str_pad((Alumno::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);
        $this->modalKey++;
        $this->showModal = true;
    }

    public function editar($id)
    {
        $alumno = Alumno::with('persona', 'familiares.persona.user')->findOrFail($id);
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

        // Load tutor data
        $familiar = $alumno->familiares->first();

        if ($familiar) {
            $this->showFamilia = true;
            $this->cargarPersonaAProperties($familiar->persona, 'tutor_');
            $this->tutor_parentesco = $familiar->parentesco;

            if ($familiar->persona?->user) {
                $this->tutor_user_email = $familiar->persona->user->email;
            }
        }

        $this->credenciales = null;
        $this->modalKey++;
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
                    'apellido_materno' => $this->apellido_materno ?: null,
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

                $alumno = Alumno::create([
                    'persona_id' => $persona->id,
                    'grado_id' => $this->grado_id,
                    'grupo_id' => $grupo?->id,
                    'ciclo_escolar_id' => $grupo?->ciclo_escolar_id,
                    'matricula' => $this->matricula,
                    'estatus' => 'activo',
                ]);
            }

            // Save tutor data
            if ($this->showFamilia) {
                $this->guardarFamilia($alumno);
            }
        });

        $this->dispatch('toast', message: 'Alumno guardado exitosamente.', type: 'success');
        $this->resetModal();
    }

    /**
     * Save the tutor and create/update their user account.
     */
    private function guardarFamilia(Alumno $alumno): void
    {
        // If editing, try to reuse existing tutor persona
        $tutorPersona = null;

        if ($this->editId) {
            $previousFamiliar = AlumnoFamilia::where('alumno_id', $alumno->id)->first();
            if ($previousFamiliar) {
                $tutorPersona = $previousFamiliar->persona;
            }
            // Delete all existing family links (will recreate below)
            $alumno->familiares()->delete();
        }

        $tutorData = [
            'nombre' => $this->tutor_nombre,
            'apellido_paterno' => $this->tutor_apellido_paterno,
            'apellido_materno' => $this->tutor_apellido_materno ?: null,
            'telefono' => $this->tutor_telefono ?: null,
            'telefono_2' => $this->tutor_telefono_2 ?: null,
            'email' => $this->tutor_email ?: null,
            'fecha_nacimiento' => $this->tutor_fecha_nacimiento ?: null,
            'domicilio' => $this->tutor_domicilio ?: null,
        ];

        if ($tutorPersona) {
            $tutorPersona->update($tutorData);
        } else {
            $tutorPersona = Persona::create($tutorData);
        }

        AlumnoFamilia::create([
            'alumno_id' => $alumno->id,
            'persona_id' => $tutorPersona->id,
            'parentesco' => $this->tutor_parentesco,
        ]);

        // Handle user account for the tutor
        if ($tutorPersona->user) {
            $userData = [
                'name' => trim("{$this->tutor_nombre} {$this->tutor_apellido_paterno}"),
                'email' => $this->tutor_user_email,
            ];
            if ($this->tutor_user_password) {
                $userData['password'] = bcrypt($this->tutor_user_password);
            }
            $tutorPersona->user->update($userData);
        } else {
            $user = User::create([
                'name' => trim("{$this->tutor_nombre} {$this->tutor_apellido_paterno}"),
                'email' => $this->tutor_user_email,
                'password' => bcrypt($this->tutor_user_password),
                'persona_id' => $tutorPersona->id,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('Tutor');
        }
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

    public function resetForm(): void
    {
        $this->editId = null;
        $this->reset([
            'nombre', 'apellido_paterno', 'apellido_materno', 'curp', 'telefono',
            'grado_id', 'grupo_id', 'matricula',
            'showFamilia',
            'tutor_nombre', 'tutor_apellido_paterno', 'tutor_apellido_materno',
            'tutor_parentesco', 'tutor_telefono', 'tutor_telefono_2',
            'tutor_email', 'tutor_fecha_nacimiento', 'tutor_domicilio',
            'tutor_user_email', 'tutor_user_password',
        ]);
    }

    public function resetModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    /**
     * Load a Persona's data into a property prefix.
     */
    private function cargarPersonaAProperties(?Persona $persona, string $prefix): void
    {
        if (! $persona) {
            return;
        }

        $this->{$prefix.'nombre'} = $persona->nombre ?? '';
        $this->{$prefix.'apellido_paterno'} = $persona->apellido_paterno ?? '';
        $this->{$prefix.'apellido_materno'} = $persona->apellido_materno ?? '';
        $this->{$prefix.'telefono'} = $persona->telefono ?? '';
        $this->{$prefix.'telefono_2'} = $persona->telefono_2 ?? '';
        $this->{$prefix.'email'} = $persona->email ?? '';
        $this->{$prefix.'fecha_nacimiento'} = $persona->fecha_nacimiento?->format('Y-m-d') ?? '';
        $this->{$prefix.'domicilio'} = $persona->domicilio ?? '';
    }
}
