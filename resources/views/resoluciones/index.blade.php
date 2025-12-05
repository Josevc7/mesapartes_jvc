@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Resoluciones</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Expediente</th>
                                    <th>Tipo</th>
                                    <th>Funcionario</th>
                                    <th>Fecha</th>
                                    <th>Notificado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($resoluciones as $resolucion)
                                <tr>
                                    <td>{{ $resolucion->numero_resolucion }}</td>
                                    <td>{{ $resolucion->expediente->codigo_expediente }}</td>
                                    <td>
                                        <span class="badge badge-{{ $resolucion->tipo_resolucion === 'aprobado' ? 'success' : ($resolucion->tipo_resolucion === 'rechazado' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($resolucion->tipo_resolucion) }}
                                        </span>
                                    </td>
                                    <td>{{ $resolucion->funcionarioResolutor->name }}</td>
                                    <td>{{ $resolucion->fecha_resolucion->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($resolucion->notificado)
                                            <span class="badge badge-success">Sí</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('resoluciones.show', $resolucion) }}" class="btn btn-sm btn-info">Ver</a>
                                        @if(!$resolucion->notificado)
                                            <form action="{{ route('resoluciones.notificar', $resolucion) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-warning">Notificar</button>
                                            </form>
                                        @endif
                                        @if($resolucion->ruta_documento_resolucion)
                                            <a href="{{ route('resoluciones.descargar', $resolucion) }}" class="btn btn-sm btn-secondary">PDF</a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No hay resoluciones registradas</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $resoluciones->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection