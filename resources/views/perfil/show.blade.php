@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-user"></i> Mi Perfil</h4>
                    <a href="{{ route('perfil.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> 
                        @php
                            $user = auth()->user();
                            $canEditAll = false;
                            if ($user->role->nombre === 'Administrador') {
                                $canEditAll = true;
                            } elseif ($user->role->nombre === 'Ciudadano') {
                                $expedientesEnTramite = \App\Models\Expediente::where('id_ciudadano', $user->id)
                                    ->whereHas('estadoExpediente', fn($q) => $q->whereNotIn('slug', ['archivado', 'resuelto']))
                                    ->exists();
                                $canEditAll = !$expedientesEnTramite;
                            }
                        @endphp
                        @if($canEditAll)
                            Editar Perfil
                        @else
                            Editar Datos Básicos
                        @endif
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="profile-avatar mb-3">
                                <i class="fas fa-user-circle fa-5x text-muted"></i>
                            </div>
                            <h5>{{ $user->name }}</h5>
                            <span class="badge bg-primary">{{ $user->role->nombre }}</span>
                        </div>
                        <div class="col-md-8">
                            <h6>Información Personal</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nombre Completo:</strong></td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>DNI:</strong></td>
                                    <td>{{ $user->dni ?? 'No registrado' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Teléfono:</strong></td>
                                    <td>{{ $user->telefono ?? 'No registrado' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Dirección:</strong></td>
                                    <td>{{ $user->direccion ?? 'No registrada' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Rol:</strong></td>
                                    <td>{{ $user->role->nombre }}</td>
                                </tr>
                                @if($user->area)
                                <tr>
                                    <td><strong>Área:</strong></td>
                                    <td>{{ $user->area->nombre }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Estado:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $user->activo ? 'success' : 'danger' }}">
                                            {{ $user->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Registro:</strong></td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Última actualización:</strong></td>
                                    <td>{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection