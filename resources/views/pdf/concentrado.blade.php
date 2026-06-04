<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Concentrado de Calificaciones</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            color: #1a1a1a;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2563eb;
        }
        .header h1 {
            font-size: 14pt;
            color: #2563eb;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 8pt;
            color: #6b7280;
        }
        .info-grid {
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
        }
        .info-row {
            margin-bottom: 3px;
            font-size: 8pt;
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 7.5pt;
        }
        th {
            background-color: #2563eb;
            color: #ffffff;
            padding: 5px 6px;
            text-align: center;
            font-weight: 600;
        }
        th:first-child {
            text-align: left;
        }
        td {
            padding: 4px 6px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }
        td:first-child {
            text-align: left;
            font-weight: 500;
        }
        .nota-alta { color: #059669; }
        .nota-baja { color: #dc2626; }
        .promedio-row td {
            background-color: #f0f5ff;
            font-weight: 700;
        }
        .footer {
            text-align: center;
            font-size: 7pt;
            color: #9ca3af;
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Concentrado de Calificaciones</h1>
        <p>Sistema de Control Escolar</p>
    </div>

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
    </table>

    <div class="footer">
        Generado el {{ $generated_at }} — Sistema de Control Escolar
    </div>
</body>
</html>
