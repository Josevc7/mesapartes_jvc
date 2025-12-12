@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Crear Resolución</h3>
                </div>
                <form action="{{ route('resoluciones.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <input type="hidden" name="id_expediente" value="{{ $expediente->id }}">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Expediente</label>
                                    <input type="text" class="form-control" value="{{ $expediente->codigo_expediente }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Asunto</label>
                                    <input type="text" class="form-control" value="{{ $expediente->asunto }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="numero_resolucion">Número de Resolución *</label>
                                    <input type="text" name="numero_resolucion" id="numero_resolucion" 
                                           class="form-control @error('numero_resolucion') is-invalid @enderror" 
                                           value="{{ old('numero_resolucion') }}" required>
                                    @error('numero_resolucion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo_resolucion">Tipo de Resolución *</label>
                                    <select name="tipo_resolucion" id="tipo_resolucion" 
                                            class="form-control @error('tipo_resolucion') is-invalid @enderror" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="aprobado" {{ old('tipo_resolucion') === 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                                        <option value="rechazado" {{ old('tipo_resolucion') === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                                        <option value="observado" {{ old('tipo_resolucion') === 'observado' ? 'selected' : '' }}>Observado</option>
                                    </select>
                                    @error('tipo_resolucion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="fundamento_legal">Fundamento Legal</label>
                            <textarea name="fundamento_legal" id="fundamento_legal" 
                                      class="form-control @error('fundamento_legal') is-invalid @enderror" 
                                      rows="4">{{ old('fundamento_legal') }}</textarea>
                            @error('fundamento_legal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" 
                                      class="form-control @error('observaciones') is-invalid @enderror" 
                                      rows="3">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="documento_resolucion">Documento de Resolución (PDF)</label>
                            <input type="file" name="documento_resolucion" id="documento_resolucion" 
                                   class="form-control-file @error('documento_resolucion') is-invalid @enderror" 
                                   accept=".pdf">
                            @error('documento_resolucion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Máximo 10MB, solo archivos PDF</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between pt-3">
                            <a href="{{ route('resoluciones.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="fas fa-arrow-left me-2"></i>Volver a Resoluciones
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                                <i class="fas fa-gavel me-2"></i>Crear Resolución
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection