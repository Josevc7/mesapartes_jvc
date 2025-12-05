@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Resolución {{ $resolucion->numero_resolucion }}</h3>
                    <div>
                        @if(!$resolucion->notificado)
                            <form action="{{ route('resoluciones.notificar', $resolucion) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-warning">Notificar</button>
                            </form>
                        @endif
                        @if($resolucion->ruta_documento_resolucion)
                            <a href="{{ route('resoluciones.descargar', $resolucion) }}" class="btn btn-secondary">Descargar PDF</a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Información del Expediente</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Código:</strong></td>
                                    <td>{{ $resolucion->expediente->codigo_expediente }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Asunto:</strong></td>
                                    <td>{{ $resolucion->expediente->asunto }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tipo de Trámite:</strong></td>
                                    <td>{{ $resolucion->expediente->tipoTramite->nombre }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ciudadano:</strong></td>
                                    <td>{{ $resolucion->expediente->ciudadano->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Información de la Resolución</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Número:</strong></td>
                                    <td>{{ $resolucion->numero_resolucion }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tipo:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $resolucion->tipo_resolucion === 'aprobado' ? 'success' : ($resolucion->tipo_resolucion === 'rechazado' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($resolucion->tipo_resolucion) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Funcionario:</strong></td>
                                    <td>{{ $resolucion->funcionarioResolutor->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha Resolución:</strong></td>
                                    <td>{{ $resolucion->fecha_resolucion->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Notificado:</strong></td>
                                    <td>
                                        @if($resolucion->notificado)
                                            <span class="badge badge-success">Sí - {{ $resolucion->fecha_notificacion->format('d/m/Y H:i') }}</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($resolucion->fundamento_legal)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Fundamento Legal</h5>
                            <div class="card">
                                <div class="card-body">
                                    {{ $resolucion->fundamento_legal }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($resolucion->observaciones)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Observaciones</h5>
                            <div class="card">
                                <div class="card-body">
                                    {{ $resolucion->observaciones }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('resoluciones.index') }}" class="btn btn-secondary">Volver</a>
                    <a href="{{ route('expedientes.show', $resolucion->expediente) }}" class="btn btn-primary">Ver Expediente</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection