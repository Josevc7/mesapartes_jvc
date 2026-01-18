<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Expedientes</title>
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
            font-size: 18px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .info-box {
            background-color: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .info-box h3 {
            margin: 0 0 10px;
            font-size: 12px;
            color: #333;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
            background-color: #e9ecef;
            border: 1px solid #dee2e6;
        }
        .stat-item .number {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        .stat-item .label {
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
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: white;
            font-size: 9px;
        }
        td {
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            color: white;
        }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-danger { background-color: #dc3545; }
        .badge-info { background-color: #17a2b8; }
        .badge-secondary { background-color: #6c757d; }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE EXPEDIENTES</h1>
        <p>Sistema de Mesa de Partes</p>
        <p>Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
    </div>

    <div class="info-box">
        <h3>Filtros Aplicados</h3>
        <p>
            @if($areaInfo)
                <strong>Área:</strong> {{ $areaInfo->nombre }} |
            @endif
            @if($tipoTramiteInfo)
                <strong>Tipo de Trámite:</strong> {{ $tipoTramiteInfo->nombre }} |
            @endif
            @if($estado)
                <strong>Estado:</strong> {{ ucfirst(str_replace('_', ' ', $estado)) }} |
            @endif
            <strong>Total de Registros:</strong> {{ $estadisticas['total'] }}
        </p>
    </div>

    <div class="stats-grid">
        <div class="stat-item">
            <div class="number">{{ $estadisticas['total'] }}</div>
            <div class="label">Total Expedientes</div>
        </div>
        <div class="stat-item">
            <div class="number">{{ $estadisticas['por_estado']['resuelto'] ?? 0 }}</div>
            <div class="label">Resueltos</div>
        </div>
        <div class="stat-item">
            <div class="number">{{ ($estadisticas['por_estado']['derivado'] ?? 0) + ($estadisticas['por_estado']['en_proceso'] ?? 0) }}</div>
            <div class="label">En Proceso</div>
        </div>
        <div class="stat-item">
            <div class="number">{{ $estadisticas['por_estado']['archivado'] ?? 0 }}</div>
            <div class="label">Archivados</div>
        </div>
    </div>

    <h3>Detalle de Expedientes</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 12%">Código</th>
                <th style="width: 10%">Fecha</th>
                <th style="width: 25%">Asunto</th>
                <th style="width: 15%">Tipo Trámite</th>
                <th style="width: 12%">Área</th>
                <th style="width: 8%">Estado</th>
                <th style="width: 18%">Remitente</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expedientes as $exp)
            <tr>
                <td>{{ $exp->codigo_expediente }}</td>
                <td>{{ $exp->created_at->format('d/m/Y') }}</td>
                <td>{{ Str::limit($exp->asunto, 50) }}</td>
                <td>{{ Str::limit($exp->tipoTramite?->nombre ?? 'N/A', 25) }}</td>
                <td>{{ Str::limit($exp->area?->nombre ?? 'Sin asignar', 20) }}</td>
                <td>
                    @php
                        $badgeClass = match($exp->estado) {
                            'resuelto', 'notificado' => 'badge-success',
                            'derivado', 'en_proceso' => 'badge-warning',
                            'observado' => 'badge-danger',
                            'archivado' => 'badge-secondary',
                            default => 'badge-info'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $exp->estado)) }}</span>
                </td>
                <td>{{ Str::limit($exp->persona?->nombre_completo ?? $exp->remitente ?? 'N/A', 30) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }} | Sistema Mesa de Partes</p>
    </div>
</body>
</html>
