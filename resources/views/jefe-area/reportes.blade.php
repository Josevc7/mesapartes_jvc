@extends('layouts.app')

@section('title', 'Reportes del Área')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Reportes del Área</h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-building me-1"></i>
                        {{ auth()->user()->area->nombre ?? 'N/A' }} - Año {{ $reportes['año_actual'] ?? now()->year }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('jefe-area.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas Principales -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $reportes['total_expedientes'] ?? 0 }}</h3>
                            <p class="mb-0">Total Expedientes</p>
                        </div>
                        <i class="fas fa-folder fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $reportes['total_resueltos'] ?? 0 }}</h3>
                            <p class="mb-0">Resueltos</p>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ round($reportes['tiempos_promedio']->promedio_dias ?? 0, 1) }}</h3>
                            <p class="mb-0">Días Promedio</p>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $reportes['cumplimiento_porcentaje'] ?? 0 }}%</h3>
                            <p class="mb-0">Cumplimiento</p>
                        </div>
                        <i class="fas fa-chart-pie fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de Expedientes por Mes -->
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Expedientes por Mes ({{ $reportes['año_actual'] ?? now()->year }})</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartExpedientesMes" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Distribución por Prioridad -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Por Prioridad</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartPrioridad" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Rendimiento por Funcionario -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Rendimiento por Funcionario</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Funcionario</th>
                                    <th class="text-center">Total Asignados</th>
                                    <th class="text-center">Resueltos</th>
                                    <th class="text-center">Pendientes</th>
                                    <th class="text-center">Vencidos</th>
                                    <th class="text-center">Tiempo Prom.</th>
                                    <th>Efectividad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportes['funcionarios_rendimiento'] ?? [] as $funcionario)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-primary text-white rounded-circle me-2"
                                                 style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                {{ substr($funcionario->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <strong>{{ $funcionario->name }}</strong>
                                                <br><small class="text-muted">{{ $funcionario->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $funcionario->total_asignados }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $funcionario->resueltos }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">{{ $funcionario->pendientes }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $funcionario->vencidos > 0 ? 'danger' : 'secondary' }}">
                                            {{ $funcionario->vencidos }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {{ $funcionario->tiempo_promedio }} días
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                @php
                                                    $efectividad = $funcionario->efectividad;
                                                    $colorEfect = $efectividad >= 80 ? 'success' : ($efectividad >= 60 ? 'warning' : 'danger');
                                                @endphp
                                                <div class="progress-bar bg-{{ $colorEfect }}"
                                                     style="width: {{ $efectividad }}%">
                                                    {{ $efectividad }}%
                                                </div>
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

    <div class="row">
        <!-- Expedientes por Tipo de Trámite -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Por Tipo de Trámite</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($reportes['por_tipo_tramite'] ?? [] as $tipo)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $tipo['nombre'] }}
                            <span class="badge bg-primary rounded-pill">{{ $tipo['total'] }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Análisis y Recomendaciones -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Análisis del Área</h5>
                </div>
                <div class="card-body">
                    @php
                        $cumplimiento = $reportes['cumplimiento_porcentaje'] ?? 0;
                        $tiempoPromedio = $reportes['tiempos_promedio']->promedio_dias ?? 0;
                    @endphp

                    @if($cumplimiento >= 80)
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle me-1"></i> Excelente Cumplimiento</h6>
                        <p class="mb-0">El área mantiene un {{ $cumplimiento }}% de cumplimiento en plazos. Continuar con las buenas prácticas.</p>
                    </div>
                    @elseif($cumplimiento >= 60)
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-1"></i> Cumplimiento Regular</h6>
                        <p class="mb-0">El {{ 100 - $cumplimiento }}% de expedientes no cumplen plazos. Se recomienda revisar la distribución de carga.</p>
                    </div>
                    @else
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-times-circle me-1"></i> Cumplimiento Bajo</h6>
                        <p class="mb-0">Solo {{ $cumplimiento }}% de expedientes cumplen plazos. Requiere atención urgente.</p>
                    </div>
                    @endif

                    @if($tiempoPromedio <= 15)
                    <div class="alert alert-success">
                        <h6><i class="fas fa-clock me-1"></i> Tiempo de Atención Óptimo</h6>
                        <p class="mb-0">Promedio de {{ round($tiempoPromedio, 1) }} días está dentro del rango esperado.</p>
                    </div>
                    @elseif($tiempoPromedio <= 25)
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-clock me-1"></i> Tiempo de Atención Alto</h6>
                        <p class="mb-0">Promedio de {{ round($tiempoPromedio, 1) }} días. Considerar optimizar procesos.</p>
                    </div>
                    @else
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-clock me-1"></i> Tiempo de Atención Crítico</h6>
                        <p class="mb-0">Promedio de {{ round($tiempoPromedio, 1) }} días excede lo permitido. Revisión urgente.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de Expedientes por Mes
    const ctxMes = document.getElementById('chartExpedientesMes').getContext('2d');
    new Chart(ctxMes, {
        type: 'bar',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
                label: 'Recibidos',
                data: [
                    @for($i = 1; $i <= 12; $i++)
                        {{ $reportes['expedientes_por_mes'][$i]->total ?? 0 }},
                    @endfor
                ],
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Resueltos',
                data: [
                    @for($i = 1; $i <= 12; $i++)
                        {{ $reportes['resueltos_por_mes'][$i]->total ?? 0 }},
                    @endfor
                ],
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gráfico de Prioridad
    const ctxPrioridad = document.getElementById('chartPrioridad').getContext('2d');
    new Chart(ctxPrioridad, {
        type: 'doughnut',
        data: {
            labels: ['Urgente', 'Alta', 'Normal', 'Baja'],
            datasets: [{
                data: [
                    {{ $reportes['por_prioridad']['urgente']->total ?? 0 }},
                    {{ $reportes['por_prioridad']['alta']->total ?? 0 }},
                    {{ $reportes['por_prioridad']['normal']->total ?? 0 }},
                    {{ $reportes['por_prioridad']['baja']->total ?? 0 }}
                ],
                backgroundColor: [
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(13, 202, 240, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@endpush
@endsection
