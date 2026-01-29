@extends('layouts.app')

@section('title', 'Seguimiento de Expedientes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-search-location me-2"></i>Seguimiento de Expedientes
                    </h4>
                    <span class="badge bg-primary">{{ $rol }}</span>
                </div>

                <div class="card-body">
                    <!-- Filtros -->
                    <form method="GET" action="{{ route('panel.seguimiento.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" name="buscar" class="form-control"
                                       placeholder="Codigo, asunto, DNI..."
                                       value="{{ request('buscar') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach($estados as $key => $label)
                                        <option value="{{ $key }}" {{ request('estado') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if(in_array($rol, ['Administrador', 'Mesa de Partes']))
                            <div class="col-md-2">
                                <label class="form-label">Area</label>
                                <select name="area" class="form-select">
                                    <option value="">Todas</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id_area }}" {{ request('area') == $area->id_area ? 'selected' : '' }}>
                                            {{ $area->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-md-2">
                                <label class="form-label">Desde</label>
                                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Hasta</label>
                                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Tabla de Expedientes -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Codigo</th>
                                    <th>Solicitante</th>
                                    <th>Asunto</th>
                                    <th>Area</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expedientes as $expediente)
                                @php
                                    $estadoColor = match($expediente->estado) {
                                        'recepcionado' => 'secondary',
                                        'registrado' => 'secondary',
                                        'clasificado' => 'info',
                                        'derivado' => 'primary',
                                        'en_proceso' => 'warning',
                                        'observado' => 'danger',
                                        'resuelto' => 'success',
                                        'notificado' => 'success',
                                        'archivado' => 'dark',
                                        default => 'secondary'
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $expediente->codigo_expediente }}</strong>
                                        @if($expediente->prioridad == 'urgente')
                                            <span class="badge bg-danger ms-1">Urgente</span>
                                        @elseif($expediente->prioridad == 'alta')
                                            <span class="badge bg-warning ms-1">Alta</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($rol !== 'Ciudadano')
                                            @if($expediente->persona)
                                                {{ $expediente->persona->nombre_completo }}
                                                <br><small class="text-muted">{{ $expediente->persona->numero_documento }}</small>
                                            @else
                                                {{ $expediente->remitente ?? 'N/A' }}
                                            @endif
                                        @else
                                            {{ $expediente->persona?->nombre_completo ?? 'Mi expediente' }}
                                        @endif
                                    </td>
                                    <td>
                                        <span title="{{ $expediente->asunto }}">
                                            {{ Str::limit($expediente->asunto, 50) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $expediente->tipoTramite->nombre ?? 'Sin tipo' }}
                                        </small>
                                    </td>
                                    <td>
                                        {{ $expediente->area->nombre ?? 'Sin asignar' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $estadoColor }}">
                                            {{ $estados[$expediente->estado] ?? ucfirst($expediente->estado) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $expediente->created_at->format('d/m/Y') }}
                                        <br>
                                        <small class="text-muted">{{ $expediente->created_at->format('H:i') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('panel.seguimiento.show', $expediente->codigo_expediente) }}"
                                           class="btn btn-sm btn-info" title="Ver detalle">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No se encontraron expedientes</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginacion -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            Mostrando {{ $expedientes->firstItem() ?? 0 }} - {{ $expedientes->lastItem() ?? 0 }}
                            de {{ $expedientes->total() }} expedientes
                        </small>
                        {{ $expedientes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
