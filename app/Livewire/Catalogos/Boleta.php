<?php

namespace App\Livewire\Catalogos;

use App\Models\Alumno;
use App\Models\BoletaObservacion;
use App\Models\Calificacion;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\PeriodoEvaluacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Livewire\Component;

class Boleta extends Component
{
    public function mount(): void
    {
        $this->periodos = collect();
        $this->materias = collect();

        $user = auth()->user();

        if ($user->hasRole('Docente')) {
            $this->esDocente = true;
            $grupo = Grupo::where('docente_id', $user->id)->with('grado', 'cicloEscolar')->first();
            if ($grupo) {
                $this->grupoUnico = $grupo;
                $this->grupo_id = $grupo->id;
                $this->cargarAlumnosDelGrupo();
            }
        }
    }

    public $grupo_id = '';

    public $alumno_id = '';

    public $alumnos = [];

    public $cargado = false;

    public bool $esDocente = false;

    public ?Grupo $grupoUnico = null;

    /** @var array<string, mixed> */
    public $alumnoData = [];

    /** @var array<int, array<int, float|null>> [materia_id][periodo_id] => calificacion */
    public $calificaciones = [];

    /** @var Collection<int, Materia> */
    public Collection $materias;

    /** @var Collection<int, PeriodoEvaluacion> */
    public Collection $periodos;

    /** @var array */
    public $observaciones = [];

    /** @var array<int, float|null> [periodo_id => promedio] */
    public $promedios = [];

    public function render()
    {
        $user = auth()->user();

        // Docente: no necesita re-consultar grupos, ya lo hizo en mount
        $grupos = $this->esDocente
            ? collect()
            : Grupo::with('grado', 'cicloEscolar')->orderBy('grado_id')->get();

        return view('livewire.catalogos.boleta', [
            'grupos' => $grupos,
        ]);
    }

    public function cargarAlumnosDelGrupo(): void
    {
        $this->resetCarga();
        $this->alumno_id = '';
        $this->alumnos = [];

        if ($this->grupo_id) {
            $grupo = Grupo::with('grado')->find($this->grupo_id);
            if ($grupo) {
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
            }
        }
    }

    public function updatedGrupoId(): void
    {
        $this->cargarAlumnosDelGrupo();
    }

    public function updatedAlumnoId(): void
    {
        $this->resetCarga();
    }

    public function cargar()
    {
        $this->validate([
            'alumno_id' => 'required|exists:alumnos,id',
        ]);

        $alumno = Alumno::with('persona', 'grado', 'grupo', 'grupo.cicloEscolar')
            ->findOrFail($this->alumno_id);

        $this->alumnoData = $alumno->toArray();

        // Periodos del ciclo escolar del alumno
        $this->periodos = PeriodoEvaluacion::where('ciclo_escolar_id', $alumno->ciclo_escolar_id)
            ->orderBy('orden')
            ->get();

        // Materias del grado del alumno
        $this->materias = Materia::where('grado_id', $alumno->grado_id)
            ->orderBy('nombre')
            ->get();

        // Calificaciones
        $query = Calificacion::where('alumno_id', $alumno->id)
            ->where('grupo_id', $alumno->grupo_id);

        $notas = $query->get();

        // Build matrix [materia_id][periodo_id] => calificacion
        $matrix = [];
        foreach ($this->materias as $materia) {
            $matrix[$materia->id] = [];
            foreach ($this->periodos as $periodo) {
                $nota = $notas->firstWhere(fn ($n) => $n->materia_id === $materia->id && $n->periodo_evaluacion_id === $periodo->id
                );
                $matrix[$materia->id][$periodo->id] = $nota?->calificacion;
            }
        }
        $this->calificaciones = $matrix;

        // Promedios por periodo
        $promedios = [];
        foreach ($this->periodos as $periodo) {
            $notasPeriodo = $notas->where('periodo_evaluacion_id', $periodo->id)->pluck('calificacion')->filter();
            $promedios[$periodo->id] = $notasPeriodo->count() > 0 ? round($notasPeriodo->avg(), 1) : null;
        }
        $this->promedios = $promedios;

        // Observaciones
        $obsQuery = BoletaObservacion::where('alumno_id', $alumno->id)
            ->where('grupo_id', $alumno->grupo_id);

        $this->observaciones = $obsQuery->with('periodoEvaluacion')->get()->toArray();

        $this->cargado = true;
    }

    public function descargarPDF()
    {
        $this->cargar(); // asegurar datos actualizados

        $data = [
            'alumno' => $this->alumnoData,
            'materias' => $this->materias,
            'periodos' => $this->periodos,
            'calificaciones' => $this->calificaciones,
            'promedios' => $this->promedios,
            'observaciones' => $this->observaciones,
            'generated_at' => now()->format('d/m/Y H:i'),
            'periodoSeleccionado' => 'Todos los periodos',
        ];

        $pdf = Pdf::loadView('pdf.boleta', $data);

        $matricula = $this->alumnoData['matricula'] ?? 'alumno';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "boleta-{$matricula}.pdf"
        );
    }

    public function resetCarga(): void
    {
        $this->cargado = false;
        $this->alumnoData = [];
        $this->calificaciones = [];
        $this->observaciones = [];
        $this->promedios = [];
    }
}
