<?php

namespace App\Livewire\Catalogos;

use App\Models\Grupo;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Asistencia extends Component
{
    public $grupo_id = '';

    public string $fecha = '';

    public $alumnos = [];

    /** @var array<int, string> [alumno_id => estatus] */
    public $estatusList = [];

    /** @var array<int, string> [alumno_id => motivo] */
    public $motivos = [];

    public $cargado = false;

    public function mount(): void
    {
        $this->fecha = Carbon::today()->format('Y-m-d');
    }

    public function render()
    {
        $user = auth()->user();

        $grupos = $user->hasRole('Docente')
            ? Grupo::where('docente_id', $user->id)->with('grado', 'cicloEscolar')->orderBy('grado_id')->get()
            : Grupo::with('grado', 'cicloEscolar')->orderBy('grado_id')->get();

        return view('livewire.catalogos.asistencia', [
            'grupos' => $grupos,
        ]);
    }

    public function updatedGrupoId(): void
    {
        $this->resetCarga();
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
        $existentes = \App\Models\Asistencia::where('grupo_id', $this->grupo_id)
            ->where('fecha', $this->fecha)
            ->with('justificante')
            ->get()
            ->keyBy('alumno_id');

        $this->estatusList = [];
        $this->motivos = [];

        foreach ($this->alumnos as $alumno) {
            $asis = $existentes->get($alumno['id']);
            $this->estatusList[$alumno['id']] = $asis?->estatus ?? 'asistio';
            $this->motivos[$alumno['id']] = $asis?->justificante?->motivo ?? '';
        }

        $this->cargado = true;
    }

    public function guardar()
    {
        $this->validate([
            'estatusList.*' => 'required|in:asistio,falta,retardo,justificado',
            'motivos.*' => 'nullable|string|max:500',
        ]);

        $grupo = Grupo::findOrFail($this->grupo_id);

        foreach ($this->alumnos as $alumno) {
            $alumnoId = $alumno['id'];
            $estatus = $this->estatusList[$alumnoId];

            $asistencia = \App\Models\Asistencia::updateOrCreate(
                [
                    'alumno_id' => $alumnoId,
                    'grupo_id' => $this->grupo_id,
                    'fecha' => $this->fecha,
                ],
                [
                    'estatus' => $estatus,
                ]
            );

            // Si es justificado, crear o actualizar justificante con motivo
            if ($estatus === 'justificado' && ($this->motivos[$alumnoId] ?? null)) {
                $asistencia->justificante()->updateOrCreate(
                    ['asistencia_id' => $asistencia->id],
                    ['motivo' => $this->motivos[$alumnoId]]
                );
            } elseif ($estatus !== 'justificado' && $asistencia->justificante) {
                // Si ya no es justificado, eliminar el justificante (opcional)
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
        $this->motivos = [];
        $this->cargado = false;
    }
}
