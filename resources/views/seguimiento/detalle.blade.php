<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expediente {{ $expediente->codigo_expediente }} - Mesa de Partes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>{{ __('Expediente') }} {{ $expediente->codigo_expediente }}</h4>
                            <a href="{{ route('seguimiento.form') }}" class="btn btn-light btn-sm">
                                Nueva Consulta
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Información General -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Información del Expediente</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Código:</strong></td>
                                        <td>{{ $expediente->codigo_expediente }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Fecha Registro:</strong></td>
                                        <td>{{ $expediente->fecha_registro->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tipo Trámite:</strong></td>
                                        <td>{{ $expediente->tipoTramite->nombre }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Canal:</strong></td>
                                        <td>{{ ucfirst($expediente->canal) }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Estado Actual</h6>
                                <div class="alert alert-{{ $expediente->estado == 'resuelto' ? 'success' : ($expediente->estado == 'en_proceso' ? 'warning' : 'info') }}">
                                    <h5 class="mb-1">
                                        <span class="badge bg-{{ $expediente->estado == 'resuelto' ? 'success' : ($expediente->estado == 'en_proceso' ? 'warning' : 'primary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $expediente->estado)) }}
                                        </span>
                                    </h5>
                                    <p class="mb-0">
                                        @switch($expediente->estado)
                                            @case('pendiente')
                                                Su expediente está pendiente de clasificación
                                                @break
                                            @case('derivado')
                                                Su expediente ha sido derivado al área correspondiente
                                                @break
                                            @case('en_proceso')
                                                Su expediente está siendo atendido
                                                @break
                                            @case('resuelto')
                                                Su expediente ha sido resuelto
                                                @break
                                            @case('archivado')
                                                Su expediente ha sido archivado
                                                @break
                                        @endswitch
                                    </p>
                                </div>

                                @if($expediente->derivacionActual())
                                <div class="mt-3">
                                    <strong>Área Actual:</strong> {{ $expediente->derivacionActual()->area->nombre ?? 'Mesa de Partes' }}<br>
                                    <strong>Plazo:</strong> {{ $expediente->derivacionActual()->plazo_dias ?? $expediente->tipoTramite->plazo_dias }} días hábiles
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Asunto -->
                        <div class="mb-4">
                            <h6>Asunto del Trámite</h6>
                            <div class="alert alert-light">
                                {{ $expediente->asunto }}
                            </div>
                        </div>

                        <!-- Observaciones -->
                        @if($expediente->observaciones->count() > 0)
                        <div class="mb-4">
                            <h6>Observaciones</h6>
                            @foreach($expediente->observaciones as $observacion)
                            <div class="alert alert-{{ $observacion->tipo == 'observacion' ? 'warning' : 'info' }}">
                                <strong>{{ ucfirst($observacion->tipo) }}:</strong> {{ $observacion->descripcion }}
                                @if($observacion->fecha_limite)
                                <br><small>Fecha límite para subsanar: {{ $observacion->fecha_limite }}</small>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <!-- Historial -->
                        <div class="mb-4">
                            <h6>Historial del Expediente</h6>
                            <div class="timeline">
                                @forelse($expediente->historial->sortByDesc('fecha') as $historial)
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary rounded-circle p-2" style="width: 40px; height: 40px;"></div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ $historial->descripcion }}</h6>
                                        <small class="text-muted">
                                            {{ $historial->fecha->format('d/m/Y H:i') }} - 
                                            {{ $historial->usuario->name }}
                                        </small>
                                    </div>
                                </div>
                                @empty
                                <p class="text-muted">No hay historial disponible.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="alert alert-info">
                            <h6>Información Importante</h6>
                            <ul class="mb-0">
                                <li>Conserve el código de expediente para futuras consultas</li>
                                <li>Los plazos se cuentan en días hábiles</li>
                                <li>Para consultas adicionales, acérquese a nuestras oficinas</li>
                                @if($expediente->estado == 'resuelto')
                                <li><strong>Su trámite ha sido resuelto. Puede recoger la respuesta en nuestras oficinas.</strong></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>