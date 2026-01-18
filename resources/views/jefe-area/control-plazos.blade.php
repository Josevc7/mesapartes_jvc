@extends('layouts.app')

@section('title', 'Control de Plazos')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Control de Plazos y Cumplimiento</h2>
                    <p class="text-muted mb-0">Monitoreo de expedientes críticos del área</p>
                </div>
                <a href="{{ route('jefe-area.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Estadísticas de Plazos -->
    <div class="row mb-4">
        <div class="col-md-2 col-sm-4 col-6 mb-3">
            <div class="card border-danger h-100">
                <div class="card-body text-center py-3">
                    <h2 class="text-danger mb-0">{{ $stats['vencidos'] ?? 0 }}</h2>
                    <small class="text-muted">Vencidos</small>
                    <div class="mt-2">
                        <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-3">
            <div class="card border-warning h-100">
                <div class="card-body text-center py-3">
                    <h2 class="text-warning mb-0">{{ $stats['por_vencer'] ?? 0 }}</h2>
                    <small class="text-muted">Por Vencer</small>
                    <div class="mt-2">
                        <i class="fas fa-clock text-warning fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-3">
            <div class="card border-success h-100">
                <div class="card-body text-center py-3">
                    <h2 class="text-success mb-0">{{ $stats['en_plazo'] ?? 0 }}</h2>
                    <small class="text-muted">En Plazo</small>
                    <div class="mt-2">
                        <i class="fas fa-check-circle text-success fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-3">
            <div class="card border-secondary h-100">
                <div class="card-body text-center py-3">
                    <h2 class="text-secondary mb-0">{{ $stats['sin_asignar'] ?? 0 }}</h2>
                    <small class="text-muted">Sin Asignar</small>
                    <div class="mt-2">
                        <i class="fas fa-user-slash text-secondary fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-3">
            <div class="card border-info h-100">
                <div class="card-body text-center py-3">
                    <h2 class="text-info mb-0">{{ $stats['por_aprobar'] ?? 0 }}</h2>
                    <small class="text-muted">Por Aprobar</small>
                    <div class="mt-2">
                        <i class="fas fa-clipboard-check text-info fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-3">
            <div class="card border-primary h-100">
                <div class="card-body text-center py-3">
                    <h2 class="text-primary mb-0">{{ $cumplimiento['porcentaje'] ?? 0 }}%</h2>
                    <small class="text-muted">Cumplimiento</small>
                    <div class="mt-2">
                        <i class="fas fa-chart-pie text-primary fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Expedientes Críticos (Vencidos y Por Vencer) -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Expedientes que Requieren Atención Inmediata
                    </h5>
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
                                    <th>Prioridad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expedientesCriticos as $expediente)
                                <tr class="{{ $expediente->dias_vencido > 0 ? 'table-danger' : 'table-warning' }}">
                                    <td>
                                        <strong>{{ $expediente->codigo_expediente }}</strong>
                                        @if($expediente->dias_vencido > 0)
                                            <br><small class="text-danger fw-bold">VENCIDO</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ Str::limit($expediente->asunto, 30) }}
                                        @if($expediente->tipoTramite)
                                            <br><small class="text-muted">{{ $expediente->tipoTramite->nombre }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($expediente->funcionarioAsignado)
                                            <span class="badge bg-info">{{ $expediente->funcionarioAsignado->name }}</span>
                                        @else
                                            <span class="badge bg-secondary">Sin asignar</span>
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
                                        @if($expediente->fecha_limite)
                                            <br><small class="text-muted">Límite: {{ $expediente->fecha_limite->format('d/m/Y') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $prioridadColor = match($expediente->prioridad) {
                                                'urgente' => 'danger',
                                                'alta' => 'warning',
                                                'normal' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $prioridadColor }}">
                                            {{ ucfirst($expediente->prioridad ?? 'Normal') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('jefe-area.show-expediente', $expediente) }}"
                                               class="btn btn-outline-primary" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-warning"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalExtender{{ $expediente->id_expediente }}"
                                                    title="Extender plazo">
                                                <i class="fas fa-calendar-plus"></i>
                                            </button>
                                            @if(!$expediente->funcionarioAsignado)
                                            <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalAsignarPlazos{{ $expediente->id_expediente }}"
                                                    title="Asignar">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Extender Plazo -->
                                <div class="modal fade" id="modalExtender{{ $expediente->id_expediente }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('jefe-area.extender-plazo', $expediente) }}">
                                                @csrf
                                                <div class="modal-header bg-warning">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-calendar-plus me-2"></i>
                                                        Extender Plazo
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Expediente:</strong> {{ $expediente->codigo_expediente }}</p>
                                                    <p><strong>Plazo original:</strong> {{ $expediente->plazo_original ?? 'N/A' }} días</p>
                                                    <p><strong>Fecha límite actual:</strong>
                                                        {{ $expediente->fecha_limite ? $expediente->fecha_limite->format('d/m/Y') : 'N/A' }}
                                                    </p>
                                                    <hr>
                                                    <div class="mb-3">
                                                        <label class="form-label">Días adicionales *</label>
                                                        <input type="number" name="dias_adicionales" class="form-control"
                                                               min="1" max="30" required placeholder="Ej: 5">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Motivo de la extensión *</label>
                                                        <textarea name="motivo" class="form-control" rows="3" required
                                                                  placeholder="Justifique la extensión del plazo..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-warning">
                                                        <i class="fas fa-save me-1"></i> Extender Plazo
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Asignar desde Plazos -->
                                @if(!$expediente->funcionarioAsignado)
                                <div class="modal fade" id="modalAsignarPlazos{{ $expediente->id_expediente }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('jefe-area.asignar-expediente', $expediente) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-user-plus me-2"></i>
                                                        Asignar Expediente
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Expediente:</strong> {{ $expediente->codigo_expediente }}</p>
                                                    <div class="mb-3">
                                                        <label class="form-label">Seleccionar Funcionario *</label>
                                                        <select name="funcionario_id" class="form-select" required>
                                                            <option value="">-- Seleccione --</option>
                                                            @foreach($funcionarios as $func)
                                                                <option value="{{ $func->id }}">
                                                                    {{ $func->name }} (Carga: {{ $func->carga_trabajo }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Observaciones</label>
                                                        <textarea name="observaciones" class="form-control" rows="2"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-save me-1"></i> Asignar
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h5 class="text-success">Excelente</h5>
                        <p class="text-muted">No hay expedientes críticos en este momento</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-md-4">
            <!-- Expedientes Sin Asignar -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-user-slash me-2"></i>
                        Expedientes Sin Asignar ({{ $expedientesSinAsignar->count() ?? 0 }})
                    </h6>
                </div>
                <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                    @if(isset($expedientesSinAsignar) && $expedientesSinAsignar->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($expedientesSinAsignar as $exp)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $exp->codigo_expediente }}</strong>
                                <br><small class="text-muted">{{ Str::limit($exp->asunto, 25) }}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalAsignarRapido{{ $exp->id_expediente }}">
                                <i class="fas fa-user-plus"></i>
                            </button>
                        </li>

                        <!-- Modal Asignar Rápido -->
                        <div class="modal fade" id="modalAsignarRapido{{ $exp->id_expediente }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('jefe-area.asignar-expediente', $exp) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h6 class="modal-title">Asignar {{ $exp->codigo_expediente }}</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <select name="funcionario_id" class="form-select form-select-sm" required>
                                                <option value="">Seleccione</option>
                                                @foreach($funcionarios as $func)
                                                    <option value="{{ $func->id }}">{{ $func->name }} ({{ $func->carga_trabajo }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="modal-footer py-1">
                                            <button type="submit" class="btn btn-sm btn-primary">Asignar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </ul>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-check text-success"></i>
                        <p class="mb-0 small text-muted">Todos asignados</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Análisis de Cumplimiento -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Análisis de Cumplimiento</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Cumplimiento General</span>
                            <strong>{{ $cumplimiento['porcentaje'] ?? 0 }}%</strong>
                        </div>
                        <div class="progress" style="height: 20px;">
                            @php
                                $cumplimientoPorc = $cumplimiento['porcentaje'] ?? 0;
                                $cumplimientoColor = $cumplimientoPorc >= 80 ? 'success' : ($cumplimientoPorc >= 60 ? 'warning' : 'danger');
                            @endphp
                            <div class="progress-bar bg-{{ $cumplimientoColor }}"
                                 style="width: {{ $cumplimientoPorc }}%">
                                {{ $cumplimientoPorc }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $cumplimiento['en_plazo'] ?? 0 }} de {{ $cumplimiento['total'] ?? 0 }} resueltos en plazo
                        </small>
                    </div>
                </div>
            </div>

            <!-- Carga por Funcionario -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Carga por Funcionario</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($funcionarios as $func)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{{ Str::limit($func->name, 20) }}</span>
                                <span class="badge bg-{{ $func->carga_trabajo > 10 ? 'danger' : ($func->carga_trabajo > 5 ? 'warning' : 'success') }}">
                                    {{ $func->carga_trabajo }} exp.
                                </span>
                            </div>
                            <div class="progress mt-1" style="height: 5px;">
                                <div class="progress-bar bg-{{ $func->carga_trabajo > 10 ? 'danger' : ($func->carga_trabajo > 5 ? 'warning' : 'success') }}"
                                     style="width: {{ min(($func->carga_trabajo / 15) * 100, 100) }}%"></div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Recomendadas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Acciones Recomendadas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($stats['vencidos'] > 0)
                        <div class="col-md-3 mb-3">
                            <div class="alert alert-danger mb-0">
                                <h6><i class="fas fa-exclamation-triangle me-1"></i> Urgente</h6>
                                <p class="mb-2">{{ $stats['vencidos'] }} expediente(s) vencido(s)</p>
                                <a href="{{ route('jefe-area.expedientes', ['vencidos' => 1]) }}" class="btn btn-sm btn-danger">
                                    Revisar ahora
                                </a>
                            </div>
                        </div>
                        @endif
                        @if($stats['sin_asignar'] > 0)
                        <div class="col-md-3 mb-3">
                            <div class="alert alert-secondary mb-0">
                                <h6><i class="fas fa-user-slash me-1"></i> Sin Asignar</h6>
                                <p class="mb-2">{{ $stats['sin_asignar'] }} expediente(s)</p>
                                <a href="{{ route('jefe-area.expedientes', ['funcionario' => 'sin_asignar']) }}" class="btn btn-sm btn-secondary">
                                    Asignar
                                </a>
                            </div>
                        </div>
                        @endif
                        @if($stats['por_vencer'] > 0)
                        <div class="col-md-3 mb-3">
                            <div class="alert alert-warning mb-0">
                                <h6><i class="fas fa-clock me-1"></i> Por Vencer</h6>
                                <p class="mb-2">{{ $stats['por_vencer'] }} en próximos 3 días</p>
                                <a href="{{ route('jefe-area.expedientes', ['por_vencer' => 1]) }}" class="btn btn-sm btn-warning">
                                    Ver lista
                                </a>
                            </div>
                        </div>
                        @endif
                        @if($stats['por_aprobar'] > 0)
                        <div class="col-md-3 mb-3">
                            <div class="alert alert-info mb-0">
                                <h6><i class="fas fa-clipboard-check me-1"></i> Por Aprobar</h6>
                                <p class="mb-2">{{ $stats['por_aprobar'] }} listo(s) para validar</p>
                                <a href="{{ route('jefe-area.validar-documentos') }}" class="btn btn-sm btn-info">
                                    Validar
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
