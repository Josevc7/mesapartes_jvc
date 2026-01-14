<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Partes DRTC - Iniciar Sesion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }
        .login-wrapper {
            display: flex;
            min-height: 100vh;
        }
        /* Panel izquierdo - Logo */
        .logo-panel {
            width: 45%;
            background: linear-gradient(135deg, #cc5500 0%, #2c2c2c 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 2rem;
        }
        .logo-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            pointer-events: none;
        }
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }
        .shape:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 15%;
            left: 10%;
            animation-delay: 0s;
        }
        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            bottom: 20%;
            right: 5%;
            animation-delay: 2s;
        }
        .shape:nth-child(3) {
            width: 70px;
            height: 70px;
            bottom: 30%;
            left: 15%;
            animation-delay: 4s;
        }
        .shape:nth-child(4) {
            width: 50px;
            height: 50px;
            top: 60%;
            right: 20%;
            animation-delay: 1s;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-25px) rotate(180deg); }
        }
        .logo-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        .logo-icon {
            width: 280px;
            height: 280px;
            background: rgba(255, 255, 255, 1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2.5rem;
            padding: 30px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
            border: 6px solid rgba(255, 255, 255, 0.4);
            animation: pulse 3s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3); }
            50% { box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4); }
        }
        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .logo-title {
            color: white;
            font-size: 2.4rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.4);
            letter-spacing: 0.5px;
        }
        .logo-subtitle {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1.25rem;
            margin-bottom: 2.5rem;
            font-weight: 300;
            letter-spacing: 1px;
        }
        .logo-features {
            text-align: left;
            color: rgba(255, 255, 255, 0.85);
        }
        .logo-features li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
        }
        .logo-features li i {
            margin-right: 12px;
            color: #ffb366;
            width: 20px;
        }
        /* Panel derecho - Formulario */
        .form-panel {
            width: 55%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #f8f9fa;
        }
        .form-container {
            width: 100%;
            max-width: 480px;
        }
        .form-header {
            margin-bottom: 2rem;
        }
        .form-header h2 {
            color: #2c2c2c;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .form-header p {
            color: #6c757d;
            font-size: 0.95rem;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        .form-control:focus {
            border-color: #cc5500;
            box-shadow: 0 0 0 0.2rem rgba(204, 85, 0, 0.15);
        }
        .input-group-text {
            background: white;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 12px 0 0 12px;
            padding: 15px;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }
        .input-group:focus-within .input-group-text {
            border-color: #cc5500;
        }
        .btn-login {
            background: linear-gradient(135deg, #cc5500 0%, #ff7700 100%);
            border: none;
            padding: 15px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            color: white;
        }
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(204, 85, 0, 0.4);
            color: white;
        }
        .btn-register {
            border: 2px solid #cc5500;
            color: #cc5500;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            background: #cc5500;
            color: white;
        }
        .credentials-info {
            background: white;
            border-radius: 15px;
            padding: 1.25rem;
            margin-top: 1.5rem;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.4rem 0;
            border-bottom: 1px solid #f1f3f4;
            font-size: 0.85rem;
        }
        .credential-item:last-child {
            border-bottom: none;
        }
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #dee2e6;
        }
        .divider span {
            padding: 0 1rem;
            color: #6c757d;
            font-size: 0.85rem;
            font-weight: 500;
        }
        /* Responsive */
        @media (max-width: 992px) {
            .login-wrapper {
                flex-direction: column;
            }
            .logo-panel {
                width: 100%;
                padding: 3rem 2rem;
                min-height: auto;
            }
            .logo-icon {
                width: 180px;
                height: 180px;
                margin-bottom: 1.5rem;
                padding: 20px;
            }
            .logo-title {
                font-size: 1.8rem;
            }
            .logo-subtitle {
                font-size: 1.1rem;
                margin-bottom: 1.5rem;
            }
            .logo-features {
                display: none;
            }
            .form-panel {
                width: 100%;
                padding: 2rem 1.5rem;
            }
        }
        @media (max-width: 576px) {
            .logo-panel {
                padding: 2rem 1rem;
            }
            .logo-icon {
                width: 150px;
                height: 150px;
                padding: 18px;
            }
            .logo-title {
                font-size: 1.5rem;
            }
            .form-container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Panel Izquierdo - Logo -->
        <div class="logo-panel">
            <div class="floating-shapes">
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
            </div>

            <div class="logo-content">
                <div class="logo-icon">
                    <img src="{{ asset('images/logo-drtc.png') }}" alt="Logo DRTC">
                </div>
                <h1 class="logo-title">Mesa de Partes DRTC</h1>
                <p class="logo-subtitle">Sistema Digital de Tramites</p>

                <ul class="logo-features list-unstyled">
                    <li><i class="fas fa-check-circle"></i> Registro de expedientes en linea</li>
                    <li><i class="fas fa-check-circle"></i> Seguimiento en tiempo real</li>
                    <li><i class="fas fa-check-circle"></i> Notificaciones automaticas</li>
                    <li><i class="fas fa-check-circle"></i> Gestion documental segura</li>
                </ul>
            </div>

            <div class="text-center mt-auto pt-4" style="position: relative; z-index: 2;">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="fas fa-shield-alt text-white me-2"></i>
                    <small class="text-white opacity-90 fw-semibold">Acceso Seguro</small>
                </div>
                <small class="text-white opacity-75">
                    © 2024 Direccion Regional de Transportes y Comunicaciones
                </small>
            </div>
        </div>

        <!-- Panel Derecho - Formulario -->
        <div class="form-panel">
            <div class="form-container">
                <div class="form-header">
                    <h2><i class="fas fa-sign-in-alt me-2" style="color: #cc5500;"></i>Iniciar Sesion</h2>
                    <p>Ingresa tus credenciales para acceder al sistema</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger border-0 mb-4" style="border-radius: 12px;">
                        @foreach ($errors->all() as $error)
                            <p class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold text-dark mb-2">
                            <i class="fas fa-envelope me-2" style="color: #cc5500;"></i>Correo Electronico
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
                        <label for="password" class="form-label fw-semibold text-dark mb-2">
                            <i class="fas fa-lock me-2" style="color: #cc5500;"></i>Contrasena
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock text-muted"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Ingrese su contrasena" required>
                        </div>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesion
                        </button>
                    </div>
                </form>

                <div class="divider">
                    <span>¿No tienes cuenta?</span>
                </div>

                <div class="text-center">
                    <a href="{{ route('register') }}" class="btn btn-register">
                        <i class="fas fa-user-plus me-2"></i>Registrate como Ciudadano
                    </a>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Registro disponible solo para ciudadanos
                        </small>
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
                        <span class="fw-semibold">Jefe de Area:</span>
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
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formContainer = document.querySelector('.form-container');
            formContainer.style.opacity = '0';
            formContainer.style.transform = 'translateX(30px)';

            setTimeout(() => {
                formContainer.style.transition = 'all 0.6s ease';
                formContainer.style.opacity = '1';
                formContainer.style.transform = 'translateX(0)';
            }, 100);
        });
    </script>
</body>
</html>
