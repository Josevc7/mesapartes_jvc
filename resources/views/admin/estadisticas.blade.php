@extends('layouts.app')

@section('title', 'Estadísticas Globales')

@section('content')
<div class="container-fluid">
    <!-- Filtros de Período -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fechaInicio" value="{{ $fechaInicio }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fechaFin" value="{{ $fechaFin }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Período Rápido</label>
                            <select class="form-select" id="periodoRapido" onchange="aplicarPeriodo()">
                                <option value="">Personalizado</option>
                                <option value="hoy">Hoy</option>
                                <option value="semana">Esta Semana</option>
                                <option value="mes">Este Mes</option>
                                <option value="trimestre">Este Trimestre</option>
                                <option value="año">Este Año</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" onclick="actualizarEstadisticas()">
                                <i class="fas fa-sync"></i> Actualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Principales -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h3>{{ $kpis['total_expedientes'] }}</h3>
                    <p class="mb-0">Total Expedientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h3>{{ $kpis['completados'] }}</h3>
                    <p class="mb-0">Completados</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center bg-warning text-white">
                <div class="card-body">
                    <h3>{{ $kpis['en_proceso'] }}</h3>
                    <p class="mb-0">En Proceso</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center bg-danger text-white">
                <div class="card-body">
                    <h3>{{ $kpis['vencidos'] }}</h3>
                    <p class="mb-0">Vencidos</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center bg-info text-white">
                <div class="card-body">
                    <h3>{{ number_format($kpis['tiempo_promedio'], 1) }}</h3>
                    <p class="mb-0">Días Promedio</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center bg-secondary text-white">
                <div class="card-body">
                    <h3>{{ $kpis['eficiencia'] }}%</h3>
                    <p class="mb-0">Eficiencia</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Principales -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> Tendencia de Expedientes</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoTendencia" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie"></i> Distribución por Estado</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoEstados" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis por Área y Tipo de Trámite -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-building"></i> Rendimiento por Área</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoAreas" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-tags"></i> Top Tipos de Trámite</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoTiposTramite" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas de Análisis Detallado -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-users"></i> Rendimiento por Usuario</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Asignados</th>
                                    <th>Completados</th>
                                    <th>% Eficiencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rendimientoUsuarios as $usuario)
                                <tr>
                                    <td>{{ $usuario['nombre'] }}</td>
                                    <td>{{ $usuario['asignados'] }}</td>
                                    <td>{{ $usuario['completados'] }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" style="width: {{ $usuario['eficiencia'] }}%">
                                                {{ $usuario['eficiencia'] }}%
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
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-clock"></i> Análisis de Tiempos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tipo Trámite</th>
                                    <th>Plazo (días)</th>
                                    <th>Promedio Real</th>
                                    <th>Cumplimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analisisTiempos as $tiempo)
                                <tr>
                                    <td>{{ $tiempo['tipo'] }}</td>
                                    <td>{{ $tiempo['plazo'] }}</td>
                                    <td>{{ number_format($tiempo['promedio'], 1) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $tiempo['cumplimiento'] >= 80 ? 'success' : ($tiempo['cumplimiento'] >= 60 ? 'warning' : 'danger') }}">
                                            {{ $tiempo['cumplimiento'] }}%
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
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de Tendencia
const ctxTendencia = document.getElementById('graficoTendencia').getContext('2d');
new Chart(ctxTendencia, {
    type: 'line',
    data: {
        labels: {!! json_encode($graficoTendencia['labels']) !!},
        datasets: [{
            label: 'Registrados',
            data: {!! json_encode($graficoTendencia['registrados']) !!},
            borderColor: '#cc5500',
            backgroundColor: 'rgba(204, 85, 0, 0.1)'
        }, {
            label: 'Completados',
            data: {!! json_encode($graficoTendencia['completados']) !!},
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Gráfico de Estados
const ctxEstados = document.getElementById('graficoEstados').getContext('2d');
new Chart(ctxEstados, {
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

// Gráfico de Áreas
const ctxAreas = document.getElementById('graficoAreas').getContext('2d');
new Chart(ctxAreas, {
    type: 'bar',
    data: {
        labels: {!! json_encode($graficoAreas['labels']) !!},
        datasets: [{
            label: 'Expedientes',
            data: {!! json_encode($graficoAreas['data']) !!},
            backgroundColor: '#cc5500'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Gráfico de Tipos de Trámite
const ctxTipos = document.getElementById('graficoTiposTramite').getContext('2d');
new Chart(ctxTipos, {
    type: 'horizontalBar',
    data: {
        labels: {!! json_encode($graficoTiposTramite['labels']) !!},
        datasets: [{
            label: 'Cantidad',
            data: {!! json_encode($graficoTiposTramite['data']) !!},
            backgroundColor: '#17a2b8'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

function aplicarPeriodo() {
    const periodo = document.getElementById('periodoRapido').value;
    const hoy = new Date();
    let inicio, fin;
    
    switch(periodo) {
        case 'hoy':
            inicio = fin = hoy.toISOString().split('T')[0];
            break;
        case 'semana':
            inicio = new Date(hoy.setDate(hoy.getDate() - hoy.getDay())).toISOString().split('T')[0];
            fin = new Date().toISOString().split('T')[0];
            break;
        case 'mes':
            inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
            fin = new Date().toISOString().split('T')[0];
            break;
        case 'trimestre':
            const trimestre = Math.floor(hoy.getMonth() / 3);
            inicio = new Date(hoy.getFullYear(), trimestre * 3, 1).toISOString().split('T')[0];
            fin = new Date().toISOString().split('T')[0];
            break;
        case 'año':
            inicio = new Date(hoy.getFullYear(), 0, 1).toISOString().split('T')[0];
            fin = new Date().toISOString().split('T')[0];
            break;
    }
    
    if (inicio && fin) {
        document.getElementById('fechaInicio').value = inicio;
        document.getElementById('fechaFin').value = fin;
    }
}

function actualizarEstadisticas() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    
    window.location.href = `{{ route('admin.estadisticas') }}?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
}
</script>
@endsection
@endsection