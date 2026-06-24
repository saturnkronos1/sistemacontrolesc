<?php

namespace App\Services\Reportes;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\CicloEscolar;
use App\Models\Materia;
use App\Models\PeriodoEvaluacion;
use App\Support\MembreteHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KardexService
{
    /**
     * Cargar datos del kardex de un alumno.
     *
     * @return array{alumnoData: array, kardexData: array}
     */
    public function cargar(int $alumnoId): array
    {
        $alumno = Alumno::with('persona')->findOrFail($alumnoId);
        $alumnoData = $alumno->toArray();

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

        return [
            'alumnoData' => $alumnoData,
            'kardexData' => $ciclos,
        ];
    }

    /**
     * Generar y descargar PDF del kardex.
     */
    public function descargarPDF(int $alumnoId): StreamedResponse
    {
        $result = $this->cargar($alumnoId);

        $data = array_merge(MembreteHelper::data(), [
            'titulo' => 'Kardex del Alumno',
            'alumno' => $result['alumnoData'],
            'ciclos' => $result['kardexData'],
            'generated_at' => now()->format('d/m/Y H:i'),
        ]);

        $pdf = Pdf::loadView('pdf.kardex', $data);
        $matricula = $result['alumnoData']['matricula'] ?? 'alumno';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "kardex-{$matricula}.pdf"
        );
    }
}
