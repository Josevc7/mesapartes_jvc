@extends('layouts.app')

@section('title', 'Logs del Sistema')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-list-alt"></i> Logs del Sistema</h4>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select" id="filtroAccion">
                                <option value="">Todas las Acciones</option>
                                <option value="LOGIN">Login</option>
                                <option value="LOGOUT">Logout</option>
                                <option value="CREAR">Crear</option>
                                <option value="EDITAR">Editar</option>
                                <option value="ELIMINAR">Eliminar</option>
                                <option value="DERIVAR">Derivar</option>
                                <option value="PROCESAR">Procesar</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filtroUsuario">
                                <option value="">Todos los Usuarios</option>
                                @foreach($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="filtroFecha" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" onclick="aplicarFiltros()">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <button class="btn btn-secondary" onclick="limpiarFiltros()">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de Logs -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaLogs">
                            <thead class="table-dark">
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
                            <tbody>
                                @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        @if($log->usuario)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-title bg-primary rounded-circle">
                                                        {{ substr($log->usuario->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <strong>{{ $log->usuario->name }}</strong><br>
                                                    <small class="text-muted">{{ $log->usuario->role->nombre }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Sistema</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $log->accion == 'LOGIN' ? 'success' : ($log->accion == 'ELIMINAR' ? 'danger' : 'primary') }}">
                                            {{ $log->accion }}
                                        </span>
                                    </td>
                                    <td>{{ $log->tabla }}</td>
                                    <td>{{ $log->id_registro }}</td>
                                    <td>
                                        <small class="text-muted">{{ $log->ip_address }}</small>
                                    </td>
                                    <td>
                                        @if($log->datos_anteriores || $log->datos_nuevos)
                                            <button class="btn btn-sm btn-info" onclick="verDetalles({{ $log->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-center">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ver Detalles -->
<div class="modal fade" id="modalDetalles" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="contenidoDetalles">
                    <!-- Contenido cargado dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function aplicarFiltros() {
    const accion = document.getElementById('filtroAccion').value;
    const usuario = document.getElementById('filtroUsuario').value;
    const fecha = document.getElementById('filtroFecha').value;
    
    const params = new URLSearchParams();
    if (accion) params.append('accion', accion);
    if (usuario) params.append('id_usuario', usuario);
    if (fecha) params.append('fecha', fecha);
    
    window.location.href = '{{ route("admin.logs") }}?' + params.toString();
}

function limpiarFiltros() {
    document.getElementById('filtroAccion').value = '';
    document.getElementById('filtroUsuario').value = '';
    document.getElementById('filtroFecha').value = '';
    window.location.href = '{{ route("admin.logs") }}';
}

function verDetalles(logId) {
    fetch(`/admin/logs/${logId}/detalles`)
        .then(response => response.json())
        .then(data => {
            let contenido = '<div class="row">';
            
            if (data.datos_anteriores) {
                contenido += '<div class="col-md-6">';
                contenido += '<h6 class="text-danger">Datos Anteriores:</h6>';
                contenido += '<pre class="bg-light p-2 rounded">' + JSON.stringify(JSON.parse(data.datos_anteriores), null, 2) + '</pre>';
                contenido += '</div>';
            }
            
            if (data.datos_nuevos) {
                contenido += '<div class="col-md-6">';
                contenido += '<h6 class="text-success">Datos Nuevos:</h6>';
                contenido += '<pre class="bg-light p-2 rounded">' + JSON.stringify(JSON.parse(data.datos_nuevos), null, 2) + '</pre>';
                contenido += '</div>';
            }
            
            contenido += '</div>';
            
            document.getElementById('contenidoDetalles').innerHTML = contenido;
            new bootstrap.Modal(document.getElementById('modalDetalles')).show();
        });
}
</script>
@endsection
@endsection