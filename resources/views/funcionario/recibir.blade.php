@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Recibir Expediente') }} - {{ $expediente->codigo_expediente }}</h4>
                </div>

                <div class="card-body">
                    <!-- Información del Expediente -->
                    <div class="alert alert-info">
                        <h6>Información del Expediente</h6>
                        <p><strong>Código:</strong> {{ $expediente->codigo_expediente }}</p>
                        <p><strong>Ciudadano:</strong> {{ $expediente->ciudadano->name }} ({{ $expediente->ciudadano->dni }})</p>
                        <p><strong>Tipo de Trámite:</strong> {{ $expediente->tipoTramite->nombre }}</p>
                        <p><strong>Asunto:</strong> {{ $expediente->asunto }}</p>
                        <p><strong>Fecha de Registro:</strong> {{ $expediente->fecha_registro }}</p>
                        <p class="mb-0"><strong>Prioridad:</strong> 
                            <span class="badge bg-{{ $expediente->prioridad == 'urgente' ? 'danger' : ($expediente->prioridad == 'alta' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($expediente->prioridad) }}
                            </span>
                        </p>
                    </div>

                    <!-- Información de Derivación -->
                    @if($derivacion = $expediente->derivaciones->last())
                    <div class="alert alert-warning">
                        <h6>Información de Derivación</h6>
                        <p><strong>Derivado por:</strong> Mesa de Partes</p>
                        <p><strong>Fecha de Derivación:</strong> {{ $derivacion->fecha_derivacion }}</p>
                        <p><strong>Plazo:</strong> {{ $derivacion->plazo_dias }} días</p>
                        @if($derivacion->observaciones)
                        <p><strong>Observaciones:</strong> {{ $derivacion->observaciones }}</p>
                        @endif
                        <p class="mb-0"><strong>Estado:</strong> 
                            <span class="badge bg-warning">{{ ucfirst($derivacion->estado) }}</span>
                        </p>
                    </div>
                    @endif

                    <!-- Documentos del Expediente -->
                    @if($expediente->documentos->where('tipo', 'entrada')->count() > 0)
                    <div class="mb-4">
                        <h6>Documentos Adjuntos</h6>
                        <div class="list-group">
                            @foreach($expediente->documentos->where('tipo', 'entrada') as $documento)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $documento->nombre }}</strong>
                                </div>
                                <a href="{{ Storage::url($documento->ruta_pdf) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    Ver PDF
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <hr>

                    <!-- Formulario de Recepción -->
                    <form method="POST" action="{{ route('funcionario.recibir', $expediente) }}">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3">Confirmar Recepción del Expediente</h5>

                        <div class="mb-3">
                            <label for="observaciones_recepcion" class="form-label">{{ __('Observaciones de Recepción') }}</label>
                            <textarea id="observaciones_recepcion" class="form-control @error('observaciones_recepcion') is-invalid @enderror" 
                                      name="observaciones_recepcion" rows="3">{{ old('observaciones_recepcion') }}</textarea>
                            <div class="form-text">Opcional: Agregue comentarios sobre la recepción del expediente.</div>
                            @error('observaciones_recepcion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input @error('confirmar_recepcion') is-invalid @enderror" 
                                       type="checkbox" value="1" id="confirmar_recepcion" name="confirmar_recepcion" required>
                                <label class="form-check-label" for="confirmar_recepcion">
                                    Confirmo que he recibido el expediente y me hago responsable de su procesamiento
                                </label>
                                @error('confirmar_recepcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <strong>Importante:</strong> Al confirmar la recepción, el expediente cambiará a estado "En Proceso" 
                            y comenzará a contar el tiempo de atención según el plazo establecido.
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('funcionario.index') }}" class="btn btn-secondary me-md-2">
                                {{ __('Volver') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('Confirmar Recepción') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection