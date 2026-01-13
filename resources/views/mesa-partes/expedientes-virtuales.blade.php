@extends('layouts.app')

@section('title', 'Expedientes Virtuales Pendientes')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>
                        <i class="fas fa-globe text-primary me-2"></i>
                        Expedientes Virtuales Pendientes
                    </h2>
                    <p class="text-muted mb-0">Expedientes ingresados por ciudadanos que requieren clasificación y derivación</p>
                </div>
                <div>
                    <a href="{{ route('mesa-partes.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
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
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ $expedientes->count() }} Expediente(s) Pendiente(s) de Clasificación
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Fecha Registro</th>
                                    <th>Ciudadano</th>
                                    <th>Tipo Trámite</th>
                                    <th>Asunto</th>
                                    <th>Documentos</th>
                                    <th width="250">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expedientes as $expediente)
                                <tr>
                                    <td>
                                        <strong class="text-primary">{{ $expediente->codigo_expediente }}</strong>
                                        <br>
                                        <span class="badge bg-info">
                                            <i class="fas fa-globe"></i> Virtual
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $expediente->created_at->format('d/m/Y') }}<br>
                                            {{ $expediente->created_at->format('H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($expediente->persona)
                                            <strong>{{ $expediente->persona->nombre_completo }}</strong><br>
                                            <small class="text-muted">
                                                {{ $expediente->persona->tipo_documento }}: {{ $expediente->persona->numero_documento }}
                                            </small>
                                        @else
                                            {{ $expediente->remitente ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $expediente->tipoTramite->nombre ?? 'Sin tipo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span title="{{ $expediente->asunto }}">
                                            {{ Str::limit($expediente->asunto, 40) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">
                                            <i class="fas fa-file-pdf"></i> {{ $expediente->documentos->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('mesa-partes.show', $expediente) }}"
                                               class="btn btn-outline-info"
                                               title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('mesa-partes.clasificar-virtual', $expediente) }}"
                                               class="btn btn-success"
                                               title="Clasificar y Derivar">
                                                <i class="fas fa-share"></i> Clasificar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $expedientes->links() }}
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                    <h4>No hay expedientes virtuales pendientes</h4>
                    <p class="text-muted">Todos los expedientes ingresados por ciudadanos han sido procesados</p>
                    <a href="{{ route('mesa-partes.dashboard') }}" class="btn btn-primary mt-3">
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
