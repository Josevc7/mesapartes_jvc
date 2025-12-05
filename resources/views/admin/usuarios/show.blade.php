@extends('layouts.app')

@section('title', 'Ver Usuario')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-user"></i> Detalles del Usuario</h4>
                    <div>
                        <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('admin.usuarios') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">ID:</th>
                                    <td>{{ $usuario->id }}</td>
                                </tr>
                                <tr>
                                    <th>Nombre:</th>
                                    <td>{{ $usuario->name }}</td>
                                </tr>
                                <tr>
                                    <th>DNI:</th>
                                    <td>{{ $usuario->dni }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $usuario->email }}</td>
                                </tr>
                                <tr>
                                    <th>Teléfono:</th>
                                    <td>{{ $usuario->telefono ?? 'No registrado' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Rol:</th>
                                    <td>
                                        <span class="badge bg-info">{{ $usuario->role->nombre }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Área:</th>
                                    <td>{{ $usuario->area->nombre ?? 'No asignada' }}</td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>
                                        @if($usuario->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Creado:</th>
                                    <td>{{ $usuario->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Actualizado:</th>
                                    <td>{{ $usuario->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar"></i> Estadísticas</h5>
                </div>
                <div class="card-body">
                    @if($usuario->rol_id == 4) <!-- Funcionario -->
                        <div class="mb-3">
                            <strong>Expedientes Asignados:</strong>
                            <span class="badge bg-primary">{{ $usuario->expedientesAsignados->count() }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Expedientes Pendientes:</strong>
                            <span class="badge bg-warning">{{ $usuario->expedientesAsignados->where('estado', 'en_proceso')->count() }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Expedientes Completados:</strong>
                            <span class="badge bg-success">{{ $usuario->expedientesAsignados->where('estado', 'completado')->count() }}</span>
                        </div>
                    @elseif($usuario->rol_id == 6) <!-- Ciudadano -->
                        <div class="mb-3">
                            <strong>Expedientes Registrados:</strong>
                            <span class="badge bg-primary">{{ $usuario->expedientes->count() }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Expedientes Activos:</strong>
                            <span class="badge bg-info">{{ $usuario->expedientes->whereIn('estado', ['pendiente', 'derivado', 'en_proceso'])->count() }}</span>
                        </div>
                    @else
                        <p class="text-muted">No hay estadísticas disponibles para este rol.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection