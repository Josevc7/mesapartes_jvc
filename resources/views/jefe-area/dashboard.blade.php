@extends('layouts.app')

@section('title', 'Dashboard Jefe de Área')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2>Dashboard - Jefe de Área</h2>
            <p class="text-muted">Área: {{ auth()->user()->area->nombre ?? 'Sin área asignada' }}</p>
        </div>
    </div>

    <!-- Estadísticas -->
    <!-- <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['total_expedientes'] }}</h3>
                    <p>Total Expedientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['pendientes'] }}</h3>
                    <p>Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['vencidos'] }}</h3>
                    <p>Vencidos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['resueltos_mes'] }}</h3>
                    <p>Resueltos Este Mes</p>
                </div>
            </div>
        </div>
    </div>-->

    <!-- Acciones Rápidas -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-tasks fa-3x text-primary mb-3"></i>
                    <h5>Supervisar Expedientes</h5>
                    <p>Revisar y aprobar expedientes del área</p>
                    <a href="{{ route('jefe-area.expedientes') }}" class="btn btn-primary">Ver Expedientes</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-3x text-success mb-3"></i>
                    <h5>Reportes del Área</h5>
                    <p>Estadísticas y rendimiento</p>
                    <a href="{{ route('jefe-area.reportes') }}" class="btn btn-success">Ver Reportes</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                    <h5>Control de Plazos</h5>
                    <p>Expedientes próximos a vencer</p>
                    <a href="{{ route('jefe-area.control-plazos') }}" class="btn btn-warning">Control Plazos</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Fila Adicional de Acciones -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-info mb-3"></i>
                    <h5>Supervisión Avanzada</h5>
                    <p>Control detallado por funcionario</p>
                    <a href="{{ route('jefe-area.supervision') }}" class="btn btn-info">Supervisar</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-gavel fa-3x text-secondary mb-3"></i>
                    <h5>Autorizaciones</h5>
                    <p>Procesos que requieren aprobación</p>
                    <a href="{{ route('jefe-area.expedientes', ['estado' => 'Resuelto']) }}" class="btn btn-secondary">Aprobar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Expedientes Críticos -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Expedientes que Requieren Atención</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>Acceso Rápido:</strong> Utiliza las opciones del menú superior para acceder a Control de Plazos, Supervisión Avanzada y Reportes detallados.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection