@extends('layouts.app')

@section('title', 'Mis Expedientes')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Mis Expedientes</h2>
                <a href="{{ route('ciudadano.registrar-expediente') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Expediente
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Todos mis Expedientes ({{ $expedientes->total() }})</h5>
                </div>
                <div class="card-body">
                    @if($expedientes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Asunto</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Área</th>
                                        <th>Fecha</th>
                                        <th>Documentos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expedientes as $expediente)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $expediente->codigo_expediente }}</strong>
                                            @if($expediente->prioridad == 'Urgente')
                                                <br><span class="badge bg-danger">Urgente</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ Str::limit($expediente->asunto, 30) }}</strong>
                                            @if($expediente->observaciones_funcionario)
                                                <br><small class="text-muted">Con observaciones</small>
                                            @endif
                                        </td>
                                        <td>{{ $expediente->tipoTramite->nombre ?? 'Sin clasificar' }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $expediente->estado == 'Resuelto' ? 'success' : 
                                                ($expediente->estado == 'Observado' ? 'warning' : 
                                                ($expediente->estado == 'En Proceso' ? 'info' : 'secondary')) 
                                            }}">
                                                {{ $expediente->estado }}
                                            </span>
                                            @if($expediente->estado == 'Observado')
                                                <br><small class="text-warning">Requiere atención</small>
                                            @endif
                                        </td>
                                        <td>{{ $expediente->area->nombre ?? 'Sin asignar' }}</td>
                                        <td>
                                            {{ $expediente->created_at->format('d/m/Y') }}
                                            <br><small class="text-muted">{{ $expediente->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $expediente->documentos->count() }}</span>
                                            @if($expediente->documentos->where('tipo', 'Respuesta')->count() > 0)
                                                <br><small class="text-success">Con respuesta</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical btn-group-sm">
                                                <a href="{{ route('ciudadano.seguimiento', $expediente->codigo_expediente) }}" 
                                                   class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                                <a href="{{ route('ciudadano.acuse-recibo', $expediente->codigo_expediente) }}"
                                                   class="btn btn-outline-info" title="DESCARGAR CARGO">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                @if($expediente->documentos->where('tipo', 'Respuesta')->count() > 0)
                                                    <button class="btn btn-outline-success btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#documentosModal{{ $expediente->id }}">
                                                        <i class="fas fa-file-pdf"></i> Respuesta
                                                    </button>
                                                @endif
                                                @if($expediente->puedeEliminar(auth()->user()))
                                                    <button class="btn btn-outline-danger btn-sm" 
                                                            onclick="eliminarExpediente({{ $expediente->id }}, '{{ $expediente->codigo_expediente }}')">
                                                        <i class="fas fa-trash"></i> Eliminar
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal Documentos -->
                                    @if($expediente->documentos->where('tipo', 'Respuesta')->count() > 0)
                                    <div class="modal fade" id="documentosModal{{ $expediente->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Documentos de Respuesta</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @foreach($expediente->documentos->where('tipo', 'Respuesta') as $documento)
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div>
                                                            <strong>{{ $documento->nombre }}</strong>
                                                            <br><small class="text-muted">{{ $documento->created_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                        <a href="{{ route('ciudadano.descargar-documento', $documento) }}" 
                                                           class="btn btn-sm btn-success">
                                                            <i class="fas fa-download"></i> Descargar
                                                        </a>
                                                    </div>
                                                    @if(!$loop->last)<hr>@endif
                                                    @endforeach
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{ $expedientes->links() }}
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No tiene expedientes registrados</h4>
                            <p class="text-muted">Comience creando su primer expediente</p>
                            <a href="{{ route('ciudadano.registrar-expediente') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus"></i> Crear Primer Expediente
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function eliminarExpediente(id, codigo) {
    if (confirm(`¿Está seguro de eliminar el expediente ${codigo}? Esta acción no se puede deshacer.`)) {
        fetch(`/ciudadano/expedientes/${id}/eliminar`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.success);
                location.reload();
            } else {
                alert(data.error || 'Error al eliminar expediente');
            }
        })
        .catch(error => {
            alert('Error al eliminar expediente');
        });
    }
}
</script>
@endsection