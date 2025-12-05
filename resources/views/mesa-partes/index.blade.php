@extends('layouts.app')

@section('title', 'Mesa de Partes - Expedientes')

@section('styles')
<style>
/* Botones de acción compactos */
.btn-group .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

/* Hover effects */
.btn-outline-primary:hover {
    background-color: #0d6efd;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3);
}

.btn-outline-success:hover {
    background-color: #198754;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(25, 135, 84, 0.3);
}

.btn-outline-warning:hover {
    background-color: #ffc107;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
}

.btn-outline-info:hover {
    background-color: #0dcaf0;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(13, 202, 240, 0.3);
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
}

/* Transiciones suaves */
.btn {
    transition: all 0.2s ease-in-out;
}

/* Tooltips personalizados */
.tooltip-inner {
    background-color: #333;
    color: #fff;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
</style>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Mesa de Partes - Gestión de Expedientes</h2>
                <div>
                    <a href="{{ route('mesa-partes.registrar') }}" class="btn btn-success me-2">
                        <i class="fas fa-plus"></i> Registrar Documento
                    </a>
                    <a href="{{ route('mesa-partes.monitoreo') }}" class="btn btn-warning">
                        <i class="fas fa-clock"></i> Monitoreo
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Documento</th>
                                    <th>Solicitante</th>
                                    <th>Asunto</th>
                                    <th>Tipo Trámite</th>
                                    <th>Estado</th>
                                    <th>Área Actual</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expedientes as $expediente)
                                <tr>
                                    <td><strong>{{ $expediente->codigo_expediente }}</strong></td>
                                    <td>
                                        @if($expediente->persona)
                                            <span class="badge bg-secondary">{{ $expediente->persona->tipo_documento }}</span>
                                            {{ $expediente->persona->numero_documento }}
                                        @else
                                            {{ $expediente->dni_remitente ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($expediente->persona)
                                            {{ $expediente->persona->nombre_completo }}
                                            @if($expediente->persona->tipo_persona == 'JURIDICA')
                                                <br><small class="text-muted">{{ $expediente->persona->representante_legal }}</small>
                                            @endif
                                        @else
                                            {{ $expediente->remitente ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($expediente->asunto, 40) }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $expediente->tipoTramite->nombre ?? 'Sin clasificar' }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="badge bg-{{ $expediente->getColorEstadoInteligente() }} mb-1">
                                                {{ $expediente->getEstadoFormateadoInteligente() }}
                                            </span>
                                            
                                            @php
                                                $estadoInteligente = $expediente->estado_inteligente;
                                                $progreso = match($estadoInteligente) {
                                                    'pendiente' => 10,
                                                    'clasificado' => 30,
                                                    'asignado', 'por_recibir' => 50,
                                                    'derivado' => 60,
                                                    'en_proceso' => 80,
                                                    'resuelto' => 100,
                                                    'aprobado' => 100,
                                                    'archivado' => 100,
                                                    default => 0
                                                };
                                            @endphp
                                            
                                            <!-- Barra de progreso -->
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar bg-{{ $expediente->getColorEstadoInteligente() }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $progreso }}%" 
                                                     aria-valuenow="{{ $progreso }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            
                                            @if($estadoInteligente === 'por_recibir')
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> Esperando recepción
                                                </small>
                                            @elseif($estadoInteligente === 'clasificado')
                                                <small class="text-muted">
                                                    <i class="fas fa-arrow-right"></i> Listo para derivar
                                                </small>
                                            @elseif($estadoInteligente === 'asignado')
                                                <small class="text-muted">
                                                    <i class="fas fa-user"></i> {{ Str::limit($expediente->funcionarioAsignado->name ?? 'Asignado', 15) }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($expediente->area)
                                            <small>{{ $expediente->area->nombre }}</small>
                                        @else
                                            <span class="text-muted">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td>{{ $expediente->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- Ver Expediente -->
                                            <a href="{{ route('mesa-partes.show', $expediente) }}" 
                                               class="btn btn-outline-primary btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               title="Ver detalles del expediente">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <!-- Descargar Cargo -->
                                            <a href="{{ route('mesa-partes.cargo-recepcion', $expediente) }}" 
                                               class="btn btn-outline-success btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               title="Descargar cargo de recepción">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            
                                            @php
                                                $estadoInteligente = $expediente->estado_inteligente;
                                            @endphp
                                            
                                            @if($estadoInteligente === 'pendiente')
                                            <!-- Clasificar -->
                                            <a href="{{ route('mesa-partes.clasificar', $expediente) }}" 
                                               class="btn btn-outline-warning btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               title="Clasificar expediente">
                                                <i class="fas fa-tags"></i>
                                            </a>
                                            @endif
                                            
                                            @if($estadoInteligente === 'clasificado')
                                            <!-- Derivar/Asignar -->
                                            <a href="{{ route('mesa-partes.derivar', $expediente) }}" 
                                               class="btn btn-outline-info btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               title="Derivar a funcionario">
                                                <i class="fas fa-share"></i>
                                            </a>
                                            @endif
                                            
                                            @if($estadoInteligente === 'asignado' || $estadoInteligente === 'por_recibir')
                                            <!-- Reasignar -->
                                            <a href="{{ route('mesa-partes.derivar', $expediente) }}" 
                                               class="btn btn-outline-secondary btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               title="Reasignar funcionario">
                                                <i class="fas fa-exchange-alt"></i>
                                            </a>
                                            @endif
                                            
                                            @if(in_array($expediente->estado, ['resuelto']))
                                            <!-- Archivar -->
                                            <button type="button" 
                                                    class="btn btn-outline-secondary btn-sm" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Archivar expediente"
                                                    onclick="archivarExpediente({{ $expediente->id }})">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No hay expedientes registrados</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $expedientes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Función para archivar expediente
function archivarExpediente(expedienteId) {
    if (confirm('¿Está seguro de archivar este expediente?')) {
        // Crear formulario dinámico
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/mesa-partes/expedientes/${expedienteId}/archivar`;
        
        // Token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
        
        // Método PUT
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PUT';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection