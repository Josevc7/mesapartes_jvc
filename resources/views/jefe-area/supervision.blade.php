@extends('layouts.app')

@section('title', 'Supervisión Avanzada')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2>Supervisión Avanzada del Área</h2>
            <p class="text-muted">Control detallado de funcionarios y procesos</p>
        </div>
    </div>

    <!-- Panel de Control por Funcionario -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Estado por Funcionario</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($funcionarios ?? [] as $funcionario)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-{{ $funcionario->carga_trabajo > 10 ? 'danger' : ($funcionario->carga_trabajo > 5 ? 'warning' : 'success') }}">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar bg-primary text-white rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            {{ substr($funcionario->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $funcionario->name }}</h6>
                                            <small class="text-muted">{{ $funcionario->email }}</small>
                                        </div>
                                    </div>
                                    
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="border-end">
                                                <h5 class="text-primary mb-0">{{ $funcionario->asignados ?? 0 }}</h5>
                                                <small>Asignados</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border-end">
                                                <h5 class="text-success mb-0">{{ $funcionario->resueltos ?? 0 }}</h5>
                                                <small>Resueltos</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <h5 class="text-warning mb-0">{{ $funcionario->pendientes ?? 0 }}</h5>
                                            <small>Pendientes</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Carga de Trabajo</small>
                                            <small>{{ $funcionario->carga_trabajo ?? 0 }}/15</small>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $funcionario->carga_trabajo > 10 ? 'danger' : ($funcionario->carga_trabajo > 5 ? 'warning' : 'success') }}" 
                                                 style="width: {{ min(($funcionario->carga_trabajo ?? 0) / 15 * 100, 100) }}%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <button class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#funcionarioModal{{ $funcionario->id }}">
                                            Ver Detalle
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Detalle Funcionario -->
                        <div class="modal fade" id="funcionarioModal{{ $funcionario->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $funcionario->name }} - Detalle</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Estadísticas</h6>
                                                <ul class="list-unstyled">
                                                    <li>Expedientes asignados: <strong>{{ $funcionario->asignados ?? 0 }}</strong></li>
                                                    <li>Expedientes resueltos: <strong>{{ $funcionario->resueltos ?? 0 }}</strong></li>
                                                    <li>Tiempo promedio: <strong>{{ $funcionario->tiempo_promedio ?? 0 }} días</strong></li>
                                                    <li>Efectividad: <strong>{{ $funcionario->efectividad ?? 0 }}%</strong></li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Expedientes Actuales</h6>
                                                <div class="list-group list-group-flush">
                                                    @foreach($funcionario->expedientes_actuales ?? [] as $exp)
                                                    <div class="list-group-item d-flex justify-content-between">
                                                        <span>{{ $exp->codigo }}</span>
                                                        <span class="badge bg-info">{{ $exp->estado }}</span>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        <button type="button" class="btn btn-primary">Reasignar Expedientes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Procesos Críticos -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Procesos que Requieren Autorización</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Expediente</th>
                                    <th>Tipo</th>
                                    <th>Funcionario</th>
                                    <th>Motivo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($procesos_especiales ?? [] as $proceso)
                                <tr>
                                    <td>{{ $proceso->codigo_expediente }}</td>
                                    <td>{{ $proceso->tipo_autorizacion }}</td>
                                    <td>{{ $proceso->funcionario->name }}</td>
                                    <td>{{ Str::limit($proceso->motivo, 30) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-success">Autorizar</button>
                                        <button class="btn btn-sm btn-danger">Rechazar</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay procesos pendientes de autorización</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary">
                            <i class="fas fa-balance-scale"></i> Redistribuir Carga
                        </button>
                        <button class="btn btn-warning">
                            <i class="fas fa-bell"></i> Enviar Recordatorios
                        </button>
                        <button class="btn btn-info">
                            <i class="fas fa-chart-line"></i> Generar Reporte
                        </button>
                        <button class="btn btn-success">
                            <i class="fas fa-check-double"></i> Aprobar Lote
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Alertas del Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning alert-sm">
                        <small><strong>3</strong> expedientes próximos a vencer</small>
                    </div>
                    <div class="alert alert-danger alert-sm">
                        <small><strong>1</strong> funcionario sobrecargado</small>
                    </div>
                    <div class="alert alert-info alert-sm">
                        <small><strong>5</strong> expedientes listos para aprobar</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection