<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Publica de Expedientes - DRTC</title>
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
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        .form-control:focus, .form-select:focus {
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
        .btn-consulta {
            background: linear-gradient(135deg, #cc5500 0%, #ff7700 100%);
            border: none;
            padding: 15px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            color: white;
        }
        .btn-consulta:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(204, 85, 0, 0.4);
            color: white;
        }
        .btn-volver {
            border: 2px solid #6c757d;
            color: #6c757d;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-volver:hover {
            background: #6c757d;
            color: white;
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
                <h1 class="logo-title">Mesa de Partes DRTC-Apurimac</h1>
                <p class="logo-subtitle">Sistema de Tramite Documentario - DRTC Apurimac</p>

                <ul class="logo-features list-unstyled">
                    <li><i class="fas fa-search"></i> Consulta el estado de tu expediente</li>
                    <li><i class="fas fa-clock"></i> Seguimiento en tiempo real</li>
                    <li><i class="fas fa-file-alt"></i> Historial de movimientos</li>
                    <li><i class="fas fa-lock"></i> Acceso seguro con tu documento</li>
                </ul>
            </div>

            <div class="text-center mt-auto pt-4" style="position: relative; z-index: 2;">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="fas fa-shield-alt text-white me-2"></i>
                    <small class="text-white opacity-90 fw-semibold">Consulta Segura</small>
                </div>
                <small class="text-white opacity-75">
                    2026 Direccion Regional de Transportes y Comunicaciones Apurimac
                </small>
            </div>
        </div>

        <!-- Panel Derecho - Formulario -->
        <div class="form-panel">
            <div class="form-container">
                <div class="form-header">
                    <h2><i class="fas fa-search me-2" style="color: #cc5500;"></i>Consulta Publica de Expedientes</h2>
                    <p>Ingresa los datos para consultar el estado de tu tramite</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger border-0 mb-4" style="border-radius: 12px;">
                        @foreach ($errors->all() as $error)
                            <p class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger border-0 mb-4" style="border-radius: 12px;">
                        <p class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('seguimiento.buscar') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="codigo_expediente" class="form-label fw-semibold text-dark mb-2">
                            <i class="fas fa-file-alt me-2" style="color: #cc5500;"></i>Numero de Expediente <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-hashtag text-muted"></i>
                            </span>
                            <input type="text" class="form-control @error('codigo_expediente') is-invalid @enderror"
                                   id="codigo_expediente" name="codigo_expediente"
                                   value="{{ old('codigo_expediente') }}"
                                   placeholder="Ej: 2025-000001" required>
                        </div>
                        <small class="form-text text-muted ms-2">
                            <i class="fas fa-info-circle me-1"></i>Ingrese el codigo completo o parcial
                        </small>
                        @error('codigo_expediente')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="tipo_documento" class="form-label fw-semibold text-dark mb-2">
                            <i class="fas fa-id-card me-2" style="color: #cc5500;"></i>Tipo de Documento <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('tipo_documento') is-invalid @enderror"
                                id="tipo_documento" name="tipo_documento" required onchange="cambiarTipoDocumento()">
                            <option value="DNI" {{ old('tipo_documento', 'DNI') == 'DNI' ? 'selected' : '' }}>DNI (Persona Natural)</option>
                            <option value="RUC" {{ old('tipo_documento') == 'RUC' ? 'selected' : '' }}>RUC (Persona Juridica)</option>
                        </select>
                        @error('tipo_documento')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="numero_documento" class="form-label fw-semibold text-dark mb-2">
                            <i class="fas fa-keyboard me-2" style="color: #cc5500;"></i>
                            <span id="label_documento">DNI</span> del Solicitante <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user text-muted"></i>
                            </span>
                            <input type="text" class="form-control @error('numero_documento') is-invalid @enderror"
                                   id="numero_documento" name="numero_documento"
                                   value="{{ old('numero_documento') }}"
                                   placeholder="Ingrese su documento" maxlength="8" required
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <small id="ayuda_documento" class="form-text text-muted ms-2">
                            <i class="fas fa-info-circle me-1"></i>Ingrese 8 digitos
                        </small>
                        @error('numero_documento')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-consulta">
                            <i class="fas fa-search me-2"></i>Consultar Expediente
                        </button>
                    </div>
                </form>

                <div class="divider">
                    <span>Opciones</span>
                </div>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="btn btn-volver">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesion
                    </a>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Ingrese el numero de expediente y su documento para consultar el estado de su tramite
                        </small>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function cambiarTipoDocumento() {
            const tipo = document.getElementById('tipo_documento').value;
            const inputDoc = document.getElementById('numero_documento');
            const labelDoc = document.getElementById('label_documento');
            const ayudaDoc = document.getElementById('ayuda_documento');

            if (tipo === 'RUC') {
                inputDoc.maxLength = 11;
                inputDoc.placeholder = 'Ingrese RUC';
                labelDoc.textContent = 'RUC';
                ayudaDoc.innerHTML = '<i class="fas fa-info-circle me-1"></i>Ingrese 11 digitos';
            } else {
                inputDoc.maxLength = 8;
                inputDoc.placeholder = 'Ingrese DNI';
                labelDoc.textContent = 'DNI';
                ayudaDoc.innerHTML = '<i class="fas fa-info-circle me-1"></i>Ingrese 8 digitos';
            }
            inputDoc.value = '';
        }

        document.addEventListener('DOMContentLoaded', function() {
            cambiarTipoDocumento();

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
