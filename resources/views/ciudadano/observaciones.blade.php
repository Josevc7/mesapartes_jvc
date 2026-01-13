@extends('layouts.app')

@section('title', 'Observaciones Pendientes')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Observaciones Pendientes
                    </h2>
                    <p class="text-muted mb-0">Expedientes que requieren su atención para subsanar información</p>
                </div>
                <a href="{{ route('ciudadano.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($expedientes->count() > 0)
                <div class="row">
                    @foreach($expedientes as $expediente)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card border-warning h-100">
                            <div class="card-header bg-warning bg-opacity-10">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-file-alt me-2"></i>
                                    {{ $expediente->codigo_expediente }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">
                                    <strong>Asunto:</strong><br>
                                    {{ Str::limit($expediente->asunto, 80) }}
                                </p>

                                <div class="mb-2">
                                    <span class="badge bg-info">
                                        {{ $expediente->tipoTramite->nombre ?? 'Sin tipo' }}
                                    </span>
                                </div>

                                <p class="text-muted small mb-2">
                                    <i class="fas fa-building me-1"></i>
                                    {{ $expediente->area->nombre ?? 'Sin área' }}
                                </p>

                                <p class="text-muted small mb-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    Registrado: {{ $expediente->created_at->format('d/m/Y') }}
                                </p>

                                @php
                                    $observacionesPendientes = $expediente->observaciones->where('estado', 'pendiente')->count();
                                    $observacionMasReciente = $expediente->observaciones->first();
                                @endphp

                                @if($observacionMasReciente)
                                <div class="alert alert-warning mb-2">
                                    <small>
                                        <strong>Observación:</strong><br>
                                        {{ Str::limit($observacionMasReciente->descripcion, 100) }}
                                    </small>
                                </div>

                                @if($observacionMasReciente->fecha_limite)
                                <p class="text-danger small">
                                    <i class="fas fa-clock me-1"></i>
                                    <strong>Plazo límite:</strong>
                                    {{ \Carbon\Carbon::parse($observacionMasReciente->fecha_limite)->format('d/m/Y') }}
                                    ({{ \Carbon\Carbon::parse($observacionMasReciente->fecha_limite)->diffForHumans() }})
                                </p>
                                @endif
                                @endif
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="{{ route('ciudadano.ver-observacion', $expediente) }}"
                                   class="btn btn-warning w-100">
                                    <i class="fas fa-reply me-2"></i>
                                    Responder Observación
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h4>No tiene observaciones pendientes</h4>
                        <p class="text-muted">Todos sus expedientes están en orden</p>
                        <a href="{{ route('ciudadano.dashboard') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-home me-2"></i>
                            Ir al Dashboard
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
