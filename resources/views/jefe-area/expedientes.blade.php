@extends('layouts.app')

@section('title', 'Supervisión de Expedientes')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Supervisión de Expedientes</h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-building me-1"></i>
                        Área: <strong>{{ auth()->user()->area->nombre ?? 'N/A' }}</strong>
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

    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-primary text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $estadisticas['total'] ?? 0 }}</h4>
                            <small>Total</small>
                        </div>
                        <i class="fas fa-folder fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-warning text-dark">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $estadisticas['pendientes'] ?? 0 }}</h4>
                            <small>Pendientes</small>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-success text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $estadisticas['resueltos'] ?? 0 }}</h4>
                            <small>Resueltos</small>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card bg-secondary text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $estadisticas['sin_asignar'] ?? 0 }}</h4>
                            <small>Sin Asignar</small>
                        </div>
                        <i class="fas fa-user-slash fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros de Búsqueda</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('jefe-area.expedientes') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="buscar" class="form-control" placeholder="Código o asunto..."
                               value="{{ request('buscar') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="">Todos</option>
                            <option value="derivado" {{ request('estado') == 'derivado' ? 'selected' : '' }}>Derivado</option>
                            <option value="recepcionado" {{ request('estado') == 'recepcionado' ? 'selected' : '' }}>Recepcionado</option>
                            <option value="asignado" {{ request('estado') == 'asignado' ? 'selected' : '' }}>Asignado</option>
                            <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                            <option value="resuelto" {{ request('estado') == 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                            <option value="aprobado" {{ request('estado') == 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                            <option value="observado" {{ request('estado') == 'observado' ? 'selected' : '' }}>Observado</option>
                            <option value="archivado" {{ request('estado') == 'archivado' ? 'selected' : '' }}>Archivado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Personal</label>
                        <select name="funcionario" class="form-select">
                            <option value="">Todos</option>
                            <option value="sin_asignar" {{ request('funcionario') == 'sin_asignar' ? 'selected' : '' }}>Sin Asignar</option>
                            @foreach($funcionarios as $funcionario)
                                <option value="{{ $funcionario->id }}" {{ request('funcionario') == $funcionario->id ? 'selected' : '' }}>
                                    {{ $funcionario->name }} - {{ $funcionario->area->nombre ?? 'N/A' }} ({{ $funcionario->carga_trabajo }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Prioridad</label>
                        <select name="prioridad" class="form-select">
                            <option value="">Todas</option>
                            <option value="urgente" {{ request('prioridad') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                            <option value="alta" {{ request('prioridad') == 'alta' ? 'selected' : '' }}>Alta</option>
                            <option value="normal" {{ request('prioridad') == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="baja" {{ request('prioridad') == 'baja' ? 'selected' : '' }}>Baja</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipo Trámite</label>
                        <select name="tipo_tramite" class="form-select">
                            <option value="">Todos</option>
                            @foreach($tiposTramite ?? [] as $tipo)
                                <option value="{{ $tipo->id_tipo_tramite }}" {{ request('tipo_tramite') == $tipo->id_tipo_tramite ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('jefe-area.expedientes') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-2">
                        <label class="form-label">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" name="vencidos" value="1" id="vencidos"
                                   {{ request('vencidos') ? 'checked' : '' }}>
                            <label class="form-check-label text-danger" for="vencidos">
                                <i class="fas fa-exclamation-triangle"></i> Solo vencidos
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="por_vencer" value="1" id="por_vencer"
                                   {{ request('por_vencer') ? 'checked' : '' }}>
                            <label class="form-check-label text-warning" for="por_vencer">
                                <i class="fas fa-clock"></i> Por vencer (3 días)
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Asignación masiva -->
    <form id="formAsignacionMasiva" method="POST" action="{{ route('jefe-area.asignacion-masiva') }}">
        @csrf
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Expedientes del Área
                    <span class="badge bg-primary ms-2">{{ $expedientes->total() }}</span>
                </h5>
                <div>
                    <button type="button" class="btn btn-warning btn-sm" id="btnAsignacionMasiva" disabled
                            data-bs-toggle="modal" data-bs-target="#modalAsignacionMasiva">
                        <i class="fas fa-users"></i> Asignación Masiva (<span id="countSeleccionados">0</span>)
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th>Código</th>
                                <th>Asunto</th>
                                <th>Funcionario</th>
                                <th>Estado</th>
                                <th>Plazo</th>
                                <th>Prioridad</th>
                                <th width="200">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expedientes as $expediente)
                            @php
                                $rowClass = '';
                                if ($expediente->dias_vencido > 0) {
                                    $rowClass = 'table-danger';
                                } elseif ($expediente->dias_restantes !== null && $expediente->dias_restantes <= 3) {
                                    $rowClass = 'table-warning';
                                } elseif (!$expediente->funcionarioAsignado) {
                                    $rowClass = 'table-secondary';
                                }
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td>
                                    <input type="checkbox" class="form-check-input expediente-checkbox"
                                           name="expedientes[]" value="{{ $expediente->id_expediente }}">
                                </td>
                                <td>
                                    <strong>{{ $expediente->codigo_expediente }}</strong>
                                    @php
                                        $derivacionActual = $expediente->derivaciones->where('id_area_destino', auth()->user()->id_area)->first();
                                        $numRegistroArea = $derivacionActual?->numero_registro_area;
                                    @endphp
                                    @if($numRegistroArea)
                                        <br><small class="text-success" title="Número de Registro del Área">
                                            <i class="fas fa-stamp"></i> {{ $numRegistroArea }}
                                        </small>
                                    @endif
                                    @if($expediente->prioridad === 'urgente')
                                        <br><span class="badge bg-danger">Urgente</span>
                                    @elseif($expediente->prioridad === 'alta')
                                        <br><span class="badge bg-warning text-dark">Alta</span>
                                    @endif
                                </td>
                                <td>
                                    <span title="{{ $expediente->asunto }}">{{ Str::limit($expediente->asunto, 35) }}</span>
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
                                    @php
                                        $estadoColor = match($expediente->estado) {
                                            'derivado' => 'primary',
                                            'recepcionado' => 'info',
                                            'asignado' => 'secondary',
                                            'en_proceso' => 'warning',
                                            'resuelto' => 'success',
                                            'aprobado' => 'success',
                                            'observado' => 'danger',
                                            'archivado' => 'dark',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $estadoColor }}">
                                        {{ ucfirst(str_replace('_', ' ', $expediente->estado)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($expediente->dias_vencido > 0)
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            {{ $expediente->dias_vencido }}d vencido
                                        </span>
                                    @elseif($expediente->dias_restantes !== null)
                                        <span class="badge bg-{{ $expediente->dias_restantes <= 3 ? 'warning text-dark' : 'light text-dark' }}">
                                            {{ $expediente->dias_restantes }}d restantes
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $prioridadColor = match($expediente->prioridad) {
                                            'urgente' => 'danger',
                                            'alta' => 'warning',
                                            'normal' => 'info',
                                            'baja' => 'secondary',
                                            default => 'light'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $prioridadColor }}">
                                        {{ ucfirst($expediente->prioridad ?? 'Normal') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('jefe-area.show-expediente', $expediente) }}"
                                           class="btn btn-outline-primary" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($expediente->estado === 'derivado')
                                            <button type="button" class="btn btn-outline-success btn-recepcionar"
                                                    data-expediente-id="{{ $expediente->id_expediente }}"
                                                    data-expediente-codigo="{{ $expediente->codigo_expediente }}"
                                                    title="Recepcionar expediente">
                                                <i class="fas fa-inbox"></i>
                                            </button>
                                        @endif
                                        @if(in_array($expediente->estado, ['recepcionado', 'derivado', 'asignado', 'en_proceso']))
                                        <button type="button" class="btn btn-outline-secondary btn-asignar"
                                                data-expediente-id="{{ $expediente->id_expediente }}"
                                                data-expediente-codigo="{{ $expediente->codigo_expediente }}"
                                                data-expediente-asunto="{{ Str::limit($expediente->asunto, 50) }}"
                                                data-funcionario-actual="{{ $expediente->funcionarioAsignado->name ?? 'Sin asignar' }}"
                                                data-funcionario-id="{{ $expediente->id_funcionario_asignado }}"
                                                title="Asignar">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-derivar"
                                                data-expediente-id="{{ $expediente->id_expediente }}"
                                                data-expediente-codigo="{{ $expediente->codigo_expediente }}"
                                                data-expediente-asunto="{{ Str::limit($expediente->asunto, 50) }}"
                                                title="Derivar a otra área">
                                            <i class="fas fa-share"></i>
                                        </button>
                                        @endif
                                        @if($expediente->estado === 'resuelto')
                                            <button type="button" class="btn btn-outline-success btn-aprobar"
                                                    data-expediente-id="{{ $expediente->id_expediente }}"
                                                    data-expediente-codigo="{{ $expediente->codigo_expediente }}"
                                                    data-expediente-asunto="{{ Str::limit($expediente->asunto, 80) }}"
                                                    title="Aprobar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-rechazar"
                                                    data-expediente-id="{{ $expediente->id_expediente }}"
                                                    data-expediente-codigo="{{ $expediente->codigo_expediente }}"
                                                    data-expediente-asunto="{{ Str::limit($expediente->asunto, 50) }}"
                                                    title="Rechazar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        @if($expediente->estado === 'aprobado')
                                            <button type="button" class="btn btn-outline-secondary btn-archivar"
                                                    data-expediente-id="{{ $expediente->id_expediente }}"
                                                    data-expediente-codigo="{{ $expediente->codigo_expediente }}"
                                                    title="Archivar expediente">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">No se encontraron expedientes con los filtros seleccionados</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $expedientes->withQueryString()->links() }}
            </div>
        </div>

        <!-- Modal Asignación Masiva -->
        <div class="modal fade" id="modalAsignacionMasiva" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            <i class="fas fa-users me-2"></i>
                            Asignación Masiva
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Se asignarán <strong><span id="countMasivo">0</span></strong> expedientes al funcionario seleccionado.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Funcionario Destino *</label>
                            <select name="funcionario_id" class="form-select" required>
                                <option value="">-- Seleccione --</option>
                                @foreach($funcionarios as $func)
                                    <option value="{{ $func->id }}">
                                        {{ $func->name }} - {{ $func->area->nombre ?? 'N/A' }} (Carga: {{ $func->carga_trabajo }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motivo de la asignación *</label>
                            <textarea name="motivo" class="form-control" rows="3" required minlength="5"
                                      placeholder="Ej: Redistribución de carga de trabajo..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i> Asignar Seleccionados
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Asignar Individual (Único - Dinámico) -->
<div class="modal fade" id="modalAsignarExpediente" tabindex="-1" aria-labelledby="modalAsignarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAsignarExpediente" method="POST" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAsignarLabel">
                        <i class="fas fa-user-plus me-2"></i>
                        Asignar Expediente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Expediente:</strong> <span id="asignarCodigo"></span></p>
                    <p><strong>Asunto:</strong> <span id="asignarAsunto"></span></p>
                    <p><strong>Asignado actual:</strong> <span id="asignarFuncionarioActual"></span></p>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Funcionario</label>
                        <select name="funcionario_id" id="asignarFuncionarioSelect" class="form-select" required>
                            <option value="">-- Seleccione --</option>
                            @foreach($funcionarios as $func)
                                <option value="{{ $func->id }}">
                                    {{ $func->name }} - {{ $func->area->nombre ?? 'N/A' }}
                                    (Carga: {{ $func->carga_trabajo }} expedientes)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones (opcional)</label>
                        <textarea name="observaciones" id="asignarObservaciones" class="form-control" rows="2"
                                  placeholder="Motivo de la asignación..."></textarea>
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

<!-- Modal Aprobar (Único - Dinámico) -->
<div class="modal fade" id="modalAprobarExpediente" tabindex="-1" aria-labelledby="modalAprobarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalAprobarLabel">
                    <i class="fas fa-check-circle me-2"></i>
                    Aprobar Expediente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de aprobar el expediente?</p>
                <p><strong id="aprobarCodigo"></strong></p>
                <p id="aprobarAsunto"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formAprobarExpediente" method="POST" action="" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Aprobar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rechazar (Único - Dinámico) -->
<div class="modal fade" id="modalRechazarExpediente" tabindex="-1" aria-labelledby="modalRechazarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formRechazarExpediente" method="POST" action="">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalRechazarLabel">
                        <i class="fas fa-times-circle me-2"></i>
                        Rechazar Expediente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Expediente:</strong> <span id="rechazarCodigo"></span></p>
                    <p><strong>Asunto:</strong> <span id="rechazarAsunto"></span></p>
                    <div class="mb-3">
                        <label class="form-label">Motivo del Rechazo *</label>
                        <textarea name="motivo_rechazo" id="rechazarMotivo" class="form-control" rows="3"
                                  required minlength="10"
                                  placeholder="Explique el motivo del rechazo..."></textarea>
                        <small class="text-muted">Mínimo 10 caracteres</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> Rechazar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Recepcionar (Único - Dinámico) -->
<div class="modal fade" id="modalRecepcionarExpediente" tabindex="-1" aria-labelledby="modalRecepcionarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalRecepcionarLabel">
                    <i class="fas fa-inbox me-2"></i>
                    Recepcionar Expediente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>¿Confirma la recepción oficial del expediente en el área?</p>
                <p><strong>Expediente:</strong> <span id="recepcionarCodigo" class="text-primary fw-bold"></span></p>
                <div class="alert alert-success">
                    <i class="fas fa-stamp me-1"></i>
                    <strong>Al recepcionar se generará el NÚMERO DE REGISTRO DEL ÁREA</strong>
                    <br><small>Este número identifica oficialmente el documento dentro del área.</small>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-1"></i>
                    El expediente quedará bajo responsabilidad del área, se iniciará el control de plazos y podrá ser asignado a un funcionario.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formRecepcionarExpediente" method="POST" action="" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Recepcionar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Archivar (Único - Dinámico) -->
<div class="modal fade" id="modalArchivarExpediente" tabindex="-1" aria-labelledby="modalArchivarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="modalArchivarLabel">
                    <i class="fas fa-archive me-2"></i>
                    Archivar Expediente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>¿Confirma que desea archivar el expediente?</p>
                <p><strong>Expediente:</strong> <span id="archivarCodigo"></span></p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Al archivar, el expediente quedará cerrado y no podrá ser modificado. Esta acción indica que el trámite ha sido completamente finalizado.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formArchivarExpediente" method="POST" action="" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-archive me-1"></i> Archivar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Derivar Expediente a otra Área -->
<div class="modal fade" id="modalDerivarExpediente" tabindex="-1" aria-labelledby="modalDerivarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formDerivarExpediente" method="POST" action="">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalDerivarLabel">
                        <i class="fas fa-share me-2"></i>
                        Derivar Expediente a otra Área
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <p class="mb-1"><strong>Expediente:</strong> <span id="derivarCodigo" class="text-primary"></span></p>
                        <p class="mb-0"><strong>Asunto:</strong> <span id="derivarAsunto"></span></p>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-building text-info me-1"></i>Área de Destino *
                            </label>
                            <select name="id_area_destino" id="derivarAreaDestino" class="form-select" required>
                                <option value="">-- Seleccione Área --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user text-info me-1"></i>Funcionario (Opcional)
                            </label>
                            <select name="id_funcionario_destino" id="derivarFuncionarioDestino" class="form-select">
                                <option value="">-- Sin asignar específico --</option>
                            </select>
                            <small class="text-muted">El jefe del área destino podrá asignarlo</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt text-info me-1"></i>Plazo (días) *
                            </label>
                            <input type="number" name="plazo_dias" id="derivarPlazoDias" class="form-control"
                                   value="5" min="1" max="90" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-flag text-info me-1"></i>Prioridad
                            </label>
                            <select name="prioridad" id="derivarPrioridad" class="form-select">
                                <option value="normal" selected>Normal</option>
                                <option value="baja">Baja</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-clock text-info me-1"></i>Fecha Límite
                            </label>
                            <input type="text" id="derivarFechaLimite" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-comment text-info me-1"></i>Observaciones / Motivo de Derivación *
                        </label>
                        <textarea name="observaciones" id="derivarObservaciones" class="form-control" rows="3"
                                  required minlength="10"
                                  placeholder="Indique el motivo de la derivación..."></textarea>
                        <small class="text-muted">Mínimo 10 caracteres</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-info text-white">
                        <i class="fas fa-share me-1"></i>Derivar Expediente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.expediente-checkbox');
    const btnAsignacionMasiva = document.getElementById('btnAsignacionMasiva');
    const countSeleccionados = document.getElementById('countSeleccionados');
    const countMasivo = document.getElementById('countMasivo');

    function updateCount() {
        const checked = document.querySelectorAll('.expediente-checkbox:checked').length;
        countSeleccionados.textContent = checked;
        countMasivo.textContent = checked;
        btnAsignacionMasiva.disabled = checked === 0;
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateCount();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateCount);
    });

    // Modal Asignar - Dinámico
    const modalAsignar = new bootstrap.Modal(document.getElementById('modalAsignarExpediente'));
    document.querySelectorAll('.btn-asignar').forEach(btn => {
        btn.addEventListener('click', function() {
            const expedienteId = this.dataset.expedienteId;
            const codigo = this.dataset.expedienteCodigo;
            const asunto = this.dataset.expedienteAsunto;
            const funcionarioActual = this.dataset.funcionarioActual;
            const funcionarioId = this.dataset.funcionarioId;

            document.getElementById('asignarCodigo').textContent = codigo;
            document.getElementById('asignarAsunto').textContent = asunto;
            document.getElementById('asignarFuncionarioActual').textContent = funcionarioActual;

            // Actualizar la acción del formulario
            document.getElementById('formAsignarExpediente').action =
                '{{ url("jefe-area/expedientes") }}/' + expedienteId + '/asignar';

            // Seleccionar el funcionario actual si existe
            const selectFunc = document.getElementById('asignarFuncionarioSelect');
            selectFunc.value = funcionarioId || '';

            // Limpiar observaciones
            document.getElementById('asignarObservaciones').value = '';

            modalAsignar.show();
        });
    });

    // Modal Aprobar - Dinámico
    const modalAprobar = new bootstrap.Modal(document.getElementById('modalAprobarExpediente'));
    document.querySelectorAll('.btn-aprobar').forEach(btn => {
        btn.addEventListener('click', function() {
            const expedienteId = this.dataset.expedienteId;
            const codigo = this.dataset.expedienteCodigo;
            const asunto = this.dataset.expedienteAsunto;

            document.getElementById('aprobarCodigo').textContent = codigo;
            document.getElementById('aprobarAsunto').textContent = asunto;

            // Actualizar la acción del formulario
            document.getElementById('formAprobarExpediente').action =
                '{{ url("jefe-area/expedientes") }}/' + expedienteId + '/aprobar';

            modalAprobar.show();
        });
    });

    // Modal Rechazar - Dinámico
    const modalRechazar = new bootstrap.Modal(document.getElementById('modalRechazarExpediente'));
    document.querySelectorAll('.btn-rechazar').forEach(btn => {
        btn.addEventListener('click', function() {
            const expedienteId = this.dataset.expedienteId;
            const codigo = this.dataset.expedienteCodigo;
            const asunto = this.dataset.expedienteAsunto;

            document.getElementById('rechazarCodigo').textContent = codigo;
            document.getElementById('rechazarAsunto').textContent = asunto;

            // Actualizar la acción del formulario
            document.getElementById('formRechazarExpediente').action =
                '{{ url("jefe-area/expedientes") }}/' + expedienteId + '/rechazar';

            // Limpiar el motivo
            document.getElementById('rechazarMotivo').value = '';

            modalRechazar.show();
        });
    });

    // Modal Recepcionar - Dinámico
    const modalRecepcionar = new bootstrap.Modal(document.getElementById('modalRecepcionarExpediente'));
    document.querySelectorAll('.btn-recepcionar').forEach(btn => {
        btn.addEventListener('click', function() {
            const expedienteId = this.dataset.expedienteId;
            const codigo = this.dataset.expedienteCodigo;

            document.getElementById('recepcionarCodigo').textContent = codigo;

            // Actualizar la acción del formulario
            document.getElementById('formRecepcionarExpediente').action =
                '{{ url("jefe-area/expedientes") }}/' + expedienteId + '/recepcionar';

            modalRecepcionar.show();
        });
    });

    // Modal Archivar - Dinámico
    const modalArchivar = new bootstrap.Modal(document.getElementById('modalArchivarExpediente'));
    document.querySelectorAll('.btn-archivar').forEach(btn => {
        btn.addEventListener('click', function() {
            const expedienteId = this.dataset.expedienteId;
            const codigo = this.dataset.expedienteCodigo;

            document.getElementById('archivarCodigo').textContent = codigo;

            // Actualizar la acción del formulario
            document.getElementById('formArchivarExpediente').action =
                '{{ url("jefe-area/expedientes") }}/' + expedienteId + '/archivar';

            modalArchivar.show();
        });
    });

    // Modal Derivar - Dinámico
    const modalDerivar = new bootstrap.Modal(document.getElementById('modalDerivarExpediente'));
    const selectAreaDestino = document.getElementById('derivarAreaDestino');
    const selectFuncionarioDestino = document.getElementById('derivarFuncionarioDestino');
    const inputPlazoDias = document.getElementById('derivarPlazoDias');
    const inputFechaLimite = document.getElementById('derivarFechaLimite');

    // Calcular fecha límite al cambiar plazo
    function calcularFechaLimite() {
        const dias = parseInt(inputPlazoDias.value) || 5;
        const fecha = new Date();
        fecha.setDate(fecha.getDate() + dias);
        inputFechaLimite.value = fecha.toLocaleDateString('es-PE', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    }

    inputPlazoDias.addEventListener('change', calcularFechaLimite);
    inputPlazoDias.addEventListener('input', calcularFechaLimite);

    // Cargar funcionarios al seleccionar área
    selectAreaDestino.addEventListener('change', function() {
        const areaId = this.value;
        selectFuncionarioDestino.innerHTML = '<option value="">-- Sin asignar específico --</option>';

        if (areaId) {
            fetch(`{{ url('jefe-area/areas') }}/${areaId}/funcionarios`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(func => {
                        const option = document.createElement('option');
                        option.value = func.id;
                        option.textContent = `${func.nombre} - ${func.cargo || 'Funcionario'} (Carga: ${func.carga || 0})`;
                        selectFuncionarioDestino.appendChild(option);
                    });
                })
                .catch(error => console.error('Error cargando funcionarios:', error));
        }
    });

    // Abrir modal de derivar
    document.querySelectorAll('.btn-derivar').forEach(btn => {
        btn.addEventListener('click', function() {
            const expedienteId = this.dataset.expedienteId;
            const codigo = this.dataset.expedienteCodigo;
            const asunto = this.dataset.expedienteAsunto;

            document.getElementById('derivarCodigo').textContent = codigo;
            document.getElementById('derivarAsunto').textContent = asunto;

            // Resetear formulario
            document.getElementById('formDerivarExpediente').reset();
            selectFuncionarioDestino.innerHTML = '<option value="">-- Sin asignar específico --</option>';
            inputPlazoDias.value = 5;
            calcularFechaLimite();

            // Actualizar la acción del formulario
            document.getElementById('formDerivarExpediente').action =
                '{{ url("jefe-area/expedientes") }}/' + expedienteId + '/derivar';

            // Cargar áreas disponibles
            fetch('{{ route("jefe-area.areas-para-derivacion") }}')
                .then(response => response.json())
                .then(data => {
                    selectAreaDestino.innerHTML = '<option value="">-- Seleccione Área --</option>';
                    data.forEach(area => {
                        const option = document.createElement('option');
                        option.value = area.id;
                        option.textContent = area.nombre;
                        if (area.jefe) {
                            option.textContent += ` (Jefe: ${area.jefe})`;
                        }
                        selectAreaDestino.appendChild(option);
                    });
                })
                .catch(error => console.error('Error cargando áreas:', error));

            modalDerivar.show();
        });
    });
});
</script>
@endsection
