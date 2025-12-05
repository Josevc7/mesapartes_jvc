@extends('layouts.app')

@section('title', 'Solicitar Información Adicional')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Solicitar Información Adicional</h4>
                    <small class="text-muted">Expediente: {{ $expediente->codigo_expediente }}</small>
                </div>
                <div class="card-body">
                    <!-- Información del Expediente -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>Asunto:</strong><br>
                            {{ $expediente->asunto }}
                        </div>
                        <div class="col-md-6">
                            <strong>Remitente:</strong><br>
                            {{ $expediente->remitente ?? $expediente->ciudadano->name ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <hr>
                    
                    <form method="POST" action="{{ route('funcionario.solicitar-info', $expediente) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Información Requerida *</label>
                            <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                      id="observaciones" name="observaciones" rows="5" required>{{ old('observaciones') }}</textarea>
                            <div class="form-text">
                                Especifique claramente qué información o documentos adicionales necesita el remitente para continuar con el trámite.
                            </div>
                            @error('observaciones')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="plazo_respuesta" class="form-label">Plazo para Respuesta (días) *</label>
                            <select class="form-select @error('plazo_respuesta') is-invalid @enderror" 
                                    id="plazo_respuesta" name="plazo_respuesta" required>
                                <option value="">Seleccionar plazo</option>
                                <option value="3" {{ old('plazo_respuesta') == '3' ? 'selected' : '' }}>3 días</option>
                                <option value="5" {{ old('plazo_respuesta') == '5' ? 'selected' : '' }}>5 días</option>
                                <option value="7" {{ old('plazo_respuesta') == '7' ? 'selected' : '' }}>7 días</option>
                                <option value="10" {{ old('plazo_respuesta') == '10' ? 'selected' : '' }}>10 días</option>
                                <option value="15" {{ old('plazo_respuesta') == '15' ? 'selected' : '' }}>15 días</option>
                                <option value="30" {{ old('plazo_respuesta') == '30' ? 'selected' : '' }}>30 días</option>
                            </select>
                            @error('plazo_respuesta')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Importante:</strong>
                            <ul class="mb-0 mt-2">
                                <li>El expediente quedará en estado "Observado" hasta que el remitente proporcione la información solicitada.</li>
                                <li>Se enviará una notificación automática al remitente con los requerimientos.</li>
                                <li>El plazo comenzará a contar desde la fecha de envío de esta solicitud.</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('funcionario.show', $expediente) }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-paper-plane"></i> Enviar Solicitud
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection