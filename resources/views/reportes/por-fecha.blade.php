@extends('layouts.app')

@section('title', 'Reporte de Trámites por Fecha')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-calendar-alt"></i> Reporte de Trámites por Fecha</h2>
                <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filtros de Búsqueda</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reportes.por-fecha') }}" id="filtrosForm">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control"
                                       value="{{ $fechaInicio }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" name="fecha_fin" class="form-control"
                                       value="{{ $fechaFin }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo de Trámite</label>
                                <select name="tipo_tramite" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach($tiposTramite as $tipo)
                                        <option value="{{ $tipo->id_tipo_tramite }}"
                                                {{ $tipoTramite == $tipo->id_tipo_tramite ? 'selected' : '' }}>
                                            {{ $tipo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Área</label>
                                <select name="area" class="form-select">
                                    <option value="">Todas</option>
                                    @foreach($areas as $areaItem)
                                        <option value="{{ $areaItem->id_area }}"
                                                {{ $area == $areaItem->id_area ? 'selected' : '' }}>
                                            {{ $areaItem->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach($estados as $est)
                                        <option value="{{ $est }}" {{ $estado == $est ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $est)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $estadisticas['total'] }}</h3>
                    <small>Total de Trámites</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $estadisticas['por_estado']['resuelto'] ?? 0 }}</h3>
                    <small>Resueltos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ ($estadisticas['por_estado']['derivado'] ?? 0) + ($estadisticas['por_estado']['en_proceso'] ?? 0) }}</h3>
                    <small>En Proceso</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ ($estadisticas['por_estado']['recepcionado'] ?? 0) + ($estadisticas['por_estado']['registrado'] ?? 0) }}</h3>
                    <small>Pendientes</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Trámites por Día</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartPorDia" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Por Estado</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartPorEstado" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribución por Tipo y Área -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Por Tipo de Trámite</h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Tipo de Trámite</th>
                                <th class="text-center">Cantidad</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estadisticas['por_tipo_tramite'] as $tipo)
                            <tr>
                                <td>{{ $tipo->tipoTramite->nombre ?? 'Sin tipo' }}</td>
                                <td class="text-center">{{ $tipo->total }}</td>
                                <td>
                                    @php $pct = $estadisticas['total'] > 0 ? round(($tipo->total / $estadisticas['total']) * 100, 1) : 0; @endphp
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $pct }}%">{{ $pct }}%</div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-building"></i> Por Área</h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Área</th>
                                <th class="text-center">Cantidad</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estadisticas['por_area'] as $areaItem)
                            <tr>
                                <td>{{ $areaItem->area->nombre ?? 'Sin asignar' }}</td>
                                <td class="text-center">{{ $areaItem->total }}</td>
                                <td>
                                    @php $pct = $estadisticas['total'] > 0 ? round(($areaItem->total / $estadisticas['total']) * 100, 1) : 0; @endphp
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" style="width: {{ $pct }}%">{{ $pct }}%</div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Expedientes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-table"></i> Detalle de Expedientes</h5>
                    <a href="{{ route('reportes.exportar', request()->query()) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Exportar CSV
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Fecha</th>
                                    <th>Asunto</th>
                                    <th>Tipo Trámite</th>
                                    <th>Área</th>
                                    <th>Estado</th>
                                    <th>Prioridad</th>
                                    <th>Remitente</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expedientes as $exp)
                                <tr>
                                    <td>
                                        <a href="{{ route('mesa-partes.show', $exp) }}" class="text-decoration-none">
                                            {{ $exp->codigo_expediente }}
                                        </a>
                                    </td>
                                    <td>{{ $exp->created_at->format('d/m/Y') }}</td>
                                    <td>{{ Str::limit($exp->asunto, 40) }}</td>
                                    <td>{{ $exp->tipoTramite->nombre ?? 'N/A' }}</td>
                                    <td>{{ $exp->area->nombre ?? 'Sin asignar' }}</td>
                                    <td>
                                        @php
                                            $colorEstado = match($exp->estado) {
                                                'resuelto' => 'success',
                                                'archivado' => 'secondary',
                                                'derivado', 'en_proceso' => 'primary',
                                                'recepcionado', 'registrado' => 'warning',
                                                default => 'info'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $colorEstado }}">
                                            {{ ucfirst(str_replace('_', ' ', $exp->estado)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $colorPrioridad = match($exp->prioridad) {
                                                'urgente' => 'danger',
                                                'alta' => 'warning',
                                                'normal' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $colorPrioridad }}">
                                            {{ ucfirst($exp->prioridad) }}
                                        </span>
                                    </td>
                                    <td>{{ $exp->persona->nombre_completo ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No se encontraron expedientes en el rango de fechas seleccionado
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($expedientes->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                         {{ $expedientes->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico por día
    const porDiaData = @json($estadisticas['por_dia']);
    new Chart(document.getElementById('chartPorDia').getContext('2d'), {
        type: 'line',
        data: {
            labels: porDiaData.map(d => {
                const fecha = new Date(d.fecha);
                return fecha.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit' });
            }),
            datasets: [{
                label: 'Trámites',
                data: porDiaData.map(d => d.total),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Gráfico por estado
    const porEstado = @json($estadisticas['por_estado']);
    const estadoLabels = Object.keys(porEstado).map(e => e.charAt(0).toUpperCase() + e.slice(1).replace('_', ' '));
    const estadoValues = Object.values(porEstado);
    const estadoColors = Object.keys(porEstado).map(e => {
        switch(e) {
            case 'resuelto': return '#198754';
            case 'archivado': return '#6c757d';
            case 'derivado': case 'en_proceso': return '#0d6efd';
            case 'recepcionado': case 'registrado': return '#ffc107';
            default: return '#0dcaf0';
        }
    });

    new Chart(document.getElementById('chartPorEstado').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: estadoLabels,
            datasets: [{
                data: estadoValues,
                backgroundColor: estadoColors
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
});
</script>
@endsection
