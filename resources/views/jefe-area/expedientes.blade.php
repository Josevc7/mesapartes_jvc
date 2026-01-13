@extends('layouts.app')

@section('title', 'Supervisión de Expedientes')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Supervisión de Expedientes</h2>
                <span class="badge bg-info">Área: {{ auth()->user()->area->nombre ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <select name="estado" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="Derivado" {{ request('estado') == 'Derivado' ? 'selected' : '' }}>Derivado</option>
                                <option value="En Proceso" {{ request('estado') == 'En Proceso' ? 'selected' : '' }}>En Proceso</option>
                                <option value="Resuelto" {{ request('estado') == 'Resuelto' ? 'selected' : '' }}>Resuelto</option>
                                <option value="Aprobado" {{ request('estado') == 'Aprobado' ? 'selected' : '' }}>Aprobado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="funcionario" class="form-select">
                                <option value="">Todos los funcionarios</option>
                                @foreach($funcionarios as $funcionario)
                                    <option value="{{ $funcionario->id }}" {{ request('funcionario') == $funcionario->id ? 'selected' : '' }}>
                                        {{ $funcionario->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <a href="{{ route('jefe-area.expedientes') }}" class="btn btn-secondary">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Expedientes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Expedientes del Área ({{ $expedientes->total() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Asunto</th>
                                    <th>Funcionario</th>
                                    <th>Estado</th>
                                    <th>Días Transcurridos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expedientes as $expediente)
                                <tr class="{{ floor($expediente->created_at->diffInDays()) > 15 ? 'table-warning' : '' }}">
                                    <td>
                                        <strong>{{ $expediente->codigo_expediente }}</strong>
                                        @if($expediente->prioridad == 'Urgente')
                                            <span class="badge bg-danger">Urgente</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($expediente->asunto, 40) }}</td>
                                    <td>{{ $expediente->funcionarioAsignado->name ?? 'Sin asignar' }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $expediente->estado == 'Resuelto' ? 'success' : 
                                            ($expediente->estado == 'En Proceso' ? 'info' : 'warning') 
                                        }}">
                                            {{ $expediente->estado }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ floor($expediente->created_at->diffInDays()) }} días
                                        @if(floor($expediente->created_at->diffInDays()) > 15)
                                            <i class="fas fa-exclamation-triangle text-warning"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('funcionario.show', $expediente) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                        
                                        @if($expediente->estado == 'Resuelto')
                                            <button type="button" class="btn btn-sm btn-success"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#aprobarModal{{ $expediente->id_expediente }}">
                                                Aprobar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rechazarModal{{ $expediente->id_expediente }}">
                                                Rechazar
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Modal Aprobar -->
                                <div class="modal fade" id="aprobarModal{{ $expediente->id_expediente }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Aprobar Expediente</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>¿Está seguro de aprobar el expediente <strong>{{ $expediente->codigo_expediente }}</strong>?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <form method="POST" action="{{ route('jefe-area.aprobar', $expediente) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">Aprobar</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Rechazar -->
                                <div class="modal fade" id="rechazarModal{{ $expediente->id_expediente }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Rechazar Expediente</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('jefe-area.rechazar', $expediente) }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Motivo del Rechazo</label>
                                                        <textarea class="form-control" name="motivo_rechazo" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-danger">Rechazar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No hay expedientes en esta área</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $expedientes->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection