@extends('layouts.app')

@section('title', 'Reporte por Remitente')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-user"></i> Reporte por Remitente</h2>
                <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Panel de Busqueda -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-search"></i> Buscar Remitente</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reportes.por-remitente') }}">
                        <div class="mb-3">
                            <label class="form-label">Nombre o Razon Social</label>
                            <input type="text" name="busqueda" class="form-control"
                                   placeholder="Buscar por nombre..." value="{{ $busqueda }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de Documento</label>
                            <select name="tipo_documento" class="form-select">
                                <option value="">Todos</option>
                                <option value="DNI" {{ $tipoDocumento == 'DNI' ? 'selected' : '' }}>DNI</option>
                                <option value="RUC" {{ $tipoDocumento == 'RUC' ? 'selected' : '' }}>RUC</option>
                                <option value="CE" {{ $tipoDocumento == 'CE' ? 'selected' : '' }}>Carnet Extranjeria</option>
                                <option value="PASAPORTE" {{ $tipoDocumento == 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                                <option value="OTROS" {{ $tipoDocumento == 'OTROS' ? 'selected' : '' }}>Otros</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Numero de Documento</label>
                            <input type="text" name="numero_documento" class="form-control"
                                   placeholder="Ej: 12345678" value="{{ $numeroDocumento }}">
                        </div>
                        <button type="submit" class="btn btn-info w-100">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Top Remitentes del Mes -->
            <div class="card">
                <div class="card-header bg-warning">
                    <h6 class="mb-0"><i class="fas fa-star"></i> Top Remitentes ({{ now()->format('F Y') }})</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($topRemitentes as $index => $persona)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-{{ $index < 3 ? 'warning' : 'secondary' }} me-2">{{ $index + 1 }}</span>
                                <small>{{ $persona->nombre_completo ?? $persona->razon_social }}</small>
                            </div>
                            <span class="badge bg-primary">{{ $persona->expedientes_count }}</span>
                        </li>
                        @empty
                        <li class="list-group-item text-muted text-center">Sin datos este mes</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Panel de Resultados -->
        <div class="col-md-8">
            @if($personas->count() > 0 && !$personaSeleccionada)
            <!-- Resultados de busqueda -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Resultados de Busqueda ({{ $personas->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Nombre / Razon Social</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Expedientes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($personas as $persona)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $persona->tipo_documento }}</span>
                                        {{ $persona->numero_documento }}
                                    </td>
                                    <td>{{ $persona->nombre_completo ?? $persona->razon_social }}</td>
                                    <td>
                                        <span class="badge bg-{{ $persona->tipo_persona == 'natural' ? 'info' : 'success' }}">
                                            {{ ucfirst($persona->tipo_persona) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $persona->expedientes_count }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('reportes.por-remitente', ['persona_id' => $persona->id_persona]) }}"
                                           class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @if($personaSeleccionada)
            <!-- Detalle de Persona Seleccionada -->
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user-circle"></i>
                            {{ $personaSeleccionada->nombre_completo ?? $personaSeleccionada->razon_social }}
                        </h5>
                        <a href="{{ route('reportes.por-remitente') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-times"></i> Cerrar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Datos de la persona -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Tipo:</th>
                                    <td>
                                        <span class="badge bg-{{ $personaSeleccionada->tipo_persona == 'natural' ? 'info' : 'success' }}">
                                            {{ ucfirst($personaSeleccionada->tipo_persona) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Documento:</th>
                                    <td>{{ $personaSeleccionada->tipo_documento }} - {{ $personaSeleccionada->numero_documento }}</td>
                                </tr>
                                @if($personaSeleccionada->email)
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $personaSeleccionada->email }}</td>
                                </tr>
                                @endif
                                @if($personaSeleccionada->telefono)
                                <tr>
                                    <th>Telefono:</th>
                                    <td>{{ $personaSeleccionada->telefono }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body py-2">
                                            <h4 class="mb-0">{{ $personaSeleccionada->expedientes_count }}</h4>
                                            <small>Total</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card bg-success text-white">
                                        <div class="card-body py-2">
                                            <h4 class="mb-0">{{ $personaSeleccionada->resueltos_count }}</h4>
                                            <small>Resueltos</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body py-2">
                                            <h4 class="mb-0">{{ $personaSeleccionada->pendientes_count }}</h4>
                                            <small>Pendientes</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expedientes de la persona -->
                    <h6><i class="fas fa-folder-open"></i> Historial de Expedientes</h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Codigo</th>
                                    <th>Fecha</th>
                                    <th>Asunto</th>
                                    <th>Tipo Tramite</th>
                                    <th>Area</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expedientesPersona as $exp)
                                <tr>
                                    <td>
                                        <a href="{{ route('mesa-partes.show', $exp) }}" class="text-decoration-none">
                                            {{ $exp->codigo_expediente }}
                                        </a>
                                    </td>
                                    <td>{{ $exp->created_at->format('d/m/Y') }}</td>
                                    <td>{{ Str::limit($exp->asunto, 35) }}</td>
                                    <td>{{ $exp->tipoTramite->nombre ?? 'N/A' }}</td>
                                    <td>{{ $exp->area->nombre ?? 'Sin asignar' }}</td>
                                    <td>
                                        @php
                                            $colorEstado = match($exp->estado) {
                                                'resuelto' => 'success',
                                                'archivado' => 'secondary',
                                                'derivado', 'en_proceso' => 'primary',
                                                default => 'warning'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $colorEstado }}">
                                            {{ ucfirst(str_replace('_', ' ', $exp->estado)) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No hay expedientes registrados</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($expedientesPersona->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $expedientesPersona->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if(!$personas->count() && !$personaSeleccionada && ($busqueda || $numeroDocumento))
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No se encontraron resultados</h5>
                    <p class="text-muted">Intente con otros criterios de busqueda</p>
                </div>
            </div>
            @endif

            @if(!$personas->count() && !$personaSeleccionada && !$busqueda && !$numeroDocumento)
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-user-search fa-4x text-info mb-3"></i>
                    <h5>Buscar Remitente</h5>
                    <p class="text-muted">Use el formulario de busqueda para encontrar expedientes por remitente</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
