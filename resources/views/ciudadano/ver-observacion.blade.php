@extends('layouts.app')

@section('title', 'Responder Observación')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-reply text-warning me-2"></i>
                    Responder Observación
                </h2>
                <a href="{{ route('ciudadano.observaciones') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>

            <!-- Información del Expediente -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        Información del Expediente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Código:</strong> {{ $expediente->codigo_expediente }}</p>
                            <p><strong>Tipo de Trámite:</strong> {{ $expediente->tipoTramite->nombre ?? 'Sin tipo' }}</p>
                            <p><strong>Área:</strong> {{ $expediente->area->nombre ?? 'Sin área' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Estado:</strong>
                                <span class="badge bg-warning">{{ ucfirst($expediente->estado) }}</span>
                            </p>
                            <p><strong>Fecha Registro:</strong> {{ $expediente->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Asunto:</strong></p>
                            <div class="alert alert-light">
                                {{ $expediente->asunto }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observaciones Pendientes -->
            @if($expediente->observaciones->where('estado', 'pendiente')->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Observaciones Solicitadas
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($expediente->observaciones->where('estado', 'pendiente') as $index => $observacion)
                    <div class="alert alert-warning{{ $index > 0 ? ' mt-3' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="alert-heading">
                                    <i class="fas fa-user me-1"></i>
                                    Observación de: {{ $observacion->usuario->name ?? 'Sistema' }}
                                </h6>
                                <p class="mb-2">{{ $observacion->descripcion }}</p>

                                @if($observacion->tipo)
                                <p class="mb-2">
                                    <strong>Tipo:</strong>
                                    <span class="badge bg-{{ $observacion->tipo == 'subsanacion' ? 'danger' : 'info' }}">
                                        {{ ucfirst($observacion->tipo) }}
                                    </span>
                                </p>
                                @endif

                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Solicitada: {{ $observacion->created_at->format('d/m/Y H:i') }}
                                </small>

                                @if($observacion->fecha_limite)
                                <br>
                                <small class="text-danger">
                                    <i class="fas fa-calendar-times me-1"></i>
                                    <strong>Plazo límite:</strong>
                                    {{ \Carbon\Carbon::parse($observacion->fecha_limite)->format('d/m/Y') }}
                                    ({{ \Carbon\Carbon::parse($observacion->fecha_limite)->diffForHumans() }})
                                </small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Formulario de Respuesta -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Su Respuesta
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('ciudadano.responder-observacion', $expediente) }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="respuesta" class="form-label">
                                <i class="fas fa-comment me-1"></i>
                                Descripción de su respuesta *
                            </label>
                            <textarea class="form-control @error('respuesta') is-invalid @enderror"
                                      id="respuesta"
                                      name="respuesta"
                                      rows="5"
                                      required
                                      placeholder="Explique cómo está subsanando la observación...">{{ old('respuesta') }}</textarea>
                            @error('respuesta')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Máximo 1000 caracteres. Sea claro y específico en su respuesta.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="documentos" class="form-label">
                                <i class="fas fa-paperclip me-1"></i>
                                Documentos de Subsanación
                            </label>
                            <input type="file"
                                   class="form-control @error('documentos.*') is-invalid @enderror"
                                   id="documentos"
                                   name="documentos[]"
                                   multiple
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            @error('documentos.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Puede adjuntar múltiples archivos. Formatos permitidos: PDF, DOC, DOCX, JPG, PNG. Máximo 5MB por archivo.
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Importante:</strong> Una vez enviada su respuesta, el expediente será revisado nuevamente por el funcionario asignado. Recibirá una notificación cuando se procese su subsanación.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>
                                Enviar Respuesta
                            </button>
                            <a href="{{ route('ciudadano.observaciones') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                No hay observaciones pendientes para este expediente.
            </div>
            @endif

            <!-- Documentos del Expediente -->
            @if($expediente->documentos->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-folder-open me-2"></i>
                        Documentos del Expediente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($expediente->documentos as $documento)
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                                    <h6 class="card-title">{{ $documento->nombre }}</h6>
                                    <p class="card-text">
                                        <small class="text-muted">{{ ucfirst($documento->tipo) }}</small>
                                    </p>
                                    <a href="{{ Storage::url($documento->ruta_pdf) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> Ver
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
