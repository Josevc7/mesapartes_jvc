<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Partes DRTC - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #cc5500 0%, #ff7700 100%);
            min-height: 100vh;
        }
        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(204, 85, 0, 0.3);
        }
        .card-header {
            background: #cc5500 !important;
            border-radius: 15px 15px 0 0 !important;
            padding: 2rem;
        }
        .btn-login {
            background: #cc5500;
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
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
            <div class="col-md-5 col-lg-4">
                <div class="card login-card">
                    <div class="card-header text-white text-center">
                        <div class="logo-icon">
                            <i class="fas fa-file-alt" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="mb-0">Mesa de Partes DRTC</h4>
                        <p class="mb-0 mt-2 opacity-75">Sistema Digital de Trámites</p>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger border-0" style="background-color: #f8d7da; color: #721c24;">
                                @foreach ($errors->all() as $error)
                                    <p class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background-color: #f8f9fa; border-color: #dee2e6;">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ old('email') }}" placeholder="usuario@ejemplo.com" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background-color: #f8f9fa; border-color: #dee2e6;">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Ingrese su contraseña" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-login text-white">
                                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <div class="border-top pt-3">
                                <p class="text-muted mb-2">¿NO TIENES CUENTA?</p>
                                <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-user-plus me-2"></i>Regístrate
                                </a>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Solo para ciudadanos
                                    </small>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Acceso seguro al sistema
                                </small>
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
</body>
</html>