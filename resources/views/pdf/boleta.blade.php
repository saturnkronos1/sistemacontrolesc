@extends('pdf.layouts.membrete')

@section('title', 'Boleta de Calificaciones')

@push('styles')
    <style>
        .boleta-header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2563eb;
        }
        .boleta-header h1 {
            font-size: 14pt;
            color: #2563eb;
            margin-bottom: 4px;
        }
        .boleta-header p {
            font-size: 8pt;
            color: #6b7280;
        }
        .observaciones {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .obs-item {
            margin-bottom: 5px;
            font-size: 8pt;
        }
        .obs-periodo {
            font-weight: 600;
            color: #2563eb;
        }
        .timestamp {
            text-align: center;
            font-size: 7pt;
            color: #9ca3af;
            margin-top: 15px;
        }
    </style>
@endpush

@section('content')
    <div class="boleta-header">
        <h1>Boleta de Calificaciones</h1>
        <p>Sistema de Control Escolar</p>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">Matrícula:</span>
            {{ $alumno['matricula'] ?? '—' }}
        </div>
        <div class="info-row">
            <span class="info-label">Nombre:</span>
            {{ $alumno['persona']['apellido_paterno'] ?? '' }} {{ $alumno['persona']['apellido_materno'] ?? '' }}, {{ $alumno['persona']['nombre'] ?? '' }}
        </div>
        <div class="info-row">
            <span class="info-label">Grado y Grupo:</span>
            {{ $alumno['grado']['nombre'] ?? '—' }} - {{ $alumno['grupo']['nombre'] ?? '—' }}
        </div>
        <div class="info-row">
            <span class="info-label">Ciclo Escolar:</span>
            {{ $alumno['grupo']['ciclo_escolar']['nombre'] ?? '—' }}
        </div>
        <div class="info-row">
            <span class="info-label">Periodo:</span>
            {{ $periodoSeleccionado }}
        </div>
    </div>

    <div class="section-title">Calificaciones</div>

    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Campo Formativo</th>
                @foreach($periodos as $periodo)
                    <th>{{ $periodo->nombre }}</th>
                @endforeach
                <th>Promedio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($materias as $materia)
                <tr>
                    <td>{{ $materia->nombre }}</td>
                    @foreach($periodos as $periodo)
                        <td>
                            @php $nota = $calificaciones[$materia->id][$periodo->id] ?? null; @endphp
                            @if($nota !== null)
                                <span class="{{ $nota >= 6 ? 'nota-alta' : 'nota-baja' }}">{{ number_format($nota, 1) }}</span>
                            @else
                                —
                            @endif
                        </td>
                    @endforeach
                    <td>
                        @php
                            $notasMateria = collect($periodos->toArray())->map(fn($p) => $calificaciones[$materia->id][$p['id']] ?? null)->filter();
                            $promMateria = $notasMateria->count() > 0 ? round($notasMateria->avg(), 1) : null;
                        @endphp
                        @if($promMateria !== null)
                            <span class="{{ $promMateria >= 6 ? 'nota-alta' : 'nota-baja' }}">{{ number_format($promMateria, 1) }}</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="promedio-row">
                <td>Promedio General</td>
                @foreach($periodos as $periodo)
                    <td>
                        @if(($promedios[$periodo->id] ?? null) !== null)
                            <span class="{{ $promedios[$periodo->id] >= 6 ? 'nota-alta' : 'nota-baja' }}">{{ number_format($promedios[$periodo->id], 1) }}</span>
                        @else
                            —
                        @endif
                    </td>
                @endforeach
                <td>
                    @php
                        $promGeneral = collect($promedios)->filter()->avg();
                    @endphp
                    @if($promGeneral)
                        <span class="{{ $promGeneral >= 6 ? 'nota-alta' : 'nota-baja' }}">{{ number_format($promGeneral, 1) }}</span>
                    @else
                        —
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>

    @if(count($observaciones))
        <div class="section-title">Observaciones</div>
        <div class="observaciones">
            @foreach($observaciones as $obs)
                <div class="obs-item">
                    @if($obs['periodo_evaluacion'] ?? null)
                        <span class="obs-periodo">{{ $obs['periodo_evaluacion']['nombre'] }}:</span>
                    @endif
                    {{ $obs['observacion'] }}
                </div>
            @endforeach
        </div>
    @endif

    <div class="timestamp">
        Generado el {{ $generated_at }}
    </div>
@endsection
