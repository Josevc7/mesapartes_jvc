@extends('layouts.app')

@section('title', 'Clasificar Expediente')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Clasificar Expediente: {{ $expediente->codigo_expediente }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Asunto:</strong> {{ $expediente->asunto }}
                    </div>
                    <div class="mb-3">
                        <strong>Remitente:</strong> {{ $expediente->remitente ?? $expediente->ciudadano->name ?? 'N/A' }}
                    </div>
                    
                    <hr>
                    
                    <form method="POST" action="{{ route('mesa-partes.update-clasificacion', $expediente) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="id_tipo_tramite" class="form-label">Tipo de Trámite *</label>
                            <select class="form-select @error('id_tipo_tramite') is-invalid @enderror" 
                                    id="id_tipo_tramite" name="id_tipo_tramite" required>
                                <option value="">Seleccionar tipo</option>
                                @foreach($tipoTramites as $tipo)
                                    <option value="{{ $tipo->id_tipo_tramite }}" {{ old('id_tipo_tramite') == $tipo->id_tipo_tramite ? 'selected' : '' }}>
                                        {{ $tipo->nombre }} ({{ $tipo->dias_limite }} días)
                                    </option>
                                @endforeach
                            </select>
                            @error('id_tipo_tramite')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="id_area" class="form-label">Área Destino *</label>
                            <select class="form-select @error('id_area') is-invalid @enderror" 
                                    id="id_area" name="id_area" required>
                                <option value="">Seleccionar área</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id_area }}" {{ old('id_area') == $area->id_area ? 'selected' : '' }}>
                                        {{ $area->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_area')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="prioridad" class="form-label">Prioridad *</label>
                            <select class="form-select @error('prioridad') is-invalid @enderror" 
                                    id="prioridad" name="prioridad" required>
                                <option value="">Seleccionar prioridad</option>
                                <option value="baja" {{ old('prioridad') == 'baja' ? 'selected' : '' }}>🟢 Baja - Trámites regulares</option>
                                <option value="normal" {{ old('prioridad') == 'normal' ? 'selected' : '' }} selected>🔵 Normal - Trámites estándar</option>
                                <option value="alta" {{ old('prioridad') == 'alta' ? 'selected' : '' }}>🟡 Alta - Vencimientos próximos</option>
                                <option value="urgente" {{ old('prioridad') == 'urgente' ? 'selected' : '' }}>🔴 Urgente - Emergencias</option>
                            </select>
                            @error('prioridad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="observaciones_clasificacion" class="form-label">Observaciones de Clasificación</label>
                            <textarea class="form-control" id="observaciones_clasificacion" name="observaciones_clasificacion" 
                                      rows="3" placeholder="Observaciones adicionales sobre la clasificación del expediente...">{{ old('observaciones_clasificacion') }}</textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Criterios de Clasificación</h6>
                            <ul class="mb-0 small">
                                <li><strong>Tipo de Trámite:</strong> Determina el área competente y plazo de atención</li>
                                <li><strong>Área Destino:</strong> Unidad orgánica responsable del trámite</li>
                                <li><strong>Prioridad:</strong> Urgencia según normativa y plazos legales</li>
                            </ul>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between pt-4 border-top">
                            <a href="{{ route('mesa-partes.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="fas fa-arrow-left me-2"></i>Volver a Expedientes
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                                <i class="fas fa-check me-2"></i>Clasificar Expediente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection