<?php

namespace App\Livewire\Catalogos;

use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\PeriodoEvaluacion;
use App\Services\Reportes\ConcentradoService;
use App\Services\Reportes\InasistenciasService;
use App\Services\Reportes\KardexService;
use App\Services\Reportes\TutoresService;
use App\Support\CicloActivoService;
use Illuminate\Support\Collection;
use Livewire\Component;

class Reportes extends Component
{
    public string $reporte = 'concentrado';

    // ─── Filters comunes ───
    public $ciclo_escolar_id = '';

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

        $activo = app(CicloActivoService::class)->get();
        if ($activo) {
            $this->ciclo_escolar_id = $activo->id;
        }
    }

    public function render()
    {
        $user = auth()->user();

        $ciclosEscolares = CicloEscolar::orderBy('nombre')->get();

        $gruposQuery = $user->hasRole('Docente')
            ? Grupo::where('docente_id', $user->id)
            : Grupo::query();

        if ($this->ciclo_escolar_id) {
            $gruposQuery->where('ciclo_escolar_id', $this->ciclo_escolar_id);
        }

        $grupos = $gruposQuery->with('grado', 'cicloEscolar')->orderBy('grado_id')->get();

        $periodos = collect();
        $materias = collect();
        $alumnosSelect = [];

        // Los periodos dependen del ciclo escolar, no del grupo — cargar siempre
        // que haya un ciclo seleccionado para que el select se muestre poblado.
        if ($this->ciclo_escolar_id && in_array($this->reporte, ['concentrado', 'kardex', 'inasistencias'])) {
            $periodos = PeriodoEvaluacion::where('ciclo_escolar_id', $this->ciclo_escolar_id)
                ->orderBy('orden')
                ->get();
        }

        if ($this->grupo_id) {
            $grupo = Grupo::with('grado', 'cicloEscolar')->find($this->grupo_id);
            if ($grupo) {
                if ($this->reporte === 'concentrado') {
                    $materias = Materia::where('grado_id', $grupo->grado_id)
                        ->orderBy('nombre')
                        ->get();
                }
                if ($this->reporte === 'kardex') {
                    $alumnosSelect = $grupo->alumnos()
                        ->activosConPersona()
                        ->get()
                        ->toArray();
                }
            }
        }

        return view('livewire.catalogos.reportes', [
            'ciclosEscolares' => $ciclosEscolares,
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

    public function updatedCicloEscolarId(): void
    {
        $this->grupo_id = '';
        $this->alumno_id = '';
        $this->periodo_id = '';
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
            $result = app(TutoresService::class)->cargar(search: $this->search);
            $this->tutoresData = $result['tutoresData'];
            $this->cargado = true;
        } elseif (mb_strlen($this->search) === 0) {
            $this->resetCarga();
        }
    }

    public function cargar()
    {
        $this->validate();

        $result = match ($this->reporte) {
            'concentrado' => app(ConcentradoService::class)->cargar(
                grupoId: (int) $this->grupo_id,
                periodoId: $this->periodo_id ? (int) $this->periodo_id : null,
            ),
            'kardex' => app(KardexService::class)->cargar(
                alumnoId: (int) $this->alumno_id,
            ),
            'inasistencias' => app(InasistenciasService::class)->cargar(
                grupoId: (int) $this->grupo_id,
                fechaDesde: $this->fecha_desde,
                fechaHasta: $this->fecha_hasta,
            ),
            'alumnos-por-tutor' => app(TutoresService::class)->cargar(
                search: $this->search,
            ),
        };

        foreach ($result as $key => $value) {
            $this->{$key} = $value;
        }

        $this->cargado = true;
    }

    protected function rules()
    {
        return match ($this->reporte) {
            'concentrado' => ['grupo_id' => 'required|exists:grupos,id'],
            'kardex' => ['alumno_id' => 'required|exists:alumnos,id'],
            'inasistencias' => ['grupo_id' => 'required|exists:grupos,id'],
            'alumnos-por-tutor' => [],
        };
    }

    // ─── Descargar PDF ───

    public function descargarPDF()
    {
        return match ($this->reporte) {
            'concentrado' => app(ConcentradoService::class)->descargarPDF(
                grupoId: (int) $this->grupo_id,
                periodoId: $this->periodo_id ? (int) $this->periodo_id : null,
                alumnos: $this->alumnos,
                materias: $this->materias,
                periodos: $this->periodos,
                calificaciones: $this->calificaciones,
                promedios: $this->promedios,
            ),
            'kardex' => app(KardexService::class)->descargarPDF(
                alumnoId: (int) $this->alumno_id,
            ),
            'inasistencias' => app(InasistenciasService::class)->descargarPDF(
                grupoId: (int) $this->grupo_id,
                fechaDesde: $this->fecha_desde,
                fechaHasta: $this->fecha_hasta,
            ),
            'alumnos-por-tutor' => app(TutoresService::class)->descargarPDF(
                search: $this->search,
            ),
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
