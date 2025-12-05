@extends('layouts.app')

@section('title', 'Auditoría del Sistema')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Auditoría del Sistema</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                    <th>Tabla</th>
                                    <th>Registro ID</th>
                                    <th>IP</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($auditorias as $auditoria)
                                <tr>
                                    <td>{{ $auditoria->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ $auditoria->usuario->name ?? 'Sistema' }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $auditoria->accion == 'CREATE' ? 'success' : 
                                            ($auditoria->accion == 'UPDATE' ? 'warning' : 'danger') 
                                        }}">
                                            {{ $auditoria->accion }}
                                        </span>
                                    </td>
                                    <td>{{ $auditoria->tabla }}</td>
                                    <td>{{ $auditoria->id_registro }}</td>
                                    <td>{{ $auditoria->ip }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="verDetalles({{ $auditoria->id }})">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        {{ $auditorias->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalles -->
<div class="modal fade" id="modalDetalles" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de Auditoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detallesContent">
                    <!-- Contenido dinámico -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function verDetalles(id) {
    fetch(`/admin/auditoria/${id}/detalles`)
        .then(response => response.json())
        .then(data => {
            let content = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Usuario:</strong> ${data.usuario ? data.usuario.name : 'Sistema'}<br>
                        <strong>Acción:</strong> ${data.accion}<br>
                        <strong>Tabla:</strong> ${data.tabla}<br>
                        <strong>Registro ID:</strong> ${data.id_registro}<br>
                        <strong>IP:</strong> ${data.ip}<br>
                        <strong>User Agent:</strong> ${data.user_agent || 'N/A'}<br>
                        <strong>Fecha:</strong> ${new Date(data.created_at).toLocaleString()}
                    </div>
                </div>
                <hr>
            `;
            
            if (data.datos_anteriores) {
                content += `
                    <h6>Datos Anteriores:</h6>
                    <pre class="bg-light p-2">${JSON.stringify(data.datos_anteriores, null, 2)}</pre>
                `;
            }
            
            if (data.datos_nuevos) {
                content += `
                    <h6>Datos Nuevos:</h6>
                    <pre class="bg-light p-2">${JSON.stringify(data.datos_nuevos, null, 2)}</pre>
                `;
            }
            
            document.getElementById('detallesContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('modalDetalles')).show();
        });
}
</script>
@endsection