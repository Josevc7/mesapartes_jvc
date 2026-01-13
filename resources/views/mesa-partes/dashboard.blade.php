@extends('layouts.app')

@section('title', 'Mesa de Partes - Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Resumen del Día  las estadisticas de tramite -->
   <!-- <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                   <div class="d-flex justify-content-between">
                        <div>
                           <h4>{{ $estadisticas['registrados_hoy'] }}</h4>
                             <p class="mb-0">Registrados Hoy</p>
                        </div>
                        <div class="align-self-center">
                        <i class="fas fa-file-plus fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['pendientes_clasificar'] }}</h4>
                            <p class="mb-0">Pendientes Clasificar</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tags fa-2x"></i>
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
                            <h4>{{ $estadisticas['pendientes_derivar'] }}</h4>
                            <p class="mb-0">Pendientes Derivar</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-share fa-2x"></i>
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
    </div>-->

    <div class="row">
        <!-- Acciones Rápidas -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('mesa-partes.registrar') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Registrar Documento
                        </a>
                        <a href="{{ route('mesa-partes.expedientes-virtuales') }}" class="btn btn-primary">
                            <i class="fas fa-globe"></i> Expedientes Virtuales
                            @php
                                $virtualesPendientes = \App\Models\Expediente::where('canal', 'virtual')
                                    ->where('estado', 'recepcionado')
                                    ->count();
                            @endphp
                            @if($virtualesPendientes > 0)
                                <span class="badge bg-danger">{{ $virtualesPendientes }}</span>
                            @endif
                        </a>
                        <a href="{{ route('mesa-partes.index') }}?estado=Registrado" class="btn btn-warning">
                            <i class="fas fa-tags"></i> Clasificar Pendientes
                        </a>
                        <a href="{{ route('mesa-partes.index') }}?estado=Clasificado" class="btn btn-info">
                            <i class="fas fa-share"></i> Derivar Pendientes
                        </a>
                        <a href="{{ route('mesa-partes.monitoreo') }}" class="btn btn-danger">
                            <i class="fas fa-clock"></i> Monitoreo de Plazos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expedientes Recientes -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Expedientes Recientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Remitente</th>
                                    <th>Asunto</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expedientesRecientes as $expediente)
                                <tr>
                                    <td><strong>{{ $expediente->codigo_expediente }}</strong></td>
                                    <td>{{ $expediente->remitente ?? $expediente->ciudadano->name ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($expediente->asunto, 30) }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $expediente->estado == 'Registrado' ? 'secondary' : 
                                            ($expediente->estado == 'Clasificado' ? 'warning' : 'info') 
                                        }}">
                                            {{ $expediente->estado }}
                                        </span>
                                    </td>
                                    <td>{{ $expediente->created_at->format('d/m H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('mesa-partes.show', $expediente) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($expediente->estado == 'Registrado')
                                            <a href="{{ route('mesa-partes.clasificar', $expediente) }}" 
                                               class="btn btn-outline-warning btn-sm">
                                                <i class="fas fa-tags"></i>
                                            </a>
                                            @elseif($expediente->estado == 'Clasificado')
                                            <a href="{{ route('mesa-partes.derivar', $expediente) }}" 
                                               class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-share"></i>
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
    </div>

    <!-- Alertas y Recordatorios -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Alertas y Recordatorios</h5>
                </div>
                <div class="card-body">
                    @if($alertas->count() > 0)
                        @foreach($alertas as $alerta)
                        <div class="alert alert-{{ $alerta->tipo }} alert-dismissible fade show">
                            <strong>{{ $alerta->titulo }}:</strong> {{ $alerta->mensaje }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endforeach
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> No hay alertas pendientes. Todo está al día.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection