@extends('layouts.app')

@section('title', 'Clasificar Expediente')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2">
                    <h5 class="mb-0"><i class="fas fa-tags text-warning me-1"></i>Clasificar Expediente - {{ $expediente->codigo_expediente }}</h5>
                </div>

                <div class="card-body py-2">
                    <!-- Resumen del expediente -->
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <div class="bg-light rounded p-2" style="font-size: 0.82rem;">
                                <strong>Asunto:</strong> {{ Str::limit($expediente->asunto, 50) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light rounded p-2" style="font-size: 0.82rem;">
                                <strong>Remitente:</strong> {{ $expediente->remitente ?? $expediente->ciudadano->name ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('mesa-partes.update-clasificacion', $expediente) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-2">
                            <div class="col-md-4">
                                <label for="id_tipo_tramite" class="form-label" style="font-size: 0.82rem;">Tipo de Tramite *</label>
                                <select class="form-select form-select-sm @error('id_tipo_tramite') is-invalid @enderror"
                                        id="id_tipo_tramite" name="id_tipo_tramite" required>
                                    <option value="">Seleccionar tipo</option>
                                    @foreach($tipoTramites as $tipo)
                                        <option value="{{ $tipo->id_tipo_tramite }}" {{ old('id_tipo_tramite') == $tipo->id_tipo_tramite ? 'selected' : '' }}>
                                            {{ $tipo->nombre }} ({{ $tipo->dias_limite }} dias)
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_tipo_tramite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="id_area" class="form-label" style="font-size: 0.82rem;">Area Destino *</label>
                                <select class="form-select form-select-sm @error('id_area') is-invalid @enderror"
                                        id="id_area" name="id_area" required>
                                    <option value="">Seleccionar area</option>
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

                            <div class="col-md-4">
                                <label for="prioridad" class="form-label" style="font-size: 0.82rem;">Prioridad *</label>
                                <select class="form-select form-select-sm @error('prioridad') is-invalid @enderror"
                                        id="prioridad" name="prioridad" required>
                                    <option value="">Seleccionar prioridad</option>
                                    <option value="baja" {{ old('prioridad') == 'baja' ? 'selected' : '' }}>Baja</option>
                                    <option value="normal" {{ old('prioridad') == 'normal' ? 'selected' : '' }} selected>Normal</option>
                                    <option value="alta" {{ old('prioridad') == 'alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="urgente" {{ old('prioridad') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                </select>
                                @error('prioridad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="observaciones_clasificacion" class="form-label" style="font-size: 0.82rem;">Observaciones</label>
                            <input type="text" class="form-control form-control-sm" id="observaciones_clasificacion" name="observaciones_clasificacion"
                                   placeholder="Observaciones opcionales..." value="{{ old('observaciones_clasificacion') }}">
                        </div>

                        <div class="alert alert-info py-2 mb-2" style="font-size: 0.78rem;">
                            <h6 class="mb-1" style="font-size: 0.82rem;"><i class="fas fa-info-circle me-1"></i>Criterios de Clasificacion</h6>
                            <ul class="mb-0 ps-3">
                                <li><strong>Tipo de Tramite:</strong> Determina el area competente y plazo de atencion</li>
                                <li><strong>Area Destino:</strong> Unidad organica responsable del tramite</li>
                                <li><strong>Prioridad:</strong> Urgencia segun normativa y plazos legales</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between pt-2 border-top">
                            <a href="{{ route('mesa-partes.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Volver
                            </a>
                            <button type="submit" class="btn btn-success btn-sm px-4">
                                <i class="fas fa-check me-1"></i>Clasificar Expediente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
