@extends('layouts.app')

@section('title', 'Mesa de Partes - Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row g-3">
        <!-- Acciones Rapidas -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2">
                    <h6 class="card-title mb-0"><i class="fas fa-bolt text-warning me-1"></i>Acciones Rapidas</h6>
                </div>
                <div class="card-body p-2">
                    <div class="d-grid gap-1">
                        <a href="{{ route('mesa-partes.registrar') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus me-1"></i>Registrar Documento
                        </a>
                        <a href="{{ route('mesa-partes.expedientes-virtuales') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-globe me-1"></i>Expedientes Virtuales
                            @php
                                $virtualesPendientes = \App\Models\Expediente::where('canal', 'virtual')
                                    ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'recepcionado'))
                                    ->count();
                            @endphp
                            @if($virtualesPendientes > 0)
                                <span class="badge bg-danger ms-1">{{ $virtualesPendientes }}</span>
                            @endif
                        </a>
                        <a href="{{ route('mesa-partes.index') }}?estado=Registrado" class="btn btn-warning btn-sm">
                            <i class="fas fa-tags me-1"></i>Clasificar Pendientes
                        </a>
                        <a href="{{ route('mesa-partes.index') }}?estado=Clasificado" class="btn btn-info btn-sm">
                            <i class="fas fa-share me-1"></i>Derivar Pendientes
                        </a>
                        <a href="{{ route('mesa-partes.monitoreo') }}" class="btn btn-danger btn-sm">
                            <i class="fas fa-clock me-1"></i>Monitoreo de Plazos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expedientes Recientes -->
        <div class="col-md-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fas fa-clock text-primary me-1"></i>Expedientes Recientes</h6>
                    <a href="{{ route('mesa-partes.index') }}" class="btn btn-outline-primary btn-sm">Ver todos</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="font-size: 0.82rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Codigo</th>
                                    <th>Remitente</th>
                                    <th>Asunto</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expedientesRecientes as $expediente)
                                <tr>
                                    <td><strong class="text-primary">{{ $expediente->codigo_expediente }}</strong></td>
                                    <td>{{ Str::limit($expediente->remitente ?? $expediente->ciudadano->name ?? 'N/A', 20) }}</td>
                                    <td>{{ Str::limit($expediente->asunto, 30) }}</td>
                                    <td>
                                        <span class="badge bg-{{
                                            $expediente->estado == 'Registrado' ? 'secondary' :
                                            ($expediente->estado == 'Clasificado' ? 'warning' : 'info')
                                        }}" style="font-size: 0.7rem;">
                                            {{ $expediente->estado }}
                                        </span>
                                    </td>
                                    <td style="font-size: 0.75rem;">{{ $expediente->created_at->format('d/m H:i') }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('mesa-partes.show', $expediente) }}"
                                               class="btn btn-outline-primary btn-sm py-0 px-1" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($expediente->estado == 'Registrado')
                                            <a href="{{ route('mesa-partes.clasificar', $expediente) }}"
                                               class="btn btn-outline-warning btn-sm py-0 px-1" title="Clasificar">
                                                <i class="fas fa-tags"></i>
                                            </a>
                                            @elseif($expediente->estado == 'Clasificado')
                                            <a href="{{ route('mesa-partes.derivar', $expediente) }}"
                                               class="btn btn-outline-info btn-sm py-0 px-1" title="Derivar">
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

    <!-- Alertas -->
    <div class="row mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2">
                    <h6 class="card-title mb-0"><i class="fas fa-bell text-danger me-1"></i>Alertas y Recordatorios</h6>
                </div>
                <div class="card-body py-2">
                    @if($alertas->count() > 0)
                        @foreach($alertas as $alerta)
                        <div class="alert alert-{{ $alerta->tipo }} alert-dismissible fade show py-2 mb-1" style="font-size: 0.82rem;">
                            <strong>{{ $alerta->titulo }}:</strong> {{ $alerta->mensaje }}
                            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                        </div>
                        @endforeach
                    @else
                        <div class="alert alert-success py-2 mb-0" style="font-size: 0.82rem;">
                            <i class="fas fa-check-circle me-1"></i> No hay alertas pendientes. Todo esta al dia.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
