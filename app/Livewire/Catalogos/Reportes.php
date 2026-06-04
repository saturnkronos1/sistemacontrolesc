<?php

namespace App\Livewire\Catalogos;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\PeriodoEvaluacion;
use App\Models\Persona;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Livewire\Component;

class Reportes extends Component
{
    public string $reporte = 'concentrado';

    // ─── Filters comunes ───
    public $grupo_id = '';

    // Concentrado
    public $periodo_id = '';

    // Kardex
    public $alumno_id = '';

    // Inasistencias
    public $fecha_desde = '';

    public $fecha_hasta = '';

    // Alumnos por tutor
    public $search = '';

    // ─── Data ───
    public $cargado = false;

    /** @var array<int, array> */
    public $alumnos = [];

    /** @var Collection<int, Materia> */
    public Collection $materias;

    /** @var Collection<int, PeriodoEvaluacion> */
    public Collection $periodos;

    /** @var array<int, array<int, array<int, float|null>>> [alumno_id][materia_id][periodo_id] => calificacion */
    public $calificaciones = [];

    /** @var array<int, float|null> [alumno_id => promedio] */
    public $promedios = [];

    /** @var array */
    public $alumnoData = [];

    /** @var array */
    public $kardexData = [];

    /** @var array */
    public $inasistenciasData = [];

    /** @var array */
    public $tutoresData = [];

    public function mount(): void
    {
        $this->materias = collect();
        $this->periodos = collect();
    }

