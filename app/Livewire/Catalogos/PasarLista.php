<?php

namespace App\Livewire\Catalogos;

use App\Models\Asistencia as AsistenciaModel;
use App\Models\CicloEscolar;
use App\Models\Grupo;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class PasarLista extends Component
{
    use WithFileUploads;

    public $ciclo_escolar_id = '';

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

    public bool $esDocente = false;

    public bool $modoLectura = false;

    public function mount(): void
    {
        $user = auth()->user();

        $this->esDocente = $user->hasRole('Docente');
        $this->fecha = Carbon::today()->format('Y-m-d');

        if ($this->esDocente) {
            $grupo = Grupo::where('docente_id', $user->id)->first();
            if ($grupo) {
                $this->ciclo_escolar_id = $grupo->ciclo_escolar_id;
                $this->grupo_id = $grupo->id;
                $this->cargarAlumnos();
            }
        } else {
            $activo = CicloEscolar::activo()->first();
            if ($activo) {
                $this->ciclo_escolar_id = $activo->id;
            }
        }
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

        $grupos = $gruposQuery->with('grado', 'cicloEscolar')
            ->orderBy('grado_id')
            ->get();

        return view('livewire.catalogos.pasar-lista', [
            'ciclosEscolares' => $ciclosEscolares,
            'grupos' => $grupos,
        ]);
    }

    public function updatedCicloEscolarId(): void
    {
        $this->grupo_id = '';
        $this->resetCarga();
        $this->fecha = Carbon::today()->format('Y-m-d');
    }

    public function cambiarEstatus($alumnoId): void
    {
        $ciclo = [
            'asistio' => 'falta',
            'falta' => 'retardo',
            'retardo' => 'pendiente',
            'pendiente' => 'asistio',
        ];

        $this->estatusList[$alumnoId] = $ciclo[$this->estatusList[$alumnoId]] ?? 'asistio';

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

        if ($this->esDocente && $existentes->isNotEmpty()) {
            $this->modoLectura = true;
        } else {
            $this->modoLectura = false;
        }
    }

    public function guardar()
    {
        $this->validate([
            'estatusList.*' => 'required|in:asistio,falta,retardo,pendiente',
            'justificanteMotivos.*' => 'nullable|string|max:500',
            'justificanteArchivos.*' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
        ]);

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
                $asistencia->justificante()->delete();
            }
        }

        $this->dispatch('toast', message: 'Asistencias guardadas exitosamente.', type: 'success');

        $this->cargarAlumnos();

        if ($this->esDocente) {
            $this->modoLectura = true;
        }
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
