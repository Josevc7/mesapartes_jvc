@extends('layouts.app')

@section('title', 'Dashboard Jefe de Área')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Dashboard - Jefe de Área</h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-building me-1"></i>
                        Área: <strong>{{ auth()->user()->area->nombre ?? 'Sin área asignada' }}</strong>
                    </p>
                </div>
                <div>
                    <span class="badge bg-primary fs-6">
                        <i class="fas fa-calendar me-1"></i>
                        {{ now()->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Principales -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['total_expedientes'] ?? 0 }}</h3>
                            <p class="mb-0">Total Expedientes</p>
                        </div>
                        <i class="fas fa-folder fa-2x opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('jefe-area.expedientes') }}" class="text-white small">
                        Ver todos <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['pendientes'] ?? 0 }}</h3>
                            <p class="mb-0">Pendientes</p>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('jefe-area.expedientes', ['estado' => 'en_proceso']) }}" class="text-dark small">
                        Ver pendientes <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['vencidos'] ?? 0 }}</h3>
                            <p class="mb-0">Vencidos</p>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('jefe-area.control-plazos') }}" class="text-white small">
                        Control de plazos <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['resueltos_mes'] ?? 0 }}</h3>
                            <p class="mb-0">Resueltos (Mes)</p>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('jefe-area.reportes') }}" class="text-white small">
                        Ver reportes <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda fila de estadísticas -->
    <div class="row mb-4">
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="text-info mb-0">{{ $stats['por_aprobar'] ?? 0 }}</h4>
                            <p class="mb-0 text-muted">Por Aprobar</p>
                        </div>
                        <a href="{{ route('jefe-area.validar-documentos') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-check"></i> Validar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-secondary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="text-secondary mb-0">{{ $stats['sin_asignar'] ?? 0 }}</h4>
                            <p class="mb-0 text-muted">Sin Asignar</p>
                        </div>
                        <a href="{{ route('jefe-area.expedientes', ['funcionario' => 'sin_asignar']) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-user-plus"></i> Asignar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-danger h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="text-danger mb-0">{{ $stats['urgentes'] ?? 0 }}</h4>
                            <p class="mb-0 text-muted">Urgentes</p>
                        </div>
                        <a href="{{ route('jefe-area.expedientes', ['prioridad' => 'urgente']) }}" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-bolt"></i> Ver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <a href="{{ route('jefe-area.expedientes') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-tasks fa-2x d-block mb-2"></i>
                                <small>Ver Expedientes</small>
                            </a>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <a href="{{ route('jefe-area.validar-documentos') }}" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-check-double fa-2x d-block mb-2"></i>
                                <small>Validar Docs</small>
                            </a>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <a href="{{ route('jefe-area.control-plazos') }}" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-clock fa-2x d-block mb-2"></i>
                                <small>Control Plazos</small>
                            </a>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <a href="{{ route('jefe-area.supervision') }}" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-users fa-2x d-block mb-2"></i>
                                <small>Supervisión</small>
                            </a>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <a href="{{ route('jefe-area.reportes') }}" class="btn btn-outline-secondary w-100 py-3">
                                <i class="fas fa-chart-bar fa-2x d-block mb-2"></i>
                                <small>Reportes</small>
                            </a>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <a href="{{ route('jefe-area.metas') }}" class="btn btn-outline-dark w-100 py-3">
                                <i class="fas fa-bullseye fa-2x d-block mb-2"></i>
                                <small>Metas</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Expedientes Críticos -->
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-circle text-danger me-2"></i>
                        Expedientes que Requieren Atención
                    </h5>
                    <a href="{{ route('jefe-area.control-plazos') }}" class="btn btn-sm btn-outline-primary">
                        Ver todos
                    </a>
                </div>
                <div class="card-body p-0">
                    @if(isset($expedientesCriticos) && $expedientesCriticos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Asunto</th>
                                    <th>Funcionario</th>
                                    <th>Estado Plazo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expedientesCriticos as $expediente)
                                <tr class="{{ $expediente->dias_vencido > 0 ? 'table-danger' : 'table-warning' }}">
                                    <td>
                                        <strong>{{ $expediente->codigo_expediente }}</strong>
                                        @if($expediente->prioridad === 'urgente')
                                            <span class="badge bg-danger">Urgente</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($expediente->asunto, 30) }}</td>
                                    <td>
                                        @if($expediente->funcionarioAsignado)
                                            {{ $expediente->funcionarioAsignado->name }}
                                        @else
                                            <span class="text-danger">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($expediente->dias_vencido > 0)
                                            <span class="badge bg-danger">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ $expediente->dias_vencido }} días vencido
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-clock"></i>
                                                {{ $expediente->dias_restantes }} días restantes
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('jefe-area.show-expediente', $expediente) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted mb-0">No hay expedientes críticos en este momento</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Carga de Funcionarios -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Carga por Funcionario
                    </h5>
                    <a href="{{ route('jefe-area.supervision') }}" class="btn btn-sm btn-outline-info">
                        Supervisar
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($funcionarios) && $funcionarios->count() > 0)
                        @foreach($funcionarios as $funcionario)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-{{ $funcionario->pendientes > 10 ? 'danger' : ($funcionario->pendientes > 5 ? 'warning' : 'success') }} text-white rounded-circle me-2"
                                     style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                    {{ substr($funcionario->name, 0, 1) }}
                                </div>
                                <div>
                                    <small class="d-block fw-bold">{{ Str::limit($funcionario->name, 20) }}</small>
                                    <small class="text-muted">{{ $funcionario->pendientes }} pendientes</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="progress" style="width: 60px; height: 8px;">
                                    <div class="progress-bar bg-{{ $funcionario->pendientes > 10 ? 'danger' : ($funcionario->pendientes > 5 ? 'warning' : 'success') }}"
                                         style="width: {{ min(($funcionario->pendientes / 15) * 100, 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-center text-muted">No hay funcionarios en el área</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Indicadores de rendimiento -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Rendimiento del Área</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Tiempo Promedio de Atención</span>
                            <strong>{{ round($stats['promedio_atencion'] ?? 0, 1) }} días</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            @php
                                $promedioPorc = min((($stats['promedio_atencion'] ?? 0) / 30) * 100, 100);
                            @endphp
                            <div class="progress-bar bg-{{ $promedioPorc > 70 ? 'danger' : ($promedioPorc > 50 ? 'warning' : 'success') }}"
                                 style="width: {{ $promedioPorc }}%"></div>
                        </div>
                        <small class="text-muted">Meta: 30 días máximo</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Resumen del Día</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="text-primary mb-0">{{ $stats['por_aprobar'] ?? 0 }}</h4>
                            <small class="text-muted">Por Aprobar</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-warning mb-0">{{ $stats['sin_asignar'] ?? 0 }}</h4>
                            <small class="text-muted">Sin Asignar</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-danger mb-0">{{ $stats['vencidos'] ?? 0 }}</h4>
                            <small class="text-muted">Vencidos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
