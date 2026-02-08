@extends('layouts.app')

@section('content')
@php
use Illuminate\Support\Facades\Storage;
@endphp
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('Expediente') }} {{ $expediente->codigo_expediente }}</h4>
                    <div>
                        <a href="{{ route('mesa-partes.index') }}" class="btn btn-secondary">Volver</a>
                        @if($expediente->estado == 'pendiente')
                            <a href="{{ route('mesa-partes.clasificar', $expediente) }}" class="btn btn-warning">Clasificar</a>
                        @endif
                        @if(in_array($expediente->estado, ['pendiente', 'derivado']))
                            <a href="{{ route('mesa-partes.derivar', $expediente) }}" class="btn btn-primary">Derivar</a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <!-- Información del Expediente -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Información del Expediente</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Código:</strong></td>
                                    <td>{{ $expediente->codigo_expediente }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha Registro:</strong></td>
                                    <td>{{ $expediente->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tipo Trámite:</strong></td>
                                    <td>{{ $expediente->tipoTramite->nombre ?? 'Sin clasificar' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Canal:</strong></td>
                                    <td>{{ ucfirst($expediente->canal) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Estado:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $expediente->estado == 'pendiente' ? 'warning' : ($expediente->estado == 'derivado' ? 'info' : 'success') }}">
                                            {{ ucfirst(str_replace('_', ' ', $expediente->estado)) }}
                                        </span>
                                    </td>
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
                            <h6>Datos del Remitente</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Nombre:</strong></td>
                                    <td>{{ $expediente->remitente ?? ($expediente->ciudadano->name ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>DNI:</strong></td>
                                    <td>{{ $expediente->dni_remitente ?? ($expediente->ciudadano->dni ?? 'N/A') }}</td>
                                </tr>
                                @if($expediente->ciudadano)
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $expediente->ciudadano->email }}</td>
                                </tr>
                                @endif
                            </table>

                            @if($expediente->area)
                            <h6 class="mt-3">Área Asignada</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Área:</strong></td>
                                    <td>{{ $expediente->area->nombre }}</td>
                                </tr>
                                @if($expediente->funcionarioAsignado)
                                <tr>
                                    <td><strong>Funcionario:</strong></td>
                                    <td>{{ $expediente->funcionarioAsignado->name }}</td>
                                </tr>
                                @endif
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
                        @if($expediente->observaciones)
                        <h6>Observaciones Iniciales</h6>
                        <div class="alert alert-info">
                            {{ $expediente->observaciones }}
                        </div>
                        @endif
                    </div>

                    <!-- Documentos -->
                    @if($expediente->documentos->count() > 0)
                    <div class="mb-4">
                        <h6>Documentos Adjuntos</h6>
                        <div class="row">
                            @foreach($expediente->documentos as $documento)
                            <div class="col-md-4 mb-2">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                                        <h6 class="card-title">{{ $documento->nombre }}</h6>
                                        <p class="card-text">
                                            <small class="text-muted">{{ ucfirst($documento->tipo) }}</small>
                                        </p>
                                        <a href="{{ Storage::url($documento->ruta_pdf) }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Ver PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Historial -->
                    <div class="mb-4">
                        <h6>Historial del Expediente</h6>
                        <div class="timeline">
                            @forelse($expediente->historial->sortByDesc('fecha') as $historial)
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle p-2 text-white text-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">{{ $historial->descripcion }}</h6>
                                    <small class="text-muted">
                                        {{ $historial->created_at->format('d/m/Y H:i') }} - 
                                        {{ $historial->usuario->name ?? 'Sistema' }} ({{ $historial->usuario->role->nombre ?? 'Sistema' }})
                                    </small>
                                </div>
                            </div>
                            @empty
                            <p class="text-muted">No hay historial registrado.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Acciones Rápidas -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Acciones Disponibles</h6>
                            <div class="btn-group" role="group">
                                <a href="{{ route('mesa-partes.cargo', $expediente) }}" class="btn btn-success">
                                    <i class="fas fa-download"></i> Descargar Cargo
                                </a>
                                @if($expediente->estado == 'pendiente')
                                    <a href="{{ route('mesa-partes.clasificar', $expediente) }}" class="btn btn-warning">
                                        <i class="fas fa-tags"></i> Clasificar
                                    </a>
                                @endif
                                @if(in_array($expediente->estado, ['pendiente', 'derivado']))
                                    <a href="{{ route('mesa-partes.derivar', $expediente) }}" class="btn btn-primary">
                                        <i class="fas fa-share"></i> Derivar
                                    </a>
                                @endif
                                <a href="{{ route('seguimiento.consulta', $expediente->codigo_expediente) }}" class="btn btn-info" target="_blank">
                                    <i class="fas fa-search"></i> Ver Seguimiento Público
                                </a>
                                @if(!in_array($expediente->estado, ['archivado']))
                                    <a href="{{ route('mesa-partes.rectificar-datos', $expediente) }}" class="btn btn-outline-warning">
                                        <i class="fas fa-edit"></i> Rectificar Datos
                                    </a>
                                @endif
                                @if($expediente->estado == 'derivado' && $expediente->derivaciones->where('estado', 'pendiente')->count() > 0)
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalAnularDerivacion">
                                        <i class="fas fa-undo"></i> Anular Derivación
                                    </button>
                                @endif
                                @if($expediente->estado == 'resuelto')
                                    <form method="POST" action="{{ route('mesa-partes.archivar', $expediente) }}" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-secondary" onclick="return confirm('¿Archivar expediente?')">
                                            <i class="fas fa-archive"></i> Archivar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Modal Anular Derivación (Opción A) --}}
@if($expediente->estado == 'derivado' && $expediente->derivaciones->where('estado', 'pendiente')->count() > 0)
<div class="modal fade" id="modalAnularDerivacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Anular Derivación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('mesa-partes.anular-derivacion', $expediente) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Atención:</strong> Esta acción anulará la derivación actual.
                        El expediente quedará disponible para ser derivado al área correcta.
                        La derivación anulada quedará registrada en el historial.
                    </div>

                    @php
                        $derivacionPendiente = $expediente->derivaciones->where('estado', 'pendiente')->sortByDesc('id_derivacion')->first();
                    @endphp

                    @if($derivacionPendiente)
                    <div class="mb-3">
                        <strong>Derivación actual:</strong>
                        <ul class="mb-0 mt-1">
                            <li>Área destino: <strong>{{ $derivacionPendiente->areaDestino->nombre ?? 'N/A' }}</strong></li>
                            <li>Fecha: {{ $derivacionPendiente->fecha_derivacion?->format('d/m/Y H:i') }}</li>
                            <li>Estado: <span class="badge bg-warning">Pendiente (no recepcionada)</span></li>
                        </ul>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label for="motivo_anulacion" class="form-label fw-bold">Motivo de anulación *</label>
                        <textarea class="form-control" id="motivo_anulacion" name="motivo_anulacion"
                                  rows="3" required minlength="10"
                                  placeholder="Ej: Se derivó al área equivocada. El trámite corresponde a la Dirección de..."></textarea>
                        <div class="form-text">Mínimo 10 caracteres. Este motivo quedará registrado en el historial.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-undo me-1"></i> Confirmar Anulación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection