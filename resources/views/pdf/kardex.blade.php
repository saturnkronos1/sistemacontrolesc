<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kardex del Alumno</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #1a1a1a;
            padding: 25px;
        }
        .header {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 12px;
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
            margin-bottom: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
        }
        .info-row {
            margin-bottom: 3px;
            font-size: 9pt;
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
        }
        .ciclo-title {
            font-size: 10pt;
            font-weight: 700;
            color: #2563eb;
            margin-top: 16px;
            margin-bottom: 6px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e5e7eb;
        }
        .ciclo-subtitle {
            font-size: 8pt;
            color: #6b7280;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 8pt;
        }
        th {
            background-color: #2563eb;
            color: #ffffff;
            padding: 6px 8px;
            text-align: center;
            font-weight: 600;
        }
        th:first-child {
            text-align: left;
        }
        td {
            padding: 5px 8px;
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
            margin-top: 25px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kardex del Alumno</h1>
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
    </div>

    @forelse($ciclos as $cicloItem)
        <div class="ciclo-title">{{ $cicloItem['ciclo']?->nombre ?? 'Ciclo desconocido' }}</div>
        <div class="ciclo-subtitle">{{ $cicloItem['grado'] }} - {{ $cicloItem['grupo'] }}</div>

        <table>
            <thead>
                <tr>
                    <th style="width: 30%;">Materia</th>
                    @foreach($cicloItem['periodos'] as $periodo)
                        <th>{{ $periodo->nombre }}</th>
                    @endforeach
                    <th>Promedio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cicloItem['materias'] as $materia)
                    <tr>
                        <td>{{ $materia->nombre }}</td>
                        @foreach($cicloItem['periodos'] as $periodo)
                            <td>
                                @php $nota = $cicloItem['calificaciones'][$materia->id][$periodo->id] ?? null; @endphp
                                @if($nota !== null)
                                    <span class="{{ $nota >= 6 ? 'nota-alta' : 'nota-baja' }}">{{ number_format((float)$nota, 1) }}</span>
                                @else
                                    —
                                @endif
                            </td>
                        @endforeach
                        <td>
                            @php
                                $vals = collect($cicloItem['periodos']->toArray())->map(fn($p) => $cicloItem['calificaciones'][$materia->id][$p['id']] ?? null)->filter();
                                $prom = $vals->count() > 0 ? round($vals->avg(), 1) : null;
                            @endphp
                            @if($prom !== null)
                                <span class="{{ $prom >= 6 ? 'nota-alta' : 'nota-baja' }}">{{ number_format($prom, 1) }}</span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p style="text-align: center; color: #9ca3af; margin-top: 30px;">No se encontraron calificaciones registradas.</p>
    @endforelse

    <div class="footer">
        Generado el {{ $generated_at }} — Sistema de Control Escolar
    </div>
</body>
</html>
