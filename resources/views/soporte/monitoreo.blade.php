@extends('layouts.app')

@section('title', 'Monitoreo del Sistema')

@section('content')
<div class="container-fluid">
    <!-- Estado del Sistema -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-{{ $sistema['estado'] == 'operativo' ? 'success' : 'danger' }} text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5>Sistema</h5>
                            <p class="mb-0">{{ ucfirst($sistema['estado']) }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-{{ $sistema['estado'] == 'operativo' ? 'check-circle' : 'exclamation-triangle' }} fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5>Usuarios Activos</h5>
                            <p class="mb-0">{{ $sistema['usuarios_activos'] }}</p>
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
                            <h5>Uso CPU</h5>
                            <p class="mb-0">{{ $sistema['cpu_uso'] }}%</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-microchip fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5>Memoria</h5>
                            <p class="mb-0">{{ $sistema['memoria_uso'] }}%</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-memory fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Logs del Sistema -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Logs del Sistema</h4>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" onclick="filtrarLogs('todos')">Todos</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="filtrarLogs('error')">Errores</button>
                        <button class="btn btn-sm btn-outline-warning" onclick="filtrarLogs('warning')">Advertencias</button>
                        <button class="btn btn-sm btn-outline-info" onclick="filtrarLogs('info')">Info</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm">
                            <thead class="sticky-top bg-light">
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>Nivel</th>
                                    <th>Mensaje</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody id="tablaLogs">
                                @foreach($logs as $log)
                                <tr data-nivel="{{ strtolower($log->nivel) }}">
                                    <td class="text-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $log->nivel == 'ERROR' ? 'danger' : 
                                            ($log->nivel == 'WARNING' ? 'warning' : 
                                            ($log->nivel == 'INFO' ? 'info' : 'secondary')) 
                                        }}">
                                            {{ $log->nivel }}
                                        </span>
                                    </td>
                                    <td>{{ $log->mensaje }}</td>
                                    <td>{{ $log->usuario->name ?? 'Sistema' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas y Alertas -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title">Estadísticas de Hoy</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary">{{ $estadisticas['expedientes_hoy'] }}</h4>
                            <small class="text-muted">Expedientes</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $estadisticas['resueltos_hoy'] }}</h4>
                            <small class="text-muted">Resueltos</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-warning">{{ $estadisticas['derivaciones_hoy'] }}</h4>
                            <small class="text-muted">Derivaciones</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info">{{ $estadisticas['usuarios_conectados'] }}</h4>
                            <small class="text-muted">Conectados</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Alertas del Sistema</h5>
                </div>
                <div class="card-body">
                    @if($alertas->count() > 0)
                        @foreach($alertas as $alerta)
                        <div class="alert alert-{{ $alerta->tipo == 'critica' ? 'danger' : ($alerta->tipo == 'advertencia' ? 'warning' : 'info') }} alert-dismissible fade show">
                            <strong>{{ ucfirst($alerta->tipo) }}:</strong>
                            {{ $alerta->mensaje }}
                            <small class="d-block mt-1">{{ $alerta->created_at->diffForHumans() }}</small>
                            <button type="button" class="btn-close" onclick="cerrarAlerta({{ $alerta->id }})"></button>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p>No hay alertas activas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Rendimiento -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Rendimiento del Sistema (Últimas 24 horas)</h4>
                </div>
                <div class="card-body">
                    <canvas id="graficoRendimiento" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Filtrar logs por nivel
function filtrarLogs(nivel) {
    const filas = document.querySelectorAll('#tablaLogs tr');
    
    filas.forEach(fila => {
        const nivelFila = fila.dataset.nivel;
        const mostrar = nivel === 'todos' || nivelFila === nivel;
        fila.style.display = mostrar ? '' : 'none';
    });
    
    // Actualizar botón activo
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

// Cerrar alerta
function cerrarAlerta(id) {
    fetch(`/soporte/alertas/${id}/cerrar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
}

// Gráfico de rendimiento
const ctx = document.getElementById('graficoRendimiento').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($graficoLabels) !!},
        datasets: [{
            label: 'CPU %',
            data: {!! json_encode($graficoCPU) !!},
            borderColor: 'rgb(255, 99, 132)',
            tension: 0.1
        }, {
            label: 'Memoria %',
            data: {!! json_encode($graficoMemoria) !!},
            borderColor: 'rgb(54, 162, 235)',
            tension: 0.1
        }, {
            label: 'Usuarios Activos',
            data: {!! json_encode($graficoUsuarios) !!},
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true
            }
        }
    }
});

// Auto-refresh cada 30 segundos
setInterval(() => {
    location.reload();
}, 30000);
</script>
@endsection