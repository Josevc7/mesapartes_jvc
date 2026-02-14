@extends('layouts.app')

@section('title', 'Gestión de Roles')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-user-tag"></i> Gestión de Roles</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRol">
                        <i class="fas fa-plus"></i> Nuevo Rol
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Usuarios</th>
                                    <th>Permisos</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $rol)
                                <tr>
                                    <td>{{ $rol->id_rol }}</td>
                                    <td><strong>{{ $rol->nombre }}</strong></td>
                                    <td>{{ $rol->descripcion ?? 'Sin descripción' }}</td>
                                    <td><span class="badge bg-info">{{ $rol->users_count }}</span></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if(in_array($rol->nombre, ['Ciudadano', 'Administrador']))
                                                <span class="badge bg-success" title="Mesa Virtual">MV</span>
                                            @endif
                                            @if(in_array($rol->nombre, ['Mesa de Partes', 'Administrador']))
                                                <span class="badge bg-primary" title="Mesa de Partes">MP</span>
                                            @endif
                                            @if(in_array($rol->nombre, ['Jefe de Área', 'Administrador']))
                                                <span class="badge bg-warning" title="Supervisión">SUP</span>
                                            @endif
                                            @if(in_array($rol->nombre, ['Funcionario', 'Administrador']))
                                                <span class="badge bg-info" title="Expedientes">EXP</span>
                                            @endif
                                            @if(in_array($rol->nombre, ['Soporte', 'Administrador']))
                                                <span class="badge bg-secondary" title="Soporte">SOP</span>
                                            @endif
                                            @if($rol->nombre == 'Administrador')
                                                <span class="badge bg-danger" title="Administración">ADM</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($rol->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-warning" onclick="editarRol({{ $rol->id_rol }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if($rol->users_count == 0)
                                            <form action="{{ route('admin.roles.destroy', $rol) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear/Editar Rol -->
<div class="modal fade" id="modalRol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRolTitle">Nuevo Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRol" method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Rol *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" checked>
                            <label class="form-check-label" for="activo">Rol Activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Rol</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
function editarRol(id) {
    fetch(`${window.APP_URL}/admin/roles/${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalRolTitle').textContent = 'Editar Rol';
            document.getElementById('formRol').action = `${window.APP_URL}/admin/roles/${id}`;
            document.getElementById('formRol').innerHTML += '<input type="hidden" name="_method" value="PUT">';
            
            document.getElementById('nombre').value = data.nombre;
            document.getElementById('descripcion').value = data.descripcion || '';
            document.getElementById('activo').checked = data.activo;
            
            new bootstrap.Modal(document.getElementById('modalRol')).show();
        });
}

document.getElementById('modalRol').addEventListener('hidden.bs.modal', function() {
    document.getElementById('modalRolTitle').textContent = 'Nuevo Rol';
    document.getElementById('formRol').action = '{{ route("admin.roles.store") }}';
    document.getElementById('formRol').reset();
    document.querySelector('input[name="_method"]')?.remove();
});
</script>
@endsection
@endsection