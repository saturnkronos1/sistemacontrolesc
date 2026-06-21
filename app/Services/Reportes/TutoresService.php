<?php

namespace App\Services\Reportes;

use App\Models\Persona;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TutoresService
{
    /**
     * Cargar datos de alumnos por tutor.
     *
     * @return array{tutoresData: array}
     */
    public function cargar(string $search = ''): array
    {
        $query = Persona::whereHas('familiares')
            ->with(['familiares' => function ($q) {
                $q->with(['alumno.persona']);
            }]);

        if ($search) {
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

        return ['tutoresData' => $data];
    }

    /**
     * Generar y descargar PDF de alumnos por tutor.
     */
    public function descargarPDF(string $search = ''): StreamedResponse
    {
        $result = $this->cargar($search);

        $data = [
            'titulo' => 'Alumnos por Tutor',
            'tutores' => $result['tutoresData'],
            'filtro' => $search ?: 'Todos',
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.alumnos-por-tutor', $data);
        $suffix = $search ? str_replace(' ', '-', $search) : 'todos';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "alumnos-por-tutor-{$suffix}.pdf"
        );
    }
}
