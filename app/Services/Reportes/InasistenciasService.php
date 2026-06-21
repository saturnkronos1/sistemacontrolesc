<?php

namespace App\Services\Reportes;

use App\Models\Asistencia;
use App\Models\Grupo;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InasistenciasService
{
    /**
     * Cargar datos de inasistencias de un grupo.
     *
     * @return array{inasistenciasData: array}
     */
    public function cargar(int $grupoId, string $fechaDesde = '', string $fechaHasta = ''): array
    {
        $grupo = Grupo::with('grado')->findOrFail($grupoId);

        $alumnos = $grupo->alumnos()
            ->activosConPersona()
            ->get();

        $query = Asistencia::where('grupo_id', $grupoId);
        if ($fechaDesde) {
            $query->where('fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->where('fecha', '<=', $fechaHasta);
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

        return ['inasistenciasData' => $data];
    }

    /**
     * Generar y descargar PDF de inasistencias.
     */
    public function descargarPDF(int $grupoId, string $fechaDesde = '', string $fechaHasta = ''): StreamedResponse
    {
        $result = $this->cargar($grupoId, $fechaDesde, $fechaHasta);

        $grupo = Grupo::with('grado', 'cicloEscolar')->find($grupoId);

        $data = [
            'titulo' => 'Reporte de Inasistencias',
            'grupo' => $grupo,
            'alumnos' => $result['inasistenciasData'],
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.inasistencias', $data);
        $grupoNombre = $grupo ? "{$grupo->grado?->nombre}-{$grupo->nombre}" : 'grupo';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "inasistencias-{$grupoNombre}.pdf"
        );
    }
}
