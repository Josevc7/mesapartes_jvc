@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- Alertas --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-users me-2"></i>Gestión de Usuarios</h4>
                    <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Nuevo Usuario
                    </a>
                </div>
                <div class="card-body">
                    {{-- Filtros --}}
                    <form method="GET" action="{{ route('admin.usuarios') }}" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Buscar</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, DNI o email...">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Rol</label>
                            <select class="form-select" name="id_rol" onchange="this.form.submit()">
                                <option value="">-- Todos --</option>
                                @foreach($roles as $rol)
                                <option value="{{ $rol->id_rol }}" {{ request('id_rol') == $rol->id_rol ? 'selected' : '' }}>
                                    {{ $rol->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Área</label>
                            <select class="form-select" name="id_area" onchange="this.form.submit()">
                                <option value="">-- Todas --</option>
                                @foreach($areas as $area)
                                <option value="{{ $area->id_area }}" {{ request('id_area') == $area->id_area ? 'selected' : '' }}>
                                    {{ $area->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" onchange="this.form.submit()">
                                <option value="">-- Todos --</option>
                                <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            @if(request('buscar') || request('id_rol') || request('id_area') || request('estado'))
                            <a href="{{ route('admin.usuarios') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Limpiar
                            </a>
                            @endif
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>DNI</th>
                                    <th>Teléfono</th>
                                    <th>Rol</th>
                                    <th>Área</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($usuarios as $usuario)
                                <tr>
                                    <td>{{ $usuario->id }}</td>
                                    <td>
                                        <strong>{{ $usuario->name }}</strong>
                                    </td>
                                    <td>{{ $usuario->email }}</td>
                                    <td>{{ $usuario->dni }}</td>
                                    <td>{{ $usuario->telefono ?? '-' }}</td>
                                    <td>
                                        @php
                                            $rolColors = [
                                                'Administrador' => 'danger',
                                                'Mesa de Partes' => 'primary',
                                                'Jefe de Área' => 'warning',
                                                'Funcionario' => 'info',
                                                'Ciudadano' => 'secondary',
                                            ];
                                            $color = $rolColors[$usuario->role->nombre ?? ''] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ $usuario->role->nombre ?? 'Sin rol' }}</span>
                                    </td>
                                    <td>
                                        @if($usuario->area)
                                            <small>{{ $usuario->area->nombre }}</small>
                                        @else
                                            <small class="text-muted">N/A</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $usuario->activo ? 'success' : 'danger' }}">
                                            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.usuarios.show', $usuario) }}" class="btn btn-sm btn-info" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($usuario->id != auth()->user()->id)
                                            <button class="btn btn-sm btn-{{ $usuario->activo ? 'secondary' : 'success' }}"
                                                    onclick="toggleUsuario({{ $usuario->id }})"
                                                    title="{{ $usuario->activo ? 'Desactivar' : 'Activar' }}">
                                                <i class="fas fa-{{ $usuario->activo ? 'ban' : 'check' }}"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger"
                                                    onclick="eliminarUsuario({{ $usuario->id }}, '{{ addslashes($usuario->name) }}')"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fas fa-users-slash fa-2x mb-2 d-block"></i>
                                        No se encontraron usuarios
                                        @if(request('buscar') || request('id_rol') || request('id_area') || request('estado'))
                                            con los filtros aplicados.
                                            <br><a href="{{ route('admin.usuarios') }}">Ver todos</a>
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            Mostrando {{ $usuarios->firstItem() ?? 0 }} - {{ $usuarios->lastItem() ?? 0 }} de {{ $usuarios->total() }} usuarios
                        </small>
                        {{ $usuarios->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form oculto para eliminar -->
<form id="formEliminar" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="_method" value="DELETE">
</form>

<script>
function toggleUsuario(id) {
    if (confirm('¿Está seguro de cambiar el estado de este usuario?')) {
        fetch(`${window.APP_URL}/admin/usuarios/${id}/toggle-estado`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            alert('Error al cambiar el estado del usuario');
            console.error(error);
        });
    }
}

function eliminarUsuario(id, nombre) {
    if (confirm(`¿Está seguro de eliminar al usuario "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
        const form = document.getElementById('formEliminar');
        form.action = `${window.APP_URL}/admin/usuarios/${id}`;
        form.submit();
    }
}
</script>
@endsection
