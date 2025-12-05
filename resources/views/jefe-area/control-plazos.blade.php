@extends('layouts.app')

@section('title', 'Control de Plazos')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2>Control de Plazos y Cumplimiento</h2>
            <p class="text-muted">Monitoreo de expedientes críticos del área</p>
        </div>
    </div>

    <!-- Alertas Críticas -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5><i class="fas fa-exclamation-triangle"></i> Vencidos</h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-danger">{{ $stats['vencidos'] ?? 0 }}</h2>
                    <p>Expedientes vencidos</p>
                    <a href="{{ route('jefe-area.expedientes', ['vencidos' => 1]) }}" class="btn btn-danger btn-sm">Ver Todos</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5><i class="fas fa-clock"></i> Por Vencer</h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-warning">{{ $stats['por_vencer'] ?? 0 }}</h2>
                    <p>Próximos 3 días</p>
                    <a href="{{ route('jefe-area.expedientes', ['por_vencer' => 1]) }}" class="btn btn-warning btn-sm">Ver Todos</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5><i class="fas fa-check-circle"></i> En Plazo</h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-success">{{ $stats['en_plazo'] ?? 0 }}</h2>
                    <p>Expedientes normales</p>
                    <a href="{{ route('jefe-area.expedientes') }}" class="btn btn-success btn-sm">Ver Todos</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Expedientes Críticos -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Expedientes que Requieren Atención Inmediata</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Asunto</th>
                                    <th>Funcionario</th>
                                    <th>Días Vencido/Restantes</th>
                                    <th>Prioridad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expedientes_criticos ?? [] as $expediente)
                                <tr class="{{ $expediente->dias_vencido > 0 ? 'table-danger' : 'table-warning' }}">
                                    <td>
                                        <strong>{{ $expediente->codigo_expediente }}</strong>
                                        @if($expediente->dias_vencido > 0)
                                            <br><small class="text-danger">VENCIDO</small>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($expediente->asunto, 40) }}</td>
                                    <td>
                                        {{ $expediente->funcionarioAsignado->name ?? 'Sin asignar' }}
                                        @if(!$expediente->funcionarioAsignado)
                                            <br><small class="text-danger">¡Sin asignar!</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($expediente->dias_vencido > 0)
                                            <span class="badge bg-danger">{{ $expediente->dias_vencido }} días vencido</span>
                                        @else
                                            <span class="badge bg-warning">{{ $expediente->dias_restantes }} días restantes</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $expediente->prioridad == 'Urgente' ? 'danger' : 'warning' }}">
                                            {{ $expediente->prioridad ?? 'Media' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('funcionario.show', $expediente) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#recordatorioModal{{ $expediente->id }}">
                                            Recordatorio
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal Recordatorio -->
                                <div class="modal fade" id="recordatorioModal{{ $expediente->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Enviar Recordatorio</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Enviar recordatorio al funcionario <strong>{{ $expediente->funcionarioAsignado->name ?? 'N/A' }}</strong> sobre el expediente:</p>
                                                <p><strong>{{ $expediente->codigo_expediente }}</strong></p>
                                                <textarea class="form-control" placeholder="Mensaje adicional (opcional)" rows="3"></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="button" class="btn btn-warning">Enviar Recordatorio</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-success">
                                        <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                        No hay expedientes críticos en este momento
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis de Cumplimiento -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Análisis de Cumplimiento</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>Cumplimiento General</label>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: 75%">75%</div>
                        </div>
                        <small class="text-muted">Expedientes resueltos en plazo</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>Eficiencia del Área</label>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-info" style="width: 82%">82%</div>
                        </div>
                        <small class="text-muted">Comparado con otras áreas</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>Tiempo Promedio</label>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-warning" style="width: 60%">12 días</div>
                        </div>
                        <small class="text-muted">De 20 días máximo permitido</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Acciones Recomendadas</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Revisar expedientes vencidos
                            <span class="badge bg-danger">{{ $stats['vencidos'] ?? 0 }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Reasignar expedientes sin funcionario
                            <span class="badge bg-warning">{{ $stats['sin_asignar'] ?? 0 }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Enviar recordatorios
                            <span class="badge bg-info">{{ $stats['por_vencer'] ?? 0 }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Aprobar expedientes resueltos
                            <span class="badge bg-success">{{ $stats['por_aprobar'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection