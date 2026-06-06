<?php

namespace App\Livewire\Catalogos;

use App\Models\Alumno;
use App\Models\AlumnoFamilia;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Alumnos extends Component
{
    use WithPagination;

    public $showModal = false;

    public $editId = null;

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

    // ─── Family / Parents / Tutors ───

    public $showFamilia = false;

    public $tipo_registro = 'padres'; // 'padres' | 'tutor_legal'

    public $tutor_designado = 'padre1'; // 'padre1' | 'padre2' | 'tutor_legal'

    // Parent 1
    public $p1_nombre = '';

    public $p1_apellido_paterno = '';

    public $p1_apellido_materno = '';

    public $p1_parentesco = 'Padre';

    public $p1_telefono = '';

    public $p1_telefono_2 = '';

    public $p1_email = '';

    public $p1_fecha_nacimiento = '';

    public $p1_domicilio = '';

    // Parent 2 (optional)
    public $p2_activo = false;

    public $p2_nombre = '';

    public $p2_apellido_paterno = '';

    public $p2_apellido_materno = '';

    public $p2_parentesco = 'Madre';

    public $p2_telefono = '';

    public $p2_telefono_2 = '';

    public $p2_email = '';

    public $p2_fecha_nacimiento = '';

    public $p2_domicilio = '';

    // Tutor legal
    public $tl_nombre = '';

    public $tl_apellido_paterno = '';

    public $tl_apellido_materno = '';

    public $tl_telefono = '';

    public $tl_telefono_2 = '';

    public $tl_email = '';

    public $tl_fecha_nacimiento = '';

    public $tl_domicilio = '';

    // Generated credentials (shown after save)
    public $credenciales = null;

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
            'telefono' => 'nullable|string|max:20',
            'grado_id' => 'required|exists:grados,id',
            'matricula' => 'required|string|max:20|unique:alumnos,matricula,'.$alumnoId,
        ];

        // Family validation when showFamilia is active
        if ($this->showFamilia) {
            if ($this->tipo_registro === 'padres') {
                $rules = array_merge($rules, [
                    'p1_nombre' => 'required|string|max:100',
                    'p1_apellido_paterno' => 'required|string|max:100',
                    'p1_apellido_materno' => 'nullable|string|max:100',
                    'p1_parentesco' => 'required|in:Padre,Madre',
                    'p1_telefono' => 'nullable|string|max:20',
                    'p1_email' => 'nullable|email|max:100',
                    'p1_fecha_nacimiento' => 'nullable|date',
                    'p2_nombre' => 'required_if:p2_activo,true|string|max:100',
                    'p2_apellido_paterno' => 'required_if:p2_activo,true|string|max:100',
                    'p2_apellido_materno' => 'nullable|string|max:100',
                    'p2_parentesco' => 'required_if:p2_activo,true|in:Padre,Madre',
                    'p2_telefono' => 'nullable|string|max:20',
                    'p2_email' => 'nullable|email|max:100',
                    'p2_fecha_nacimiento' => 'nullable|date',
                    'tutor_designado' => 'required|in:padre1,padre2',
                ]);
            } else {
                $rules = array_merge($rules, [
                    'tl_nombre' => 'required|string|max:100',
                    'tl_apellido_paterno' => 'required|string|max:100',
                    'tl_apellido_materno' => 'nullable|string|max:100',
                    'tl_telefono' => 'nullable|string|max:20',
                    'tl_email' => 'nullable|email|max:100',
                    'tl_fecha_nacimiento' => 'nullable|date',
                ]);
            }
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
            'grados' => Grado::orderBy('nombre')->get(),
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

        // Load family data
        $familiares = $alumno->familiares;

        if ($familiares->isNotEmpty()) {
            $this->showFamilia = true;

            // Separate parents from legal tutor
            $padres = $familiares->whereIn('parentesco', ['Padre', 'Madre']);
            $tutorLegal = $familiares->where('parentesco', 'Tutor')->first();

            if ($tutorLegal && $padres->isEmpty()) {
                // Tutor legal mode
                $this->tipo_registro = 'tutor_legal';
                $this->tutor_designado = 'tutor_legal';
                $this->cargarPersonaAProperties($tutorLegal->persona, 'tl_');
            } else {
                // Parents mode
                $this->tipo_registro = 'padres';
                $padre1 = $padres->first();
                $padre2 = $padres->skip(1)->first();

                if ($padre1) {
                    $this->cargarPersonaAProperties($padre1->persona, 'p1_');
                    $this->p1_parentesco = $padre1->parentesco;
                }

                if ($padre2) {
                    $this->p2_activo = true;
                    $this->cargarPersonaAProperties($padre2->persona, 'p2_');
                    $this->p2_parentesco = $padre2->parentesco;
                }

                // Determine which parent is the designated tutor (has user account)
                $tutor = $padres->first(fn ($f) => $f->persona->user);
                if ($tutor) {
                    $this->tutor_designado = $tutor->is($padre1) ? 'padre1' : 'padre2';
                }
            }
        }

        $this->credenciales = null;
        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        $this->credenciales = null;

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

                // Delete existing family records to rebuild them
                $alumno->familiares()->delete();
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

            // Save family data
            if ($this->showFamilia) {
                $this->guardarFamilia($alumno);
            }
        });

        $message = 'Alumno guardado exitosamente.';
        if ($this->credenciales) {
            $message .= ' Cuenta de tutor creada — Usuario: '.$this->credenciales['email'].' / Contraseña: '.$this->credenciales['password'];
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->resetModal();
    }

    /**
     * Save family members and create tutor account if needed.
     */
    private function guardarFamilia(Alumno $alumno): void
    {
        $tutorPersona = null;

        if ($this->tipo_registro === 'padres') {
            // Parent 1
            $padre1 = Persona::create([
                'nombre' => $this->p1_nombre,
                'apellido_paterno' => $this->p1_apellido_paterno,
                'apellido_materno' => $this->p1_apellido_materno ?: null,
                'telefono' => $this->p1_telefono ?: null,
                'telefono_2' => $this->p1_telefono_2 ?: null,
                'email' => $this->p1_email ?: null,
                'fecha_nacimiento' => $this->p1_fecha_nacimiento ?: null,
                'domicilio' => $this->p1_domicilio ?: null,
            ]);

            AlumnoFamilia::create([
                'alumno_id' => $alumno->id,
                'persona_id' => $padre1->id,
                'parentesco' => $this->p1_parentesco,
            ]);

            if ($this->tutor_designado === 'padre1') {
                $tutorPersona = $padre1;
            }

            // Parent 2 (optional)
            if ($this->p2_activo) {
                $padre2 = Persona::create([
                    'nombre' => $this->p2_nombre,
                    'apellido_paterno' => $this->p2_apellido_paterno,
                    'apellido_materno' => $this->p2_apellido_materno ?: null,
                    'telefono' => $this->p2_telefono ?: null,
                    'telefono_2' => $this->p2_telefono_2 ?: null,
                    'email' => $this->p2_email ?: null,
                    'fecha_nacimiento' => $this->p2_fecha_nacimiento ?: null,
                    'domicilio' => $this->p2_domicilio ?: null,
                ]);

                AlumnoFamilia::create([
                    'alumno_id' => $alumno->id,
                    'persona_id' => $padre2->id,
                    'parentesco' => $this->p2_parentesco,
                ]);

                if ($this->tutor_designado === 'padre2') {
                    $tutorPersona = $padre2;
                }
            }
        } else {
            // Tutor legal
            $tutorPersona = Persona::create([
                'nombre' => $this->tl_nombre,
                'apellido_paterno' => $this->tl_apellido_paterno,
                'apellido_materno' => $this->tl_apellido_materno ?: null,
                'telefono' => $this->tl_telefono ?: null,
                'telefono_2' => $this->tl_telefono_2 ?: null,
                'email' => $this->tl_email ?: null,
                'fecha_nacimiento' => $this->tl_fecha_nacimiento ?: null,
                'domicilio' => $this->tl_domicilio ?: null,
            ]);

            AlumnoFamilia::create([
                'alumno_id' => $alumno->id,
                'persona_id' => $tutorPersona->id,
                'parentesco' => 'Tutor',
            ]);
        }

        // Auto-create User account for the designated tutor if they don't have one
        if ($tutorPersona && ! $tutorPersona->user) {
            $this->crearCuentaTutor($tutorPersona);
        }
    }

    /**
     * Create a User account for a tutor and assign Tutor role.
     */
    private function crearCuentaTutor(Persona $persona): void
    {
        $password = Str::password(10);

        // Generate a unique email based on persona data
        $baseEmail = $persona->email
            ? $persona->email
            : Str::slug($persona->nombre.'.'.$persona->apellido_paterno).'@tutor.local';

        $email = $baseEmail;
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $email = pathinfo($baseEmail, PATHINFO_FILENAME).$counter.'@tutor.local';
            $counter++;
        }

        $user = User::create([
            'name' => trim("{$persona->nombre} {$persona->apellido_paterno}"),
            'email' => $email,
            'password' => bcrypt($password),
            'persona_id' => $persona->id,
            'email_verified_at' => now(),
        ]);

        $user->assignRole('Tutor');

        $this->credenciales = [
            'email' => $email,
            'password' => $password,
        ];
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

    public function updatedTipoRegistro(): void
    {
        // Reset family fields when changing registration type
        $this->reset([
            'p1_nombre', 'p1_apellido_paterno', 'p1_apellido_materno',
            'p1_telefono', 'p1_telefono_2', 'p1_email',
            'p1_fecha_nacimiento', 'p1_domicilio',
            'p2_nombre', 'p2_apellido_paterno', 'p2_apellido_materno',
            'p2_telefono', 'p2_telefono_2', 'p2_email',
            'p2_fecha_nacimiento', 'p2_domicilio', 'p2_activo',
            'tl_nombre', 'tl_apellido_paterno', 'tl_apellido_materno',
            'tl_telefono', 'tl_telefono_2', 'tl_email',
            'tl_fecha_nacimiento', 'tl_domicilio',
        ]);

        if ($this->tipo_registro === 'tutor_legal') {
            $this->tutor_designado = 'tutor_legal';
        } else {
            $this->tutor_designado = 'padre1';
        }
    }

    public function agregarPadre2(): void
    {
        $this->p2_activo = true;
    }

    public function quitarPadre2(): void
    {
        $this->p2_activo = false;
        $this->reset([
            'p2_nombre', 'p2_apellido_paterno', 'p2_apellido_materno',
            'p2_parentesco', 'p2_telefono', 'p2_telefono_2', 'p2_email',
            'p2_fecha_nacimiento', 'p2_domicilio',
        ]);

        if ($this->tutor_designado === 'padre2') {
            $this->tutor_designado = 'padre1';
        }
    }

    public function resetModal()
    {
        $this->showModal = false;
        $this->editId = null;
        $this->reset([
            'nombre', 'apellido_paterno', 'apellido_materno', 'curp', 'telefono',
            'grado_id', 'grupo_id', 'matricula',
            'showFamilia', 'tipo_registro', 'tutor_designado',
            'p1_nombre', 'p1_apellido_paterno', 'p1_apellido_materno',
            'p1_parentesco', 'p1_telefono', 'p1_telefono_2', 'p1_email',
            'p1_fecha_nacimiento', 'p1_domicilio',
            'p2_activo', 'p2_nombre', 'p2_apellido_paterno', 'p2_apellido_materno',
            'p2_parentesco', 'p2_telefono', 'p2_telefono_2', 'p2_email',
            'p2_fecha_nacimiento', 'p2_domicilio',
            'tl_nombre', 'tl_apellido_paterno', 'tl_apellido_materno',
            'tl_telefono', 'tl_telefono_2', 'tl_email',
            'tl_fecha_nacimiento', 'tl_domicilio',
            'credenciales',
        ]);
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
