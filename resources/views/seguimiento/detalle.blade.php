@extends('layouts.app')

@section('title', 'Detalle de Expediente')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>Expediente {{ $expediente->codigo_expediente }}
                    </h4>
                    <a href="{{ route('panel.seguimiento.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <div class="card-body">
                    <!-- Estado Actual -->
                    @php
                        $estadoColor = match($expediente->estado) {
                            'recepcionado', 'registrado' => 'secondary',
                            'clasificado' => 'info',
                            'derivado' => 'primary',
                            'en_proceso' => 'warning',
                            'observado' => 'danger',
                            'resuelto', 'notificado' => 'success',
                            'archivado' => 'dark',
                            default => 'secondary'
                        };
                    @endphp
                    <div class="alert alert-{{ $estadoColor }} mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Estado: {{ \App\Models\Expediente::ESTADOS[$expediente->estado] ?? ucfirst($expediente->estado) }}
                                </h5>
                                <p class="mb-0">
                                    @if($expediente->area)
                                        Area responsable: <strong>{{ $expediente->area->nombre }}</strong>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                @if($expediente->prioridad == 'urgente')
                                    <span class="badge bg-danger fs-6">URGENTE</span>
                                @elseif($expediente->prioridad == 'alta')
                                    <span class="badge bg-warning fs-6">Prioridad Alta</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Informacion del Expediente -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Informacion del Expediente</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td width="40%"><strong>Codigo:</strong></td>
                                            <td>{{ $expediente->codigo_expediente }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tipo de Tramite:</strong></td>
                                            <td>{{ $expediente->tipoTramite->nombre ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Fecha Registro:</strong></td>
                                            <td>{{ $expediente->fecha_registro?->format('d/m/Y H:i') ?? $expediente->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Canal:</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $expediente->canal == 'virtual' ? 'info' : 'secondary' }}">
                                                    {{ ucfirst($expediente->canal) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Folios:</strong></td>
                                            <td>{{ $expediente->folios ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Informacion del Solicitante -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Solicitante</h6>
                                </div>
                                <div class="card-body">
                                    @if($esCiudadano)
                                        <!-- Vista limitada para ciudadano -->
                                        <table class="table table-sm mb-0">
                                            @if($expediente->persona)
                                            <tr>
                                                <td width="40%"><strong>Documento:</strong></td>
                                                <td>{{ $expediente->persona->tipo_documento }} - {{ $expediente->persona->numero_documento }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Nombre:</strong></td>
                                                <td>{{ $expediente->persona->nombre_completo }}</td>
                                            </tr>
                                            @else
                                            <tr>
                                                <td><strong>Remitente:</strong></td>
                                                <td>{{ $expediente->remitente ?? 'N/A' }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    @else
                                        <!-- Vista completa para funcionarios -->
                                        <table class="table table-sm mb-0">
                                            @if($expediente->persona)
                                            <tr>
                                                <td width="40%"><strong>Tipo:</strong></td>
                                                <td>{{ $expediente->persona->tipo_persona }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Documento:</strong></td>
                                                <td>{{ $expediente->persona->tipo_documento }} - {{ $expediente->persona->numero_documento }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Nombre:</strong></td>
                                                <td>{{ $expediente->persona->nombre_completo }}</td>
                                            </tr>
                                            @if($expediente->persona->telefono)
                                            <tr>
                                                <td><strong>Telefono:</strong></td>
                                                <td>{{ $expediente->persona->telefono }}</td>
                                            </tr>
                                            @endif
                                            @if($expediente->persona->email)
                                            <tr>
                                                <td><strong>Email:</strong></td>
                                                <td>{{ $expediente->persona->email }}</td>
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
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Asunto -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>Asunto</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $expediente->asunto }}</p>
                        </div>
                    </div>

                    <!-- Historial de Movimientos -->
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <h6 class="mb-0 text-white">
                                <i class="fas fa-history me-2 text-white"></i>Historial de Movimientos
                                <span class="badge bg-light text-dark ms-2">{{ $historial->count() }} registros</span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th style="width: 15%">Fecha/Hora</th>
                                            @if(!$esCiudadano)
                                            <th style="width: 15%">Funcionario</th>
                                            @endif
                                            <th style="width: 15%">Area</th>
                                            <th style="width: 15%">Accion</th>
                                            <th style="width: 12%">Estado</th>
                                            <th>Detalle</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($historial as $item)
                                        @php
                                            $estadoColor = match($item->estado) {
                                                'recepcionado', 'registrado' => 'secondary',
                                                'clasificado' => 'info',
                                                'derivado' => 'primary',
                                                'en_proceso' => 'warning',
                                                'resuelto' => 'success',
                                                'observado' => 'danger',
                                                'archivado' => 'dark',
                                                default => 'secondary'
                                            };
                                            $accionColor = match($item->accion) {
                                                'REGISTRO' => 'secondary',
                                                'CLASIFICACION' => 'info',
                                                'DERIVACION' => 'primary',
                                                'RECEPCION' => 'success',
                                                'EN_PROCESO' => 'warning',
                                                'RESOLUCION' => 'success',
                                                'APROBACION' => 'success',
                                                'RECHAZO' => 'danger',
                                                'OBSERVACION' => 'warning',
                                                'ARCHIVO' => 'dark',
                                                'ASIGNACION' => 'info',
                                                'ADJUNTO' => 'secondary',
                                                'CAMBIO_ESTADO' => 'warning',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $item->fecha?->format('d/m/Y') }}</strong><br>
                                                <small class="text-muted">{{ $item->fecha?->format('H:i') }}</small>
                                            </td>
                                            @if(!$esCiudadano)
                                            <td>
                                                <i class="fas fa-user-tie text-muted me-1"></i>
                                                {{ $item->usuario?->name ?? 'Sistema' }}
                                            </td>
                                            @endif
                                            <td>
                                                <i class="fas fa-building text-muted me-1"></i>
                                                {{ $item->area?->nombre ?? 'N/A' }}
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $accionColor }}">
                                                    {{ $item->accion_legible }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($item->estado)
                                                <span class="badge bg-{{ $estadoColor }}">
                                                    {{ strtoupper(str_replace('_', ' ', $item->estado)) }}
                                                </span>
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($esCiudadano)
                                                    {{ $item->descripcion_publica }}
                                                @else
                                                    {{ $item->detalle ?? $item->descripcion }}
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="{{ $esCiudadano ? 5 : 6 }}" class="text-center py-4">
                                                <i class="fas fa-history fa-2x text-muted mb-2"></i>
                                                <p class="text-muted mb-0">No hay movimientos registrados</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Documentos (solo si no es ciudadano o tiene documentos publicos) -->
                    @if(!$esCiudadano && $expediente->documentos->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-paperclip me-2"></i>Documentos Adjuntos</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($expediente->documentos as $documento)
                                <div class="col-md-3 mb-3">
                                    <div class="card border">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                                            <p class="mb-1 small">{{ Str::limit($documento->nombre, 25) }}</p>
                                            <span class="badge bg-secondary mb-2">{{ $documento->tipo }}</span>
                                            <br>
                                            <a href="{{ route('documentos.visualizar', $documento->id_documento) }}"
                                               target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Resolucion (si existe) -->
                    @if($expediente->resolucion)
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-gavel me-2"></i>Resolucion</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Numero:</strong> {{ $expediente->resolucion->numero_resolucion }}</p>
                                    <p><strong>Fecha:</strong> {{ $expediente->resolucion->fecha_emision?->format('d/m/Y') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Tipo:</strong> {{ ucfirst($expediente->resolucion->tipo) }}</p>
                                    @if(!$esCiudadano && $expediente->resolucion->ruta_documento)
                                    <a href="{{ route('resoluciones.descargar', $expediente->resolucion->id_resolucion) }}"
                                       class="btn btn-success">
                                        <i class="fas fa-download me-1"></i> Descargar Resolucion
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Informacion para el ciudadano -->
                    @if($esCiudadano)
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Informacion Importante</h6>
                        <ul class="mb-0">
                            <li>Conserve el codigo <strong>{{ $expediente->codigo_expediente }}</strong> para futuras consultas</li>
                            <li>Recibira notificaciones sobre cambios en su expediente</li>
                            @if($expediente->estado == 'resuelto')
                            <li><strong>Su tramite ha sido resuelto.</strong></li>
                            @endif
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
