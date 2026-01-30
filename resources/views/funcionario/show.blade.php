@extends('layouts.app')

@section('title', 'Detalle del Expediente')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Expediente: {{ $expediente->codigo_expediente }}</h4>
                    @php
                        $badgeClass = match($expediente->estado) {
                            'asignado' => 'primary',
                            'en_proceso' => 'info',
                            'en_revision' => 'warning',
                            default => 'secondary'
                        };
                    @endphp
                    <span class="badge bg-{{ $badgeClass }}">
                        {{ $expediente->getEstadoFormateado() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Remitente:</strong><br>
                            {{ $expediente->remitente ?? $expediente->ciudadano->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha de Registro:</strong><br>
                            {{ $expediente->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <strong>Asunto:</strong><br>
                        {{ $expediente->asunto }}
                    </div>
                    
                    @if($expediente->tipoTramite)
                    <div class="mt-3">
                        <strong>Tipo de Trámite:</strong><br>
                        {{ $expediente->tipoTramite->nombre }}
                    </div>
                    @endif
                    
                    @if($expediente->observaciones)
                    <div class="mt-3">
                        <strong>Observaciones Iniciales:</strong><br>
                        {{ $expediente->observaciones }}
                    </div>
                    @endif
                    
                    @if($expediente->observaciones_funcionario)
                    <div class="mt-3">
                        <strong>Observaciones del Funcionario:</strong><br>
                        {{ $expediente->observaciones_funcionario }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Documentos -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Documentos</h5>
                </div>
                <div class="card-body">
                    @if($expediente->documentos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expediente->documentos as $documento)
                                    <tr>
                                        <td>{{ $documento->nombre }}</td>
                                        <td>{{ $documento->tipo }}</td>
                                        <td>{{ $documento->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ Storage::url($documento->ruta_pdf) }}" target="_blank" class="btn btn-sm btn-outline-primary">Ver</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No hay documentos adjuntos</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Panel de Acciones -->
        <div class="col-md-4">
            <!-- Botón Volver -->
            <div class="card mb-3">
                <div class="card-body">
                    <a href="{{ route('funcionario.dashboard') }}" class="btn btn-outline-secondary btn-lg w-100 px-4">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                    </a>
                </div>
            </div>
            @if($expediente->estado == 'asignado')
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-inbox me-2"></i>Expediente Asignado</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        El Jefe de Área le ha asignado este expediente para su atención.
                    </div>
                    <form method="POST" action="{{ route('funcionario.recibir', $expediente) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check-circle me-1"></i> Recibir y Comenzar a Procesar
                        </button>
                    </form>
                </div>
            </div>
            @endif

            @if($expediente->estado == 'en_proceso')
            <div class="card">
                <div class="card-header">
                    <h5>Acciones</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('funcionario.procesar', $expediente) }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-edit me-1"></i> Procesar Expediente
                    </a>

                    @php
                        $tieneDocumentoAdjunto = $expediente->documentos()
                            ->whereIn('tipo', ['informe', 'respuesta', 'resolucion', 'oficio'])
                            ->exists();
                    @endphp

                    <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#resolverModal">
                        <i class="fas fa-paper-plane me-1"></i> Devolver al Jefe
                    </button>

                    @if(!$tieneDocumentoAdjunto)
                    <div class="alert alert-warning py-2 small mb-2">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Debe adjuntar un documento antes de devolver al jefe.
                    </div>
                    @endif

                    <button type="button" class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#solicitarInfoModal">
                        <i class="fas fa-question-circle me-1"></i> Solicitar Info al Ciudadano
                    </button>
                    <a href="{{ route('funcionario.historial', $expediente) }}" class="btn btn-outline-info w-100 mb-2">
                        <i class="fas fa-history me-1"></i> Ver Historial
                    </a>
                    <a href="{{ route('funcionario.documentos', $expediente) }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-folder-open me-1"></i> Gestionar Documentos
                    </a>
                </div>
            </div>

            <!-- Adjuntar Documento -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Adjuntar Documento</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('funcionario.adjuntar-documento', $expediente) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm" name="nombre" placeholder="Nombre del documento" required>
                        </div>
                        <div class="mb-2">
                            <select class="form-select form-select-sm" name="tipo" required>
                                <option value="">Tipo</option>
                                <option value="informe">Informe</option>
                                <option value="respuesta">Respuesta</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <input type="file" class="form-control form-control-sm" name="documento" accept=".pdf,.doc,.docx" required>
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100">Adjuntar</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Devolver al Jefe -->
<div class="modal fade" id="resolverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i>Devolver al Jefe de Área</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('funcionario.resolver', $expediente) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i>
                        El expediente será enviado al <strong>Jefe de Área</strong> para su revisión y aprobación.
                    </div>

                    @php
                        $docsAdjuntos = $expediente->documentos()
                            ->whereIn('tipo', ['informe', 'respuesta', 'resolucion', 'oficio'])
                            ->get();
                    @endphp

                    @if($docsAdjuntos->count() > 0)
                    <p><strong>Documentos adjuntos:</strong></p>
                    <ul class="list-group list-group-flush mb-3">
                        @foreach($docsAdjuntos as $doc)
                        <li class="list-group-item py-1">
                            <i class="fas fa-file-pdf text-danger me-1"></i>
                            {{ $doc->nombre }} <span class="badge bg-secondary">{{ ucfirst($doc->tipo) }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Sin documentos adjuntos.</strong> Debe adjuntar al menos un documento (informe, respuesta, resolución u oficio) antes de devolver.
                    </div>
                    @endif

                    <p class="text-muted small">
                        <i class="fas fa-history me-1"></i>
                        Se registrará en el historial: quién devolvió, fecha/hora y documento adjunto.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" {{ $docsAdjuntos->count() === 0 ? 'disabled' : '' }}>
                        <i class="fas fa-paper-plane me-1"></i> Devolver al Jefe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Solicitar Info -->
<div class="modal fade" id="solicitarInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Solicitar Información</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('funcionario.solicitar-info', $expediente) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Plazo de Respuesta (días)</label>
                        <input type="number" class="form-control" name="plazo_respuesta" min="1" max="30" value="15" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Solicitar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection