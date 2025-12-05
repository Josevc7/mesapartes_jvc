@extends('layouts.app')

@section('title', 'Gestión de Metas y KPIs')

@section('content')
<div class="container-fluid">
    <!-- Resumen de KPIs -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $kpis['expedientes_mes'] }}</h4>
                            <p class="mb-0">Expedientes Este Mes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-alt fa-2x"></i>
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
                            <h4>{{ $kpis['resueltos_mes'] }}</h4>
                            <p class="mb-0">Resueltos Este Mes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
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
                            <h4>{{ number_format($kpis['tiempo_promedio'], 1) }}</h4>
                            <p class="mb-0">Días Promedio</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
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
                            <h4>{{ $kpis['eficiencia'] }}%</h4>
                            <p class="mb-0">Eficiencia</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Metas del Área -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Metas del Área</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMeta">
                        <i class="fas fa-plus"></i> Nueva Meta
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th>Tipo</th>
                                    <th>Meta</th>
                                    <th>Actual</th>
                                    <th>Progreso</th>
                                    <th>Periodo</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metas as $meta)
                                <tr>
                                    <td>{{ $meta->descripcion }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $meta->tipo }}</span>
                                    </td>
                                    <td>{{ $meta->valor_meta }}</td>
                                    <td>{{ $meta->valor_actual }}</td>
                                    <td>
                                        @php
                                            $progreso = $meta->valor_meta > 0 ? ($meta->valor_actual / $meta->valor_meta) * 100 : 0;
                                            $progreso = min($progreso, 100);
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $progreso >= 100 ? 'success' : ($progreso >= 75 ? 'info' : ($progreso >= 50 ? 'warning' : 'danger')) }}" 
                                                 style="width: {{ $progreso }}%">
                                                {{ number_format($progreso, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $meta->periodo }}</td>
                                    <td>
                                        <span class="badge bg-{{ $meta->activa ? 'success' : 'secondary' }}">
                                            {{ $meta->activa ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-warning" onclick="editarMeta({{ $meta->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-{{ $meta->activa ? 'danger' : 'success' }}" 
                                                    onclick="toggleMeta({{ $meta->id }})">
                                                <i class="fas fa-{{ $meta->activa ? 'pause' : 'play' }}"></i>
                                            </button>
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

        <!-- Rendimiento por Funcionario -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Rendimiento por Funcionario</h4>
                </div>
                <div class="card-body">
                    @foreach($rendimientoFuncionarios as $funcionario)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>{{ $funcionario->name }}</span>
                            <span class="badge bg-primary">{{ $funcionario->expedientes_resueltos }}</span>
                        </div>
                        <div class="progress mt-1">
                            @php
                                $maxExpedientes = $rendimientoFuncionarios->max('expedientes_resueltos');
                                $porcentaje = $maxExpedientes > 0 ? ($funcionario->expedientes_resueltos / $maxExpedientes) * 100 : 0;
                            @endphp
                            <div class="progress-bar" style="width: {{ $porcentaje }}%"></div>
                        </div>
                        <small class="text-muted">
                            Promedio: {{ number_format($funcionario->tiempo_promedio, 1) }} días
                        </small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva/Editar Meta -->
<div class="modal fade" id="modalMeta" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formMeta" method="POST" action="{{ route('jefe-area.metas.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Meta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" class="form-control" name="descripcion" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Meta</label>
                        <select class="form-select" name="tipo" required>
                            <option value="">Seleccionar...</option>
                            <option value="expedientes">Número de Expedientes</option>
                            <option value="tiempo">Tiempo Promedio (días)</option>
                            <option value="eficiencia">Eficiencia (%)</option>
                            <option value="satisfaccion">Satisfacción (%)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valor Meta</label>
                        <input type="number" class="form-control" name="valor_meta" step="0.1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Periodo</label>
                        <select class="form-select" name="periodo" required>
                            <option value="mensual">Mensual</option>
                            <option value="trimestral">Trimestral</option>
                            <option value="semestral">Semestral</option>
                            <option value="anual">Anual</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" name="fecha_inicio" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" name="fecha_fin" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection