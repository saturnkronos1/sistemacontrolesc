<?php

namespace App\Services\Reportes;

use App\Models\Calificacion;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\PeriodoEvaluacion;
use App\Models\User;
use App\Support\MembreteHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConcentradoService
{
    /**
     * Cargar datos del concentrado de calificaciones.
     *
     * @return array{alumnos: array, materias: Collection, periodos: Collection, calificaciones: array, promedios: array}
     */
    public function cargar(int $grupoId, ?int $periodoId): array
    {
        $grupo = Grupo::with('grado', 'cicloEscolar')->findOrFail($grupoId);

        $periodos = PeriodoEvaluacion::where('ciclo_escolar_id', $grupo->ciclo_escolar_id)
            ->orderBy('orden')
            ->get();

        $materias = Materia::where('grado_id', $grupo->grado_id)
            ->orderBy('nombre')
            ->get();

        $alumnos = $grupo->alumnos()
            ->activosConPersona()
            ->get()
            ->toArray();

        $query = Calificacion::where('grupo_id', $grupoId);
        if ($periodoId) {
            $query->where('periodo_evaluacion_id', $periodoId);
        }
        $notas = $query->get();

        $matrix = [];
        $promedios = [];
        foreach ($alumnos as $alumno) {
            $alumnoId = $alumno['id'];
            $matrix[$alumnoId] = [];
            $notasAlumno = collect();
            foreach ($materias as $materia) {
                $matrix[$alumnoId][$materia->id] = [];
                foreach ($periodos as $periodo) {
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

        return [
            'alumnos' => $alumnos,
            'materias' => $materias,
            'periodos' => $periodos,
            'calificaciones' => $matrix,
            'promedios' => $promedios,
        ];
    }

    /**
     * Generar y descargar PDF del concentrado.
     */
    public function descargarPDF(int $grupoId, ?int $periodoId, array $alumnos, Collection $materias, Collection $periodos, array $calificaciones, array $promedios): StreamedResponse
    {
        $grupo = Grupo::with('grado', 'cicloEscolar')->find($grupoId);

        $director = User::role('Director')->first();

        $data = array_merge(MembreteHelper::data(), [
            'titulo' => 'Concentrado de Calificaciones',
            'grupo' => $grupo,
            'alumnos' => $alumnos,
            'materias' => $materias,
            'periodos' => $periodos,
            'calificaciones' => $calificaciones,
            'promedios' => $promedios,
            'modoMultiple' => is_null($periodoId) && $periodos->count() > 1,
            'periodoSeleccionado' => $periodoId
                ? PeriodoEvaluacion::find($periodoId)?->nombre
                : 'Todos los periodos',
            'generated_at' => now()->format('d/m/Y H:i'),
            'director' => $director?->name ?? '—',
        ]);

        $pdf = Pdf::loadView('pdf.concentrado', $data);
        $pdf->setPaper('letter', 'portrait');
        $grupoNombre = $grupo ? "{$grupo->grado?->nombre}-{$grupo->nombre}" : 'grupo';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "calificaciones-{$grupoNombre}.pdf"
        );
    }
}
