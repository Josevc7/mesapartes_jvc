<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Partes DRTC - Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #cc5500 0%, #ff7700 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .card-header {
            background: linear-gradient(135deg, #cc5500 0%, #2c2c2c 100%) !important;
            border: none;
            padding: 3rem 2rem 2rem;
            position: relative;
        }
        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
        }
        .logo-container {
            position: relative;
            z-index: 2;
        }
        .logo-icon {
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            backdrop-filter: blur(10px);
            padding: 25px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            border: 4px solid rgba(255, 255, 255, 0.3);
        }
        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .btn-login {
            background: linear-gradient(135deg, #cc5500 0%, #2c2c2c 100%);
            border: none;
            padding: 15px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(204, 85, 0, 0.4);
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #cc5500;
            box-shadow: 0 0 0 0.2rem rgba(204, 85, 0, 0.25);
            transform: translateY(-2px);
        }
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 12px 0 0 12px;
            padding: 15px;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }
        .credentials-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        .credential-item:last-child {
            border-bottom: none;
        }
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        .card-body {
            padding: 2.5rem;
        }
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }
            .card-header {
                padding: 2rem 1.5rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="container login-container">
        <div class="row justify-content-center w-100">
            <div class="col-xl-4 col-lg-5 col-md-6">
                <div class="card login-card">
                    <div class="card-header text-white text-center">
                        <div class="logo-container">
                            <div class="logo-icon">
                                <img src="{{ asset('images/logo-drtc.png') }}" alt="Logo DRTC">
                            </div>
                            <h3 class="mb-2 fw-bold">Mesa de Partes DRTC</h3>
                            <p class="mb-0 opacity-90">Sistema Digital de Trámites</p>
                        </div>
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
                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold text-dark mb-3">
                                    <i class="fas fa-envelope me-2" style="color: #cc5500;"></i>Correo Electrónico
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ old('email') }}" placeholder="usuario@ejemplo.com" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold text-dark mb-3">
                                    <i class="fas fa-lock me-2" style="color: #cc5500;"></i>Contraseña
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Ingrese su contraseña" required>
                                </div>
                            </div>
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-login text-white">
                                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center">
                            <div class="border-top pt-4 mb-4">
                                <p class="text-muted mb-3 fw-semibold">¿NO TIENES CUENTA?</p>
                                <a href="{{ route('register') }}" class="btn btn-lg px-4" style="border: 2px solid #cc5500; color: #cc5500;">
                                    <i class="fas fa-user-plus me-2"></i>Regístrate como Ciudadano
                                </a>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Registro disponible solo para ciudadanos
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Credenciales de Prueba -->
                        <div class="credentials-info">
                            <h6 class="text-center mb-3" style="color: #cc5500;">
                                <i class="fas fa-key me-2"></i>Credenciales de Prueba
                            </h6>
                            <div class="credential-item">
                                <span class="fw-semibold">Administrador:</span>
                                <small class="text-muted">admin@mesapartes.gob.pe / admin123</small>
                            </div>
                            <div class="credential-item">
                                <span class="fw-semibold">Mesa de Partes:</span>
                                <small class="text-muted">mesapartes@mesapartes.gob.pe / mesa123</small>
                            </div>
                            <div class="credential-item">
                                <span class="fw-semibold">Jefe de Área:</span>
                                <small class="text-muted">jefe@mesapartes.gob.pe / jefe123</small>
                            </div>
                            <div class="credential-item">
                                <span class="fw-semibold">Funcionario:</span>
                                <small class="text-muted">funcionario@mesapartes.gob.pe / func123</small>
                            </div>
                            <div class="credential-item">
                                <span class="fw-semibold">Ciudadano:</span>
                                <small class="text-muted">villafuerte@gmail.com / ciudadano123</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="fas fa-shield-alt text-white me-2"></i>
                        <small class="text-white opacity-90 fw-semibold">Acceso Seguro al Sistema</small>
                    </div>
                    <small class="text-white opacity-75">
                        © 2024 Dirección Regional de Transportes y Comunicaciones
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Animación suave al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.login-card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>