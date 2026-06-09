<?php

namespace App\Livewire\Catalogos;

use App\Models\Calificacion;
use App\Models\CalificacionLog;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\PeriodoEvaluacion;
use Livewire\Component;

class Calificaciones extends Component
{
    public $grupo_id = '';

    public $materia_id = '';

    public $periodo_id = '';

    /** @var array<int, string> [alumno_id => calificacion] */
    public $notas = [];

    public $alumnos = [];

    public $cargado = false;

    public function render()
    {
        $user = auth()->user();

        // Docente solo ve sus grupos, el resto ve todos
        $grupos = $user->hasRole('Docente')
            ? Grupo::where('docente_id', $user->id)->with('grado', 'cicloEscolar')->orderBy('grado_id')->get()
            : Grupo::with('grado', 'cicloEscolar')->orderBy('grado_id')->get();

        $materias = $this->grupo_id
            ? Materia::where('grado_id', Grupo::find($this->grupo_id)?->grado_id)
                ->orderBy('nombre')
                ->get()
            : collect();

        $periodos = $this->grupo_id
            ? PeriodoEvaluacion::where('ciclo_escolar_id', Grupo::find($this->grupo_id)?->ciclo_escolar_id)
                ->orderBy('orden')
                ->get()
            : collect();

        return view('livewire.catalogos.calificaciones', [
            'grupos' => $grupos,
            'materias' => $materias,
            'periodos' => $periodos,
        ]);
    }

    public function updatedGrupoId(): void
    {
        $this->resetSeleccion();
    }

    public function updatedMateriaId(): void
    {
        $this->resetNotas();
    }

    public function updatedPeriodoId(): void
    {
        $this->resetNotas();
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
            ->where('alumnos.estatus', 'activo')
            ->with('persona')
            ->join('personas', 'alumnos.persona_id', '=', 'personas.id')
            ->orderBy('personas.apellido_paterno')
            ->orderBy('personas.apellido_materno')
            ->orderBy('personas.nombre')
            ->select('alumnos.*')
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
            'notas.*' => 'nullable|numeric|min:0|max:10',
        ]);

        $userId = auth()->id();

        foreach ($this->notas as $alumnoId => $calificacion) {
            $calificacion = $calificacion === '' ? null : (float) $calificacion;

            $existente = Calificacion::where('alumno_id', $alumnoId)
                ->where('grupo_id', $this->grupo_id)
                ->where('materia_id', $this->materia_id)
                ->where('periodo_evaluacion_id', $this->periodo_id)
                ->first();

            if ($existente) {
                $oldValue = $existente->calificacion;

                if ($oldValue != $calificacion) {
                    $existente->update(['calificacion' => $calificacion]);

                    CalificacionLog::create([
                        'calificacion_id' => $existente->id,
                        'user_id' => $userId,
                        'old_calificacion' => $oldValue,
                        'new_calificacion' => $calificacion,
                    ]);
                }
            } elseif ($calificacion !== null) {
                $nueva = Calificacion::create([
                    'alumno_id' => $alumnoId,
                    'grupo_id' => $this->grupo_id,
                    'materia_id' => $this->materia_id,
                    'periodo_evaluacion_id' => $this->periodo_id,
                    'calificacion' => $calificacion,
                ]);

                CalificacionLog::create([
                    'calificacion_id' => $nueva->id,
                    'user_id' => $userId,
                    'old_calificacion' => null,
                    'new_calificacion' => $calificacion,
                ]);
            }
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
