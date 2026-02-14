@extends('layouts.app')

@section('title', 'Matriz de Control')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-table"></i> Matriz de Control - Usuarios, Áreas y Permisos</h4>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <select class="form-select" id="filtroRol">
                                <option value="">Todos los Roles</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filtroArea">
                                <option value="">Todas las Áreas</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filtroEstado">
                                <option value="">Todos los Estados</option>
                                <option value="1">Activos</option>
                                <option value="0">Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" onclick="aplicarFiltros()">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                        </div>
                    </div>

                    <!-- Matriz de Control -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="matrizControl">
                            <thead class="table-dark">
                                <tr>
                                    <th rowspan="2" class="align-middle">Usuario</th>
                                    <th rowspan="2" class="align-middle">Rol</th>
                                    <th rowspan="2" class="align-middle">Área</th>
                                    <th rowspan="2" class="align-middle">Estado</th>
                                    <th colspan="6" class="text-center">Permisos por Módulo</th>
                                    <th rowspan="2" class="align-middle">Acciones</th>
                                </tr>
                                <tr>
                                    <th class="text-center">Mesa Virtual</th>
                                    <th class="text-center">Mesa Partes</th>
                                    <th class="text-center">Supervisión</th>
                                    <th class="text-center">Expedientes</th>
                                    <th class="text-center">Soporte</th>
                                    <th class="text-center">Admin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usuarios as $usuario)
                                <tr data-rol="{{ $usuario->rol_id }}" data-area="{{ $usuario->area_id }}" data-estado="{{ $usuario->activo ? 1 : 0 }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <div class="avatar-title bg-primary rounded-circle">
                                                    {{ substr($usuario->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <strong>{{ $usuario->name }}</strong><br>
                                                <small class="text-muted">{{ $usuario->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $usuario->role->nombre }}</span>
                                    </td>
                                    <td>{{ $usuario->area->nombre ?? 'N/A' }}</td>
                                    <td>
                                        @if($usuario->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    
                                    <!-- Permisos por módulo -->
                                    <td class="text-center">
                                        @if(in_array($usuario->role->nombre, ['Ciudadano', 'Administrador']))
                                            <i class="fas fa-check text-success" title="Acceso completo"></i>
                                        @else
                                            <i class="fas fa-times text-danger" title="Sin acceso"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(in_array($usuario->role->nombre, ['Mesa de Partes', 'Administrador']))
                                            <i class="fas fa-check text-success" title="Acceso completo"></i>
                                        @else
                                            <i class="fas fa-times text-danger" title="Sin acceso"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(in_array($usuario->role->nombre, ['Jefe de Área', 'Administrador']))
                                            <i class="fas fa-check text-success" title="Acceso completo"></i>
                                        @else
                                            <i class="fas fa-times text-danger" title="Sin acceso"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(in_array($usuario->role->nombre, ['Funcionario', 'Administrador']))
                                            <i class="fas fa-check text-success" title="Acceso completo"></i>
                                        @else
                                            <i class="fas fa-times text-danger" title="Sin acceso"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(in_array($usuario->role->nombre, ['Soporte', 'Administrador']))
                                            <i class="fas fa-check text-success" title="Acceso completo"></i>
                                        @else
                                            <i class="fas fa-times text-danger" title="Sin acceso"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($usuario->role->nombre == 'Administrador')
                                            <i class="fas fa-check text-success" title="Acceso completo"></i>
                                        @else
                                            <i class="fas fa-times text-danger" title="Sin acceso"></i>
                                        @endif
                                    </td>
                                    
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.usuarios.show', $usuario) }}" class="btn btn-sm btn-info" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-secondary" onclick="cambiarEstado({{ $usuario->id }})" title="Cambiar Estado">
                                                <i class="fas fa-toggle-{{ $usuario->activo ? 'on' : 'off' }}"></i>
                                            </button>
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

    <!-- Resumen por Rol -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie"></i> Resumen por Rol</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($resumenRoles as $rol => $datos)
                        <div class="col-md-2">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $rol }}</h5>
                                    <h3 class="text-primary">{{ $datos['total'] }}</h3>
                                    <small class="text-muted">
                                        {{ $datos['activos'] }} activos / {{ $datos['inactivos'] }} inactivos
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen por Área -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-building"></i> Resumen por Área</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($resumenAreas as $area => $cantidad)
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $area }}</h6>
                                    <h4 class="text-info">{{ $cantidad }}</h4>
                                    <small class="text-muted">usuarios asignados</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function aplicarFiltros() {
    const filtroRol = document.getElementById('filtroRol').value;
    const filtroArea = document.getElementById('filtroArea').value;
    const filtroEstado = document.getElementById('filtroEstado').value;
    
    const filas = document.querySelectorAll('#matrizControl tbody tr');
    
    filas.forEach(fila => {
        let mostrar = true;
        
        if (filtroRol && fila.dataset.rol !== filtroRol) {
            mostrar = false;
        }
        
        if (filtroArea && fila.dataset.area !== filtroArea) {
            mostrar = false;
        }
        
        if (filtroEstado && fila.dataset.estado !== filtroEstado) {
            mostrar = false;
        }
        
        fila.style.display = mostrar ? '' : 'none';
    });
}

function cambiarEstado(usuarioId) {
    if (confirm('¿Está seguro de cambiar el estado del usuario?')) {
        fetch(`${window.APP_URL}/admin/usuarios/${usuarioId}/toggle-estado`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al cambiar el estado');
            }
        });
    }
}

// Limpiar filtros
document.addEventListener('DOMContentLoaded', function() {
    const btnLimpiar = document.createElement('button');
    btnLimpiar.className = 'btn btn-secondary ms-2';
    btnLimpiar.innerHTML = '<i class="fas fa-times"></i> Limpiar';
    btnLimpiar.onclick = function() {
        document.getElementById('filtroRol').value = '';
        document.getElementById('filtroArea').value = '';
        document.getElementById('filtroEstado').value = '';
        aplicarFiltros();
    };
    
    document.querySelector('.col-md-3:last-child').appendChild(btnLimpiar);
});
</script>
@endsection
@endsection