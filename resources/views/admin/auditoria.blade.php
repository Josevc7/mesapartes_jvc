@extends('layouts.app')

@section('title', 'Auditoría del Sistema')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Registro de Auditoría</h3>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="filtroAccion" onchange="filtrarAuditoria()">
                            <option value="">Todas las acciones</option>
                            <option value="crear">Crear</option>
                            <option value="actualizar">Actualizar</option>
                            <option value="eliminar">Eliminar</option>
                            <option value="derivar">Derivar</option>
                            <option value="resolver">Resolver</option>
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                        </select>
                        <select class="form-select form-select-sm" id="filtroUsuario" onchange="filtrarAuditoria()">
                            <option value="">Todos los usuarios</option>
                            @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                            @endforeach
                        </select>
                        <input type="date" class="form-control form-control-sm" id="filtroFecha" onchange="filtrarAuditoria()">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                    <th>Tabla</th>
                                    <th>Registro ID</th>
                                    <th>IP</th>
                                    <th>Detalles</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAuditoria">
                                @foreach($auditorias as $auditoria)
                                <tr data-accion="{{ $auditoria->accion }}" 
                                    data-usuario="{{ $auditoria->id_usuario }}" 
                                    data-fecha="{{ $auditoria->created_at->format('Y-m-d') }}">
                                    <td class="text-nowrap">{{ $auditoria->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ $auditoria->usuario->name ?? 'Sistema' }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $auditoria->accion == 'crear' ? 'success' : 
                                            ($auditoria->accion == 'actualizar' ? 'info' : 
                                            ($auditoria->accion == 'eliminar' ? 'danger' : 
                                            ($auditoria->accion == 'login' ? 'primary' : 'secondary'))) 
                                        }}">
                                            {{ ucfirst($auditoria->accion) }}
                                        </span>
                                    </td>
                                    <td>{{ $auditoria->tabla }}</td>
                                    <td>{{ $auditoria->id_registro }}</td>
                                    <td>{{ $auditoria->ip }}</td>
                                    <td>
                                        @if($auditoria->detalles)
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="verDetalles({{ $auditoria->id }})" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDetalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $auditorias->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas de Auditoría -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['total_acciones'] }}</h4>
                            <p class="mb-0">Total Acciones</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['acciones_hoy'] }}</h4>
                            <p class="mb-0">Acciones Hoy</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['usuarios_activos'] }}</h4>
                            <p class="mb-0">Usuarios Activos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['acciones_criticas'] }}</h4>
                            <p class="mb-0">Acciones Críticas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
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
                <h5 class="modal-title">Detalles de la Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="contenidoDetalles">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function filtrarAuditoria() {
    const accion = document.getElementById('filtroAccion').value;
    const usuario = document.getElementById('filtroUsuario').value;
    const fecha = document.getElementById('filtroFecha').value;
    
    const filas = document.querySelectorAll('#tablaAuditoria tr');
    
    filas.forEach(fila => {
        const accionFila = fila.dataset.accion;
        const usuarioFila = fila.dataset.usuario;
        const fechaFila = fila.dataset.fecha;
        
        let mostrar = true;
        
        if (accion && accionFila !== accion) mostrar = false;
        if (usuario && usuarioFila !== usuario) mostrar = false;
        if (fecha && fechaFila !== fecha) mostrar = false;
        
        fila.style.display = mostrar ? '' : 'none';
    });
}

function verDetalles(id) {
    fetch(`/admin/auditoria/${id}/detalles`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('contenidoDetalles').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Información General</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Usuario:</strong></td><td>${data.usuario}</td></tr>
                            <tr><td><strong>Fecha:</strong></td><td>${data.fecha}</td></tr>
                            <tr><td><strong>IP:</strong></td><td>${data.ip}</td></tr>
                            <tr><td><strong>User Agent:</strong></td><td>${data.user_agent || 'N/A'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Detalles de la Acción</h6>
                        <pre class="bg-light p-2 rounded">${JSON.stringify(data.detalles, null, 2)}</pre>
                    </div>
                </div>
            `;
        });
}
</script>
@endsection