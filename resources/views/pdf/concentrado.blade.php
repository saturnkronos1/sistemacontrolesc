@extends('pdf.layouts.membrete')

@section('title', 'Calificaciones - ' . ($periodoSeleccionado ?? ''))

@push('styles')
    <style>
        .title-section {
            text-align: center;
            margin-bottom: 14px;
        }
        .title-section h1 {
            font-family: 'Arial', 'DejaVu Sans', sans-serif;
            font-size: 13pt;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .title-section h2 {
            font-family: 'Arial', 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .title-section p {
            font-family: 'Arial', 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            font-weight: 600;
        }
        .data-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            width: 100%;
        }
        .data-col {
            width: 48%;
        }
        .data-item {
            margin-bottom: 6px;
            font-size: 8.5pt;
        }
        .data-item .label-data {
            font-weight: 700;
        }
        .data-item .line-value {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 170px;
            padding: 0 4px 1px 4px;
            font-weight: 600;
            font-size: 8.5pt;
        }
        .table-wrap {
            margin-bottom: 14px;
        }
        table th.top-header {
            background-color: #ffffff;
            color: #000000;
            padding: 6px 4px;
            text-align: center;
            font-weight: 700;
            font-size: 8pt;
        }
        table td.nombre-cell {
            text-align: left;
            padding-left: 5px;
            font-weight: 500;
            font-size: 7.5pt;
        }
        table td.num-cell {
            text-align: center;
            font-weight: 400;
            width: 25px;
        }
        .promedio-header {
            background-color: #ffffff !important;
            color: #000000 !important;
        }
        .promedio-cell {
            background-color: #ffffff;
            font-weight: 700;
        }
        .promedio-label {
            text-align: left !important;
            padding-left: 5px;
            font-weight: 700;
            font-size: 7.5pt;
        }
        .firmas-section {
            margin-top: 28px;
            width: 100%;
        }
        .firmas-section table {
            width: 100%;
            border: none;
            margin-bottom: 0;
        }
        .firmas-section table,
        .firmas-section th,
        .firmas-section td {
            border: none;
        }
    </style>
@endpush

@section('content')
    {{-- 1. Título centrado --}}
    <div class="title-section">
        <h1>CALIFICACIONES</h1>
        <h2>{{ $periodoSeleccionado }}</h2>
        <p>CICLO ESCOLAR: {{ $grupo?->cicloEscolar?->nombre ?? '—' }}</p>
    </div>

    {{-- 2. Datos generales en dos columnas (tabla para DomPDF) --}}
    <table style="margin-bottom: 10px; border: none;">
        <tr style="border: none;">
            <td style="width: 50%; border: none; padding: 0; vertical-align: top;">
                <div class="data-item">
                    <span class="label-data">ESCUELA:</span>
                    <span class="line-value" style="min-width: 200px;">{{ $escuela }}</span>
                </div>
                <div class="data-item" style="margin-top: 6px;">
                    <span class="label-data">GRADO:</span>
                    <span class="line-value" style="min-width: 120px;">{{ $grupo?->grado?->nombre ?? '—' }}</span>
                </div>
            </td>
            <td style="width: 50%; border: none; padding: 0; vertical-align: top;">
                <div class="data-item">
                    <span class="label-data">C.C.T.:</span>
                    <span class="line-value" style="min-width: 120px;">{{ $cct }}</span>
                </div>
                <div class="data-item" style="margin-top: 6px;">
                    <span class="label-data">GRUPO:</span>
                    <span class="line-value" style="min-width: 120px;">{{ $grupo?->nombre ?? '—' }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- 3. Tabla principal --}}
    <div class="table-wrap">
        <table>
            <thead>
                @if($modoMultiple)
                    {{-- Fila 1: encabezado principal con CAMPOS FORMATIVOS ancho --}}
                    <tr>
                        <th class="top-header" style="width: 25px;" rowspan="3">N/P</th>
                        <th class="top-header" style="width: 22%;" rowspan="3">NOMBRE(S)</th>
                        <th class="top-header" colspan="{{ $materias->count() * 4 }}">CAMPOS FORMATIVOS</th>
                        <th class="top-header" style="width: 50px;" rowspan="3">PROM.<br>GRAL.</th>
                    </tr>
                    {{-- Fila 2: nombres de materias --}}
                    <tr>
                        @foreach($materias as $materia)
                            <th colspan="4" style="padding: 5px 2px; font-size: 6pt; font-weight: 600;">{{ $materia->nombre }}</th>
                        @endforeach
                    </tr>
                    {{-- Fila 3: sub-columnas de periodos --}}
                    <tr>
                        @foreach($materias as $materia)
                            <th style="padding: 3px 2px; font-size: 5.5pt;">1T</th>
                            <th style="padding: 3px 2px; font-size: 5.5pt;">2T</th>
                            <th style="padding: 3px 2px; font-size: 5.5pt;">3T</th>
                            <th style="padding: 3px 2px; font-size: 5.5pt;">PROM</th>
                        @endforeach
                    </tr>
                @else
                    {{-- Fila 1: encabezado multinivel --}}
                    <tr>
                        <th class="top-header" style="width: 25px;">N/P</th>
                        <th class="top-header" style="width: 22%;">NOMBRE(S)</th>
                        <th class="top-header" colspan="{{ $materias->count() }}">CAMPOS FORMATIVOS</th>
                        <th class="top-header" style="width: 50px;">PROM.<br>GRAL.</th>
                    </tr>
                    {{-- Fila 2: nombres de materias --}}
                    <tr>
                        <th style="padding: 4px;"></th>
                        <th style="padding: 4px;"></th>
                        @foreach($materias as $materia)
                            <th style="padding: 5px 3px; font-size: 6.5pt; font-weight: 600;">
                                {{ $materia->nombre }}
                            </th>
                        @endforeach
                        <th style="padding: 4px;"></th>
                    </tr>
                @endif
            </thead>
            <tbody>
                @foreach($alumnos as $idx => $alumno)
                    <tr>
                        <td class="num-cell">{{ $idx + 1 }}</td>
                        <td class="nombre-cell">
                            {{ $alumno['persona']['apellido_paterno'] ?? '' }} {{ $alumno['persona']['apellido_materno'] ?? '' }} {{ $alumno['persona']['nombre'] ?? '' }}
                        </td>
                        @if($modoMultiple)
                            @foreach($materias as $materia)
                                @php
                                    $val = $calificaciones[$alumno['id']][$materia->id] ?? [];
                                    $p1 = $val[$periodos[0]->id] ?? null;
                                    $p2 = $val[$periodos[1]->id] ?? null;
                                    $p3 = $val[$periodos[2]->id] ?? null;
                                    $notasAlumno = collect([$p1, $p2, $p3])->filter();
                                    $promMateria = $notasAlumno->count() > 0 ? round($notasAlumno->avg(), 1) : null;
                                @endphp
                                <td style="font-size: 7pt;">{{ $p1 !== null ? number_format($p1, 1) : '—' }}</td>
                                <td style="font-size: 7pt;">{{ $p2 !== null ? number_format($p2, 1) : '—' }}</td>
                                <td style="font-size: 7pt;">{{ $p3 !== null ? number_format($p3, 1) : '—' }}</td>
                                <td class="promedio-cell" style="font-size: 7pt;">
                                    @if($promMateria !== null)
                                        <span class="{{ $promMateria >= 6 ? 'nota-alta' : 'nota-baja' }}">{{ number_format($promMateria, 1) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            @endforeach
                        @else
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
                        @endif
                        <td class="promedio-cell">
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
                    @if($modoMultiple)
                        {{-- Solo Promedio General en modo FINAL --}}
                        <tr class="promedio-row">
                            <td colspan="2" class="promedio-label"></td>
                            <td colspan="{{ $materias->count() * 4 }}" style="text-align: center; font-weight: 700; font-size: 7.5pt;">
                                PROMEDIO GENERAL
                            </td>
                            <td class="promedio-cell">
                                @if($promedioGeneral !== null)
                                    <span class="{{ $promedioGeneral >= 6 ? 'nota-alta' : 'nota-baja' }}">{{ number_format($promedioGeneral, 1) }}</span>
                                @endif
                            </td>
                        </tr>
                    @else
                        {{-- Promedio por Campo Formativo --}}
                        <tr class="promedio-row">
                            <td colspan="2" class="promedio-label">PROMEDIO POR CAMPO FORMATIVO</td>
                            @foreach($materias as $materia)
                                <td>
                                    @if(($promediosCampo[$materia->id] ?? null) !== null)
                                        <span class="{{ $promediosCampo[$materia->id] >= 6 ? 'nota-alta' : 'nota-baja' }}">{{ number_format($promediosCampo[$materia->id], 1) }}</span>
                                    @endif
                                </td>
                            @endforeach
                            <td></td>
                        </tr>
                        {{-- Promedio General --}}
                        <tr class="promedio-row">
                            <td colspan="2" class="promedio-label"></td>
                            <td colspan="{{ $materias->count() }}" style="text-align: center; font-weight: 700; font-size: 7.5pt;">
                                PROMEDIO GENERAL
                            </td>
                            <td class="promedio-cell">
                                @if($promedioGeneral !== null)
                                    <span class="{{ $promedioGeneral >= 6 ? 'nota-alta' : 'nota-baja' }}">{{ number_format($promedioGeneral, 1) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endif
                </tfoot>
            @endif
        </table>
    </div>

    {{-- 8. Firmas --}}
    <div class="firmas-section">
        <table>
            <tr>
                <td class="firma-col-left" style="text-align: center; width: 50%; padding: 0; vertical-align: top;">
                    <div style="text-align: center; font-size: 9pt; font-weight: 700; margin-bottom: 2px;">ATENTAMENTE</div>
                    <div style="text-align: center; font-size: 8pt; font-weight: 700; margin-bottom: 2px;">PROFR.(A) {{ $grupo?->docente?->name ?? '—' }}</div>
                    <div style="border-bottom: 1px solid #000; margin: 6px auto 4px auto; width: 80%;"></div>
                    <div style="text-align: center; font-size: 7.5pt; text-transform: uppercase;">PROFESOR(A) DE GRUPO</div>
                </td>
                <td class="firma-col-right" style="text-align: center; width: 50%; padding: 0; vertical-align: top;">
                    <div style="text-align: center; font-size: 9pt; font-weight: 700; margin-bottom: 2px;">Vo. Bo.</div>
                    <div style="text-align: center; font-size: 8pt; font-weight: 700; margin-bottom: 2px;">PROFR.(A) {{ $director }}</div>
                    <div style="border-bottom: 1px solid #000; margin: 6px auto 4px auto; width: 80%;"></div>
                    <div style="text-align: center; font-size: 7.5pt; text-transform: uppercase;">DIRECTOR ESCOLAR</div>
                </td>
            </tr>
        </table>
    </div>
@endsection
