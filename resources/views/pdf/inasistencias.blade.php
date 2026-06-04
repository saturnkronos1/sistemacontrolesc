<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Inasistencias</title>
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
        .total-row td {
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
        <h1>Reporte de Inasistencias</h1>
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
        @if($fecha_desde)
            <div class="info-row">
                <span class="info-label">Fecha desde:</span>
                {{ $fecha_desde }}
            </div>
        @endif
        @if($fecha_hasta)
            <div class="info-row">
                <span class="info-label">Fecha hasta:</span>
                {{ $fecha_hasta }}
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Alumno</th>
                <th>Asistió</th>
                <th>Faltas</th>
                <th>Retardos</th>
                <th>Justificados</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alumnos as $item)
                <tr>
                    <td>{{ $item['persona']['apellido_paterno'] ?? '' }} {{ $item['persona']['apellido_materno'] ?? '' }}, {{ $item['persona']['nombre'] ?? '' }}</td>
                    <td>{{ $item['asistio'] }}</td>
                    <td>{{ $item['falta'] }}</td>
                    <td>{{ $item['retardo'] }}</td>
                    <td>{{ $item['justificado'] }}</td>
                    <td>{{ $item['total'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td>Totales</td>
                <td>{{ collect($alumnos)->sum('asistio') }}</td>
                <td>{{ collect($alumnos)->sum('falta') }}</td>
                <td>{{ collect($alumnos)->sum('retardo') }}</td>
                <td>{{ collect($alumnos)->sum('justificado') }}</td>
                <td>{{ collect($alumnos)->sum('total') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generado el {{ $generated_at }} — Sistema de Control Escolar
    </div>
</body>
</html>
