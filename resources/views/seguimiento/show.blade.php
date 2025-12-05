@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('Mi Expediente') }} {{ $expediente->codigo_expediente }}</h4>
                    <div>
                        <a href="{{ route('seguimiento.index') }}" class="btn btn-secondary">Volver</a>
                        <a href="{{ route('seguimiento.consulta', $expediente->codigo_expediente) }}" class="btn btn-info" target="_blank">
                            Ver Seguimiento Público
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Estado Actual -->
                    <div class="alert alert-{{ 
                        $expediente->estado == 'pendiente' ? 'warning' : 
                        ($expediente->estado == 'en_proceso' ? 'info' : 
                        ($expediente->estado == 'resuelto' ? 'success' : 'secondary')) 
                    }}">
                        <div class="row">
                            <div class="col-md-8">
                                <h5>
                                    <i class="fas fa-{{ 
                                        $expediente->estado == 'pendiente' ? 'clock' : 
                                        ($expediente->estado == 'en_proceso' ? 'cogs' : 
                                        ($expediente->estado == 'resuelto' ? 'check-circle' : 'archive')) 
                                    }}"></i>
                                    Estado: {{ ucfirst(str_replace('_', ' ', $expediente->estado)) }}
                                </h5>
                                <p class="mb-0">
                                    @switch($expediente->estado)
                                        @case('pendiente')
                                            Su expediente está siendo revisado por Mesa de Partes
                                            @break
                                        @case('derivado')
                                            Su expediente ha sido enviado al área correspondiente
                                            @break
                                        @case('en_proceso')
                                            Su expediente está siendo atendido por un funcionario
                                            @break
                                        @case('resuelto')
                                            Su expediente ha sido resuelto. Puede recoger la respuesta.
                                            @break
                                        @case('archivado')
                                            Su expediente ha sido archivado. El trámite está completo.
                                            @break
                                    @endswitch
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                @if($expediente->derivacionActual())
                                    <strong>Área Actual:</strong><br>
                                    {{ $expediente->derivacionActual()->area->nombre }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Información del Expediente -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Información del Solicitante</h6>
                            <table class="table table-sm">
                                @if($expediente->persona)
                                <tr>
                                    <td><strong>Documento:</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $expediente->persona->tipo_documento }}</span>
                                        {{ $expediente->persona->numero_documento }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Nombre:</strong></td>
                                    <td>{{ $expediente->persona->nombre_completo }}</td>
                                </tr>
                                @if($expediente->persona->tipo_persona == 'JURIDICA')
                                <tr>
                                    <td><strong>Representante:</strong></td>
                                    <td>{{ $expediente->persona->representante_legal }}</td>
                                </tr>
                                @endif
                                @if($expediente->persona->telefono)
                                <tr>
                                    <td><strong>Teléfono:</strong></td>
                                    <td>{{ $expediente->persona->telefono }}</td>
                                </tr>
                                @endif
                                @else
                                <tr>
                                    <td><strong>Remitente:</strong></td>
                                    <td>{{ $expediente->remitente ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>DNI:</strong></td>
                                    <td>{{ $expediente->dni_remitente ?? 'N/A' }}</td>
                                </tr>
                                @endif
                            </table>
                            
                            <h6 class="mt-3">Información del Trámite</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Código:</strong></td>
                                    <td><strong>{{ $expediente->codigo_expediente }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha Registro:</strong></td>
                                    <td>{{ $expediente->fecha_registro->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tipo Trámite:</strong></td>
                                    <td><span class="badge bg-info">{{ $expediente->tipoTramite->nombre }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Canal:</strong></td>
                                    <td>{{ ucfirst($expediente->canal) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Prioridad:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $expediente->prioridad == 'urgente' ? 'danger' : ($expediente->prioridad == 'alta' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($expediente->prioridad) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            @if($expediente->derivacionActual())
                            <h6>Información de Atención</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Área Responsable:</strong></td>
                                    <td>{{ $expediente->derivacionActual()->area->nombre }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha Derivación:</strong></td>
                                    <td>{{ $expediente->derivacionActual()->fecha_derivacion->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Plazo de Atención:</strong></td>
                                    <td>{{ $expediente->derivacionActual()->plazo_dias }} días hábiles</td>
                                </tr>
                                @php
                                    $fechaLimite = $expediente->derivacionActual()->fecha_derivacion->addDays($expediente->derivacionActual()->plazo_dias);
                                    $diasRestantes = now()->diffInDays($fechaLimite, false);
                                @endphp
                                <tr>
                                    <td><strong>Fecha Límite:</strong></td>
                                    <td>{{ $fechaLimite->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tiempo Restante:</strong></td>
                                    <td>
                                        <span class="text-{{ $diasRestantes < 0 ? 'danger' : ($diasRestantes <= 2 ? 'warning' : 'success') }}">
                                            {{ $diasRestantes < 0 ? 'Vencido (' . abs($diasRestantes) . ' días)' : $diasRestantes . ' días' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                            @endif
                        </div>
                    </div>

                    <!-- Asunto -->
                    <div class="mb-4">
                        <h6>Asunto del Trámite</h6>
                        <div class="alert alert-light">
                            {{ $expediente->asunto }}
                        </div>
                    </div>

                    <!-- Documentos -->
                    @if($expediente->documentos->count() > 0)
                    <div class="mb-4">
                        <h6>Documentos del Expediente</h6>
                        <div class="row">
                            @foreach($expediente->documentos as $documento)
                            <div class="col-md-4 mb-2">
                                <div class="card {{ $documento->tipo == 'respuesta' ? 'border-success' : 'border-primary' }}">
                                    <div class="card-body text-center">
                                        <i class="fas fa-file-pdf fa-2x {{ $documento->tipo == 'respuesta' ? 'text-success' : 'text-danger' }} mb-2"></i>
                                        <h6 class="card-title">{{ $documento->nombre }}</h6>
                                        <p class="card-text">
                                            <span class="badge bg-{{ $documento->tipo == 'entrada' ? 'primary' : ($documento->tipo == 'informe' ? 'warning' : 'success') }}">
                                                {{ ucfirst($documento->tipo) }}
                                            </span>
                                        </p>
                                        <a href="{{ Storage::url($documento->ruta_pdf) }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-download"></i> Descargar
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Observaciones -->
                    @if($expediente->observaciones->count() > 0)
                    <div class="mb-4">
                        <h6>Observaciones y Comunicaciones</h6>
                        @foreach($expediente->observaciones as $observacion)
                        <div class="alert alert-{{ $observacion->tipo == 'observacion' ? 'warning' : 'info' }}">
                            <h6><i class="fas fa-exclamation-triangle"></i> {{ ucfirst($observacion->tipo) }}</h6>
                            <p>{{ $observacion->descripcion }}</p>
                            @if($observacion->fecha_limite)
                            <small><strong>Fecha límite para subsanar:</strong> {{ $observacion->fecha_limite }}</small>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <!-- Historial -->
                    <div class="mb-4">
                        <h6>Historial de Movimientos</h6>
                        <div class="timeline">
                            @forelse($expediente->historial->sortByDesc('fecha') as $historial)
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle p-2 text-white text-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-history"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">{{ $historial->descripcion }}</h6>
                                    <small class="text-muted">
                                        {{ $historial->fecha->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                            @empty
                            <p class="text-muted">No hay movimientos registrados.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Información Importante</h6>
                        <ul class="mb-0">
                            <li>Conserve el código <strong>{{ $expediente->codigo_expediente }}</strong> para futuras consultas</li>
                            <li>Recibirá notificaciones por email sobre cambios en su expediente</li>
                            @if($expediente->estado == 'resuelto')
                            <li><strong>Su trámite ha sido resuelto.</strong> Puede descargar los documentos de respuesta o acercarse a nuestras oficinas.</li>
                            @endif
                            <li>Para consultas adicionales, puede acercarse a nuestras oficinas de atención</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection