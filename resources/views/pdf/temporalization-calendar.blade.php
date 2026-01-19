<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Temporalización - {{ $course->nombre }}</title>
    <style>
        @page {
            margin: 10px 20px;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }
        
        .header-banner {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 4px;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 10px;
        }

        .container {
            width: 100%;
            display: table;
            table-layout: fixed;
        }

        .column-left {
            display: table-cell;
            width: 63%;
            vertical-align: top;
            padding-right: 10px;
        }
        
        .column-right {
            display: table-cell;
            width: 37%;
            vertical-align: top;
        }

        .calendar-grid {
            width: 100%;
        }

        .month-container {
            page-break-inside: avoid;
            margin-bottom: 8px;
            border: 1px solid #000;
        }

        .month-name {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            font-weight: bold;
            padding: 1px 0;
            text-transform: capitalize;
            font-size: 9px;
            border-bottom: 1px solid #000;
        }

        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            text-align: center;
            table-layout: fixed;
        }

        .calendar-table th {
            background-color: #eee;
            border-bottom: 1px solid #000;
            border-right: 1px solid #ccc;
            font-weight: normal;
            width: 14.2857%;
            padding: 1px 0;
        }
        .calendar-table th:last-child {
            border-right: none;
        }

        .calendar-table td {
            height: 14px;
            width: 14.2857%;
            vertical-align: middle;
            border-right: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            padding: 0;
            line-height: 14px;
        }
        .calendar-table td:last-child {
            border-right: none;
        }
        .calendar-table tr:last-child td {
            border-bottom: none;
        }
        
        .day_num {
            display: block;
            line-height: 14px;
        }

        .bg-festivo { background-color: #e74c3c; color: white; }
        .bg-no-lectivo { background-color: #ecf0f1; }
        .bg-evaluacion { background-color: #f1c40f; }
        .bg-recuperacion { background-color: #e67e22; color: white; }
        .bg-entrega { background-color: #9b59b6; color: white; }
        .bg-inicio-fin { background-color: #2ecc71; color: white; }
        .bg-weekend { color: #aaa; background-color: #f9f9f9; }
        
        .marked-dates-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            border: 1px solid #000;
        }
        
        .marked-dates-header {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            font-weight: bold;
            padding: 2px;
            font-size: 10px;
            border: 1px solid #000;
        }
        
        .marked-dates-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            vertical-align: middle;
        }
        
        .date-col { width: 35px; white-space: nowrap; }
        
        .main-title {
            text-align: center;
            color: #2c3e50;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .sub-title {
            text-align: center;
            color: #2c3e50;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 10px;
        }

    </style>
</head>
<body>

    <div class="header-banner">
        [PRES] Temporalización académica {{ date('Y') }}/{{ date('Y')+1 }}
    </div>

    <div class="container">
        <!-- Left Column: Calendar -->
        <div class="column-left">
            <div class="main-title">{{ $course->nombre }}</div>
            <div class="sub-title">Calendario académico</div>

            @php
                $bloques = array_chunk($meses, 3, true);
            @endphp

            @foreach($bloques as $grupo)
            <!-- Simulación de fila de grid con tabla -->
            <table class="calendar-grid">
                <tr>
                    @foreach($grupo as $claveMes => $mes)
                    <td style="padding: 2px; vertical-align: top; width: 33.33%;">
                        <div class="month-container">
                            <div class="month-name">{{ $mes['nombre'] }}</div>
                            <table class="calendar-table">
                                <thead>
                                    <tr>
                                        <th>L</th><th>M</th><th>X</th><th>J</th><th>V</th><th>S</th><th>D</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // División estándar, el relleno se hizo en el Servicio
                                        $filas = array_chunk($mes['dias'], 7);
                                    @endphp
                                    @foreach($filas as $semana)
                                        <tr>
                                            @foreach($semana as $dia)
                                                @php
                                                    $claseCelda = '';
                                                    if ($dia) {
                                                        if ($dia['diaSemana'] >= 6) {
                                                            $claseCelda = 'bg-weekend';
                                                        }
                                                        
                                                        // Lógica de colores basada en el tipo de evento
                                                        foreach ($dia['eventos'] as $evento) {
                                                            $nombreTipo = strtolower($evento->type->nombre ?? '');
                                                             if (str_contains($nombreTipo, 'festivo')) $claseCelda = 'bg-festivo';
                                                             elseif (str_contains($nombreTipo, 'no lectivo')) $claseCelda = 'bg-no-lectivo';
                                                             elseif (str_contains($nombreTipo, 'evaluacion') || str_contains($nombreTipo, 'evaluación')) $claseCelda = 'bg-evaluacion';
                                                             elseif (str_contains($nombreTipo, 'recup')) $claseCelda = 'bg-recuperacion';
                                                             elseif (str_contains($nombreTipo, 'entrega') || str_contains($nombreTipo, 'boletin')) $claseCelda = 'bg-entrega';
                                                             elseif (str_contains($nombreTipo, 'comienzo') || str_contains($nombreTipo, 'fin')) $claseCelda = 'bg-inicio-fin';
                                                        }
                                                    }
                                                @endphp
                                                
                                                <td class="{{ $claseCelda }}">
                                                    @if($dia)
                                                        <span class="day_num">{{ $dia['dia'] }}</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            @if(count($semana) < 7)
                                                {{-- No debería ocurrir debido a la lógica de relleno, pero por seguridad --}}
                                                @for($i = count($semana); $i < 7; $i++)
                                                    <td></td>
                                                @endfor
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </td>
                    @endforeach
                    <!-- Rellenar meses vacíos si el bloque es menor de 3 -->
                    @if(count($grupo) < 3)
                        @for($i = count($grupo); $i < 3; $i++)
                            <td style="width: 33.33%;"></td>
                        @endfor
                    @endif
                </tr>
            </table>
            @endforeach
        </div>

        <!-- Columna Derecha: Fechas Marcadas -->
        <div class="column-right">
            <table class="marked-dates-table">
                <thead>
                    <tr>
                        <th colspan="2" class="marked-dates-header">Fechas marcadas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fechasMarcadas as $evento)
                    <tr>
                        <td class="date-col">
                            {{ \Carbon\Carbon::parse($evento->fecha)->locale('es')->translatedFormat('d M') }}
                        </td>
                        <td>
                            <!-- {{ $evento->descripcion ?? ($evento->type->nombre ?? '') }} -->
                            {{ $evento->type->nombre ?? '' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
