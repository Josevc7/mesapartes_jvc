@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2>Dashboard - {{ auth()->user()->role ? auth()->user()->role->nombre : 'Sin rol' }}</h2>
        </div>
    </div>
    
    @if(isset($stats))
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Expedientes</h5>
                    <h2>{{ $stats['total_expedientes'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Pendientes</h5>
                    <h2>{{ $stats['expedientes_pendientes'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>En Proceso</h5>
                    <h2>{{ $stats['expedientes_proceso'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Usuarios Activos</h5>
                    <h2>{{ $stats['total_usuarios'] }}</h2>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Bienvenido al Sistema</h4>
                </div>

                <div class="card-body">
                    @if(auth()->user()->role && auth()->user()->role->nombre == 'Ciudadano')
                        <!-- Dashboard Ciudadano -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-plus-circle fa-3x mb-3"></i>
                                        <h5>Nuevo Expediente</h5>
                                        <p>Registre un nuevo trámite</p>
                                        <a href="{{ route('ciudadano.registrar-expediente') }}" class="btn btn-light">Crear Expediente</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-search fa-3x mb-3"></i>
                                        <h5>Mis Expedientes</h5>
                                        <p>Consulte el estado de sus trámites</p>
                                        <a href="{{ route('seguimiento.index') }}" class="btn btn-light">Ver Expedientes</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @elseif(auth()->user()->role && in_array(auth()->user()->role->nombre, ['Mesa de Partes', 'Administrador']))
                        <!-- Dashboard Mesa de Partes -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <h5>Expedientes Pendientes</h5>
                                        <p>Clasificar y derivar</p>
                                        <a href="{{ route('mesa-partes.index') }}" class="btn btn-light">Gestionar</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                        <h5>Reportes</h5>
                                        <p>Estadísticas del sistema</p>
                                        <a href="{{ route('reportes.index') }}" class="btn btn-light">Ver Reportes</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-search fa-3x mb-3"></i>
                                        <h5>Consulta Pública</h5>
                                        <p>Buscar expedientes</p>
                                        <a href="{{ route('seguimiento.form') }}" class="btn btn-light">Consultar</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @elseif(auth()->user()->role && auth()->user()->role->nombre == 'Funcionario')
                        <!-- Dashboard Funcionario -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-tasks fa-3x mb-3"></i>
                                        <h5>Expedientes Asignados</h5>
                                        <p>Trámites para atender</p>
                                        <a href="{{ route('funcionario.index') }}" class="btn btn-light">Ver Asignados</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-clock fa-3x mb-3"></i>
                                        <h5>Próximos a Vencer</h5>
                                        <p>Expedientes urgentes</p>
                                        <a href="{{ route('funcionario.index', ['urgentes' => 1]) }}" class="btn btn-light">Ver Urgentes</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Información General -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Información del Sistema</h6>
                                <ul class="mb-0">
                                    <li>Sistema de Mesa de Partes DRTC</li>
                                    <li>Usuario: {{ auth()->user()->name }} ({{ auth()->user()->role ? auth()->user()->role->nombre : 'Sin rol' }})</li>
                                    <li>Fecha: {{ now()->format('d/m/Y H:i') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection