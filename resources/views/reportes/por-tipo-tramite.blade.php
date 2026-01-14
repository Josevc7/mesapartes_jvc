@extends('layouts.app')

@section('title', 'Reporte por Tipo de Tramite')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-folder-open"></i> Reporte por Tipo de Tramite</h2>
                <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Resumen General -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Resumen por Tipo de Tramite</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tipo de Tramite</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Resueltos</th>
                                    <th class="text-center">Pendientes</th>
                                    <th>% Resolucion</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($estadisticasPorTipo as $tipo)
                                <tr class="{{ $tipoTramiteId == $tipo->id_tipo_tramite ? 'table-active' : '' }}">
                                    <td>
                                        <strong>{{ $tipo->nombre }}</strong>
                                        @if($tipo->descripcion)
                                            <br><small class="text-muted">{{ Str::limit($tipo->descripcion, 50) }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center"><span class="badge bg-primary">{{ $tipo->expedientes_count }}</span></td>
                                    <td class="text-center"><span class="badge bg-success">{{ $tipo->resueltos_count }}</span></td>
                                    <td class="text-center"><span class="badge bg-warning">{{ $tipo->pendientes_count }}</span></td>
                                    <td>
                                        @php
                                            $pct = $tipo->expedientes_count > 0 ? round(($tipo->resueltos_count / $tipo->expedientes_count) * 100, 1) : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $pct >= 70 ? 'success' : ($pct >= 40 ? 'warning' : 'danger') }}"
                                                 style="width: {{ $pct }}%">{{ $pct }}%</div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('reportes.por-tipo-tramite', ['tipo_tramite' => $tipo->id_tipo_tramite]) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> Ver Detalle
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No hay tipos de tramite registrados</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($tipoSeleccionado)
    <!-- Detalle del Tipo Seleccionado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Detalle: {{ $tipoSeleccionado->nombre }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filtros de fecha -->
                    <form method="GET" class="mb-4">
                        <input type="hidden" name="tipo_tramite" value="{{ $tipoTramiteId }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Estadisticas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $estadisticasDetalle['total'] }}</h3>
                                    <small>Total en periodo</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $estadisticasDetalle['por_estado']['resuelto'] ?? 0 }}</h3>
                                    <small>Resueltos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3>{{ ($estadisticasDetalle['por_estado']['derivado'] ?? 0) + ($estadisticasDetalle['por_estado']['en_proceso'] ?? 0) }}</h3>
                                    <small>En Proceso</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ ($estadisticasDetalle['por_estado']['recepcionado'] ?? 0) + ($estadisticasDetalle['por_estado']['registrado'] ?? 0) }}</h3>
                                    <small>Pendientes</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <!-- Por Area -->
                        <div class="col-md-6">
                            <h6><i class="fas fa-building"></i> Distribucion por Area</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Area</th><th class="text-center">Cantidad</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($estadisticasDetalle['por_area'] as $area)
                                    <tr>
                                        <td>{{ $area->area->nombre ?? 'Sin asignar' }}</td>
                                        <td class="text-center">{{ $area->total }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Por Mes -->
                        <div class="col-md-6">
                            <h6><i class="fas fa-calendar"></i> Expedientes por Mes ({{ now()->year }})</h6>
                            <canvas id="chartPorMes" height="150"></canvas>
                        </div>
                    </div>

                    <!-- Tabla de expedientes -->
                    <h6><i class="fas fa-list"></i> Expedientes</h6>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Fecha</th>
                                    <th>Asunto</th>
                                    <th>Area</th>
                                    <th>Estado</th>
                                    <th>Remitente</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expedientes as $exp)
                                <tr>
                                    <td><a href="{{ route('mesa-partes.show', $exp) }}">{{ $exp->codigo_expediente }}</a></td>
                                    <td>{{ $exp->created_at->format('d/m/Y') }}</td>
                                    <td>{{ Str::limit($exp->asunto, 40) }}</td>
                                    <td>{{ $exp->area->nombre ?? 'N/A' }}</td>
                                    <td><span class="badge bg-info">{{ ucfirst($exp->estado) }}</span></td>
                                    <td>{{ $exp->persona->nombre_completo ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted">No hay expedientes</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($expedientes->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $expedientes->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        const datosMes = @json($estadisticasDetalle['por_mes']);
        const valores = meses.map((_, i) => {
            const dato = datosMes.find(d => d.mes === i + 1);
            return dato ? dato.total : 0;
        });

        new Chart(document.getElementById('chartPorMes').getContext('2d'), {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Expedientes',
                    data: valores,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: { scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } }
        });
    });
    </script>
    @endif
</div>
@endsection
