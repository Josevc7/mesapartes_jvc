@extends('layouts.app')

@section('title', 'Auditoría del Sistema')

@section('content')
<div class="container-fluid">
    <!-- Estadísticas de Auditoría -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ number_format($estadisticasAuditoria['total_registros']) }}</h3>
                    <small>Total de Registros</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $estadisticasAuditoria['hoy'] }}</h3>
                    <small>Acciones Hoy</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3>{{ $estadisticasAuditoria['esta_semana'] }}</h3>
                    <small>Esta Semana</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h3>{{ count($estadisticasAuditoria['por_accion']) }}</h3>
                    <small>Tipos de Acción</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen por Acción -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Resumen por Tipo de Acción</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($estadisticasAuditoria['por_accion'] as $accion => $total)
                        <div class="col-md-2 mb-2">
                            <div class="border rounded p-2 text-center">
                                <strong>{{ $accion }}</strong>
                                <br><span class="badge bg-primary fs-6">{{ $total }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-history"></i> Registro de Auditoría</h4>
            <a href="{{ route('admin.auditoria.exportar', request()->all()) }}" class="btn btn-light btn-sm">
                <i class="fas fa-download"></i> Exportar CSV
            </a>
        </div>
        <div class="card-body">
            <!-- Filtros Avanzados -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-2">
                    <label class="form-label">Acción</label>
                    <select class="form-select" name="accion">
                        <option value="">Todas</option>
                        @foreach($acciones as $accion)
                        <option value="{{ $accion }}" {{ request('accion') == $accion ? 'selected' : '' }}>
                            {{ $accion }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Usuario</label>
                    <select class="form-select" name="id_usuario">
                        <option value="">Todos</option>
                        @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" {{ request('id_usuario') == $usuario->id ? 'selected' : '' }}>
                            {{ $usuario->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tabla</label>
                    <select class="form-select" name="tabla">
                        <option value="">Todas</option>
                        @foreach($tablas as $tabla)
                        @if($tabla)
                        <option value="{{ $tabla }}" {{ request('tabla') == $tabla ? 'selected' : '' }}>
                            {{ $tabla }}
                        </option>
                        @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" name="fecha_desde" value="{{ request('fecha_desde') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">IP</label>
                    <input type="text" class="form-control" name="ip" value="{{ request('ip') }}" placeholder="192.168...">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.auditoria-completa') }}" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Limpiar
                    </a>
                </div>
            </form>

            <!-- Tabla de Auditoría -->
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Fecha/Hora</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Tabla</th>
                            <th>Registro</th>
                            <th>IP</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditorias as $auditoria)
                        <tr>
                            <td>{{ $auditoria->id_auditoria }}</td>
                            <td>
                                <small>{{ $auditoria->created_at->format('d/m/Y') }}</small>
                                <br><small class="text-muted">{{ $auditoria->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                {{ $auditoria->usuario?->name ?? 'N/A' }}
                                <br><small class="text-muted">{{ $auditoria->usuario?->email ?? '' }}</small>
                            </td>
                            <td>
                                @php
                                    $accionColor = [
                                        'CREAR' => 'success', 'ACTUALIZAR' => 'warning', 'ELIMINAR' => 'danger',
                                        'LOGIN' => 'info', 'LOGOUT' => 'secondary', 'RESET_PASSWORD' => 'primary'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $accionColor[$auditoria->accion] ?? 'secondary' }}">
                                    {{ $auditoria->accion }}
                                </span>
                            </td>
                            <td><code>{{ $auditoria->tabla ?? '-' }}</code></td>
                            <td>{{ $auditoria->registro_id ?? '-' }}</td>
                            <td><small>{{ $auditoria->ip ?? '-' }}</small></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="verDetalles({{ $auditoria->id_auditoria }})">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No se encontraron registros de auditoría</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $auditorias->withQueryString()->links() }}
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
            <div class="modal-body" id="detallesContent">
                <div class="text-center">
                    <div class="spinner-border" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function verDetalles(id) {
    const modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
    const content = document.getElementById('detallesContent');

    content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    modal.show();

    fetch(`${window.APP_URL}/admin/auditoria/${id}/detalles`)
        .then(response => response.json())
        .then(data => {
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID:</strong> ${data.id_auditoria}</p>
                        <p><strong>Acción:</strong> ${data.accion}</p>
                        <p><strong>Tabla:</strong> ${data.tabla || '-'}</p>
                        <p><strong>Registro ID:</strong> ${data.registro_id || '-'}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fecha:</strong> ${new Date(data.created_at).toLocaleString()}</p>
                        <p><strong>IP:</strong> ${data.ip || '-'}</p>
                        <p><strong>User Agent:</strong></p>
                        <small class="text-muted">${data.user_agent || '-'}</small>
                    </div>
                </div>
            `;

            if (data.datos_anteriores) {
                html += `
                    <hr>
                    <h6>Datos Anteriores:</h6>
                    <pre class="bg-light p-2 rounded">${JSON.stringify(JSON.parse(data.datos_anteriores), null, 2)}</pre>
                `;
            }

            if (data.datos_nuevos) {
                html += `
                    <h6>Datos Nuevos:</h6>
                    <pre class="bg-light p-2 rounded">${JSON.stringify(JSON.parse(data.datos_nuevos), null, 2)}</pre>
                `;
            }

            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">Error al cargar detalles</div>';
        });
}
</script>
@endsection
@endsection
