<?php

namespace App\Livewire\Catalogos;

use App\Models\Alumno;
use App\Models\AlumnoFamilia;
use App\Models\BoletaObservacion;
use App\Models\Calificacion;
use App\Models\Materia;
use App\Models\PeriodoEvaluacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TutorDashboard extends Component
{
    public string $vista = 'dashboard'; // dashboard | calificaciones | asistencias

    public ?int $alumnoId = null;

    public ?int $periodo_id = null;

    /** @var Collection<int, AlumnoFamilia> */
    public Collection $hijos;

    /** @var array<string, mixed> */
    public array $alumnoData = [];

    /** @var Collection<int, Materia> */
    public Collection $materias;

    /** @var Collection<int, PeriodoEvaluacion> */
    public Collection $periodos;

    /** @var array<int, array<int, float|null>> [materia_id][periodo_id] => calificacion */
    public array $calificaciones = [];

    /** @var array<int, float|null> [periodo_id => promedio] */
    public array $promedios = [];

    public array $observaciones = [];

    public Collection $asistencias;

    public function mount(): void
    {
        $user = auth()->user();
        $persona = $user?->persona;

        $this->hijos = $persona
            ? AlumnoFamilia::where('persona_id', $persona->id)
                ->with('alumno.persona', 'alumno.grado', 'alumno.grupo', 'alumno.cicloEscolar')
                ->get()
            : collect();

        $this->materias = collect();
        $this->periodos = collect();
        $this->asistencias = collect();
    }

    public function render()
    {
        return view('livewire.catalogos.tutor-dashboard', [
            'alumnoActivo' => $this->alumnoId
                ? Alumno::with('persona', 'grado', 'grupo', 'cicloEscolar')->find($this->alumnoId)
                : null,
        ]);
    }

    /** Ir a la vista de calificaciones de un alumno */
    public function verCalificaciones(int $alumnoId): void
    {
        $this->alumnoId = $alumnoId;
        $this->vista = 'calificaciones';
        $this->periodo_id = null;
        $this->cargarDatosAlumno();
        $this->cargarCalificaciones();
    }

    /** Ir a la vista de asistencias de un alumno */
    public function verAsistencias(int $alumnoId): void
    {
        $this->alumnoId = $alumnoId;
        $this->vista = 'asistencias';
        $this->cargarDatosAlumno();
        $this->cargarAsistencias();
    }

    /** Volver al dashboard */
    public function volver(): void
    {
        $this->vista = 'dashboard';
        $this->alumnoId = null;
        $this->alumnoData = [];
        $this->materias = collect();
        $this->periodos = collect();
        $this->calificaciones = [];
        $this->promedios = [];
        $this->observaciones = [];
        $this->asistencias = collect();
    }

    public function updatedPeriodoId(): void
    {
        if ($this->vista === 'calificaciones' && $this->alumnoId) {
            $this->cargarCalificaciones();
        }
    }

    /** Descargar boleta PDF del alumno seleccionado */
    public function descargarBoleta(?int $alumnoId = null): StreamedResponse
    {
        if ($alumnoId !== null) {
            $this->alumnoId = $alumnoId;
        }

        $this->cargarCalificaciones();

        $alumno = Alumno::with('persona', 'grado', 'grupo', 'grupo.cicloEscolar')
            ->findOrFail($this->alumnoId);

        $data = [
            'alumno' => $alumno->toArray(),
            'materias' => $this->materias,
            'periodos' => $this->periodos,
            'calificaciones' => $this->calificaciones,
            'promedios' => $this->promedios,
            'observaciones' => $this->observaciones,
            'generated_at' => now()->format('d/m/Y H:i'),
            'periodoSeleccionado' => $this->periodo_id
                ? PeriodoEvaluacion::find($this->periodo_id)?->nombre
                : 'Todos los periodos',
        ];

        $pdf = Pdf::loadView('pdf.boleta', $data);

        $suffix = $this->periodo_id ? '-periodo-'.$this->periodo_id : '';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "boleta-{$alumno->matricula}{$suffix}.pdf"
        );
    }

    private function cargarDatosAlumno(): void
    {
        $alumno = Alumno::with('persona', 'grado', 'grupo', 'grupo.cicloEscolar')
            ->findOrFail($this->alumnoId);
        $this->alumnoData = $alumno->toArray();
    }

    private function cargarCalificaciones(): void
    {
        $alumno = Alumno::findOrFail($this->alumnoId);

        $this->periodos = PeriodoEvaluacion::where('ciclo_escolar_id', $alumno->ciclo_escolar_id)
            ->orderBy('orden')
            ->get();

        $this->materias = Materia::where('grado_id', $alumno->grado_id)
            ->orderBy('nombre')
            ->get();

        $query = Calificacion::where('alumno_id', $alumno->id)
            ->where('grupo_id', $alumno->grupo_id);

        if ($this->periodo_id) {
            $query->where('periodo_evaluacion_id', $this->periodo_id);
        }

        $notas = $query->get();

        // Build matrix [materia_id][periodo_id] => calificacion
        $matrix = [];
        foreach ($this->materias as $materia) {
            $matrix[$materia->id] = [];
            foreach ($this->periodos as $periodo) {
                $nota = $notas->firstWhere(fn ($n) => $n->materia_id === $materia->id && $n->periodo_evaluacion_id === $periodo->id);
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

        if ($this->periodo_id) {
            $obsQuery->where('periodo_evaluacion_id', $this->periodo_id);
        }

        $this->observaciones = $obsQuery->with('periodoEvaluacion')->get()->toArray();
    }

    private function cargarAsistencias(): void
    {
        $alumno = Alumno::findOrFail($this->alumnoId);

        $this->asistencias = $alumno->asistencias()
            ->with('justificante')
            ->orderBy('fecha', 'desc')
            ->get();
    }
}
