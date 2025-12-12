@extends('layouts.app')

@section('title', 'Crear Usuario')

@push('styles')
<link href="{{ asset('css/ciudadano-form.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white py-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-20 rounded-circle p-3 me-3">
                            <i class="fas fa-user-plus fa-2x"></i>
                        </div>
                        <div>
                            <h3 class="mb-1 fw-bold">Crear Nuevo Usuario</h3>
                            <p class="mb-0 opacity-90">Administración - Gestión de Usuarios</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.usuarios.store') }}">
                        @csrf
                        
                        <!-- Sección 1: Información Personal -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-user text-primary fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-primary fw-bold">Información Personal</h5>
                                    <p class="text-muted mb-0 small">Datos básicos del usuario</p>
                                </div>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-semibold">
                                        <i class="fas fa-user text-primary me-2"></i>Nombre Completo *
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" 
                                           placeholder="Ingrese el nombre completo" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="dni" class="form-label fw-semibold">
                                        <i class="fas fa-id-card text-primary me-2"></i>DNI *
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('dni') is-invalid @enderror" 
                                           id="dni" name="dni" value="{{ old('dni') }}" 
                                           placeholder="Número de DNI" required>
                                    @error('dni')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Sección 2: Datos de Contacto -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-envelope text-success fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-success fw-bold">Datos de Contacto</h5>
                                    <p class="text-muted mb-0 small">Información de contacto del usuario</p>
                                </div>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold">
                                        <i class="fas fa-envelope text-success me-2"></i>Email *
                                    </label>
                                    <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" 
                                           placeholder="correo@ejemplo.com" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="telefono" class="form-label fw-semibold">
                                        <i class="fas fa-phone text-success me-2"></i>Teléfono
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('telefono') is-invalid @enderror" 
                                           id="telefono" name="telefono" value="{{ old('telefono') }}" 
                                           placeholder="Número de teléfono">
                                    @error('telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Sección 3: Asignación de Rol y Área -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-user-cog text-info fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-info fw-bold">Asignación de Rol y Área</h5>
                                    <p class="text-muted mb-0 small">Permisos y área de trabajo del usuario</p>
                                </div>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="id_rol" class="form-label fw-semibold">
                                        <i class="fas fa-user-shield text-info me-2"></i>Rol *
                                    </label>
                                    <select class="form-select form-select-lg @error('id_rol') is-invalid @enderror" 
                                            id="id_rol" name="id_rol" required>
                                        <option value="">Seleccione un rol</option>
                                        @foreach($roles as $rol)
                                            <option value="{{ $rol->id }}" {{ old('id_rol') == $rol->id ? 'selected' : '' }}>
                                                {{ $rol->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_rol')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="id_area" class="form-label fw-semibold">
                                        <i class="fas fa-building text-info me-2"></i>Área
                                    </label>
                                    <select class="form-select form-select-lg @error('id_area') is-invalid @enderror" 
                                            id="id_area" name="id_area">
                                        <option value="">Seleccione un área</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id }}" {{ old('id_area') == $area->id ? 'selected' : '' }}>
                                                {{ $area->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_area')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Sección 4: Seguridad y Estado -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-lock text-warning fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-warning fw-bold">Seguridad y Estado</h5>
                                    <p class="text-muted mb-0 small">Contraseña y estado del usuario</p>
                                </div>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-semibold">
                                        <i class="fas fa-key text-warning me-2"></i>Contraseña *
                                    </label>
                                    <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                           id="password" name="password" 
                                           placeholder="Ingrese contraseña segura" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-semibold">
                                        <i class="fas fa-check-circle text-warning me-2"></i>Confirmar Contraseña *
                                    </label>
                                    <input type="password" class="form-control form-control-lg" 
                                           id="password_confirmation" name="password_confirmation" 
                                           placeholder="Confirme la contraseña" required>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" 
                                           {{ old('activo', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="activo">
                                        <i class="fas fa-toggle-on text-success me-2"></i>Usuario Activo
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-end pt-4 border-top">
                            <a href="{{ route('admin.usuarios') }}" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="fas fa-arrow-left me-2"></i>Volver a Usuarios
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                                <i class="fas fa-user-plus me-2"></i>Crear Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection