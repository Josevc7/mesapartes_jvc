@extends('layouts.app')

@section('title', 'Dashboard Funcionario')

@section('content')
<div class="container-fluid">
    <!-- Resumen de Expedientes -->
    <!--<div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['derivados'] }}</h4>
                            <p class="mb-0">Por Recibir</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-inbox fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['en_proceso'] }}</h4>
                            <p class="mb-0">En Proceso</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-cogs fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['vencidos'] }}</h4>
                            <p class="mb-0">Vencidos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['resueltos_mes'] }}</h4>
                            <p class="mb-0">Resueltos Este Mes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>-->

    <div class="row">
        <!-- Expedientes Prioritarios -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Expedientes Prioritarios</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Asunto</th>
                                    <th>Prioridad</th>
                                    <th>Días Restantes</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expedientesPrioritarios as $expediente)
                                <tr>
                                    <td><strong>{{ $expediente->codigo_expediente }}</strong></td>
                                    <td>{{ Str::limit($expediente->asunto, 30) }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $expediente->prioridad == 'urgente' ? 'danger' : 
                                            ($expediente->prioridad == 'alta' ? 'warning' : 'info') 
                                        }}">
                                            {{ ucfirst($expediente->prioridad) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $derivacion = $expediente->derivaciones->first();
                                            $diasRestantes = ($derivacion && $derivacion->fecha_limite) ? $derivacion->fecha_limite->diffInDays(now(), false) : 0;
                                        @endphp
                                        <span class="text-{{ $diasRestantes < 0 ? 'danger' : ($diasRestantes <= 2 ? 'warning' : 'success') }}">
                                            {{ abs($diasRestantes) }} días {{ $diasRestantes < 0 ? 'vencido' : 'restantes' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $expediente->estado == 'derivado' ? 'warning' : 
                                            ($expediente->estado == 'en_proceso' ? 'info' : 'success') 
                                        }}">
                                            {{ $expediente->getEstadoFormateado() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('funcionario.show', $expediente) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($expediente->estado == 'derivado')
                                            <button class="btn btn-outline-success btn-sm" 
                                                    onclick="recibirExpediente({{ $expediente->id }})">
                                                <i class="fas fa-hand-paper"></i>
                                            </button>
                                            @elseif($expediente->estado == 'en_proceso')
                                            <a href="{{ route('funcionario.procesar', $expediente) }}" 
                                               class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-cogs"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas y Estadísticas -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('funcionario.index') }}?estado=derivado" class="btn btn-warning btn-sm">
                            <i class="fas fa-inbox"></i> Ver Expedientes por Recibir
                        </a>
                        <a href="{{ route('funcionario.index') }}?estado=en_proceso" class="btn btn-info btn-sm">
                            <i class="fas fa-cogs"></i> Ver En Proceso
                        </a>
                        <a href="{{ route('funcionario.index') }}?vencidos=1" class="btn btn-danger btn-sm">
                            <i class="fas fa-exclamation-triangle"></i> Ver Vencidos
                        </a>
                        <a href="{{ route('funcionario.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-list"></i> Todos Mis Expedientes
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Mi Rendimiento</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-success">{{ $rendimiento['resueltos_mes'] }}</h4>
                            <small>Resueltos Este Mes</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info">{{ number_format($rendimiento['tiempo_promedio'], 1) }}</h4>
                            <small>Días Promedio</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary">{{ $rendimiento['total_asignados'] }}</h4>
                            <small>Total Asignados</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning">{{ $rendimiento['pendientes'] }}</h4>
                            <small>Pendientes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    @if($alertas->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Alertas y Recordatorios</h6>
                </div>
                <div class="card-body">
                    @foreach($alertas as $alerta)
                    <div class="alert alert-{{ $alerta->tipo }} alert-dismissible fade show">
                        <strong>{{ $alerta->titulo }}:</strong> {{ $alerta->mensaje }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function recibirExpediente(id) {
    Swal.fire({
        title: '¿Recibir Expediente?',
        text: '¿Confirmas que recibes este expediente para su procesamiento?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check"></i> Sí, recibir',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Procesando...',
                text: 'Recibiendo expediente',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading()
                }
            });
            
            fetch(`${window.APP_URL}/funcionario/expedientes/${id}/recibir`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            }).then(response => {
                if (response.ok) {
                    Swal.fire({
                        title: '¡Expediente Recibido!',
                        text: 'El expediente ha sido asignado correctamente',
                        icon: 'success',
                        confirmButtonColor: '#28a745',
                        confirmButtonText: 'Continuar'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo recibir el expediente. Intenta nuevamente.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }).catch(error => {
                Swal.fire({
                    title: 'Error de Conexión',
                    text: 'Verifica tu conexión a internet e intenta nuevamente.',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}
</script>
@endsection