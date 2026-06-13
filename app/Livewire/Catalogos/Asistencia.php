<?php

namespace App\Livewire\Catalogos;

use App\Models\Asistencia as AsistenciaModel;
use App\Models\CicloEscolar;
use App\Models\Grupo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Asistencia extends Component
{
    use WithFileUploads;
    use WithPagination;

    // ─── Modo consulta ───
    public string $modo = 'pasar-lista';

    public $ciclo_escolar_id = '';

    public $alumno_id = '';

    public string $fecha_desde = '';

    public string $fecha_hasta = '';

    public $consultado = false;

    public $resumen = [];

    // ─── Pasar lista ───
    public $grupo_id = '';

    public string $fecha = '';

    public $alumnos = [];

    /** @var array<int, string> [alumno_id => estatus] */
    public $estatusList = [];

    /** @var array<int, string> [alumno_id => motivo] */
    public $justificanteMotivos = [];

    /** @var array<int, TemporaryUploadedFile|null> [alumno_id => UploadedFile] */
    public $justificanteArchivos = [];

    /** @var array<int, bool> [alumno_id => completado] */
    public $justificanteCompletado = [];

    public $cargado = false;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->hasRole('Superadmin') || $user->hasRole('Director') || $user->hasRole('Subdirector')) {
            $this->modo = 'consulta';

            $activo = CicloEscolar::activo()->first();
            if ($activo) {
                $this->ciclo_escolar_id = $activo->id;
                $this->fecha_desde = $activo->fecha_inicio->format('Y-m-d');
                $this->fecha_hasta = $activo->fecha_fin->format('Y-m-d');
                $this->consultado = true;
            }
        }

        $this->fecha = Carbon::today()->format('Y-m-d');
    }

    public function render()
    {
        $user = auth()->user();

        $ciclosEscolares = CicloEscolar::activo()->orderBy('nombre')->get();

        $gruposQuery = $user->hasRole('Docente')
            ? Grupo::where('docente_id', $user->id)
            : Grupo::query();

        if ($this->ciclo_escolar_id) {
            $gruposQuery->where('ciclo_escolar_id', $this->ciclo_escolar_id);
        }

        $grupos = $gruposQuery->with('grado', 'cicloEscolar')->orderBy('grado_id')->get();

        $esAdmin = $user->hasRole('Superadmin')
            || $user->hasRole('Director')
            || $user->hasRole('Subdirector');

        $alumnosConsulta = collect();
        if ($this->modo === 'consulta' && $this->grupo_id) {
            $grupo = Grupo::find($this->grupo_id);
            if ($grupo) {
                $alumnosConsulta = $grupo->alumnos()
                    ->where('alumnos.estatus', 'activo')
                    ->with('persona')
                    ->join('personas', 'alumnos.persona_id', '=', 'personas.id')
                    ->orderBy('personas.apellido_paterno')
                    ->orderBy('personas.apellido_materno')
                    ->orderBy('personas.nombre')
                    ->select('alumnos.*')
                    ->get();
            }
        }

        $resultados = null;

        if ($this->consultado && $this->modo === 'consulta' && $this->fecha_desde && $this->fecha_hasta) {
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
            'ciclosEscolares' => $ciclosEscolares,
            'grupos' => $grupos,
            'alumnosConsulta' => $alumnosConsulta,
            'esAdmin' => $esAdmin,
            'resultados' => $resultados,
        ]);
    }

    // ─── Modo consulta ───

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
        if ($this->modo !== 'consulta') {
            return;
        }

        $this->alumno_id = '';
        $this->resetearConsulta();
    }

    public function resetearConsulta(): void
    {
        $this->consultado = false;
        $this->resumen = [];
    }

    public function updatedModo(): void
    {
        $this->resetearConsulta();
        $this->resetCarga();
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

    // ─── Pasar lista ───

    public function cambiarEstatus($alumnoId): void
    {
        $ciclo = [
            'asistio' => 'falta',
            'falta' => 'retardo',
            'retardo' => 'pendiente',
            'pendiente' => 'asistio',
        ];

        $this->estatusList[$alumnoId] = $ciclo[$this->estatusList[$alumnoId]] ?? 'asistio';

        // Si ya no es pendiente, limpiar archivo subido temporal
        if ($this->estatusList[$alumnoId] !== 'pendiente') {
            unset($this->justificanteArchivos[$alumnoId]);
        }
    }

    public function cargarAlumnos()
    {
        $this->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'fecha' => 'required|date',
        ]);

        $grupo = Grupo::with('grado')->findOrFail($this->grupo_id);

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

        // Cargar asistencias existentes
        $existentes = AsistenciaModel::where('grupo_id', $this->grupo_id)
            ->where('fecha', $this->fecha)
            ->with('justificante')
            ->get()
            ->keyBy('alumno_id');

        $this->estatusList = [];
        $this->justificanteMotivos = [];
        $this->justificanteArchivos = [];
        $this->justificanteCompletado = [];

        foreach ($this->alumnos as $alumno) {
            $alumnoId = $alumno['id'];
            $asis = $existentes->get($alumnoId);

            if ($asis) {
                if ($asis->estatus === 'justificado') {
                    // Justificado completo — mostrar botón azul con check
                    $this->estatusList[$alumnoId] = 'pendiente';
                    $this->justificanteCompletado[$alumnoId] = true;
                } else {
                    $this->estatusList[$alumnoId] = $asis->estatus;
                }

                $this->justificanteMotivos[$alumnoId] = $asis?->justificante?->motivo ?? '';
            } else {
                $this->estatusList[$alumnoId] = 'asistio';
                $this->justificanteMotivos[$alumnoId] = '';
                $this->justificanteCompletado[$alumnoId] = false;
            }
        }

        $this->cargado = true;
    }

    public function guardar()
    {
        $this->validate([
            'estatusList.*' => 'required|in:asistio,falta,retardo,pendiente',
            'justificanteMotivos.*' => 'nullable|string|max:500',
            'justificanteArchivos.*' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
        ]);

        // Validar que pendiente tenga motivo
        foreach ($this->estatusList as $alumnoId => $estatus) {
            if ($estatus === 'pendiente' && empty(trim($this->justificanteMotivos[$alumnoId] ?? ''))) {
                $this->addError(
                    "justificanteMotivos.{$alumnoId}",
                    'El motivo es obligatorio cuando el estatus es Justificado.',
                );

                return;
            }
        }

        $grupo = Grupo::findOrFail($this->grupo_id);

        foreach ($this->alumnos as $alumno) {
            $alumnoId = $alumno['id'];
            $estatus = $this->estatusList[$alumnoId];

            // Si es pendiente y subió archivo, guardar como justificado
            $saveEstatus = $estatus;
            if ($estatus === 'pendiente' && isset($this->justificanteArchivos[$alumnoId])) {
                $saveEstatus = 'justificado';
            }

            $asistencia = AsistenciaModel::updateOrCreate(
                [
                    'alumno_id' => $alumnoId,
                    'grupo_id' => $this->grupo_id,
                    'fecha' => $this->fecha,
                ],
                [
                    'estatus' => $saveEstatus,
                ]
            );

            if ($estatus === 'pendiente') {
                // Crear o actualizar justificante
                $justificanteData = [
                    'motivo' => $this->justificanteMotivos[$alumnoId] ?? '',
                ];

                if (isset($this->justificanteArchivos[$alumnoId])) {
                    $file = $this->justificanteArchivos[$alumnoId];
                    $extension = $file->getClientOriginalExtension();
                    $filename = "{$alumnoId}_{$this->fecha}_{$asistencia->id}.{$extension}";
                    $path = $file->storeAs('justificantes', $filename, 'public');
                    $justificanteData['archivo_path'] = $path;
                }

                $asistencia->justificante()->updateOrCreate(
                    ['asistencia_id' => $asistencia->id],
                    $justificanteData
                );
            } elseif ($asistencia->justificante) {
                // Si cambió a otro estatus, eliminar justificante
                $asistencia->justificante()->delete();
            }
        }

        $this->dispatch('toast', message: 'Asistencias guardadas exitosamente.', type: 'success');

        $this->cargarAlumnos();
    }

    public function resetCarga(): void
    {
        $this->alumnos = [];
        $this->estatusList = [];
        $this->justificanteMotivos = [];
        $this->justificanteArchivos = [];
        $this->justificanteCompletado = [];
        $this->cargado = false;
    }
}
