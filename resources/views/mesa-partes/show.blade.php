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
@endsection