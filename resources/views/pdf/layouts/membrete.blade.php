<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Documento')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #1a1a1a;
            padding: 0 0 75px 0;
        }
        .header-img {
            width: 100%;
            display: block;
        }
        .content {
            padding: 12px 20px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            z-index: 1000;
        }
        .footer-img {
            width: 100%;
            display: block;
        }
        .footer-text {
            font-size: 7pt;
            font-family: 'Arial', 'DejaVu Sans', sans-serif;
            color: #4b5563;
            padding: 2px 0 5px 0;
            background: #ffffff;
        }
        /* ─── Tablas con bordes visibles (spec #9) ─── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table, th, td {
            border: 1px solid #000000;
        }
        th {
            background-color: #ffffff;
            color: #000000;
            padding: 5px 4px;
            text-align: center;
            font-weight: 700;
            font-size: 7.5pt;
        }
        td {
            background-color: #ffffff;
            color: #000000;
            padding: 4px 4px;
            text-align: center;
            font-size: 7.5pt;
        }
        td:first-child {
            text-align: left;
            font-weight: 500;
        }
        .nota-alta { color: #000000; font-weight: 700; }
        .nota-baja { color: #000000; font-weight: 700; }
        .promedio-row td {
            background-color: #ffffff;
            font-weight: 700;
        }
        /* ─── Datos en líneas ─── */
        .data-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 180px;
            padding: 0 4px 1px 4px;
            font-weight: 600;
        }
        .data-line-sm {
            min-width: 100px;
        }
        .label-data {
            font-weight: 600;
            color: #000;
        }
        .sig-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 200px;
            margin-top: 4px;
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
