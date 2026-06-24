<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Boleta de Calificaciones</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2563eb;
        }
        .header h1 {
            font-size: 16pt;
            color: #2563eb;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 9pt;
            color: #6b7280;
        }
        .info-grid {
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
        }
        .info-row {
            display: flex;
            margin-bottom: 4px;
            font-size: 9pt;
        }
        .info-label {
            width: 140px;
            color: #6b7280;
            font-weight: 600;
        }
        .info-value {
            flex: 1;
        }
        .section-title {
            font-size: 11pt;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 9pt;
        }
        th {
            background-color: #2563eb;
            color: #ffffff;
            padding: 8px 10px;
            text-align: center;
            font-weight: 600;
        }
        th:first-child {
            text-align: left;
        }
        td {
            padding: 6px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }
        td:first-child {
            text-align: left;
            font-weight: 500;
        }
        .materia-row td {
            background-color: #ffffff;
        }
        .promedio-row td {
            background-color: #f0f5ff;
            font-weight: 700;
        }
        .promedio-general td {
            background-color: #dbeafe;
            font-weight: 700;
        }
        .nota-alta { color: #059669; }
        .nota-baja { color: #dc2626; }
        .observaciones {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
        }
        .obs-item {
            margin-bottom: 6px;
            font-size: 9pt;
        }
        .obs-periodo {
            font-weight: 600;
            color: #2563eb;
        }
        .footer {
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Boleta de Calificaciones</h1>
        <p>Sistema de Control Escolar</p>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">Matrícula:</span>
            <span class="info-value">{{ $alumno['matricula'] ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Nombre:</span>
            <span class="info-value">
                {{ $alumno['persona']['apellido_paterno'] ?? '' }} {{ $alumno['persona']['apellido_materno'] ?? '' }}, {{ $alumno['persona']['nombre'] ?? '' }}
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Grado y Grupo:</span>
            <span class="info-value">{{ $alumno['grado']['nombre'] ?? '—' }} - {{ $alumno['grupo']['nombre'] ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Ciclo Escolar:</span>
            <span class="info-value">{{ $alumno['grupo']['ciclo_escolar']['nombre'] ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Periodo:</span>
            <span class="info-value">{{ $periodoSeleccionado }}</span>
        </div>
    </div>

    <div class="section-title">Calificaciones</div>

    <table>
        <thead>
            <tr>
                <th style="width: 35%;">Campo Formativo</th>
                @foreach($periodos as $periodo)
                    <th>{{ $periodo->nombre }}</th>
                @endforeach
                <th>Promedio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($materias as $materia)
                <tr class="materia-row">
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
            <tr class="promedio-general">
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

    <div class="footer">
        Generado el {{ $generated_at }} — Sistema de Control Escolar
    </div>
</body>
</html>
