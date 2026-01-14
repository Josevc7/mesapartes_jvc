@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-chart-bar"></i> Reportes del Sistema</h2>
        </div>
    </div>

    <!-- Estadisticas Rapidas -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['total_expedientes'] }}</h3>
                    <p class="mb-0">Total Expedientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['expedientes_mes'] }}</h3>
                    <p class="mb-0">Este Mes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['pendientes'] }}</h3>
                    <p class="mb-0">Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['resueltos'] }}</h3>
                    <p class="mb-0">Resueltos</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Reportes Disponibles -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-file-alt"></i> Reportes Disponibles</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Por Fecha -->
                        <div class="col-md-4">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-calendar-alt fa-3x text-success mb-3"></i>
                                    <h5>Reporte por Fecha</h5>
                                    <p class="text-muted">Filtrar tramites por rango de fechas con multiples criterios</p>
                                    <ul class="list-unstyled text-start small text-muted">
                                        <li><i class="fas fa-check text-success"></i> Filtro por fecha inicio/fin</li>
                                        <li><i class="fas fa-check text-success"></i> Filtro por tipo, area, estado</li>
                                        <li><i class="fas fa-check text-success"></i> Graficos y estadisticas</li>
                                        <li><i class="fas fa-check text-success"></i> Exportar a CSV</li>
                                    </ul>
                                    <a href="{{ route('reportes.por-fecha') }}" class="btn btn-success">
                                        <i class="fas fa-eye"></i> Ver Reporte
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Por Tipo Tramite -->
                        <div class="col-md-4">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-folder-open fa-3x text-primary mb-3"></i>
                                    <h5>Reporte por Tipo de Tramite</h5>
                                    <p class="text-muted">Analisis detallado por cada tipo de tramite</p>
                                    <ul class="list-unstyled text-start small text-muted">
                                        <li><i class="fas fa-check text-primary"></i> Resumen de todos los tipos</li>
                                        <li><i class="fas fa-check text-primary"></i> % de resolucion por tipo</li>
                                        <li><i class="fas fa-check text-primary"></i> Distribucion por area</li>
                                        <li><i class="fas fa-check text-primary"></i> Tendencia mensual</li>
                                    </ul>
                                    <a href="{{ route('reportes.por-tipo-tramite') }}" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> Ver Reporte
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Por Area -->
                        <div class="col-md-4">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-building fa-3x text-info mb-3"></i>
                                    <h5>Reporte por Area</h5>
                                    <p class="text-muted">Rendimiento y estadisticas por area</p>
                                    <ul class="list-unstyled text-start small text-muted">
                                        <li><i class="fas fa-check text-info"></i> Eficiencia por area</li>
                                        <li><i class="fas fa-check text-info"></i> Expedientes vencidos</li>
                                        <li><i class="fas fa-check text-info"></i> Rendimiento funcionarios</li>
                                        <li><i class="fas fa-check text-info"></i> Promedio de atencion</li>
                                    </ul>
                                    <a href="{{ route('reportes.por-area') }}" class="btn btn-info">
                                        <i class="fas fa-eye"></i> Ver Reporte
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Por Remitente -->
                        <div class="col-md-4">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-user fa-3x text-warning mb-3"></i>
                                    <h5>Reporte por Remitente</h5>
                                    <p class="text-muted">Busqueda y analisis por persona/empresa</p>
                                    <ul class="list-unstyled text-start small text-muted">
                                        <li><i class="fas fa-check text-warning"></i> Busqueda por nombre/DNI/RUC</li>
                                        <li><i class="fas fa-check text-warning"></i> Historial de expedientes</li>
                                        <li><i class="fas fa-check text-warning"></i> Top remitentes del mes</li>
                                        <li><i class="fas fa-check text-warning"></i> Estado de tramites</li>
                                    </ul>
                                    <a href="{{ route('reportes.por-remitente') }}" class="btn btn-warning">
                                        <i class="fas fa-eye"></i> Ver Reporte
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Tiempos de Atencion -->
                        <div class="col-md-4">
                            <div class="card h-100 border-secondary">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-3x text-secondary mb-3"></i>
                                    <h5>Tiempos de Atencion</h5>
                                    <p class="text-muted">Analisis de tiempos de respuesta</p>
                                    <ul class="list-unstyled text-start small text-muted">
                                        <li><i class="fas fa-check text-secondary"></i> Promedio general</li>
                                        <li><i class="fas fa-check text-secondary"></i> Por tipo de tramite</li>
                                        <li><i class="fas fa-check text-secondary"></i> Por area</li>
                                        <li><i class="fas fa-check text-secondary"></i> Tendencias</li>
                                    </ul>
                                    <button class="btn btn-secondary" disabled>
                                        <i class="fas fa-wrench"></i> Proximamente
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Estadisticas Generales -->
                        <div class="col-md-4">
                            <div class="card h-100 border-dark">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-pie fa-3x text-dark mb-3"></i>
                                    <h5>Dashboard Estadistico</h5>
                                    <p class="text-muted">Vision general del sistema</p>
                                    <ul class="list-unstyled text-start small text-muted">
                                        <li><i class="fas fa-check text-dark"></i> Graficos interactivos</li>
                                        <li><i class="fas fa-check text-dark"></i> KPIs principales</li>
                                        <li><i class="fas fa-check text-dark"></i> Comparativas</li>
                                        <li><i class="fas fa-check text-dark"></i> Exportacion</li>
                                    </ul>
                                    <button class="btn btn-dark" disabled>
                                        <i class="fas fa-wrench"></i> Proximamente
                                    </button>
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
