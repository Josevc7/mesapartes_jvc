@extends('layouts.app')

@section('title', 'Supervisión Avanzada')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Supervisión Avanzada de Funcionarios</h2>
                    <p class="text-muted mb-0">Control detallado del rendimiento y carga de trabajo</p>
                </div>
                <a href="{{ route('jefe-area.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Alertas del Sistema -->
    @if(isset($alertas) && $alertas->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0">
                <div class="card-body py-2">
                    @foreach($alertas as $alerta)
                    <div class="alert alert-{{ $alerta['tipo'] }} mb-2 py-2 d-flex align-items-center">
                        <i class="fas fa-{{ $alerta['icono'] }} me-2"></i>
                        {{ $alerta['mensaje'] }}
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Estadísticas Generales del Área -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $estadisticasArea['total_pendientes'] ?? 0 }}</h3>
                            <p class="mb-0">Pendientes en Área</p>
                        </div>
                        <i class="fas fa-inbox fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $estadisticasArea['total_vencidos'] ?? 0 }}</h3>
                            <p class="mb-0">Vencidos</p>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $estadisticasArea['resueltos_mes'] ?? 0 }}</h3>
                            <p class="mb-0">Resueltos (Mes)</p>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ round($estadisticasArea['promedio_carga'] ?? 0, 1) }}</h3>
                            <p class="mb-0">Promedio Carga</p>
                        </div>
                        <i class="fas fa-balance-scale fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de Funcionarios -->
    <div class="row">
        @foreach($funcionarios as $funcionario)
        <div class="col-lg-6 mb-4">
            <div class="card h-100 {{ $funcionario->carga_trabajo > 10 ? 'border-danger' : ($funcionario->vencidos > 0 ? 'border-warning' : '') }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-{{ $funcionario->carga_trabajo > 10 ? 'danger' : ($funcionario->carga_trabajo > 5 ? 'warning' : 'primary') }} text-white rounded-circle me-3"
                             style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                            {{ substr($funcionario->name, 0, 1) }}
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $funcionario->name }}</h5>
                            <small class="text-muted">{{ $funcionario->email }}</small>
                        </div>
                    </div>
                    @if($funcionario->carga_trabajo > 10)
                        <span class="badge bg-danger">Sobrecargado</span>
                    @elseif($funcionario->vencidos > 0)
                        <span class="badge bg-warning text-dark">Con vencidos</span>
                    @else
                        <span class="badge bg-success">Normal</span>
                    @endif
                </div>
                <div class="card-body">
                    <!-- Estadísticas del Funcionario -->
                    <div class="row mb-3">
                        <div class="col-4 text-center">
                            <h4 class="mb-0 text-primary">{{ $funcionario->total_asignados }}</h4>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-4 text-center">
                            <h4 class="mb-0 text-success">{{ $funcionario->resueltos }}</h4>
                            <small class="text-muted">Resueltos</small>
                        </div>
                        <div class="col-4 text-center">
                            <h4 class="mb-0 text-warning">{{ $funcionario->pendientes }}</h4>
                            <small class="text-muted">Pendientes</small>
                        </div>
                    </div>

                    <!-- Indicadores -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Vencidos</small>
                            <span class="badge bg-{{ $funcionario->vencidos > 0 ? 'danger' : 'secondary' }} fs-6">
                                {{ $funcionario->vencidos }}
                            </span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Resueltos Mes</small>
                            <span class="badge bg-info fs-6">{{ $funcionario->resueltos_mes }}</span>
                        </div>
                    </div>

                    <!-- Efectividad -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Efectividad</small>
                            <small>{{ $funcionario->efectividad }}%</small>
                        </div>
                        <div class="progress" style="height: 15px;">
                            @php
                                $efectColor = $funcionario->efectividad >= 80 ? 'success' : ($funcionario->efectividad >= 60 ? 'warning' : 'danger');
                            @endphp
                            <div class="progress-bar bg-{{ $efectColor }}"
                                 style="width: {{ $funcionario->efectividad }}%"></div>
                        </div>
                    </div>

                    <!-- Tiempo Promedio -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Tiempo Promedio de Atención</small>
                            <span class="badge bg-{{ $funcionario->tiempo_promedio <= 15 ? 'success' : ($funcionario->tiempo_promedio <= 25 ? 'warning' : 'danger') }}">
                                {{ $funcionario->tiempo_promedio }} días
                            </span>
                        </div>
                    </div>

                    <!-- Expedientes Actuales -->
                    @if($funcionario->expedientes_actuales && $funcionario->expedientes_actuales->count() > 0)
                    <div class="mt-3">
                        <h6 class="border-bottom pb-2">
                            <i class="fas fa-folder-open me-1"></i>
                            Expedientes Actuales ({{ $funcionario->expedientes_actuales->count() }})
                        </h6>
                        <div class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                            @foreach($funcionario->expedientes_actuales as $exp)
                            <div class="list-group-item px-0 py-2 {{ $exp->vencido ? 'border-start border-danger border-3' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="small">{{ $exp->codigo_expediente }}</strong>
                                        @php
                                            $estadoColor = match($exp->estado) {
                                                'derivado' => 'primary',
                                                'en_proceso' => 'info',
                                                'resuelto' => 'success',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $estadoColor }} ms-1">{{ ucfirst(str_replace('_', ' ', $exp->estado)) }}</span>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($exp->asunto, 35) }}</small>
                                    </div>
                                    <div class="text-end">
                                        @if($exp->vencido)
                                            <span class="badge bg-danger">
                                                {{ abs($exp->dias_restantes) }}d vencido
                                            </span>
                                        @elseif($exp->dias_restantes !== null)
                                            <span class="badge bg-{{ $exp->dias_restantes <= 3 ? 'warning text-dark' : 'light text-dark' }}">
                                                {{ $exp->dias_restantes }}d
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-inbox"></i>
                        <small class="d-block">Sin expedientes activos</small>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('jefe-area.expedientes', ['funcionario' => $funcionario->id]) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-list me-1"></i> Ver expedientes
                        </a>
                        @if($funcionario->carga_trabajo > 10)
                        <button type="button" class="btn btn-sm btn-outline-warning"
                                data-bs-toggle="modal" data-bs-target="#modalReasignar{{ $funcionario->id }}">
                            <i class="fas fa-exchange-alt me-1"></i> Reasignar carga
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Reasignar (solo si está sobrecargado) -->
        @if($funcionario->carga_trabajo > 10)
        <div class="modal fade" id="modalReasignar{{ $funcionario->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            <i class="fas fa-exchange-alt me-2"></i>
                            Reasignar carga de {{ $funcionario->name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">
                            Este funcionario tiene {{ $funcionario->carga_trabajo }} expedientes pendientes.
                            Seleccione expedientes para reasignar a otros funcionarios desde
                            <a href="{{ route('jefe-area.expedientes', ['funcionario' => $funcionario->id]) }}">
                                la lista de expedientes
                            </a>.
                        </p>
                        <div class="alert alert-info">
                            <strong>Sugerencia:</strong> Reasigne expedientes a:
                            @if($estadisticasArea['funcionario_menos_cargado'])
                                <br>{{ $estadisticasArea['funcionario_menos_cargado']->name }}
                                ({{ $estadisticasArea['funcionario_menos_cargado']->carga_trabajo }} expedientes)
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <a href="{{ route('jefe-area.expedientes', ['funcionario' => $funcionario->id]) }}"
                           class="btn btn-warning">
                            <i class="fas fa-list me-1"></i> Ir a expedientes
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>

    <!-- Expedientes por Aprobar -->
    @if(isset($procesosEspeciales) && $procesosEspeciales->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>
                        Expedientes Listos para Aprobación ({{ $procesosEspeciales->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Asunto</th>
                                    <th>Funcionario</th>
                                    <th>Tipo Trámite</th>
                                    <th>Fecha Resolución</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($procesosEspeciales as $exp)
                                <tr>
                                    <td><strong>{{ $exp->codigo_expediente }}</strong></td>
                                    <td>{{ Str::limit($exp->asunto, 40) }}</td>
                                    <td>{{ $exp->funcionarioAsignado->name ?? 'N/A' }}</td>
                                    <td>{{ $exp->tipoTramite->nombre ?? 'N/A' }}</td>
                                    <td>{{ $exp->fecha_resolucion ? $exp->fecha_resolucion->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        <a href="{{ route('jefe-area.show-expediente', $exp) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> Revisar
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('jefe-area.validar-documentos') }}" class="btn btn-info">
                        <i class="fas fa-check-double me-1"></i> Ir a Validar Documentos
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Resumen de Desempeño -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Mejor Rendimiento</h5>
                </div>
                <div class="card-body">
                    @php
                        $mejorFuncionario = $funcionarios->sortByDesc('efectividad')->first();
                    @endphp
                    @if($mejorFuncionario && $mejorFuncionario->total_asignados > 0)
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-success text-white rounded-circle me-3"
                             style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                            <i class="fas fa-medal"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $mejorFuncionario->name }}</h5>
                            <p class="mb-0 text-muted">Efectividad: {{ $mejorFuncionario->efectividad }}%</p>
                            <p class="mb-0 text-muted">{{ $mejorFuncionario->resueltos }} expedientes resueltos</p>
                        </div>
                    </div>
                    @else
                    <p class="text-muted text-center mb-0">Sin datos suficientes</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>Requiere Atención</h5>
                </div>
                <div class="card-body">
                    @php
                        $funcionarioProblema = $funcionarios->filter(fn($f) => $f->vencidos > 0 || $f->carga_trabajo > 10)->first();
                    @endphp
                    @if($funcionarioProblema)
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-danger text-white rounded-circle me-3"
                             style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $funcionarioProblema->name }}</h5>
                            @if($funcionarioProblema->vencidos > 0)
                                <p class="mb-0 text-danger">{{ $funcionarioProblema->vencidos }} expedientes vencidos</p>
                            @endif
                            @if($funcionarioProblema->carga_trabajo > 10)
                                <p class="mb-0 text-warning">Carga alta: {{ $funcionarioProblema->carga_trabajo }} pendientes</p>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="text-center text-success">
                        <i class="fas fa-check-circle fa-3x mb-2"></i>
                        <p class="mb-0">Todos los funcionarios al día</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
