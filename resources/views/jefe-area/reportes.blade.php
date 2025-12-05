@extends('layouts.app')

@section('title', 'Reportes del Área')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Reportes del Área</h2>
                <span class="badge bg-info">{{ auth()->user()->area->nombre ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <!-- Métricas Generales -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ $reportes['tiempos_promedio']->promedio_dias ?? 0 }}</h3>
                    <p>Días Promedio de Atención</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $reportes['funcionarios_rendimiento']->sum('resueltos') }}</h3>
                    <p>Expedientes Resueltos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3>{{ $reportes['funcionarios_rendimiento']->count() }}</h3>
                    <p>Funcionarios Activos</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Rendimiento por Funcionario -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Rendimiento por Funcionario</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Funcionario</th>
                                    <th>Total Asignados</th>
                                    <th>Resueltos</th>
                                    <th>% Efectividad</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportes['funcionarios_rendimiento'] as $funcionario)
                                <tr>
                                    <td>{{ $funcionario->name }}</td>
                                    <td>{{ $funcionario->total }}</td>
                                    <td>{{ $funcionario->resueltos }}</td>
                                    <td>
                                        @php
                                            $efectividad = $funcionario->total > 0 ? ($funcionario->resueltos / $funcionario->total) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $efectividad >= 80 ? 'success' : ($efectividad >= 60 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ $efectividad }}%">
                                                {{ number_format($efectividad, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $funcionario->activo ? 'success' : 'secondary' }}">
                                            {{ $funcionario->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
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

    <!-- Expedientes por Mes -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Expedientes por Mes ({{ now()->year }})</h5>
                </div>
                <div class="card-body">
                    <canvas id="expedientesPorMes" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Cumplimiento de Plazos</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6>Análisis de Cumplimiento</h6>
                        <ul class="mb-0">
                            <li>Expedientes en plazo: <strong>85%</strong></li>
                            <li>Expedientes vencidos: <strong>15%</strong></li>
                            <li>Tiempo promedio: <strong>{{ $reportes['tiempos_promedio']->promedio_dias ?? 0 }} días</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones de Mejora -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Recomendaciones de Mejora</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle"></i> Atención Requerida</h6>
                                <ul class="mb-0">
                                    <li>Funcionarios con baja efectividad</li>
                                    <li>Expedientes próximos a vencer</li>
                                    <li>Procesos que exceden tiempo promedio</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-success">
                                <h6><i class="fas fa-check-circle"></i> Fortalezas</h6>
                                <ul class="mb-0">
                                    <li>Alto porcentaje de resolución</li>
                                    <li>Cumplimiento de plazos legales</li>
                                    <li>Equipo de trabajo estable</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('expedientesPorMes').getContext('2d');
const expedientesPorMes = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        datasets: [{
            label: 'Expedientes',
            data: [
                @foreach(range(1, 12) as $mes)
                    {{ $reportes['expedientes_por_mes']->where('mes', $mes)->first()->total ?? 0 }},
                @endforeach
            ],
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endsection