@extends('layouts.app')

@section('title', 'Mi Mesa de Partes ')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2>Bienvenido, {{ auth()->user()->name }}</h2>
            <p class="text-muted">Ventanilla Virtual DRTC - Mesa de Partes Digital</p>
        </div>
    </div>

    <!-- Estadísticas Personales -->
    <!--<div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                    <h3>{{ $stats['total_expedientes'] }}</h3>
                    <p>Total Expedientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h3>{{ $stats['en_proceso'] }}</h3>
                    <p>En Proceso</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3>{{ $stats['resueltos'] }}</h3>
                    <p>Resueltos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h3>{{ $stats['observados'] }}</h3>
                    <p>Observados</p>
                </div>
            </div>
        </div>
    </div>-->

    <!-- Alerta de Observaciones Pendientes -->
    @if($stats['observados'] > 0)
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
        <div class="flex-grow-1">
            <h5 class="alert-heading mb-2">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ¡Atención! Tiene {{ $stats['observados'] }} {{ Str::plural('expediente', $stats['observados']) }} con observaciones pendientes
            </h5>
            <p class="mb-2">
                Algunos de sus expedientes requieren que subsane información o presente documentos adicionales para continuar con su trámite.
            </p>
            <a href="{{ route('ciudadano.observaciones') }}" class="btn btn-warning">
                <i class="fas fa-reply me-2"></i>
                Ver y Responder Observaciones
            </a>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Acciones Principales -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-plus-circle fa-4x text-primary mb-3"></i>
                    <h4>Nuevo Expediente</h4>
                    <p class="text-muted">Registre un nuevo trámite de forma virtual</p>
                    <a href="{{ route('ciudadano.registrar-expediente') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus"></i> Crear Expediente
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-search fa-4x text-info mb-3"></i>
                    <h4>Mis Expedientes</h4>
                    <p class="text-muted">Consulte el estado de sus trámites</p>
                    <a href="{{ route('ciudadano.mis-expedientes') }}" class="btn btn-info btn-lg">
                        <i class="fas fa-list"></i> Ver Expedientes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Expedientes Recientes -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Expedientes Recientes</h5>
                    <a href="{{ route('ciudadano.mis-expedientes') }}" class="btn btn-sm btn-outline-primary">Ver Todos</a>
                </div>
                <div class="card-body">
                    @if($expedientes_recientes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Asunto</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expedientes_recientes as $expediente)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $expediente->codigo_expediente }}</strong>
                                        </td>
                                        <td>{{ Str::limit($expediente->asunto, 40) }}</td>
                                        <td>{{ $expediente->tipoTramite->nombre ?? 'Sin clasificar' }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $expediente->estado == 'resuelto' ? 'success' : 
                                                ($expediente->estado == 'observado' ? 'warning' : 'info') 
                                            }}">
                                                {{ $expediente->getEstadoFormateado() }}
                                            </span>
                                        </td>
                                        <td>{{ $expediente->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('ciudadano.seguimiento', $expediente->codigo_expediente) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No tiene expedientes registrados</p>
                            <a href="{{ route('ciudadano.registrar-expediente') }}" class="btn btn-primary">
                                Crear su Primer Expediente
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Información Útil -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> Información Importante</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Todos los trámites son gratuitos</li>
                        <li><i class="fas fa-check text-success"></i> Puede consultar el estado 24/7</li>
                        <li><i class="fas fa-check text-success"></i> Recibirá notificaciones por email</li>
                        <li><i class="fas fa-check text-success"></i> Documentos en formato PDF únicamente</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-bell"></i> Notificaciones</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('ciudadano.notificaciones') }}" class="btn btn-outline-info w-100 mb-2">
                        <i class="fas fa-bell"></i> Ver Notificaciones
                    </a>
                    <small class="text-muted">
                        Manténgase informado sobre el estado de sus trámites
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection