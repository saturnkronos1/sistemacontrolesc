<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Alumnos por Tutor</title>
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
        td.left {
            text-align: left;
        }
        .child-item {
            margin-bottom: 2px;
            font-size: 7.5pt;
        }
        .parentesco {
            color: #6b7280;
            font-style: italic;
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
        <h1>Alumnos por Tutor</h1>
        <p>Sistema de Control Escolar</p>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">Filtro:</span>
            {{ $filtro }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Tutor</th>
                <th style="width: 12%;">Teléfono</th>
                <th style="width: 8%;">Hijos</th>
                <th style="width: 55%;">Alumnos</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tutores as $tutor)
                <tr>
                    <td>{{ $tutor['nombre_completo'] }}</td>
                    <td>{{ $tutor['telefono'] }}</td>
                    <td>{{ $tutor['children_count'] }}</td>
                    <td class="left">
                        @foreach($tutor['children'] as $child)
                            <div class="child-item">
                                {{ $child['alumno']?->persona?->apellido_paterno ?? '' }} {{ $child['alumno']?->persona?->apellido_materno ?? '' }}, {{ $child['alumno']?->persona?->nombre ?? '' }}
                                @if($child['parentesco'])
                                    <span class="parentesco">({{ $child['parentesco'] }})</span>
                                @endif
                            </div>
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #9ca3af; padding: 20px;">
                        No se encontraron tutores registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ $generated_at }} — Sistema de Control Escolar
    </div>
</body>
</html>
