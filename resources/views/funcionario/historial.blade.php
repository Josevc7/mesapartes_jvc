@extends('layouts.app')

@section('title', 'Historial del Expediente')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Historial: {{ $expediente->codigo_expediente }}</h4>
                <a href="{{ route('funcionario.show', $expediente) }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Timeline del Historial -->
            <div class="card">
                <div class="card-header">
                    <h5>Cronología del Expediente</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($expediente->historial->sortByDesc('created_at') as $historial)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $loop->first ? 'primary' : 'secondary' }}"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-1">{{ $historial->descripcion }}</h6>
                                    <small class="text-muted">{{ $historial->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <p class="text-muted mb-0">
                                    Por: {{ $historial->usuario->name ?? 'Sistema' }}
                                    @if($historial->usuario->role ?? null)
                                        ({{ $historial->usuario->role->nombre }})
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            @if($expediente->observaciones()->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Observaciones y Solicitudes</h5>
                </div>
                <div class="card-body">
                    @foreach($expediente->observaciones as $observacion)
                    <div class="alert alert-{{ $observacion->tipo == 'subsanacion' ? 'warning' : 'info' }}">
                        <div class="d-flex justify-content-between">
                            <strong>{{ ucfirst($observacion->tipo) }}</strong>
                            <small>{{ $observacion->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        <p class="mb-1">{{ $observacion->descripcion }}</p>
                        @if($observacion->fecha_limite)
                        <small>Plazo límite: {{ $observacion->fecha_limite->format('d/m/Y') }}</small>
                        @endif
                        <br>
                        <span class="badge bg-{{ $observacion->estado == 'pendiente' ? 'warning' : 'success' }}">
                            {{ ucfirst($observacion->estado) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Panel Lateral -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Información del Expediente</h5>
                </div>
                <div class="card-body">
                    <p><strong>Estado Actual:</strong><br>
                    <span class="badge bg-info">{{ $expediente->estado }}</span></p>
                    
                    <p><strong>Fecha de Registro:</strong><br>
                    {{ $expediente->created_at->format('d/m/Y H:i') }}</p>
                    
                    @if($expediente->fecha_resolucion)
                    <p><strong>Fecha de Resolución:</strong><br>
                    {{ $expediente->fecha_resolucion->format('d/m/Y H:i') }}</p>
                    @endif
                    
                    <p><strong>Tiempo Transcurrido:</strong><br>
                    {{ $expediente->created_at->diffForHumans() }}</p>
                    
                    @if($expediente->tipoTramite)
                    <p><strong>Plazo Máximo:</strong><br>
                    {{ $expediente->tipoTramite->dias_limite }} días</p>
                    @endif
                </div>
            </div>

            <!-- Acciones Rápidas -->
            @if($expediente->estado == 'En Proceso')
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('funcionario.procesar', $expediente) }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-cogs"></i> Procesar
                    </a>
                    <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#resolverModal">
                        <i class="fas fa-check"></i> Resolver
                    </button>
                    <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#observarModal">
                        <i class="fas fa-exclamation-triangle"></i> Observar
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline::before {
    content: '';
    position: absolute;
    left: -30px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}
</style>
@endsection