@extends('layouts.app')

@section('title', 'Gestión de Documentos')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-file-pdf"></i> Gestión de Documentos</h4>
        </div>
        <div class="card-body">
            @if(auth()->user()->role?->nombre === 'Administrador')
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Como Administrador, tiene acceso a todos los documentos del sistema.
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Documento</th>
                            <th>Expediente</th>
                            <th>Área</th>
                            <th>Tipo</th>
                            <th>Tamaño</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documentos as $documento)
                        <tr>
                            <td>
                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                {{ Str::limit($documento->nombre, 40) }}
                            </td>
                            <td>
                                <a href="{{ route('admin.expedientes.show', $documento->expediente?->id_expediente) }}">
                                    {{ $documento->expediente?->codigo_expediente ?? 'N/A' }}
                                </a>
                            </td>
                            <td>{{ $documento->expediente?->area?->nombre ?? 'Sin área' }}</td>
                            <td>{{ $documento->tipo ?? 'Adjunto' }}</td>
                            <td>{{ number_format(($documento->tamaño_archivo ?? 0) / 1024, 2) }} KB</td>
                            <td>{{ $documento->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('documentos.visualizar', $documento->id_documento) }}" class="btn btn-sm btn-primary" target="_blank" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('documentos.descargar', $documento->id_documento) }}" class="btn btn-sm btn-success" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @if(auth()->user()->role?->nombre === 'Administrador')
                                    <form action="{{ route('documentos.destroy', $documento->id_documento) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este documento?')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No hay documentos disponibles</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
