@extends('layouts.app')

@section('title', 'Seguimiento de Expediente')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Seguimiento: {{ $expediente->codigo_expediente }}</h2>
                <a href="{{ route('ciudadano.mis-expedientes') }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>

    <!-- Información del Expediente -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Información del Expediente</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Código:</strong><br>
                            <span class="text-primary fs-5">{{ $expediente->codigo_expediente }}</span></p>
                            
                            <p><strong>Estado Actual:</strong><br>
                            <span class="badge bg-{{ 
                                $expediente->estado == 'Resuelto' ? 'success' : 
                                ($expediente->estado == 'Observado' ? 'warning' : 'info') 
                            }} fs-6">{{ $expediente->estado }}</span></p>
                            
                            <p><strong>Fecha de Registro:</strong><br>
                            {{ $expediente->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tipo de Trámite:</strong><br>
                            {{ $expediente->tipoTramite->nombre ?? 'Sin clasificar' }}</p>
                            
                            <p><strong>Área Responsable:</strong><br>
                            {{ $expediente->area->nombre ?? 'Sin asignar' }}</p>
                            
                            @if($expediente->fecha_resolucion)
                            <p><strong>Fecha de Resolución:</strong><br>
                            {{ $expediente->fecha_resolucion->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <hr>
                    
                    <p><strong>Asunto:</strong><br>
                    {{ $expediente->asunto }}</p>
                    
                    @if($expediente->observaciones_funcionario)
                    <div class="alert alert-info">
                        <strong>Observaciones del Funcionario:</strong><br>
                        {{ $expediente->observaciones_funcionario }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Timeline del Expediente -->
            <div class="card mt-4">
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
                                    {{ $historial->usuario->name ?? 'Sistema' }}
                                    @if($historial->usuario->role ?? null)
                                        - {{ $historial->usuario->role->nombre }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-md-4">
            <!-- Estado Visual -->
            <div class="card">
                <div class="card-header">
                    <h5>Estado del Proceso</h5>
                </div>
                <div class="card-body">
                    <div class="progress-steps">
                        <div class="step {{ in_array($expediente->estado, ['Registrado', 'Clasificado', 'Derivado', 'En Proceso', 'Resuelto']) ? 'completed' : '' }}">
                            <div class="step-icon">1</div>
                            <div class="step-text">Registrado</div>
                        </div>
                        <div class="step {{ in_array($expediente->estado, ['Clasificado', 'Derivado', 'En Proceso', 'Resuelto']) ? 'completed' : '' }}">
                            <div class="step-icon">2</div>
                            <div class="step-text">Clasificado</div>
                        </div>
                        <div class="step {{ in_array($expediente->estado, ['Derivado', 'En Proceso', 'Resuelto']) ? 'completed' : '' }}">
                            <div class="step-icon">3</div>
                            <div class="step-text">En Proceso</div>
                        </div>
                        <div class="step {{ $expediente->estado == 'Resuelto' ? 'completed' : '' }}">
                            <div class="step-icon">4</div>
                            <div class="step-text">Resuelto</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documentos -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Documentos ({{ $expediente->documentos->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($expediente->documentos->count() > 0)
                        @foreach($expediente->documentos as $documento)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <i class="fas fa-file-pdf text-danger"></i>
                                <strong>{{ $documento->nombre }}</strong>
                                <br><small class="text-muted">{{ $documento->tipo }} - {{ $documento->created_at->format('d/m/Y') }}</small>
                            </div>
                            @if(in_array($documento->tipo, ['Respuesta', 'Resolución', 'Informe']))
                                <a href="{{ route('ciudadano.descargar-documento', $documento) }}" 
                                   class="btn btn-sm btn-success">
                                    <i class="fas fa-download"></i>
                                </a>
                            @endif
                        </div>
                        @if(!$loop->last)<hr>@endif
                        @endforeach
                    @else
                        <p class="text-muted">No hay documentos disponibles</p>
                    @endif
                </div>
            </div>

            <!-- Acciones -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('ciudadano.descargar-acuse', $expediente->codigo_expediente) }}" 
                           class="btn btn-info">
                            <i class="fas fa-download"></i> Descargar Comprobante
                        </a>
                        @if($expediente->documentos->where('tipo', 'Respuesta')->count() > 0)
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#respuestasModal">
                                <i class="fas fa-file-pdf"></i> Ver Respuestas
                            </button>
                        @endif
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Respuestas -->
@if($expediente->documentos->where('tipo', 'Respuesta')->count() > 0)
<div class="modal fade" id="respuestasModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Documentos de Respuesta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @foreach($expediente->documentos->where('tipo', 'Respuesta') as $documento)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6>{{ $documento->nombre }}</h6>
                                <p class="text-muted mb-0">
                                    Fecha: {{ $documento->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <a href="{{ route('ciudadano.descargar-documento', $documento) }}" 
                               class="btn btn-success">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endif

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

.progress-steps {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.step {
    display: flex;
    align-items: center;
    gap: 10px;
}

.step-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #6c757d;
}

.step.completed .step-icon {
    background: #28a745;
    color: white;
}

.step.completed .step-text {
    color: #28a745;
    font-weight: bold;
}
</style>
@endsection