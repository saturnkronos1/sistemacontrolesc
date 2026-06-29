<?php

namespace App\Livewire\Catalogos;

use App\Models\Calificacion;
use App\Models\CalificacionLog;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\PeriodoEvaluacion;
use App\Support\CicloActivoService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Calificaciones extends Component
{
    public $ciclo_escolar_id = '';

    public $grupo_id = '';

    public $materia_id = '';

    public $periodo_id = '';

    /** @var array<int, string> [alumno_id => calificacion] */
    public $notas = [];

    public $alumnos = [];

    public $cargado = false;

    public bool $esDocente = false;

    public ?Grupo $grupoUnico = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->esDocente = $user->hasRole('Docente');

        if ($this->esDocente) {
            $grupo = Grupo::where('docente_id', $user->id)->with('grado', 'cicloEscolar')->first();
            if ($grupo) {
                $this->grupoUnico = $grupo;
                $this->grupo_id = $grupo->id;
                $this->ciclo_escolar_id = $grupo->ciclo_escolar_id;
            }
        }
    }

    #[Computed]
    public function ciclosEscolares(): Collection
    {
        return app(CicloActivoService::class)->getAll();
    }

    #[Computed]
    public function grupos(): Collection
    {
        $user = auth()->user();
        $query = $user->hasRole('Docente')
            ? Grupo::where('docente_id', $user->id)
            : Grupo::query();

        if ($this->ciclo_escolar_id) {
            $query->where('ciclo_escolar_id', $this->ciclo_escolar_id);
        }

        return $query->with('grado', 'cicloEscolar')->orderBy('grado_id')->get();
    }

    #[Computed]
    public function grupo(): ?Grupo
    {
        return $this->grupo_id ? Grupo::find($this->grupo_id) : null;
    }

    #[Computed]
    public function materias(): Collection
    {
        $grupo = $this->grupo;

        return $grupo
            ? Materia::where('grado_id', $grupo->grado_id)->orderBy('nombre')->get()
            : collect();
    }

    #[Computed]
    public function periodos(): Collection
    {
        $grupo = $this->grupo;

        return $grupo
            ? PeriodoEvaluacion::where('ciclo_escolar_id', $grupo->ciclo_escolar_id)->orderBy('orden')->get()
            : collect();
    }

    public function render()
    {
        return view('livewire.catalogos.calificaciones', [
            'ciclosEscolares' => $this->ciclosEscolares,
            'grupos' => $this->grupos,
            'materias' => $this->materias,
            'periodos' => $this->periodos,
            'esDocente' => $this->esDocente,
            'grupoUnico' => $this->grupoUnico,
        ]);
    }

    public function updatedCicloEscolarId(): void
    {
        $this->grupo_id = '';
        $this->resetSeleccion();
    }

    public function updatedGrupoId(): void
    {
        $this->resetSeleccion();
    }

    public function updatedMateriaId(): void
    {
        $this->resetNotas();

        // Docente: auto-cargar alumnos al seleccionar materia (si ya tiene periodo)
        if ($this->esDocente && $this->materia_id && $this->periodo_id) {
            $this->cargarAlumnos();
        }
    }

    public function updatedPeriodoId(): void
    {
        $this->resetNotas();

        // Docente: auto-cargar alumnos al seleccionar periodo (si ya tiene materia)
        if ($this->esDocente && $this->periodo_id && $this->materia_id) {
            $this->cargarAlumnos();
        }
    }

    public function cargarAlumnos()
    {
        $this->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'materia_id' => 'required|exists:materias,id',
            'periodo_id' => 'required|exists:periodos_evaluacion,id',
        ]);

        $grupo = Grupo::with('grado')->findOrFail($this->grupo_id);

        // Cargar alumnos activos del grupo
        $this->alumnos = $grupo->alumnos()
            ->activosConPersona()
            ->get()
            ->toArray();

        // Cargar notas existentes
        $existentes = Calificacion::where('grupo_id', $this->grupo_id)
            ->where('materia_id', $this->materia_id)
            ->where('periodo_evaluacion_id', $this->periodo_id)
            ->get()
            ->keyBy('alumno_id');

        $this->notas = [];
        foreach ($this->alumnos as $alumno) {
            $this->notas[$alumno['id']] = $existentes->get($alumno['id'])?->calificacion ?? '';
        }

        $this->cargado = true;
    }

    public function guardar()
    {
        $this->validate([
            'notas.*' => [
                'nullable',
                'numeric',
                'min:6',
                'max:10',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
        ], [
            'notas.*.min' => 'Cada calificación debe ser al menos 6.0.',
            'notas.*.max' => 'Cada calificación debe ser máximo 10.0.',
            'notas.*.regex' => 'Cada calificación puede tener hasta 2 decimales (ej: 7.50, 8.25).',
        ]);

        $userId = auth()->id();

        // 1 query — no N+1
        $existentes = Calificacion::where('grupo_id', $this->grupo_id)
            ->where('materia_id', $this->materia_id)
            ->where('periodo_evaluacion_id', $this->periodo_id)
            ->get()
            ->keyBy('alumno_id');

        $nuevas = [];
        $logs = [];

        foreach ($this->notas as $alumnoId => $calificacion) {
            $calificacion = $calificacion === '' ? null : (float) $calificacion;

            /** @var Calificacion|null $existente */
            $existente = $existentes->get($alumnoId);

            if ($existente) {
                $oldValue = $existente->calificacion;

                if ($oldValue != $calificacion) {
                    $existente->update(['calificacion' => $calificacion]);

                    $logs[] = [
                        'calificacion_id' => $existente->id,
                        'user_id' => $userId,
                        'old_calificacion' => $oldValue,
                        'new_calificacion' => $calificacion,
                    ];
                }
            } elseif ($calificacion !== null) {
                $nuevas[] = [
                    'alumno_id' => $alumnoId,
                    'grupo_id' => $this->grupo_id,
                    'materia_id' => $this->materia_id,
                    'periodo_evaluacion_id' => $this->periodo_id,
                    'calificacion' => $calificacion,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Batch insert nuevas calificaciones
        if (! empty($nuevas)) {
            Calificacion::insert($nuevas);

            // Recuperar IDs insertados para logs
            $insertados = Calificacion::where('grupo_id', $this->grupo_id)
                ->where('materia_id', $this->materia_id)
                ->where('periodo_evaluacion_id', $this->periodo_id)
                ->whereIn('alumno_id', array_column($nuevas, 'alumno_id'))
                ->get(['id', 'alumno_id', 'calificacion']);

            foreach ($insertados as $model) {
                $logs[] = [
                    'calificacion_id' => $model->id,
                    'user_id' => $userId,
                    'old_calificacion' => null,
                    'new_calificacion' => $model->calificacion,
                ];
            }
        }

        // Batch insert logs
        if (! empty($logs)) {
            CalificacionLog::insert($logs);
        }

        $this->dispatch('toast', message: 'Calificaciones guardadas exitosamente.', type: 'success');

        // Recargar para reflejar cambios
        $this->cargarAlumnos();
    }

    public function resetSeleccion(): void
    {
        $this->materia_id = '';
        $this->periodo_id = '';
        $this->resetNotas();
    }

    public function resetNotas(): void
    {
        $this->notas = [];
        $this->alumnos = [];
        $this->cargado = false;
    }
}
