@extends('layouts.app')

@section('title', 'Detalle Expediente - ' . $expediente->codigo_expediente)

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">{{ $expediente->codigo_expediente }}</h2>
                    <p class="text-muted mb-0">{{ $expediente->asunto }}</p>
                </div>
                <div>
                    <a href="{{ route('jefe-area.expedientes') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información Principal -->
        <div class="col-md-8">
            <!-- Datos del Expediente -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Información del Expediente</h5>
                    @php
                        $estadoColor = match($expediente->estado) {
                            'derivado' => 'primary',
                            'en_proceso' => 'info',
                            'resuelto' => 'success',
                            'aprobado' => 'success',
                            'observado' => 'warning',
                            'archivado' => 'secondary',
                            default => 'secondary'
                        };
                    @endphp
                    <span class="badge bg-{{ $estadoColor }} fs-6">
                        {{ ucfirst(str_replace('_', ' ', $expediente->estado)) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Código:</strong> {{ $expediente->codigo_expediente }}</p>
                            <p><strong>Tipo de Trámite:</strong> {{ $expediente->tipoTramite->nombre ?? 'N/A' }}</p>
                            <p><strong>Fecha de Registro:</strong> {{ $expediente->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Canal:</strong> {{ ucfirst($expediente->canal ?? 'N/A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>Prioridad:</strong>
                                @php
                                    $prioridadColor = match($expediente->prioridad) {
                                        'urgente' => 'danger',
                                        'alta' => 'warning',
                                        'normal' => 'info',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $prioridadColor }}">{{ ucfirst($expediente->prioridad ?? 'Normal') }}</span>
                            </p>
                            <p><strong>Folios:</strong> {{ $expediente->folios ?? 'N/A' }}</p>
                            <p><strong>Tipo Doc. Entrante:</strong> {{ $expediente->tipo_documento_entrante ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <hr>
                    <p><strong>Asunto:</strong></p>
                    <p class="bg-light p-3 rounded">{{ $expediente->asunto }}</p>
                    @if($expediente->descripcion)
                    <p><strong>Descripción:</strong></p>
                    <p class="bg-light p-3 rounded">{{ $expediente->descripcion }}</p>
                    @endif
                </div>
            </div>

            <!-- Persona/Ciudadano -->
            @if($expediente->persona)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Datos del Solicitante</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Tipo:</strong> {{ $expediente->persona->tipo_persona }}</p>
                            <p><strong>{{ $expediente->persona->tipo_documento }}:</strong> {{ $expediente->persona->numero_documento }}</p>
                            @if($expediente->persona->tipo_persona === 'NATURAL')
                                <p><strong>Nombre:</strong> {{ $expediente->persona->nombre_completo }}</p>
                            @else
                                <p><strong>Razón Social:</strong> {{ $expediente->persona->razon_social }}</p>
                                <p><strong>Representante:</strong> {{ $expediente->persona->representante_legal ?? 'N/A' }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p><strong>Teléfono:</strong> {{ $expediente->persona->telefono ?? 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ $expediente->persona->email ?? 'N/A' }}</p>
                            <p><strong>Dirección:</strong> {{ $expediente->persona->direccion ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Documentos -->
            @if($expediente->documentos && $expediente->documentos->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-paperclip me-2"></i>Documentos Adjuntos</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($expediente->documentos as $doc)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                {{ $doc->nombre }}
                                <small class="text-muted ms-2">{{ $doc->tipo }}</small>
                            </div>
                            <a href="{{ asset('storage/' . $doc->ruta_pdf) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <!-- Historial -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historial del Expediente</h5>
                </div>
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    <ul class="list-group list-group-flush">
                        @foreach($expediente->historial->sortByDesc('created_at') as $hist)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $hist->usuario->name ?? 'Sistema' }}</strong>
                                    <br>
                                    <span>{{ $hist->descripcion }}</span>
                                </div>
                                <small class="text-muted">{{ $hist->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-md-4">
            <!-- Asignación -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-cog me-2"></i>Asignación</h5>
                </div>
                <div class="card-body">
                    <p><strong>Funcionario Asignado:</strong></p>
                    @if($expediente->funcionarioAsignado)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar bg-primary text-white rounded-circle me-2"
                                 style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                {{ substr($expediente->funcionarioAsignado->name, 0, 1) }}
                            </div>
                            <div>
                                <strong>{{ $expediente->funcionarioAsignado->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $expediente->funcionarioAsignado->email }}</small>
                            </div>
                        </div>
                    @else
                        <p class="text-danger">Sin asignar</p>
                    @endif

                    <hr>
                    <form method="POST" action="{{ route('jefe-area.asignar-expediente', $expediente) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Cambiar Asignación</label>
                            <select name="funcionario_id" class="form-select" required>
                                <option value="">-- Seleccione --</option>
                                @foreach($funcionarios as $func)
                                    <option value="{{ $func->id }}" {{ $expediente->id_funcionario_asignado == $func->id ? 'selected' : '' }}>
                                        {{ $func->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-user-edit me-1"></i> Reasignar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Acciones -->
            @if($expediente->estado === 'resuelto')
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Acciones de Validación</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Este expediente está listo para validación.</p>

                    @if($expediente->observaciones_funcionario)
                    <div class="alert alert-secondary">
                        <strong>Observaciones del Funcionario:</strong>
                        <p class="mb-0">{{ $expediente->observaciones_funcionario }}</p>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('jefe-area.aprobar', $expediente) }}" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check me-1"></i> Aprobar Expediente
                        </button>
                    </form>

                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#modalRechazar">
                        <i class="fas fa-times me-1"></i> Rechazar Expediente
                    </button>
                </div>
            </div>

            <!-- Modal Rechazar -->
            <div class="modal fade" id="modalRechazar" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('jefe-area.rechazar', $expediente) }}">
                            @csrf
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">Rechazar Expediente</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Motivo del Rechazo *</label>
                                    <textarea name="motivo_rechazo" class="form-control" rows="4" required minlength="10"
                                              placeholder="Explique detalladamente el motivo del rechazo..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger">Rechazar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            <!-- Derivaciones -->
            @if($expediente->derivaciones && $expediente->derivaciones->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-route me-2"></i>Derivaciones</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($expediente->derivaciones->sortByDesc('created_at') as $der)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-{{ $der->estado === 'pendiente' ? 'warning' : 'success' }}">
                                    {{ ucfirst($der->estado) }}
                                </span>
                                <small>{{ $der->created_at->format('d/m/Y') }}</small>
                            </div>
                            <small class="text-muted d-block mt-1">
                                A: {{ $der->areaDestino->nombre ?? 'N/A' }}
                            </small>
                            @if($der->funcionarioAsignado)
                            <small class="text-muted d-block">
                                Funcionario: {{ $der->funcionarioAsignado->name }}
                            </small>
                            @endif
                            @if($der->fecha_limite)
                            <small class="text-{{ $der->fecha_limite->isPast() ? 'danger' : 'muted' }} d-block">
                                Límite: {{ $der->fecha_limite->format('d/m/Y') }}
                                @if($der->fecha_limite->isPast() && $der->estado === 'pendiente')
                                    <span class="badge bg-danger">Vencido</span>
                                @endif
                            </small>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <!-- Observaciones -->
            @if($expediente->observaciones && $expediente->observaciones->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-comment-dots me-2"></i>Observaciones</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($expediente->observaciones as $obs)
                        <li class="list-group-item">
                            <span class="badge bg-{{ $obs->tipo === 'rechazo' ? 'danger' : 'info' }} mb-1">
                                {{ ucfirst($obs->tipo) }}
                            </span>
                            <p class="mb-1 small">{{ $obs->descripcion }}</p>
                            <small class="text-muted">{{ $obs->created_at->format('d/m/Y H:i') }}</small>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
