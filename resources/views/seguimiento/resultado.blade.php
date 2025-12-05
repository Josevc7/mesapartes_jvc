@extends('layouts.app')

@section('title', 'Resultado de Consulta')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-search"></i> Resultado de Consulta Pública</h4>
                    <small class="text-muted">Estado del expediente {{ $expediente->codigo_expediente }}</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Información Principal -->
                        <div class="col-md-8">
                            <div class="card mb-3">
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
                                                $expediente->estado == 'resuelto' ? 'success' : 
                                                ($expediente->estado == 'observado' ? 'warning' : 
                                                ($expediente->estado == 'en_proceso' ? 'info' : 'secondary')) 
                                            }} fs-6">{{ ucfirst($expediente->estado) }}</span></p>
                                            
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
                                    
                                    @if($expediente->persona)
                                    <p><strong>Solicitante:</strong><br>
                                    {{ $expediente->persona->nombre_completo }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Cronología -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Cronología del Expediente</h5>
                                </div>
                                <div class="card-body">
                                    <div class="timeline">
                                        @if($expediente->derivaciones->count() > 0)
                                            @foreach($expediente->derivaciones->sortByDesc('created_at') as $derivacion)
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-{{ $loop->first ? 'primary' : 'secondary' }}"></div>
                                                <div class="timeline-content">
                                                    <div class="d-flex justify-content-between">
                                                        <h6 class="mb-1">Derivado a {{ $derivacion->area->nombre ?? 'Área' }}</h6>
                                                        <small class="text-muted">{{ $derivacion->created_at->format('d/m/Y H:i') }}</small>
                                                    </div>
                                                    @if($derivacion->observaciones)
                                                        <p class="text-muted mb-0">{{ $derivacion->observaciones }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            @endforeach
                                        @endif
                                        
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-success"></div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between">
                                                    <h6 class="mb-1">Expediente Registrado</h6>
                                                    <small class="text-muted">{{ $expediente->created_at->format('d/m/Y H:i') }}</small>
                                                </div>
                                                <p class="text-muted mb-0">Documento ingresado al sistema</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Lateral -->
                        <div class="col-md-4">
                            <!-- Estado Visual -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5>Estado del Proceso</h5>
                                </div>
                                <div class="card-body">
                                    <div class="progress-steps">
                                        <div class="step {{ in_array($expediente->estado, ['registrado', 'clasificado', 'derivado', 'en_proceso', 'resuelto']) ? 'completed' : '' }}">
                                            <div class="step-icon">1</div>
                                            <div class="step-text">Registrado</div>
                                        </div>
                                        <div class="step {{ in_array($expediente->estado, ['clasificado', 'derivado', 'en_proceso', 'resuelto']) ? 'completed' : '' }}">
                                            <div class="step-icon">2</div>
                                            <div class="step-text">Clasificado</div>
                                        </div>
                                        <div class="step {{ in_array($expediente->estado, ['derivado', 'en_proceso', 'resuelto']) ? 'completed' : '' }}">
                                            <div class="step-icon">3</div>
                                            <div class="step-text">En Proceso</div>
                                        </div>
                                        <div class="step {{ $expediente->estado == 'resuelto' ? 'completed' : '' }}">
                                            <div class="step-icon">4</div>
                                            <div class="step-text">Resuelto</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información Adicional -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Información</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Consulta Pública</strong><br>
                                        Esta es una consulta pública del estado de su expediente. 
                                        Para mayor información, acérquese a nuestras oficinas.
                                    </div>
                                    
                                    @if($expediente->estado == 'resuelto')
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle"></i>
                                            Su trámite ha sido <strong>resuelto</strong>. 
                                            Puede acercarse a recoger su respuesta.
                                        </div>
                                    @elseif($expediente->estado == 'observado')
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Su trámite tiene <strong>observaciones</strong>. 
                                            Debe subsanar los requisitos faltantes.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('seguimiento.form') }}" class="btn btn-primary">
                            <i class="fas fa-search"></i> Nueva Consulta
                        </a>
                        <button onclick="window.print()" class="btn btn-outline-secondary">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
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

@media print {
    .btn, .navbar { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>
@endsection