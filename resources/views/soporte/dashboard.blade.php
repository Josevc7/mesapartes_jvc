@extends('layouts.app')

@section('title', 'Soporte Técnico')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2>Panel de Soporte Técnico</h2>
            <p class="text-muted">Monitoreo y mantenimiento del sistema</p>
        </div>
    </div>

    <!-- Estado del Sistema -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h3>{{ $stats['usuarios_activos'] }}</h3>
                    <p>Usuarios Activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                    <h3>{{ $stats['expedientes_hoy'] }}</h3>
                    <p>Expedientes Hoy</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-{{ $stats['errores_sistema'] > 0 ? 'danger' : 'success' }} text-white">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h3>{{ $stats['errores_sistema'] }}</h3>
                    <p>Errores Sistema</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-database fa-2x mb-2"></i>
                    <h3>{{ $stats['espacio_bd'] }} MB</h3>
                    <p>Espacio BD</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Herramientas Rápidas -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Mantenimiento</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('soporte.limpiar-cache') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-broom"></i> Limpiar Cache
                            </button>
                        </form>
                        <form method="POST" action="{{ route('soporte.respaldo') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info w-100">
                                <i class="fas fa-download"></i> Crear Respaldo
                            </button>
                        </form>
                        <a href="{{ route('soporte.logs') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-list"></i> Ver Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Usuarios</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('soporte.usuarios') }}" class="btn btn-primary w-100">
                            <i class="fas fa-users-cog"></i> Gestionar Usuarios
                        </a>
                        <button class="btn btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                            <i class="fas fa-key"></i> Resetear Contraseña
                        </button>
                        <button class="btn btn-outline-danger w-100">
                            <i class="fas fa-user-lock"></i> Bloquear Usuario
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Monitoreo</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Estado del Sistema</span>
                            <span class="badge bg-success">Online</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Base de Datos</span>
                            <span class="badge bg-success">Conectada</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Último Respaldo</span>
                            <span class="badge bg-info">Hoy</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas del Sistema -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Alertas y Notificaciones</h5>
                </div>
                <div class="card-body">
                    @if($stats['errores_sistema'] > 0)
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Atención:</strong> Se detectaron {{ $stats['errores_sistema'] }} errores en el sistema. 
                            <a href="{{ route('soporte.logs') }}" class="alert-link">Revisar logs</a>
                        </div>
                    @endif
                    
                    @if($stats['espacio_bd'] > 100)
                        <div class="alert alert-warning">
                            <i class="fas fa-database"></i>
                            <strong>Advertencia:</strong> La base de datos está ocupando {{ $stats['espacio_bd'] }} MB. 
                            Considere crear un respaldo y limpiar datos antiguos.
                        </div>
                    @endif
                    
                    @if($stats['errores_sistema'] == 0 && $stats['espacio_bd'] <= 100)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Sistema Operativo:</strong> Todos los servicios funcionan correctamente.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resetear Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="resetPasswordForm">
                    <div class="mb-3">
                        <label class="form-label">Email del Usuario</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" name="nueva_password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning">Resetear</button>
            </div>
        </div>
    </div>
</div>
@endsection