    public function render()
    {
        $user = auth()->user();

        $grupos = $user->hasRole('Docente')
            ? Grupo::where('docente_id', $user->id)->with('grado', 'cicloEscolar')->orderBy('grado_id')->get()
            : Grupo::with('grado', 'cicloEscolar')->orderBy('grado_id')->get();

        $periodos = collect();
        $materias = collect();
        $alumnosSelect = [];

        if ($this->grupo_id) {
            $grupo = Grupo::with('grado', 'cicloEscolar')->find($this->grupo_id);
            if ($grupo) {
                if (in_array($this->reporte, ['concentrado', 'kardex', 'inasistencias'])) {
                    $periodos = PeriodoEvaluacion::where('ciclo_escolar_id', $grupo->ciclo_escolar_id)
                        ->orderBy('orden')
                        ->get();
                }
                if ($this->reporte === 'concentrado') {
                    $materias = Materia::where('grado_id', $grupo->grado_id)
                        ->orderBy('nombre')
                        ->get();
                }
                if ($this->reporte === 'kardex') {
                    $alumnosSelect = $grupo->alumnos()
                        ->where('estatus', 'activo')
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

        return view('livewire.catalogos.reportes', [
            'grupos' => $grupos,
            'periodos' => $periodos,
            'materias' => $materias,
            'alumnosSelect' => $alumnosSelect,
        ]);
    }

    public function updatedReporte(): void
    {
        $this->resetCarga();
    }

    public function updatedGrupoId(): void
    {
        $this->resetCarga();
        $this->alumno_id = '';
        $this->periodo_id = '';
    }

    public function updatedSearch(): void
    {
        if ($this->reporte === 'alumnos-por-tutor' && mb_strlen($this->search) >= 2) {
            $this->cargarTutores();
        } elseif (mb_strlen($this->search) === 0) {
            $this->resetCarga();
        }
    }

    public function cargar()
    {
        match ($this->reporte) {
            'concentrado' => $this->cargarConcentrado(),
            'kardex' => $this->cargarKardex(),
            'inasistencias' => $this->cargarInasistencias(),
            'alumnos-por-tutor' => $this->cargarTutores(),
        };
    }

    // ─── Concentrado ───

    protected function cargarConcentrado(): void
    {
        $this->validate(['grupo_id' => 'required|exists:grupos,id']);

        $grupo = Grupo::with('grado', 'cicloEscolar')->findOrFail($this->grupo_id);

        $this->periodos = PeriodoEvaluacion::where('ciclo_escolar_id', $grupo->ciclo_escolar_id)
            ->orderBy('orden')
            ->get();

        $this->materias = Materia::where('grado_id', $grupo->grado_id)
            ->orderBy('nombre')
            ->get();

        $this->alumnos = $grupo->alumnos()
            ->where('estatus', 'activo')
            ->with('persona')
            ->join('personas', 'alumnos.persona_id', '=', 'personas.id')
            ->orderBy('personas.apellido_paterno')
            ->orderBy('personas.apellido_materno')
            ->orderBy('personas.nombre')
            ->select('alumnos.*')
            ->get()
            ->toArray();

        $query = Calificacion::where('grupo_id', $this->grupo_id);
        if ($this->periodo_id) {
            $query->where('periodo_evaluacion_id', $this->periodo_id);
        }
        $notas = $query->get();

        $matrix = [];
        $promedios = [];
        foreach ($this->alumnos as $alumno) {
            $alumnoId = $alumno['id'];
            $matrix[$alumnoId] = [];
            $notasAlumno = collect();
            foreach ($this->materias as $materia) {
                $matrix[$alumnoId][$materia->id] = [];
                foreach ($this->periodos as $periodo) {
                    $nota = $notas->firstWhere(fn ($n) => $n->alumno_id === $alumnoId && $n->materia_id === $materia->id && $n->periodo_evaluacion_id === $periodo->id);
                    $val = $nota?->calificacion;
                    $matrix[$alumnoId][$materia->id][$periodo->id] = $val;

                    if ($val !== null) {
                        $notasAlumno->push((float) $val);
                    }
                }
            }
            $promedios[$alumnoId] = $notasAlumno->count() > 0
                ? round($notasAlumno->avg(), 1)
                : null;
        }
        $this->calificaciones = $matrix;
        $this->promedios = $promedios;

        $this->cargado = true;
    }

    protected function descargarPDFConcentrado()
    {
        $this->cargarConcentrado();

        $grupo = Grupo::with('grado', 'cicloEscolar')->find($this->grupo_id);

        $data = [
            'titulo' => 'Concentrado de Calificaciones',
            'grupo' => $grupo,
            'alumnos' => $this->alumnos,
            'materias' => $this->materias,
            'periodos' => $this->periodos,
            'calificaciones' => $this->calificaciones,
            'promedios' => $this->promedios,
            'periodoSeleccionado' => $this->periodo_id
                ? PeriodoEvaluacion::find($this->periodo_id)?->nombre
                : 'Todos los periodos',
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.concentrado', $data);
        $grupoNombre = $grupo ? "{$grupo->grado?->nombre}-{$grupo->nombre}" : 'grupo';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "concentrado-{$grupoNombre}.pdf"
        );
    }

    // ─── Kardex ───

    protected function cargarKardex(): void
    {
        $this->validate([
            'alumno_id' => 'required|exists:alumnos,id',
        ]);

        $alumno = Alumno::with('persona')->findOrFail($this->alumno_id);
        $this->alumnoData = $alumno->toArray();

        $calificaciones = Calificacion::where('alumno_id', $alumno->id)
            ->with('grupo.cicloEscolar', 'grupo.grado')
            ->get()
            ->filter(fn ($c) => $c->grupo !== null);

        $grouped = $calificaciones->groupBy(fn ($c) => $c->grupo->ciclo_escolar_id);

        $ciclos = [];
        foreach ($grouped as $cicloId => $notas) {
            $ciclo = CicloEscolar::find($cicloId);
            if (! $ciclo) {
                continue;
            }

            $materiaIds = $notas->pluck('materia_id')->unique();
            $periodoIds = $notas->pluck('periodo_evaluacion_id')->unique();

            $materias = Materia::whereIn('id', $materiaIds)->orderBy('nombre')->get();
            $periodos = PeriodoEvaluacion::whereIn('id', $periodoIds)->orderBy('orden')->get();

            $first = $notas->first();

            $matrix = [];
            foreach ($materias as $materia) {
                $matrix[$materia->id] = [];
                foreach ($periodos as $periodo) {
                    $nota = $notas->firstWhere(fn ($n) => $n->materia_id === $materia->id && $n->periodo_evaluacion_id === $periodo->id);
                    $matrix[$materia->id][$periodo->id] = $nota?->calificacion;
                }
            }

            $ciclos[] = [
                'ciclo' => $ciclo,
                'grado' => $first->grupo->grado?->nombre ?? '—',
                'grupo' => $first->grupo->nombre ?? '—',
                'materias' => $materias,
                'periodos' => $periodos,
                'calificaciones' => $matrix,
            ];
        }

        $this->kardexData = $ciclos;
        $this->cargado = true;
    }

    protected function descargarPDFKardex()
    {
        $this->cargarKardex();

        $data = [
            'titulo' => 'Kardex del Alumno',
            'alumno' => $this->alumnoData,
            'ciclos' => $this->kardexData,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.kardex', $data);
        $matricula = $this->alumnoData['matricula'] ?? 'alumno';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "kardex-{$matricula}.pdf"
        );
    }

    // ─── Inasistencias ───

    protected function cargarInasistencias(): void
    {
        $this->validate([
            'grupo_id' => 'required|exists:grupos,id',
        ]);

        $grupo = Grupo::with('grado')->findOrFail($this->grupo_id);

        $alumnos = $grupo->alumnos()
            ->where('estatus', 'activo')
            ->with('persona')
            ->join('personas', 'alumnos.persona_id', '=', 'personas.id')
            ->orderBy('personas.apellido_paterno')
            ->orderBy('personas.apellido_materno')
            ->orderBy('personas.nombre')
            ->select('alumnos.*')
            ->get();

        $query = Asistencia::where('grupo_id', $this->grupo_id);
        if ($this->fecha_desde) {
            $query->where('fecha', '>=', $this->fecha_desde);
        }
        if ($this->fecha_hasta) {
            $query->where('fecha', '<=', $this->fecha_hasta);
        }
        $asistencias = $query->get()->groupBy('alumno_id');

        $data = [];
        foreach ($alumnos as $alumno) {
            $alumnoAsistencias = $asistencias->get($alumno->id, collect());
            $data[] = [
                'alumno_id' => $alumno->id,
                'matricula' => $alumno->matricula,
                'persona' => $alumno->persona->toArray(),
                'asistio' => $alumnoAsistencias->where('estatus', 'asistio')->count(),
                'falta' => $alumnoAsistencias->where('estatus', 'falta')->count(),
                'retardo' => $alumnoAsistencias->where('estatus', 'retardo')->count(),
                'justificado' => $alumnoAsistencias->where('estatus', 'justificado')->count(),
                'total' => $alumnoAsistencias->count(),
            ];
        }

        $this->inasistenciasData = $data;
        $this->cargado = true;
    }

    protected function descargarPDFInasistencias()
    {
        $this->cargarInasistencias();

        $grupo = Grupo::with('grado', 'cicloEscolar')->find($this->grupo_id);

        $data = [
            'titulo' => 'Reporte de Inasistencias',
            'grupo' => $grupo,
            'alumnos' => $this->inasistenciasData,
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.inasistencias', $data);
        $grupoNombre = $grupo ? "{$grupo->grado?->nombre}-{$grupo->nombre}" : 'grupo';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "inasistencias-{$grupoNombre}.pdf"
        );
    }

    // ─── Alumnos por Tutor ───

    protected function cargarTutores(): void
    {
        $query = Persona::whereHas('familiares')
            ->with(['familiares' => function ($q) {
                $q->with(['alumno.persona']);
            }]);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('apellido_materno', 'like', "%{$search}%");
            });
        }

        $tutores = $query->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombre')->get();

        $data = [];
        foreach ($tutores as $tutor) {
            $children = $tutor->familiares->map(fn ($f) => [
                'alumno' => $f->alumno,
                'parentesco' => $f->parentesco,
            ])->filter(fn ($item) => $item['alumno'] !== null);

            if ($children->isEmpty()) {
                continue;
            }

            $data[] = [
                'tutor_id' => $tutor->id,
                'nombre_completo' => trim("{$tutor->apellido_paterno} {$tutor->apellido_materno}, {$tutor->nombre}"),
                'telefono' => $tutor->telefono ?? '—',
                'children_count' => $children->count(),
                'children' => $children,
            ];
        }

        $this->tutoresData = $data;
        $this->cargado = true;
    }

    protected function descargarPDFTutores()
    {
        $this->cargarTutores();

        $data = [
            'titulo' => 'Alumnos por Tutor',
            'tutores' => $this->tutoresData,
            'filtro' => $this->search ?: 'Todos',
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.alumnos-por-tutor', $data);
        $suffix = $this->search ? str_replace(' ', '-', $this->search) : 'todos';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "alumnos-por-tutor-{$suffix}.pdf"
        );
    }

    // ─── Descargar PDF ───

    public function descargarPDF()
    {
        return match ($this->reporte) {
            'concentrado' => $this->descargarPDFConcentrado(),
            'kardex' => $this->descargarPDFKardex(),
            'inasistencias' => $this->descargarPDFInasistencias(),
            'alumnos-por-tutor' => $this->descargarPDFTutores(),
        };
    }

    public function resetCarga(): void
    {
        $this->cargado = false;
        $this->alumnos = [];
        $this->calificaciones = [];
        $this->promedios = [];
        $this->alumnoData = [];
        $this->kardexData = [];
        $this->inasistenciasData = [];
        $this->tutoresData = [];
    }
}
