@extends('layouts.app')

@section('title', 'Dashboard Administrativo')

@section('content')
<div class="container-fluid">
    <!-- Métricas Principales -->
    <!--<div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $metricas['total_expedientes'] }}</h4>
                            <p class="mb-0">Total Expedientes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-folder fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $metricas['usuarios_activos'] }}</h4>
                            <p class="mb-0">Usuarios Activos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $metricas['expedientes_pendientes'] }}</h4>
                            <p class="mb-0">Expedientes Pendientes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $metricas['expedientes_vencidos'] }}</h4>
                            <p class="mb-0">Expedientes Vencidos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>--> 

    <!-- Gráficos y Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> Expedientes por Mes (Últimos 6 meses)</h5>
                </div>
                <div class="card-body">
                    <canvas id="expedientesPorMes" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie"></i> Expedientes por Estado</h5>
                </div>
                <div class="card-body">
                    <canvas id="expedientesPorEstado" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Actividad Reciente y Alertas -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-history"></i> Actividad Reciente</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($actividadReciente as $actividad)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ $actividad->accion }}</h6>
                                <p class="timeline-text">{{ $actividad->descripcion }}</p>
                                <small class="text-muted">{{ $actividad->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-bell"></i> Alertas del Sistema</h5>
                </div>
                <div class="card-body">
                    @foreach($alertas as $alerta)
                    <div class="alert alert-{{ $alerta['tipo'] }} alert-dismissible fade show">
                        <strong>{{ $alerta['titulo'] }}</strong>
                        <p class="mb-0">{{ $alerta['mensaje'] }}</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Rendimiento por Área -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-building"></i> Rendimiento por Área</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Área</th>
                                    <th>Expedientes Asignados</th>
                                    <th>Completados</th>
                                    <th>Pendientes</th>
                                    <th>Vencidos</th>
                                    <th>% Eficiencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rendimientoPorArea as $area)
                                <tr>
                                    <td>{{ $area['nombre'] }}</td>
                                    <td>{{ $area['total'] }}</td>
                                    <td><span class="badge bg-success">{{ $area['completados'] }}</span></td>
                                    <td><span class="badge bg-warning">{{ $area['pendientes'] }}</span></td>
                                    <td><span class="badge bg-danger">{{ $area['vencidos'] }}</span></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: {{ $area['eficiencia'] }}%">
                                                {{ $area['eficiencia'] }}%
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
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -31px;
    top: 15px;
    width: 2px;
    height: calc(100% + 10px);
    background-color: #dee2e6;
}
</style>
@endsection
@endsection