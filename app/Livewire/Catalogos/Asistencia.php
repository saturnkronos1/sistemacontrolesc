<?php

namespace App\Livewire\Catalogos;

use App\Models\Asistencia as AsistenciaModel;
use App\Models\Grupo;
use App\Support\CicloActivoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Asistencia extends Component
{
    use WithPagination;

    public $ciclo_escolar_id = '';

    public $grupo_id = '';

    public $alumno_id = '';

    public string $fecha_desde = '';

    public string $fecha_hasta = '';

    public $consultado = false;

    public $resumen = [];

    public bool $esDocente = false;

    public function mount(): void
    {
        $user = auth()->user();

        $this->esDocente = $user->hasRole('Docente');

        $activo = app(CicloActivoService::class)->get();
        if ($activo) {
            $this->ciclo_escolar_id = $activo->id;
            $this->fecha_desde = $activo->fecha_inicio->format('Y-m-d');
            $this->fecha_hasta = $activo->fecha_fin->format('Y-m-d');
            $this->consultado = true;
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
    public function esAdmin(): bool
    {
        return auth()->user()->hasRole('Superadmin')
            || auth()->user()->hasRole('Director')
            || auth()->user()->hasRole('Subdirector');
    }

    #[Computed]
    public function alumnosConsulta(): Collection
    {
        if (! $this->grupo_id) {
            return collect();
        }

        $grupo = Grupo::find($this->grupo_id);

        return $grupo
            ? $grupo->alumnos()->activosConPersona()->get()
            : collect();
    }

    public function render()
    {
        $resultados = collect();

        if ($this->consultado && $this->fecha_desde && $this->fecha_hasta) {
            $canQuery = $this->grupo_id || $this->ciclo_escolar_id;
            if ($canQuery) {
                $resultados = $this->queryResultados()->paginate(15);

                $all = $this->queryResumen()->get();
                $this->resumen = [
                    'asistio' => $all->where('estatus', 'asistio')->count(),
                    'falta' => $all->where('estatus', 'falta')->count(),
                    'retardo' => $all->where('estatus', 'retardo')->count(),
                    'justificado' => $all->whereIn('estatus', ['pendiente', 'justificado'])->count(),
                    'total' => $all->count(),
                ];
            }
        }

        return view('livewire.catalogos.asistencia', [
            'ciclosEscolares' => $this->ciclosEscolares,
            'grupos' => $this->grupos,
            'alumnosConsulta' => $this->alumnosConsulta,
            'esAdmin' => $this->esAdmin,
            'resultados' => $resultados,
        ]);
    }

    protected function queryResultados()
    {
        return AsistenciaModel::select('asistencias.*')
            ->with([
                'alumno.persona',
                'grupo.grado',
                'justificante',
            ])
            ->join('alumnos', 'asistencias.alumno_id', '=', 'alumnos.id')
            ->join('personas', 'alumnos.persona_id', '=', 'personas.id')
            ->whereBetween('asistencias.fecha', [$this->fecha_desde, $this->fecha_hasta])
            ->when($this->grupo_id, fn ($q) => $q->where('asistencias.grupo_id', $this->grupo_id))
            ->when(! $this->grupo_id && $this->ciclo_escolar_id, fn ($q) => $q
                ->whereHas('grupo', fn ($q) => $q->where('ciclo_escolar_id', $this->ciclo_escolar_id))
            )
            ->when($this->alumno_id, fn ($q) => $q->where('asistencias.alumno_id', $this->alumno_id))
            ->orderBy('asistencias.fecha', 'desc')
            ->orderBy('personas.apellido_paterno')
            ->orderBy('personas.apellido_materno')
            ->orderBy('personas.nombre');
    }

    protected function queryResumen()
    {
        return AsistenciaModel::query()
            ->whereBetween('fecha', [$this->fecha_desde, $this->fecha_hasta])
            ->when($this->grupo_id, fn ($q) => $q->where('grupo_id', $this->grupo_id))
            ->when(! $this->grupo_id && $this->ciclo_escolar_id, fn ($q) => $q
                ->whereHas('grupo', fn ($q) => $q->where('ciclo_escolar_id', $this->ciclo_escolar_id))
            )
            ->when($this->alumno_id, fn ($q) => $q->where('alumno_id', $this->alumno_id));
    }

    public function updatedCicloEscolarId(): void
    {
        $this->grupo_id = '';
        $this->alumno_id = '';
        $this->resetearConsulta();
        $this->resetPage();
    }

    public function updatedGrupoId(): void
    {
        $this->alumno_id = '';
        $this->resetearConsulta();
    }

    public function resetearConsulta(): void
    {
        $this->consultado = false;
        $this->resumen = [];
    }

    public function consultar()
    {
        $this->validate([
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
        ]);

        $this->consultado = true;
    }

    public function descargarPDFConsulta()
    {
        $this->validate([
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
        ]);

        $canQuery = $this->grupo_id || $this->ciclo_escolar_id;

        if (! $canQuery) {
            return;
        }

        $grupo = $this->grupo_id
            ? Grupo::with('grado', 'cicloEscolar')->find($this->grupo_id)
            : null;

        $registros = $this->queryResultados()->get();

        $data = [
            'titulo' => 'Consulta de Asistencias',
            'grupo' => $grupo,
            'registros' => $registros,
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.asistencia-consulta', $data);

        $filename = $grupo
            ? "asistencia-{$grupo->grado?->nombre}-{$grupo->nombre}.pdf"
            : 'asistencia-consulta.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename,
        );
    }
}
