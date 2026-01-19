@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-tachometer-alt text-primary"></i> Dashboard - {{ auth()->user()->role ? auth()->user()->role->nombre : 'Sin rol' }}</h5>
        <small class="text-muted">{{ now()->format('d/m/Y H:i') }}</small>
    </div>

    @if(isset($stats))
    <!-- Indicadores compactos -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="stat-badge bg-primary text-white">
            <i class="fas fa-folder"></i>
            <strong>{{ $stats['total_expedientes'] }}</strong>
            <span>Total</span>
        </span>
        <span class="stat-badge bg-warning text-dark">
            <i class="fas fa-clock"></i>
            <strong>{{ $stats['expedientes_pendientes'] }}</strong>
            <span>Pendientes</span>
        </span>
        <span class="stat-badge bg-info text-white">
            <i class="fas fa-spinner"></i>
            <strong>{{ $stats['expedientes_proceso'] }}</strong>
            <span>En Proceso</span>
        </span>
        <span class="stat-badge bg-success text-white">
            <i class="fas fa-users"></i>
            <strong>{{ $stats['total_usuarios'] }}</strong>
            <span>Usuarios</span>
        </span>
    </div>
    @endif
    
    <!-- Acciones rápidas según rol -->
    <div class="card">
        <div class="card-header py-2">
            <h6 class="mb-0"><i class="fas fa-bolt text-warning"></i> Acciones Rápidas</h6>
        </div>
        <div class="card-body py-3">
            @if(auth()->user()->role && auth()->user()->role->nombre == 'Ciudadano')
                <!-- Dashboard Ciudadano -->
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('ciudadano.registrar-expediente') }}" class="action-card bg-primary text-white text-decoration-none">
                        <i class="fas fa-plus-circle"></i>
                        <div>
                            <strong>Nuevo Expediente</strong>
                            <small>Registrar trámite</small>
                        </div>
                    </a>
                    <a href="{{ route('seguimiento.index') }}" class="action-card bg-info text-white text-decoration-none">
                        <i class="fas fa-search"></i>
                        <div>
                            <strong>Mis Expedientes</strong>
                            <small>Consultar estado</small>
                        </div>
                    </a>
                </div>

            @elseif(auth()->user()->role && in_array(auth()->user()->role->nombre, ['Mesa de Partes', 'Administrador']))
                <!-- Dashboard Mesa de Partes -->
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('mesa-partes.index') }}" class="action-card bg-warning text-dark text-decoration-none">
                        <i class="fas fa-inbox"></i>
                        <div>
                            <strong>Expedientes</strong>
                            <small>Clasificar y derivar</small>
                        </div>
                    </a>
                    <a href="{{ route('reportes.index') }}" class="action-card bg-success text-white text-decoration-none">
                        <i class="fas fa-chart-bar"></i>
                        <div>
                            <strong>Reportes</strong>
                            <small>Estadísticas</small>
                        </div>
                    </a>
                    <a href="{{ route('seguimiento.form') }}" class="action-card bg-info text-white text-decoration-none">
                        <i class="fas fa-search"></i>
                        <div>
                            <strong>Consulta</strong>
                            <small>Buscar expediente</small>
                        </div>
                    </a>
                </div>

            @elseif(auth()->user()->role && auth()->user()->role->nombre == 'Funcionario')
                <!-- Dashboard Funcionario -->
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('funcionario.index') }}" class="action-card bg-primary text-white text-decoration-none">
                        <i class="fas fa-tasks"></i>
                        <div>
                            <strong>Asignados</strong>
                            <small>Trámites pendientes</small>
                        </div>
                    </a>
                    <a href="{{ route('funcionario.index', ['urgentes' => 1]) }}" class="action-card bg-danger text-white text-decoration-none">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Urgentes</strong>
                            <small>Por vencer</small>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Info del sistema -->
    <div class="alert alert-light border mt-3 py-2 small">
        <i class="fas fa-info-circle text-primary"></i>
        <strong>{{ auth()->user()->name }}</strong> - Sistema Mesa de Partes DRTC
    </div>
</div>

<style>
/* Badges de estadísticas */
.stat-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    font-size: 0.9rem;
}
.stat-badge i {
    font-size: 1.1rem;
}
.stat-badge strong {
    font-size: 1.3rem;
    font-weight: 700;
}
.stat-badge span {
    font-size: 0.8rem;
    opacity: 0.9;
}

/* Tarjetas de acción */
.action-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.9rem 1.4rem;
    border-radius: 10px;
    transition: transform 0.15s, opacity 0.15s;
}
.action-card:hover {
    transform: scale(1.02);
    opacity: 0.9;
}
.action-card i {
    font-size: 1.8rem;
}
.action-card div {
    display: flex;
    flex-direction: column;
    line-height: 1.3;
}
.action-card strong {
    font-size: 1rem;
}
.action-card small {
    font-size: 0.8rem;
    opacity: 0.85;
}
</style>
@endsection