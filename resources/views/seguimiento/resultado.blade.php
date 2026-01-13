@extends('layouts.app')

@section('title', 'Resultado de Consulta')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        Expediente {{ $expediente->codigo_expediente }}
                    </h4>
                    <a href="{{ route('seguimiento.consulta-form') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Nueva Consulta
                    </a>
                </div>

                <div class="card-body">
                    <!-- Estado Actual -->
                    <div class="alert alert-{{
                        $expediente->estado == 'recepcionado' ? 'info' :
                        ($expediente->estado == 'registrado' ? 'info' :
                        ($expediente->estado == 'clasificado' ? 'info' :
                        ($expediente->estado == 'derivado' ? 'primary' :
                        ($expediente->estado == 'en_proceso' ? 'warning' :
                        ($expediente->estado == 'observado' ? 'danger' :
                        ($expediente->estado == 'resuelto' ? 'success' : 'secondary'))))))
                    }}">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-2">
                                    <i class="fas fa-{{
                                        $expediente->estado == 'recepcionado' ? 'inbox' :
                                        ($expediente->estado == 'registrado' ? 'edit' :
                                        ($expediente->estado == 'clasificado' ? 'tags' :
                                        ($expediente->estado == 'derivado' ? 'share' :
                                        ($expediente->estado == 'en_proceso' ? 'cogs' :
                                        ($expediente->estado == 'observado' ? 'exclamation-triangle' :
                                        ($expediente->estado == 'resuelto' ? 'check-circle' : 'archive'))))))
                                    }} me-2"></i>
                                    Estado: <strong>{{ strtoupper(str_replace('_', ' ', $expediente->estado)) }}</strong>
                                </h5>
                                <p class="mb-0">
                                    @switch($expediente->estado)
                                        @case('recepcionado')
                                            Su expediente ha sido recepcionado y está en proceso de registro.
                                            @break
                                        @case('registrado')
                                            Su expediente ha sido registrado en el sistema.
                                            @break
                                        @case('clasificado')
                                            Su expediente ha sido clasificado y está listo para ser derivado.
                                            @break
                                        @case('derivado')
                                            Su expediente ha sido enviado al área correspondiente.
                                            @break
                                        @case('en_proceso')
                                            Su expediente está siendo atendido por un funcionario.
                                            @break
                                        @case('observado')
                                            Su expediente tiene observaciones que requieren subsanación.
                                            @break
                                        @case('resuelto')
                                            Su expediente ha sido resuelto. Puede recoger la respuesta.
                                            @break
                                        @case('notificado')
                                            Se ha notificado la resolución de su expediente.
                                            @break
                                        @case('archivado')
                                            Su expediente ha sido archivado. El trámite está completo.
                                            @break
                                        @default
                                            Su expediente está en proceso.
                                    @endswitch
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                @if($expediente->area)
                                    <strong>Área:</strong><br>
                                    <span class="badge bg-dark fs-6">{{ $expediente->area->nombre }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Información del Expediente -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">
                                <i class="fas fa-user me-2"></i>
                                Información del Solicitante
                            </h6>
                            <table class="table table-sm table-borderless">
                                @if($expediente->persona)
                                <tr>
                                    <td width="40%"><strong>Tipo Persona:</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $expediente->persona->tipo_persona }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Documento:</strong></td>
                                    <td>
                                        <span class="badge bg-info">{{ $expediente->persona->tipo_documento }}</span>
                                        {{ $expediente->persona->numero_documento }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Nombre:</strong></td>
                                    <td>{{ $expediente->persona->nombre_completo }}</td>
                                </tr>
                                @if($expediente->persona->tipo_persona == 'JURIDICA' && $expediente->persona->representante_legal)
                                <tr>
                                    <td><strong>Representante:</strong></td>
                                    <td>{{ $expediente->persona->representante_legal }}</td>
                                </tr>
                                @endif
                                @if($expediente->persona->telefono)
                                <tr>
                                    <td><strong>Teléfono:</strong></td>
                                    <td>{{ $expediente->persona->telefono }}</td>
                                </tr>
                                @endif
                                @if($expediente->persona->email)
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $expediente->persona->email }}</td>
                                </tr>
                                @endif
                                @endif
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">
                                <i class="fas fa-folder-open me-2"></i>
                                Información del Trámite
                            </h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Código:</strong></td>
                                    <td><strong class="text-primary">{{ $expediente->codigo_expediente }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha Registro:</strong></td>
                                    <td>{{ $expediente->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tipo Trámite:</strong></td>
                                    <td><span class="badge bg-info">{{ $expediente->tipoTramite->nombre ?? 'N/A' }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Canal:</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($expediente->canal) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Prioridad:</strong></td>
                                    <td>
                                        <span class="badge bg-{{
                                            $expediente->prioridad == 'urgente' ? 'danger' :
                                            ($expediente->prioridad == 'alta' ? 'warning' :
                                            ($expediente->prioridad == 'normal' ? 'primary' : 'secondary'))
                                        }}">
                                            {{ ucfirst($expediente->prioridad) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Asunto -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2">
                            <i class="fas fa-align-left me-2"></i>
                            Asunto del Trámite
                        </h6>
                        <div class="alert alert-light mb-0">
                            {{ $expediente->asunto }}
                        </div>
                    </div>

                    <!-- Derivaciones -->
                    @if($expediente->derivaciones && $expediente->derivaciones->count() > 0)
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2">
                            <i class="fas fa-route me-2"></i>
                            Ruta del Expediente
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Área</th>
                                        <th>Fecha Derivación</th>
                                        <th>Plazo</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expediente->derivaciones->sortBy('fecha_derivacion') as $derivacion)
                                    <tr>
                                        <td>
                                            <strong>{{ $derivacion->areaDestino->nombre ?? 'N/A' }}</strong>
                                        </td>
                                        <td>{{ $derivacion->fecha_derivacion->format('d/m/Y H:i') }}</td>
                                        <td>{{ $derivacion->plazo_dias }} días</td>
                                        <td>
                                            <span class="badge bg-{{
                                                $derivacion->estado == 'pendiente' ? 'warning' :
                                                ($derivacion->estado == 'atendido' ? 'success' : 'secondary')
                                            }}">
                                                {{ ucfirst($derivacion->estado) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Historial -->
                    @if($expediente->historial && $expediente->historial->count() > 0)
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2">
                            <i class="fas fa-history me-2"></i>
                            Historial de Movimientos
                        </h6>
                        <div class="timeline">
                            @foreach($expediente->historial->sortByDesc('created_at') as $historial)
                            <div class="d-flex mb-3 pb-3 border-bottom">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle p-2 text-white text-center" style="width: 45px; height: 45px;">
                                        <i class="fas fa-circle-notch"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-1">{{ $historial->descripcion }}</p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $historial->created_at->format('d/m/Y H:i') }}
                                        @if($historial->usuario)
                                        | <i class="fas fa-user me-1"></i>{{ $historial->usuario->name }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Información de Contacto -->
                    <div class="alert alert-info mb-0">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Información Importante
                        </h6>
                        <ul class="mb-0">
                            <li>Conserve el código <strong>{{ $expediente->codigo_expediente }}</strong> y su DNI para futuras consultas</li>
                            @if($expediente->estado == 'observado')
                            <li class="text-danger"><strong>ATENCIÓN:</strong> Su expediente tiene observaciones que requieren subsanación. Si es ciudadano registrado, ingrese a su cuenta para responder.</li>
                            @endif
                            @if($expediente->estado == 'resuelto')
                            <li class="text-success"><strong>Su trámite ha sido resuelto.</strong> Puede acercarse a nuestras oficinas para recoger la respuesta.</li>
                            @endif
                            <li>Para mayor información, puede acercarse a las oficinas de la DRTC Apurímac o llamar al teléfono de consultas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
