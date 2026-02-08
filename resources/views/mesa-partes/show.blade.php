@extends('layouts.app')

@section('content')
@php
use Illuminate\Support\Facades\Storage;
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-alt text-primary me-1"></i>Expediente {{ $expediente->codigo_expediente }}</h5>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('mesa-partes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                        @if($expediente->estado == 'pendiente')
                            <a href="{{ route('mesa-partes.clasificar', $expediente) }}" class="btn btn-warning">
                                <i class="fas fa-tags me-1"></i>Clasificar
                            </a>
                        @endif
                        @if(in_array($expediente->estado, ['pendiente', 'derivado']))
                            <a href="{{ route('mesa-partes.derivar', $expediente) }}" class="btn btn-primary">
                                <i class="fas fa-share me-1"></i>Derivar
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card-body py-2">
                    <!-- Informacion en dos columnas compactas -->
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Informacion del Expediente</h6>
                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.82rem;">
                                <tr><td class="text-muted" style="width: 130px;">Codigo:</td><td><strong class="text-primary">{{ $expediente->codigo_expediente }}</strong></td></tr>
                                <tr><td class="text-muted">Fecha Registro:</td><td>{{ $expediente->created_at->format('d/m/Y H:i') }}</td></tr>
                                <tr><td class="text-muted">Tipo Tramite:</td><td>{{ $expediente->tipoTramite->nombre ?? 'Sin clasificar' }}</td></tr>
                                <tr><td class="text-muted">Canal:</td><td>{{ ucfirst($expediente->canal) }}</td></tr>
                                <tr>
                                    <td class="text-muted">Estado:</td>
                                    <td>
                                        <span class="badge bg-{{ $expediente->estado == 'pendiente' ? 'warning' : ($expediente->estado == 'derivado' ? 'info' : 'success') }}">
                                            {{ ucfirst(str_replace('_', ' ', $expediente->estado)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Prioridad:</td>
                                    <td>
                                        <span class="badge bg-{{ $expediente->prioridad == 'urgente' ? 'danger' : ($expediente->prioridad == 'alta' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($expediente->prioridad) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Datos del Remitente</h6>
                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.82rem;">
                                <tr><td class="text-muted" style="width: 130px;">Nombre:</td><td>{{ $expediente->remitente ?? ($expediente->ciudadano->name ?? 'N/A') }}</td></tr>
                                <tr><td class="text-muted">DNI:</td><td>{{ $expediente->dni_remitente ?? ($expediente->ciudadano->dni ?? 'N/A') }}</td></tr>
                                @if($expediente->ciudadano)
                                <tr><td class="text-muted">Email:</td><td>{{ $expediente->ciudadano->email }}</td></tr>
                                @endif
                            </table>

                            @if($expediente->area)
                            <h6 class="text-muted mb-1 mt-2" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Area Asignada</h6>
                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.82rem;">
                                <tr><td class="text-muted" style="width: 130px;">Area:</td><td>{{ $expediente->area->nombre }}</td></tr>
                                @if($expediente->funcionarioAsignado)
                                <tr><td class="text-muted">Funcionario:</td><td>{{ $expediente->funcionarioAsignado->name }}</td></tr>
                                @endif
                            </table>
                            @endif
                        </div>
                    </div>

                    <!-- Asunto -->
                    <div class="mb-2">
                        <h6 class="text-muted mb-1" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Asunto del Tramite</h6>
                        <div class="bg-light rounded p-2" style="font-size: 0.85rem;">
                            {{ $expediente->asunto }}
                        </div>
                        @if($expediente->observaciones)
                        <h6 class="text-muted mb-1 mt-2" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Observaciones</h6>
                        <div class="bg-info bg-opacity-10 rounded p-2" style="font-size: 0.85rem;">
                            {{ $expediente->observaciones }}
                        </div>
                        @endif
                    </div>

                    <!-- Documentos -->
                    @if($expediente->documentos->count() > 0)
                    <div class="mb-2">
                        <h6 class="text-muted mb-1" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Documentos Adjuntos</h6>
                        <div class="row g-2">
                            @foreach($expediente->documentos as $documento)
                            <div class="col-md-3">
                                <div class="card border-0 bg-light">
                                    <div class="card-body text-center p-2">
                                        <i class="fas fa-file-pdf text-danger mb-1"></i>
                                        <div style="font-size: 0.78rem; font-weight: 500;">{{ Str::limit($documento->nombre, 20) }}</div>
                                        <small class="text-muted">{{ ucfirst($documento->tipo) }}</small>
                                        <a href="{{ Storage::url($documento->ruta_pdf) }}" target="_blank" class="btn btn-sm btn-primary mt-1 py-0 px-2" style="font-size: 0.72rem;">
                                            <i class="fas fa-eye me-1"></i>Ver
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Historial -->
                    <div class="mb-2">
                        <h6 class="text-muted mb-1" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Historial</h6>
                        @forelse($expediente->historial->sortByDesc('fecha') as $historial)
                        <div class="d-flex mb-1 align-items-start">
                            <div class="bg-primary rounded-circle text-white text-center me-2 flex-shrink-0" style="width: 28px; height: 28px; line-height: 28px; font-size: 0.7rem;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.82rem; font-weight: 500;">{{ $historial->descripcion }}</div>
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    {{ $historial->created_at->format('d/m/Y H:i') }} -
                                    {{ $historial->usuario->name ?? 'Sistema' }} ({{ $historial->usuario->role->nombre ?? 'Sistema' }})
                                </small>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">No hay historial registrado.</p>
                        @endforelse
                    </div>

                    <!-- Acciones -->
                    <div class="bg-light rounded p-2">
                        <h6 class="mb-1" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Acciones Disponibles</h6>
                        <div class="d-flex flex-wrap gap-1">
                            <a href="{{ route('mesa-partes.cargo', $expediente) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-download me-1"></i>Cargo
                            </a>
                            @if($expediente->estado == 'pendiente')
                                <a href="{{ route('mesa-partes.clasificar', $expediente) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-tags me-1"></i>Clasificar
                                </a>
                            @endif
                            @if(in_array($expediente->estado, ['pendiente', 'derivado']))
                                <a href="{{ route('mesa-partes.derivar', $expediente) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-share me-1"></i>Derivar
                                </a>
                            @endif
                            <a href="{{ route('seguimiento.consulta', $expediente->codigo_expediente) }}" class="btn btn-info btn-sm" target="_blank">
                                <i class="fas fa-search me-1"></i>Seguimiento
                            </a>
                            @if(!in_array($expediente->estado, ['archivado']))
                                <a href="{{ route('mesa-partes.rectificar-datos', $expediente) }}" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-edit me-1"></i>Rectificar
                                </a>
                            @endif
                            @if($expediente->estado == 'derivado' && $expediente->derivaciones->where('estado', 'pendiente')->count() > 0)
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalAnularDerivacion">
                                    <i class="fas fa-undo me-1"></i>Anular Derivacion
                                </button>
                            @endif
                            @if($expediente->estado == 'resuelto')
                                <form method="POST" action="{{ route('mesa-partes.archivar', $expediente) }}" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Archivar expediente?')">
                                        <i class="fas fa-archive me-1"></i>Archivar
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

{{-- Modal Anular Derivacion --}}
@if($expediente->estado == 'derivado' && $expediente->derivaciones->where('estado', 'pendiente')->count() > 0)
<div class="modal fade" id="modalAnularDerivacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Anular Derivacion</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('mesa-partes.anular-derivacion', $expediente) }}">
                @csrf
                <div class="modal-body py-2">
                    <div class="alert alert-warning py-2" style="font-size: 0.82rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Atencion:</strong> Se anulara la derivacion actual y el expediente quedara disponible para derivar nuevamente.
                    </div>

                    @php
                        $derivacionPendiente = $expediente->derivaciones->where('estado', 'pendiente')->sortByDesc('id_derivacion')->first();
                    @endphp

                    @if($derivacionPendiente)
                    <div class="mb-2" style="font-size: 0.82rem;">
                        <strong>Derivacion actual:</strong>
                        <ul class="mb-0 mt-1">
                            <li>Area: <strong>{{ $derivacionPendiente->areaDestino->nombre ?? 'N/A' }}</strong></li>
                            <li>Fecha: {{ $derivacionPendiente->fecha_derivacion?->format('d/m/Y H:i') }}</li>
                            <li>Estado: <span class="badge bg-warning">Pendiente</span></li>
                        </ul>
                    </div>
                    @endif

                    <div class="mb-2">
                        <label for="motivo_anulacion" class="form-label fw-bold" style="font-size: 0.82rem;">Motivo de anulacion *</label>
                        <textarea class="form-control form-control-sm" id="motivo_anulacion" name="motivo_anulacion"
                                  rows="2" required minlength="10"
                                  placeholder="Ej: Se derivo al area equivocada..."></textarea>
                        <div class="form-text" style="font-size: 0.72rem;">Minimo 10 caracteres</div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-undo me-1"></i>Confirmar Anulacion
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
