<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Área - {{ $area->nombre }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 16px;
        }
        .header h2 {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h3 {
            background-color: #333;
            color: white;
            padding: 8px;
            margin: 0 0 10px;
            font-size: 11px;
        }
        .stats-row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-box {
            display: table-cell;
            width: 20%;
            text-align: center;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .stat-box .number {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .stat-box .label {
            font-size: 9px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
            font-size: 9px;
        }
        th {
            background-color: #495057;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            color: white;
        }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-danger { background-color: #dc3545; }
        .badge-info { background-color: #17a2b8; }
        .info-line {
            margin: 5px 0;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE ÁREA</h1>
        <h2>{{ $area->nombre }}</h2>
        <p>Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
    </div>

    <div class="section">
        <h3>Información del Área</h3>
        <p class="info-line"><strong>Jefe de Área:</strong> {{ $area->jefe?->name ?? 'Sin asignar' }}</p>
        <p class="info-line"><strong>Funcionarios:</strong> {{ $area->funcionarios->count() }}</p>
        <p class="info-line"><strong>Descripción:</strong> {{ $area->descripcion ?? 'Sin descripción' }}</p>
    </div>

    <div class="section">
        <h3>Resumen Estadístico</h3>
        <div class="stats-row">
            <div class="stat-box">
                <div class="number">{{ $estadisticas['total'] }}</div>
                <div class="label">Total</div>
            </div>
            <div class="stat-box">
                <div class="number">{{ $estadisticas['resueltos'] }}</div>
                <div class="label">Resueltos</div>
            </div>
            <div class="stat-box">
                <div class="number">{{ $estadisticas['pendientes'] }}</div>
                <div class="label">Pendientes</div>
            </div>
            <div class="stat-box">
                <div class="number">{{ $estadisticas['total'] > 0 ? round(($estadisticas['resueltos'] / $estadisticas['total']) * 100) : 0 }}%</div>
                <div class="label">Eficiencia</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Distribución por Estado</h3>
        <table>
            <tr>
                <th>Estado</th>
                <th>Cantidad</th>
                <th>Porcentaje</th>
            </tr>
            @foreach($estadisticas['por_estado'] as $estado => $cantidad)
            <tr>
                <td>{{ ucfirst(str_replace('_', ' ', $estado)) }}</td>
                <td>{{ $cantidad }}</td>
                <td>{{ $estadisticas['total'] > 0 ? round(($cantidad / $estadisticas['total']) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h3>Carga por Funcionario</h3>
        <table>
            <tr>
                <th>Funcionario</th>
                <th>Expedientes</th>
            </tr>
            @foreach($estadisticas['por_funcionario'] as $funcionario => $cantidad)
            <tr>
                <td>{{ $funcionario }}</td>
                <td>{{ $cantidad }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h3>Detalle de Expedientes (Últimos 50)</h3>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Fecha</th>
                    <th>Asunto</th>
                    <th>Estado</th>
                    <th>Funcionario</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expedientes->take(50) as $exp)
                <tr>
                    <td>{{ $exp->codigo_expediente }}</td>
                    <td>{{ $exp->created_at->format('d/m/Y') }}</td>
                    <td>{{ Str::limit($exp->asunto, 40) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $exp->estado)) }}</td>
                    <td>{{ $exp->funcionarioAsignado?->name ?? 'Sin asignar' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }} | Sistema Mesa de Partes</p>
    </div>
</body>
</html>
