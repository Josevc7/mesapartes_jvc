<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Partes DRTC - Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #cc5500 0%, #ff7700 100%);
            min-height: 100vh;
        }
        .register-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(204, 85, 0, 0.3);
        }
        .card-header {
            background: #cc5500 !important;
            border-radius: 15px 15px 0 0 !important;
            padding: 2rem;
        }
        .btn-register {
            background: #cc5500;
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            background: #aa4400;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(204, 85, 0, 0.4);
        }
        .form-control:focus {
            border-color: #cc5500;
            box-shadow: 0 0 0 0.2rem rgba(204, 85, 0, 0.25);
        }
        .logo-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card register-card">
                    <div class="card-header text-white text-center">
                        <div class="logo-icon">
                            <i class="fas fa-user-plus" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="mb-0">Mesa Virtual DRTC</h4>
                        <p class="mb-0 mt-2 opacity-75">Registro de Ciudadano</p>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger border-0" style="background-color: #f8d7da; color: #721c24;">
                                @foreach ($errors->all() as $error)
                                    <p class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nombres" class="form-label fw-semibold">Nombres *</label>
                                        <input type="text" class="form-control" id="nombres" name="nombres" 
                                               value="{{ old('nombres') }}" placeholder="Ej: Juan Carlos" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="apellido_paterno" class="form-label fw-semibold">Apellido Paterno *</label>
                                        <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" 
                                               value="{{ old('apellido_paterno') }}" placeholder="Ej: Pérez" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="apellido_materno" class="form-label fw-semibold">Apellido Materno</label>
                                        <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" 
                                               value="{{ old('apellido_materno') }}" placeholder="Ej: García">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="dni" class="form-label fw-semibold">DNI *</label>
                                        <input type="text" class="form-control" id="dni" name="dni" 
                                               value="{{ old('dni') }}" placeholder="12345678" maxlength="8" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Correo Electrónico *</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background-color: #f8f9fa; border-color: #dee2e6;">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ old('email') }}" placeholder="usuario@ejemplo.com" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background-color: #f8f9fa; border-color: #dee2e6;">
                                        <i class="fas fa-phone text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control" id="telefono" name="telefono" 
                                           value="{{ old('telefono') }}" placeholder="987654321">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label fw-semibold">Contraseña *</label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background-color: #f8f9fa; border-color: #dee2e6;">
                                                <i class="fas fa-lock text-muted"></i>
                                            </span>
                                            <input type="password" class="form-control" id="password" name="password" 
                                                   placeholder="Mínimo 6 caracteres" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="password_confirmation" class="form-label fw-semibold">Confirmar Contraseña *</label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background-color: #f8f9fa; border-color: #dee2e6;">
                                                <i class="fas fa-lock text-muted"></i>
                                            </span>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" 
                                                   placeholder="Repetir contraseña" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info border-0 mb-4" style="background-color: #d1ecf1; color: #0c5460;">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Mesa Virtual:</strong> Una vez registrado podrás realizar trámites las 24 horas desde tu casa.
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-register text-white">
                                    <i class="fas fa-user-check me-2"></i>Crear Cuenta
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <div class="border-top pt-3">
                                <p class="text-muted mb-2">¿Ya tienes cuenta?</p>
                                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-white opacity-75">
                        © 2024 Dirección Regional de Transportes y Comunicaciones
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <script>
        // Validar DNI solo números
        document.getElementById('dni').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 8) {
                this.value = this.value.slice(0, 8);
            }
        });
        
        // Validar teléfono solo números
        document.getElementById('telefono').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>