@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2>Reportes del Sistema</h2>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['total_expedientes'] }}</h3>
                    <p>Total Expedientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['expedientes_mes'] }}</h3>
                    <p>Este Mes</p>
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
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['resueltos'] }}</h3>
                    <p>Resueltos</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Opciones de Reportes</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                                    <h6>Trámites por Mes</h6>
                                    <button class="btn btn-primary btn-sm">Ver Reporte</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                                    <h6>Tiempos de Atención</h6>
                                    <button class="btn btn-warning btn-sm">Ver Reporte</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-building fa-3x text-info mb-3"></i>
                                    <h6>Por Áreas</h6>
                                    <button class="btn btn-info btn-sm">Ver Reporte</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection