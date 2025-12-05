@extends('layouts.app')

@section('title', 'Expedientes Asignados')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Expedientes Asignados</h2>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Ciudadano</th>
                                    <th>Asunto</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Fecha Asignación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expedientes as $expediente)
                                <tr>
                                    <td>{{ $expediente->codigo }}</td>
                                    <td>{{ $expediente->ciudadano->name ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($expediente->asunto, 50) }}</td>
                                    <td>{{ $expediente->tipoTramite->nombre ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $expediente->estado == 'Derivado' ? 'warning' : 'info' }}">
                                            {{ $expediente->estado }}
                                        </span>
                                    </td>
                                    <td>{{ $expediente->updated_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('funcionario.show', $expediente) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                        @if($expediente->estado == 'Derivado')
                                            <form method="POST" action="{{ route('funcionario.recibir', $expediente) }}" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-success">Recibir</button>
                                            </form>
                                        @endif
                                        @if($expediente->estado == 'En Proceso')
                                            <a href="{{ route('funcionario.procesar', $expediente) }}" class="btn btn-sm btn-warning">Procesar</a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No tienes expedientes asignados</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $expedientes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection