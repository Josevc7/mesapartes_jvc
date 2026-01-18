@extends('layouts.app')

@section('title', 'Gestión de Permisos')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-shield"></i> Gestión de Roles y Permisos</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Asigne permisos específicos a cada rol del sistema. El rol Administrador tiene todos los permisos por defecto.</p>

                    <div class="row">
                        @foreach($roles as $rol)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 {{ $rol->nombre === 'Administrador' ? 'border-warning' : '' }}">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>{{ $rol->nombre }}</strong>
                                    <span class="badge bg-info">{{ $rol->users_count }} usuarios</span>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">{{ $rol->descripcion ?? 'Sin descripción' }}</p>

                                    @if($rol->nombre === 'Administrador')
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-crown"></i> Acceso total al sistema
                                        </div>
                                    @else
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-key"></i> {{ $rol->permisos->count() }} permisos asignados
                                            </small>
                                        </div>
                                        <a href="{{ route('admin.permisos.editar', $rol->id_rol) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Editar Permisos
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Módulos y Permisos del Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="accordionModulos">
                        @foreach($modulos as $modulo)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#modulo{{ $modulo->id_modulo }}">
                                    <i class="{{ $modulo->icono ?? 'fas fa-folder' }} me-2"></i>
                                    <strong>{{ $modulo->nombre }}</strong>
                                    <span class="badge bg-secondary ms-2">{{ $modulo->permisos->count() }} permisos</span>
                                </button>
                            </h2>
                            <div id="modulo{{ $modulo->id_modulo }}" class="accordion-collapse collapse" data-bs-parent="#accordionModulos">
                                <div class="accordion-body">
                                    <p class="text-muted">{{ $modulo->descripcion }}</p>
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Permiso</th>
                                                <th>Slug</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($modulo->permisos as $permiso)
                                            <tr>
                                                <td>{{ $permiso->nombre }}</td>
                                                <td><code>{{ $permiso->slug }}</code></td>
                                                <td>
                                                    @if($permiso->activo)
                                                        <span class="badge bg-success">Activo</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactivo</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
@endsection
