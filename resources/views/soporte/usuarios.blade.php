@extends('layouts.app')

@section('title', 'Gestión de Usuarios - Soporte')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Gestión de Usuarios</h2>
                <a href="{{ route('soporte.dashboard') }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Usuarios del Sistema ({{ $usuarios->total() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Área</th>
                                    <th>Expedientes</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usuarios as $usuario)
                                <tr class="{{ !$usuario->activo ? 'table-secondary' : '' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-primary text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                                {{ substr($usuario->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <strong>{{ $usuario->name }}</strong>
                                                @if(!$usuario->activo)
                                                    <br><small class="text-muted">Inactivo</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $usuario->email }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $usuario->role->nombre ?? 'Sin rol' }}</span>
                                    </td>
                                    <td>{{ $usuario->area->nombre ?? 'Sin área' }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $usuario->expedientes_asignados_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $usuario->activo ? 'success' : 'danger' }}">
                                            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#resetPasswordModal{{ $usuario->id }}">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            
                                            <form method="POST" action="{{ route('soporte.toggle-usuario', $usuario) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-{{ $usuario->activo ? 'danger' : 'success' }}">
                                                    <i class="fas fa-{{ $usuario->activo ? 'user-lock' : 'user-check' }}"></i>
                                                </button>
                                            </form>
                                            
                                            <button class="btn btn-outline-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#infoModal{{ $usuario->id }}">
                                                <i class="fas fa-info"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Reset Password -->
                                <div class="modal fade" id="resetPasswordModal{{ $usuario->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Resetear Contraseña</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('soporte.resetear-password', $usuario) }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <p>Usuario: <strong>{{ $usuario->name }}</strong></p>
                                                    <p>Email: <strong>{{ $usuario->email }}</strong></p>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nueva Contraseña</label>
                                                        <input type="password" class="form-control" name="nueva_password" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-warning">Resetear</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Info -->
                                <div class="modal fade" id="infoModal{{ $usuario->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Información del Usuario</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <strong>Nombre:</strong><br>
                                                        {{ $usuario->name }}
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Email:</strong><br>
                                                        {{ $usuario->email }}
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <strong>Rol:</strong><br>
                                                        {{ $usuario->role->nombre ?? 'Sin rol' }}
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Área:</strong><br>
                                                        {{ $usuario->area->nombre ?? 'Sin área' }}
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <strong>Expedientes:</strong><br>
                                                        {{ $usuario->expedientes_asignados_count }}
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Último acceso:</strong><br>
                                                        {{ $usuario->updated_at->format('d/m/Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $usuarios->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection