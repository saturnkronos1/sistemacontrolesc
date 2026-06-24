@extends('pdf.layouts.membrete')

@section('title', 'Concentrado de Calificaciones')

@section('content')
    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">Grupo:</span>
            {{ $grupo?->grado?->nombre ?? '—' }} - {{ $grupo?->nombre ?? '—' }}
        </div>
        <div class="info-row">
            <span class="info-label">Ciclo Escolar:</span>
            {{ $grupo?->cicloEscolar?->nombre ?? '—' }}
        </div>
        <div class="info-row">
            <span class="info-label">Periodo:</span>
            {{ $periodoSeleccionado }}
        </div>
        <div class="info-row">
            <span class="info-label">Docente:</span>
            {{ $grupo?->docente?->name ?? '—' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25%;"></th>
                <th colspan="{{ $materias->count() }}" style="text-align: center; background-color: #dbeafe; color: #2563eb; font-weight: 700; font-size: 7pt; padding: 3px 6px;">Campos Formativos</th>
                <th></th>
            </tr>
            <tr>
                <th style="width: 25%;">Alumno</th>
                @foreach($materias as $materia)
                    <th>{{ $materia->nombre }}</th>
                @endforeach
                <th>Promedio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alumnos as $alumno)
                <tr>
                    <td>{{ $alumno['persona']['apellido_paterno'] ?? '' }} {{ $alumno['persona']['apellido_materno'] ?? '' }}, {{ $alumno['persona']['nombre'] ?? '' }}</td>
                    @foreach($materias as $materia)
                        <td>
                            @php
                                $val = $calificaciones[$alumno['id']][$materia->id] ?? [];
                                $notas = collect($periodos->toArray())->map(fn($p) => $val[$p['id']] ?? null)->filter();
                                $prom = $notas->count() > 0 ? round($notas->avg(), 1) : null;
                            @endphp
                            @if($prom !== null)
                                <span class="{{ $prom >= 6 ? 'nota-alta' : 'nota-baja' }}">{{ number_format($prom, 1) }}</span>
                            @else
                                —
                            @endif
                        </td>
                    @endforeach
                    <td class="promedio-row">
                        @if(($promedios[$alumno['id']] ?? null) !== null)
                            <span class="{{ $promedios[$alumno['id']] >= 6 ? 'nota-alta' : 'nota-baja' }}">{{ number_format($promedios[$alumno['id']], 1) }}</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        @if(count($alumnos) > 1)
            @php
                $promediosCampo = [];
                foreach ($materias as $materia) {
                    $notasCampo = collect($alumnos)->map(function ($a) use ($calificaciones, $materia, $periodos) {
                        $val = $calificaciones[$a['id']][$materia->id] ?? [];
                        $notas = collect($periodos->toArray())->map(fn($p) => $val[$p['id']] ?? null)->filter();
                        return $notas->count() > 0 ? $notas->avg() : null;
                    })->filter();
                    $promediosCampo[$materia->id] = $notasCampo->count() > 0 ? round($notasCampo->avg(), 1) : null;
                }
                $promedioGeneral = collect($promedios)->filter()->avg();
                $promedioGeneral = $promedioGeneral ? round($promedioGeneral, 1) : null;
            @endphp
            <tfoot>
                <tr style="font-weight: 700; background-color: #f0f5ff;">
                    <td style="padding: 5px 6px; border-bottom: 1px solid #e5e7eb; font-size: 7.5pt;">Promedio por Campo Formativo</td>
                    @foreach($materias as $materia)
                        <td style="padding: 5px 6px; border-bottom: 1px solid #e5e7eb; text-align: center; font-size: 7.5pt;">
                            @if(($promediosCampo[$materia->id] ?? null) !== null)
                                <span class="{{ $promediosCampo[$materia->id] >= 6 ? 'nota-alta' : 'nota-baja' }}">{{ number_format($promediosCampo[$materia->id], 1) }}</span>
                            @endif
                        </td>
                    @endforeach
                    <td style="padding: 5px 6px; border-bottom: 1px solid #e5e7eb; text-align: center; font-size: 7.5pt;"></td>
                </tr>
                <tr style="font-weight: 700; background-color: #f0f5ff;">
                    <td style="padding: 5px 6px; border-bottom: 1px solid #e5e7eb; font-size: 7.5pt;"></td>
                    <td colspan="{{ $materias->count() }}" style="padding: 5px 6px; border-bottom: 1px solid #e5e7eb; text-align: center; font-size: 7.5pt;">Promedio General</td>
                    <td style="padding: 5px 6px; border-bottom: 1px solid #e5e7eb; text-align: center; font-size: 7.5pt; background-color: #dbeafe;">
                        @if($promedioGeneral !== null)
                            <span class="{{ $promedioGeneral >= 6 ? 'nota-alta' : 'nota-baja' }}">{{ number_format($promedioGeneral, 1) }}</span>
                        @endif
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div style="text-align: center; font-size: 7pt; color: #9ca3af; margin-top: 10px;">
        Generado el {{ $generated_at }} — Sistema de Control Escolar
    </div>
@endsection
