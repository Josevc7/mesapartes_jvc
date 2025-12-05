@extends('layouts.app')

@section('title', 'Detalle del Expediente')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Expediente: {{ $expediente->codigo_expediente }}</h4>
                    <span class="badge bg-{{ $expediente->estado == 'derivado' ? 'warning' : 'info' }}">
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
            @if($expediente->estado == 'derivado')
            <div class="card">
                <div class="card-header">
                    <h5>Recibir Expediente</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('funcionario.recibir', $expediente) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-success w-100">Recibir Expediente</button>
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
                    <a href="{{ route('funcionario.procesar', $expediente) }}" class="btn btn-primary w-100 mb-2">Procesar</a>
                    <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#resolverModal">Resolver</button>
                    <button type="button" class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#solicitarInfoModal">Solicitar Info</button>
                    <a href="{{ route('funcionario.derivar-form', $expediente) }}" class="btn btn-outline-warning w-100 mb-2">
                        <i class="fas fa-share"></i> Derivar a Otra Área
                    </a>
                    <a href="{{ route('funcionario.historial', $expediente) }}" class="btn btn-outline-info w-100 mb-2">Ver Historial</a>
                    <a href="{{ route('funcionario.documentos', $expediente) }}" class="btn btn-outline-secondary w-100">Gestionar Documentos</a>
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

<!-- Modal Resolver -->
<div class="modal fade" id="resolverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resolver Expediente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('funcionario.resolver', $expediente) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p>¿Está seguro de marcar este expediente como resuelto?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Resolver</button>
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