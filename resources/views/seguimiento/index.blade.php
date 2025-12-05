@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('Mis Expedientes') }}</h4>
                    <a href="{{ route('ciudadano.registrar-expediente') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Expediente
                    </a>
                </div>

                <div class="card-body">
                    <!-- Estadísticas Personales -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-info text-white text-center">
                                <div class="card-body">
                                    <h4>{{ $expedientes->total() }}</h4>
                                    <p>Total Expedientes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white text-center">
                                <div class="card-body">
                                    <h4>{{ $expedientes->where('estado', 'en_proceso')->count() }}</h4>
                                    <p>En Proceso</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white text-center">
                                <div class="card-body">
                                    <h4>{{ $expedientes->where('estado', 'resuelto')->count() }}</h4>
                                    <p>Resueltos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white text-center">
                                <div class="card-body">
                                    <h4>{{ $expedientes->where('estado', 'archivado')->count() }}</h4>
                                    <p>Archivados</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Expedientes -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Fecha Registro</th>
                                    <th>Tipo Trámite</th>
                                    <th>Asunto</th>
                                    <th>Estado</th>
                                    <th>Área Actual</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expedientes as $expediente)
                                <tr>
                                    <td>
                                        <strong>{{ $expediente->codigo_expediente }}</strong>
                                    </td>
                                    <td>{{ $expediente->fecha_registro->format('d/m/Y') }}</td>
                                    <td>{{ $expediente->tipoTramite->nombre }}</td>
                                    <td>{{ Str::limit($expediente->asunto, 50) }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $expediente->estado == 'pendiente' ? 'warning' : 
                                            ($expediente->estado == 'en_proceso' ? 'info' : 
                                            ($expediente->estado == 'resuelto' ? 'success' : 'secondary')) 
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $expediente->estado)) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $expediente->derivacionActual()?->area?->nombre ?? 'Mesa de Partes' }}
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('seguimiento.show', $expediente) }}" class="btn btn-info" title="Ver Detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('seguimiento.consulta', $expediente->codigo_expediente) }}" class="btn btn-primary" title="Seguimiento Público">
                                                <i class="fas fa-search"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <h5>No tiene expedientes registrados</h5>
                                            <p class="text-muted">Registre su primer trámite haciendo clic en "Nuevo Expediente"</p>
                                            <a href="{{ route('ciudadano.registrar-expediente') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Crear Primer Expediente
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    {{ $expedientes->links() }}

                    <!-- Información Útil -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-info-circle"></i> Información Útil</h6>
                        <ul class="mb-0">
                            <li>Puede consultar el estado de sus expedientes en cualquier momento</li>
                            <li>Recibirá notificaciones por email sobre cambios en sus trámites</li>
                            <li>Conserve el código de expediente para consultas futuras</li>
                            <li>Los plazos se cuentan en días hábiles</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection