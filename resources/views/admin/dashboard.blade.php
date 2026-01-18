@extends('layouts.app')

@section('title', 'Dashboard Administrativo')

@section('content')
<div class="container-fluid">
    <!-- Accesos Rápidos de Administrador -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-dark text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <span><i class="fas fa-shield-alt me-2"></i> Panel de Administración</span>
                        <div class="btn-group">
                            <a href="{{ route('admin.expedientes') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-folder-open"></i> Expedientes
                            </a>
                            <a href="{{ route('admin.usuarios') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-users"></i> Usuarios
                            </a>
                            <a href="{{ route('admin.permisos') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-user-shield"></i> Permisos
                            </a>
                            <a href="{{ route('admin.estados') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-exchange-alt"></i> Estados
                            </a>
                            <a href="{{ route('admin.mesa-virtual') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-desktop"></i> Mesa Virtual
                            </a>
                            <a href="{{ route('admin.auditoria-completa') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-history"></i> Auditoría
                            </a>
                            <a href="{{ route('admin.estadisticas') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-chart-bar"></i> Estadísticas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas Principales (compactas) -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.expedientes') }}" class="stat-card bg-primary text-white text-decoration-none">
            <i class="fas fa-folder"></i>
            <strong>{{ $metricas['total_expedientes'] }}</strong>
            <span>Total Expedientes</span>
        </a>
        <a href="{{ route('admin.usuarios') }}" class="stat-card bg-success text-white text-decoration-none">
            <i class="fas fa-users"></i>
            <strong>{{ $metricas['usuarios_activos'] }}</strong>
            <span>Usuarios Activos</span>
        </a>
        <a href="{{ route('admin.expedientes', ['estado' => 'en_proceso']) }}" class="stat-card bg-warning text-dark text-decoration-none">
            <i class="fas fa-clock"></i>
            <strong>{{ $metricas['expedientes_pendientes'] }}</strong>
            <span>Pendientes</span>
        </a>
        <a href="{{ route('admin.expedientes') }}" class="stat-card bg-danger text-white text-decoration-none">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>{{ $metricas['expedientes_vencidos'] }}</strong>
            <span>Vencidos</span>
        </a>
    </div>

    <!-- Gráficos y Estadísticas (compactos) -->
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="card card-compact">
                <div class="card-header py-2">
                    <h6 class="mb-0"><i class="fas fa-chart-line text-primary"></i> Expedientes por Mes</h6>
                </div>
                <div class="card-body py-2">
                    <canvas id="expedientesPorMes" height="70"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-compact">
                <div class="card-header py-2">
                    <h6 class="mb-0"><i class="fas fa-chart-pie text-info"></i> Por Estado</h6>
                </div>
                <div class="card-body py-2">
                    <canvas id="expedientesPorEstado" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Actividad Reciente y Alertas (compactos) -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card card-compact">
                <div class="card-header py-2">
                    <h6 class="mb-0"><i class="fas fa-history text-secondary"></i> Actividad Reciente</h6>
                </div>
                <div class="card-body py-2" style="max-height: 200px; overflow-y: auto;">
                    <div class="timeline-compact">
                        @foreach($actividadReciente as $actividad)
                        <div class="timeline-item-compact">
                            <span class="timeline-dot bg-primary"></span>
                            <div class="timeline-info">
                                <strong class="small">{{ $actividad->accion }}</strong>
                                <span class="text-muted small">- {{ $actividad->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-compact">
                <div class="card-header py-2">
                    <h6 class="mb-0"><i class="fas fa-bell text-warning"></i> Alertas del Sistema</h6>
                </div>
                <div class="card-body py-2" style="max-height: 200px; overflow-y: auto;">
                    @foreach($alertas as $alerta)
                    <div class="alert alert-{{ $alerta['tipo'] }} py-1 px-2 mb-1 small">
                        <strong>{{ $alerta['titulo'] }}:</strong> {{ $alerta['mensaje'] }}
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Rendimiento por Área (compacto) -->
    <div class="row">
        <div class="col-12">
            <div class="card card-compact">
                <div class="card-header py-2">
                    <h6 class="mb-0"><i class="fas fa-building text-dark"></i> Rendimiento por Área</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small">Área</th>
                                    <th class="small text-center">Total</th>
                                    <th class="small text-center">OK</th>
                                    <th class="small text-center">Pend.</th>
                                    <th class="small text-center">Venc.</th>
                                    <th class="small" style="width: 120px;">Eficiencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rendimientoPorArea as $area)
                                <tr>
                                    <td class="small">{{ Str::limit($area['nombre'], 25) }}</td>
                                    <td class="text-center small">{{ $area['total'] }}</td>
                                    <td class="text-center"><span class="badge bg-success small">{{ $area['completados'] }}</span></td>
                                    <td class="text-center"><span class="badge bg-warning small">{{ $area['pendientes'] }}</span></td>
                                    <td class="text-center"><span class="badge bg-danger small">{{ $area['vencidos'] }}</span></td>
                                    <td>
                                        <div class="progress" style="height: 12px;">
                                            <div class="progress-bar bg-info" style="width: {{ $area['eficiencia'] }}%">
                                                <small>{{ $area['eficiencia'] }}%</small>
                                            </div>
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
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de Expedientes por Mes
const ctxMes = document.getElementById('expedientesPorMes').getContext('2d');
new Chart(ctxMes, {
    type: 'line',
    data: {
        labels: {!! json_encode($graficoMeses['labels']) !!},
        datasets: [{
            label: 'Expedientes Registrados',
            data: {!! json_encode($graficoMeses['data']) !!},
            borderColor: '#cc5500',
            backgroundColor: 'rgba(204, 85, 0, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Gráfico de Expedientes por Estado
const ctxEstado = document.getElementById('expedientesPorEstado').getContext('2d');
new Chart(ctxEstado, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($graficoEstados['labels']) !!},
        datasets: [{
            data: {!! json_encode($graficoEstados['data']) !!},
            backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#6c757d', '#17a2b8']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>

<style>
/* Tarjetas de métricas compactas */
.stat-card {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.85rem;
    transition: transform 0.15s, opacity 0.15s;
}
.stat-card:hover {
    transform: scale(1.02);
    opacity: 0.9;
}
.stat-card i {
    font-size: 1rem;
}
.stat-card strong {
    font-size: 1.1rem;
}
.stat-card span {
    font-size: 0.75rem;
    opacity: 0.9;
}

/* Cards compactas */
.card-compact {
    border-radius: 6px;
}
.card-compact .card-header {
    border-bottom: 1px solid rgba(0,0,0,0.08);
}

/* Timeline compacta */
.timeline-compact {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.timeline-item-compact {
    display: flex;
    align-items: center;
    gap: 8px;
}
.timeline-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}
.timeline-info {
    line-height: 1.3;
}
</style>
@endsection
@endsection