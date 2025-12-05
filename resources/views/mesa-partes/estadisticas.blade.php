@extends('layouts.app')

@section('title', 'Estadísticas Mesa de Partes')

@section('content')
<div class="container-fluid">
    <!-- Resumen del Día -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['registrados_hoy'] }}</h4>
                            <p class="mb-0">Registrados Hoy</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-plus fa-2x"></i>
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
                            <h4>{{ $estadisticas['clasificados_hoy'] }}</h4>
                            <p class="mb-0">Clasificados Hoy</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tags fa-2x"></i>
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
                            <h4>{{ $estadisticas['derivados_hoy'] }}</h4>
                            <p class="mb-0">Derivados Hoy</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-share fa-2x"></i>
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
                            <h4>{{ $estadisticas['archivados_hoy'] }}</h4>
                            <p class="mb-0">Archivados Hoy</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-archive fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de Expedientes por Día -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Expedientes por Día (Últimos 30 días)</h4>
                </div>
                <div class="card-body">
                    <canvas id="graficoExpedientes" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Tipos de Trámite -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Tipos de Trámite Más Frecuentes</h4>
                </div>
                <div class="card-body">
                    @foreach($tiposTramiteFrecuentes as $tipo)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>{{ $tipo->nombre }}</span>
                            <span class="badge bg-primary">{{ $tipo->expedientes_count }}</span>
                        </div>
                        <div class="progress mt-1">
                            @php
                                $maxTramites = $tiposTramiteFrecuentes->first()->expedientes_count ?? 0;
                                $porcentaje = $maxTramites > 0 ? ($tipo->expedientes_count / $maxTramites) * 100 : 0;
                            @endphp
                            <div class="progress-bar" style="width: {{ $porcentaje }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Expedientes Pendientes -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Expedientes Pendientes de Clasificación/Derivación</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Asunto</th>
                                    <th>Ciudadano</th>
                                    <th>Fecha Registro</th>
                                    <th>Estado</th>
                                    <th>Días Pendiente</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expedientesPendientes as $expediente)
                                <tr>
                                    <td><strong>{{ $expediente->codigo_expediente }}</strong></td>
                                    <td>{{ Str::limit($expediente->asunto, 50) }}</td>
                                    <td>{{ $expediente->remitente ?? ($expediente->ciudadano->name ?? 'N/A') }}</td>
                                    <td>{{ $expediente->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $expediente->estado == 'pendiente' ? 'warning' : 'info' 
                                        }}">
                                            {{ ucfirst($expediente->estado) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $diasPendiente = $expediente->created_at->diffInDays(now());
                                        @endphp
                                        <span class="text-{{ $diasPendiente > 2 ? 'danger' : ($diasPendiente > 1 ? 'warning' : 'success') }}">
                                            {{ $diasPendiente }} días
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('mesa-partes.show', $expediente) }}" 
                                               class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($expediente->estado == 'pendiente')
                                            <a href="{{ route('mesa-partes.clasificar', $expediente) }}" 
                                               class="btn btn-outline-info">
                                                <i class="fas fa-tags"></i>
                                            </a>
                                            @elseif($expediente->estado == 'derivado')
                                            <a href="{{ route('mesa-partes.derivar', $expediente) }}" 
                                               class="btn btn-outline-warning">
                                                <i class="fas fa-share"></i>
                                            </a>
                                            @endif
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de expedientes por día
const ctx = document.getElementById('graficoExpedientes').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($graficoLabels) !!},
        datasets: [{
            label: 'Registrados',
            data: {!! json_encode($graficoRegistrados) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Derivados',
            data: {!! json_encode($graficoDerivados) !!},
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endsection