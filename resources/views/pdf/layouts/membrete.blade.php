<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Documento')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            color: #1a1a1a;
            padding: 0;
        }
        .header-img {
            width: 100%;
            display: block;
        }
        .content {
            padding: 15px 20px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
        }
        .footer-img {
            width: 100%;
            display: block;
        }
        .footer-text {
            font-size: 7pt;
            color: #4b5563;
            padding: 2px 0 5px 0;
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
        .section-title {
            font-size: 11pt;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
        }
    </style>
    @stack('styles')
</head>
<body>
    {{-- Header imagen --}}
    <img src="{{ $headerImg }}" alt="Membrete" class="header-img">

    {{-- Contenido --}}
    <div class="content">
        @yield('content')
    </div>

    {{-- Footer --}}
    <div class="footer">
        <img src="{{ $footerImg }}" alt="Pie" class="footer-img">
        <div class="footer-text">{{ $direccion }}</div>
    </div>
</body>
</html>
