<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Consulta de Asistencias</title>
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
        .estatus-asistio { color: #16a34a; font-weight: 600; }
        .estatus-falta { color: #dc2626; font-weight: 600; }
        .estatus-retardo { color: #d97706; font-weight: 600; }
        .estatus-justificado { color: #2563eb; font-weight: 600; }
        .resumen {
            page-break-inside: avoid;
            margin-bottom: 14px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 6px;
            font-size: 9pt;
        }
        .resumen-item {
            display: inline-block;
            margin-right: 16px;
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
        <h1>{{ $titulo }}</h1>
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
            <span class="info-label">Fecha desde:</span>
            {{ \Illuminate\Support\Carbon::parse($fecha_desde)->format('d/m/Y') }}
        </div>
        <div class="info-row">
            <span class="info-label">Fecha hasta:</span>
            {{ \Illuminate\Support\Carbon::parse($fecha_hasta)->format('d/m/Y') }}
        </div>
    </div>

    @php
        $totalAsistio = $registros->where('estatus', 'asistio')->count();
        $totalFalta = $registros->where('estatus', 'falta')->count();
        $totalRetardo = $registros->where('estatus', 'retardo')->count();
        $totalJustificado = $registros->where('estatus', 'justificado')->count();
    @endphp

    <div class="resumen">
        <span class="resumen-item"><strong>✅ Asistió:</strong> {{ $totalAsistio }}</span>
        <span class="resumen-item"><strong>❌ Falta:</strong> {{ $totalFalta }}</span>
        <span class="resumen-item"><strong>⏰ Retardo:</strong> {{ $totalRetardo }}</span>
        <span class="resumen-item"><strong>📄 Justificado:</strong> {{ $totalJustificado }}</span>
        <span class="resumen-item"><strong>Total:</strong> {{ $registros->count() }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">Fecha</th>
                <th style="width: 32%;">Alumno</th>
                <th style="width: 10%;">Grupo</th>
                <th style="width: 16%;">Estatus</th>
                <th style="width: 25%;">Motivo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registros as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->fecha->format('d/m/Y') }}</td>
                    <td>{{ $item->alumno?->persona?->apellido_paterno ?? '' }} {{ $item->alumno?->persona?->apellido_materno ?? '' }}, {{ $item->alumno?->persona?->nombre ?? '' }}</td>
                    <td>{{ $item->grupo?->grado?->nombre ?? '' }} - {{ $item->grupo?->nombre ?? '' }}</td>
                    <td class="estatus-{{ $item->estatus }}">
                        {{ ucfirst($item->estatus) }}
                    </td>
                    <td>{{ $item->justificante?->motivo ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ $generated_at }} — Sistema de Control Escolar
    </div>
</body>
</html>
