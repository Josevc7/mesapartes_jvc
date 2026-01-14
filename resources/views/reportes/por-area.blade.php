@extends('layouts.app')

@section('title', 'Reporte por Area')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-building"></i> Reporte por Area</h2>
                <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Resumen General por Area -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Rendimiento por Area</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Area</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Resueltos</th>
                                    <th class="text-center">Pendientes</th>
                                    <th class="text-center">Vencidos</th>
                                    <th>Eficiencia</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($estadisticasPorArea as $area)
                                <tr class="{{ $areaId == $area->id_area ? 'table-active' : '' }}">
                                    <td>
                                        <strong>{{ $area->nombre }}</strong>
                                        @if($area->descripcion)
                                            <br><small class="text-muted">{{ $area->descripcion }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center"><span class="badge bg-primary">{{ $area->expedientes_count }}</span></td>
                                    <td class="text-center"><span class="badge bg-success">{{ $area->resueltos_count }}</span></td>
                                    <td class="text-center"><span class="badge bg-warning">{{ $area->pendientes_count }}</span></td>
                                    <td class="text-center">
                                        @if($area->vencidos_count > 0)
                                            <span class="badge bg-danger">{{ $area->vencidos_count }}</span>
                                        @else
                                            <span class="badge bg-secondary">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $pct = $area->expedientes_count > 0 ? round(($area->resueltos_count / $area->expedientes_count) * 100, 1) : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $pct >= 70 ? 'success' : ($pct >= 40 ? 'warning' : 'danger') }}"
                                                 style="width: {{ $pct }}%">{{ $pct }}%</div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('reportes.por-area', ['area' => $area->id_area]) }}"
                                           class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-eye"></i> Ver Detalle
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No hay areas registradas</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($areaSeleccionada)
    <!-- Detalle del Area Seleccionada -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Detalle: {{ $areaSeleccionada->nombre }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <form method="GET" class="mb-4">
                        <input type="hidden" name="area" value="{{ $areaId }}">
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
                                <button type="submit" class="btn btn-success w-100">
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
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ round($estadisticasDetalle['promedio_atencion'], 1) }}</h3>
                                    <small>Dias Prom. Atencion</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <!-- Por Tipo Tramite -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-folder"></i> Por Tipo de Tramite</h6>
                                </div>
                                <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr><th>Tipo</th><th class="text-center">Cantidad</th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach($estadisticasDetalle['por_tipo'] as $tipo)
                                            <tr>
                                                <td>{{ $tipo->tipoTramite->nombre ?? 'N/A' }}</td>
                                                <td class="text-center">{{ $tipo->total }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Rendimiento Funcionarios -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-users"></i> Rendimiento de Funcionarios</h6>
                                </div>
                                <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Funcionario</th>
                                                <th class="text-center">Asignados</th>
                                                <th class="text-center">Resueltos</th>
                                                <th>%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($funcionariosArea as $func)
                                            <tr>
                                                <td>{{ $func->name }}</td>
                                                <td class="text-center">{{ $func->total_asignados }}</td>
                                                <td class="text-center">{{ $func->resueltos }}</td>
                                                <td>
                                                    @php
                                                        $pctFunc = $func->total_asignados > 0 ? round(($func->resueltos / $func->total_asignados) * 100) : 0;
                                                    @endphp
                                                    <span class="badge bg-{{ $pctFunc >= 70 ? 'success' : ($pctFunc >= 40 ? 'warning' : 'secondary') }}">
                                                        {{ $pctFunc }}%
                                                    </span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="4" class="text-muted text-center">Sin funcionarios</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de expedientes -->
                    <h6><i class="fas fa-list"></i> Expedientes del Area</h6>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Fecha</th>
                                    <th>Asunto</th>
                                    <th>Tipo Tramite</th>
                                    <th>Estado</th>
                                    <th>Funcionario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expedientes as $exp)
                                <tr>
                                    <td><a href="{{ route('mesa-partes.show', $exp) }}">{{ $exp->codigo_expediente }}</a></td>
                                    <td>{{ $exp->created_at->format('d/m/Y') }}</td>
                                    <td>{{ Str::limit($exp->asunto, 35) }}</td>
                                    <td>{{ $exp->tipoTramite->nombre ?? 'N/A' }}</td>
                                    <td><span class="badge bg-info">{{ ucfirst($exp->estado) }}</span></td>
                                    <td>{{ $exp->funcionarioAsignado->name ?? 'Sin asignar' }}</td>
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
    @endif
</div>
@endsection
