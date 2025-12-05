@extends('layouts.app')

@section('title', 'Gestión de Documentos')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Documentos: {{ $expediente->codigo_expediente }}</h4>
                <div>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#subirDocumentoModal">
                        <i class="fas fa-upload"></i> Subir Documento
                    </button>
                    <a href="{{ route('funcionario.show', $expediente) }}" class="btn btn-secondary">Volver</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Documentos del Expediente ({{ $expediente->documentos->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($expediente->documentos->count() > 0)
                        <div class="row">
                            @foreach($expediente->documentos as $documento)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-file-pdf fa-2x text-danger me-3"></i>
                                            <div>
                                                <h6 class="card-title mb-1">{{ $documento->nombre }}</h6>
                                                <small class="text-muted">{{ $documento->tipo }}</small>
                                            </div>
                                        </div>
                                        
                                        <p class="card-text">
                                            <small class="text-muted">
                                                Subido por: {{ $documento->usuario->name ?? 'Sistema' }}<br>
                                                Fecha: {{ $documento->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </p>
                                        
                                        <div class="d-grid gap-2">
                                            <a href="{{ Storage::url($documento->ruta_archivo) }}" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i> Ver Documento
                                            </a>
                                            <a href="{{ Storage::url($documento->ruta_archivo) }}" download class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-download"></i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay documentos adjuntos en este expediente</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subirDocumentoModal">
                                Subir Primer Documento
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Subir Documento -->
<div class="modal fade" id="subirDocumentoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subir Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('funcionario.adjuntar-documento', $expediente) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Documento *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required 
                               placeholder="Ej: Informe Técnico N° 001-2024">
                    </div>
                    
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo de Documento *</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="Informe">Informe</option>
                            <option value="Resolución">Resolución</option>
                            <option value="Anexo">Anexo</option>
                            <option value="Respuesta">Respuesta</option>
                            <option value="Dictamen">Dictamen</option>
                            <option value="Memorando">Memorando</option>
                            <option value="Oficio">Oficio</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="documento" class="form-label">Archivo *</label>
                        <input type="file" class="form-control" id="documento" name="documento" 
                               accept=".pdf,.doc,.docx" required>
                        <div class="form-text">Archivos permitidos: PDF, DOC, DOCX. Tamaño máximo: 10MB</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <strong>Importante:</strong> Una vez subido el documento, quedará registrado 
                            permanentemente en el expediente y será visible para Mesa de Partes y el ciudadano.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Subir Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection