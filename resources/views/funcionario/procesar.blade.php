@extends('layouts.app')

@section('title', 'Procesar Expediente')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Procesar Expediente: {{ $expediente->codigo_expediente }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <strong>Asunto:</strong> {{ $expediente->asunto }}<br>
                        <strong>Remitente:</strong> {{ $expediente->remitente ?? $expediente->ciudadano->name ?? 'N/A' }}<br>
                        <strong>Tipo:</strong> {{ $expediente->tipoTramite->nombre ?? 'N/A' }}
                    </div>
                    
                    <hr>
                    
                    <form method="POST" action="{{ route('funcionario.update-procesar', $expediente) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="accion" class="form-label">Acción a Realizar *</label>
                            <select class="form-select @error('accion') is-invalid @enderror" id="accion" name="accion" required>
                                <option value="">Seleccionar acción</option>
                                <option value="procesar" {{ old('accion') == 'procesar' ? 'selected' : '' }}>Continuar Procesando</option>
                                <option value="resolver" {{ old('accion') == 'resolver' ? 'selected' : '' }}>Resolver Expediente</option>
                                <option value="solicitar_info" {{ old('accion') == 'solicitar_info' ? 'selected' : '' }}>Solicitar Información</option>
                            </select>
                            @error('accion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="observaciones_funcionario" class="form-label">Observaciones del Funcionario *</label>
                            <textarea class="form-control @error('observaciones_funcionario') is-invalid @enderror" 
                                      id="observaciones_funcionario" name="observaciones_funcionario" rows="4" required>{{ old('observaciones_funcionario') }}</textarea>
                            <div class="form-text">Describa las acciones realizadas, decisiones tomadas o información requerida</div>
                            @error('observaciones_funcionario')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nombre_documento" class="form-label">Nombre del Documento (opcional)</label>
                            <input type="text" class="form-control" id="nombre_documento" name="nombre_documento" 
                                   value="{{ old('nombre_documento') }}" placeholder="Ej: Informe Técnico, Resolución, etc.">
                        </div>

                        <div class="mb-3">
                            <label for="documento_respuesta" class="form-label">Adjuntar Documento</label>
                            <input type="file" class="form-control @error('documento_respuesta') is-invalid @enderror" 
                                   id="documento_respuesta" name="documento_respuesta" accept=".pdf,.doc,.docx">
                            <div class="form-text">Archivos PDF, DOC o DOCX, máximo 10MB</div>
                            @error('documento_respuesta')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <strong>Información:</strong>
                            <ul class="mb-0">
                                <li><strong>Continuar Procesando:</strong> Mantiene el expediente en proceso con sus observaciones</li>
                                <li><strong>Resolver:</strong> Marca el expediente como resuelto y finaliza el trámite</li>
                                <li><strong>Solicitar Información:</strong> Envía observaciones al remitente para subsanación</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('funcionario.show', $expediente) }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Procesar Expediente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection