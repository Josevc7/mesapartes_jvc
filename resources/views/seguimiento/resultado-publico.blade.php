<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado de Consulta - DRTC Apurimac</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header-institucional {
            background: linear-gradient(135deg, #cc5500 0%, #2c2c2c 100%);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }
        .header-institucional img {
            height: 60px;
        }
        .card-expediente {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header-expediente {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 1.5rem;
        }
        .estado-badge {
            font-size: 1.1rem;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .estado-registrado, .estado-recepcionado { background: #17a2b8; color: white; }
        .estado-derivado { background: #0d6efd; color: white; }
        .estado-en_proceso, .estado-asignado { background: #ffc107; color: #212529; }
        .estado-resuelto, .estado-aprobado { background: #198754; color: white; }
        .estado-archivado { background: #6c757d; color: white; }
        .estado-observado { background: #dc3545; color: white; }

        /* Linea de progreso */
        .progress-line {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 2rem 0;
            padding: 0 1rem;
        }
        .progress-line::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 10%;
            right: 10%;
            height: 4px;
            background: #e9ecef;
            z-index: 1;
        }
        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }
        .progress-step .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
        }
        .progress-step.completed .step-icon {
            background: #198754;
            color: white;
        }
        .progress-step.active .step-icon {
            background: #ffc107;
            color: #212529;
            transform: scale(1.2);
            box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.3);
        }
        .progress-step.pending .step-icon {
            background: #e9ecef;
            color: #6c757d;
        }
        .progress-step .step-label {
            font-size: 0.75rem;
            text-align: center;
            color: #6c757d;
        }
        .progress-step.completed .step-label,
        .progress-step.active .step-label {
            color: #212529;
            font-weight: 600;
        }

        /* Info cards */
        .info-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem;
            height: 100%;
        }
        .info-card .label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        .info-card .value {
            font-size: 1rem;
            font-weight: 600;
            color: #212529;
        }

        /* Timeline historial */
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #0d6efd;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #0d6efd;
        }
        .timeline-item.first::before {
            background: #198754;
            box-shadow: 0 0 0 2px #198754;
        }
        .timeline-date {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
        .timeline-content {
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border-left: 3px solid #0d6efd;
        }
        .timeline-item.first .timeline-content {
            border-left-color: #198754;
        }

        /* Mensaje institucional */
        .mensaje-institucional {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-left: 4px solid #198754;
            padding: 1rem 1.5rem;
            border-radius: 0 8px 8px 0;
        }
        .mensaje-institucional.warning {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            border-left-color: #ff9800;
        }
        .mensaje-institucional.info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-left-color: #2196f3;
        }

        .btn-nueva-consulta {
            background: linear-gradient(135deg, #cc5500 0%, #ff7700 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
            color: white;
            transition: all 0.3s;
        }
        .btn-nueva-consulta:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(204, 85, 0, 0.4);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Header Institucional -->
    <div class="header-institucional">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('images/logo-drtc.png') }}" alt="Logo DRTC" class="me-3">
                    <div>
                        <h5 class="mb-0">DRTC Apurimac</h5>
                        <small class="opacity-75">Sistema de Tramite Documentario</small>
                    </div>
                </div>
                <a href="{{ route('seguimiento.consulta-publica') }}" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-search me-1"></i> Nueva Consulta
                </a>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        @foreach($expedientes as $expediente)
        @php
            // Determinar el paso actual del progreso
            $pasos = ['registrado', 'derivado', 'en_proceso', 'resuelto'];
            $estadoActual = $expediente->estado;
            $pasoActual = 0;

            if (in_array($estadoActual, ['registrado', 'recepcionado', 'clasificado'])) $pasoActual = 1;
            elseif ($estadoActual == 'derivado') $pasoActual = 2;
            elseif (in_array($estadoActual, ['en_proceso', 'asignado', 'aprobado'])) $pasoActual = 3;
            elseif (in_array($estadoActual, ['resuelto', 'archivado', 'notificado'])) $pasoActual = 4;

            // Mensaje segun estado
            $mensajeEstado = match($estadoActual) {
                'registrado', 'recepcionado', 'clasificado' => 'Su expediente ha sido registrado y esta siendo clasificado para su derivacion al area correspondiente.',
                'derivado' => 'Su expediente ha sido derivado al area competente y esta pendiente de recepcion.',
                'en_proceso', 'asignado' => 'Su expediente se encuentra en evaluacion por el area correspondiente.',
                'aprobado' => 'Su expediente ha sido aprobado y esta en proceso de resolucion final.',
                'resuelto' => 'Su tramite ha sido atendido. Puede recoger la respuesta en la entidad o revisar su bandeja si ingreso por via virtual.',
                'archivado' => 'Su tramite ha sido finalizado y archivado. Gracias por utilizar nuestros servicios.',
                'observado' => 'Su expediente tiene observaciones pendientes. Por favor, regularice la documentacion requerida.',
                default => 'Su expediente esta siendo procesado.'
            };

            $tipoMensaje = match($estadoActual) {
                'resuelto', 'archivado', 'aprobado' => '',
                'observado' => 'warning',
                default => 'info'
            };
        @endphp

        <div class="card card-expediente mb-4">
            <!-- Header del expediente -->
            <div class="card-header-expediente">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1">
                            <i class="fas fa-file-alt me-2"></i>
                            Expediente: {{ $expediente->codigo_expediente }}
                        </h4>
                        <p class="mb-0 opacity-75">
                            <i class="fas fa-calendar me-1"></i> Fecha de Ingreso: {{ $expediente->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="estado-badge estado-{{ $estadoActual }}">
                            {{ strtoupper(str_replace('_', ' ', $estadoActual)) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Linea de Progreso -->
                <div class="progress-line mb-4">
                    <div class="progress-step {{ $pasoActual >= 1 ? ($pasoActual == 1 ? 'active' : 'completed') : 'pending' }}">
                        <div class="step-icon">
                            @if($pasoActual > 1) <i class="fas fa-check"></i> @else 1 @endif
                        </div>
                        <span class="step-label">Registrado</span>
                    </div>
                    <div class="progress-step {{ $pasoActual >= 2 ? ($pasoActual == 2 ? 'active' : 'completed') : 'pending' }}">
                        <div class="step-icon">
                            @if($pasoActual > 2) <i class="fas fa-check"></i> @else 2 @endif
                        </div>
                        <span class="step-label">Derivado</span>
                    </div>
                    <div class="progress-step {{ $pasoActual >= 3 ? ($pasoActual == 3 ? 'active' : 'completed') : 'pending' }}">
                        <div class="step-icon">
                            @if($pasoActual > 3) <i class="fas fa-check"></i> @else 3 @endif
                        </div>
                        <span class="step-label">En Proceso</span>
                    </div>
                    <div class="progress-step {{ $pasoActual >= 4 ? 'completed' : 'pending' }}">
                        <div class="step-icon">
                            @if($pasoActual >= 4) <i class="fas fa-check"></i> @else 4 @endif
                        </div>
                        <span class="step-label">Resuelto</span>
                    </div>
                </div>

                <!-- Mensaje Institucional -->
                <div class="mensaje-institucional {{ $tipoMensaje }} mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle me-3 mt-1 fs-5"></i>
                        <div>
                            <strong>Estado de su Tramite</strong>
                            <p class="mb-0 mt-1">{{ $mensajeEstado }}</p>
                        </div>
                    </div>
                </div>

                <!-- Informacion General -->
                <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Informacion General</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="label">Tipo de Tramite</div>
                            <div class="value">{{ $expediente->tipoTramite->nombre ?? 'Solicitud' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="label">Area Responsable</div>
                            <div class="value">{{ $expediente->area->nombre ?? 'Mesa de Partes' }}</div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="info-card">
                            <div class="label">Solicitante</div>
                            <div class="value">{{ $expediente->persona->nombre_completo ?? $expediente->persona->razon_social ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Historial Simplificado -->
                <h6 class="text-muted mb-3"><i class="fas fa-history me-2"></i>Historial de Movimientos</h6>
                <div class="timeline">
                    @php
                        $historialPublico = $expediente->historial()
                            ->orderBy('created_at', 'desc')
                            ->take(10)
                            ->get();
                    @endphp

                    @forelse($historialPublico as $index => $item)
                    @php
                        // Simplificar descripcion para ciudadano (quitar nombres de funcionarios)
                        $descripcionSimple = $item->descripcion;
                        $descripcionSimple = preg_replace('/por\s+[A-Za-zÀ-ÿ\s]+\.?\s*/i', '', $descripcionSimple);
                        $descripcionSimple = preg_replace('/a\s+[A-Za-zÀ-ÿ\s]+\s+por\s+/i', 'al area correspondiente ', $descripcionSimple);
                        $descripcionSimple = preg_replace('/Jefe de Área\s*/i', '', $descripcionSimple);
                        $descripcionSimple = preg_replace('/Funcionario responsable:\s*[^.]+\.?/i', '', $descripcionSimple);
                        $descripcionSimple = preg_replace('/Estado anterior:\s*[^.]+\.?/i', '', $descripcionSimple);
                        $descripcionSimple = trim($descripcionSimple);
                        if (empty($descripcionSimple)) $descripcionSimple = $item->descripcion;
                    @endphp
                    <div class="timeline-item {{ $index === 0 ? 'first' : '' }}">
                        <div class="timeline-date">
                            <i class="fas fa-clock me-1"></i>
                            {{ $item->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="timeline-content">
                            {{ $descripcionSimple }}
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-light">
                        <i class="fas fa-info-circle me-2"></i>
                        Expediente recien registrado, pendiente de derivacion.
                    </div>
                    @endforelse
                </div>

                <!-- Documentos del ciudadano -->
                @php
                    $documentosCiudadano = $expediente->documentos->where('tipo', 'entrada');
                @endphp
                @if($documentosCiudadano->count() > 0)
                <hr class="my-4">
                <h6 class="text-muted mb-3"><i class="fas fa-paperclip me-2"></i>Documentos Adjuntos</h6>
                <div class="list-group">
                    @foreach($documentosCiudadano as $doc)
                    <a href="{{ route('documentos.visualizar', $doc->id_documento) }}"
                       class="list-group-item list-group-item-action d-flex align-items-center" target="_blank">
                        <i class="fas fa-file-pdf text-danger me-3 fs-4"></i>
                        <div>
                            <strong>{{ $doc->nombre }}</strong>
                            <br><small class="text-muted">Documento presentado por el ciudadano</small>
                        </div>
                        <i class="fas fa-external-link-alt ms-auto text-muted"></i>
                    </a>
                    @endforeach
                </div>
                @endif

                <!-- Si esta resuelto, mostrar info adicional -->
                @if(in_array($estadoActual, ['resuelto', 'archivado']))
                <hr class="my-4">
                <div class="alert alert-success">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fs-1 me-3"></i>
                        <div>
                            <h5 class="mb-1">Tramite Finalizado</h5>
                            @if($expediente->fecha_resolucion)
                            <p class="mb-0">Fecha de resolucion: <strong>{{ $expediente->fecha_resolucion->format('d/m/Y') }}</strong></p>
                            @endif
                            <p class="mb-0 mt-2">
                                <small>Puede acercarse a la entidad para recoger su respuesta o revisar su bandeja virtual.</small>
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach

        <!-- Boton Nueva Consulta -->
        <div class="text-center mt-4">
            <a href="{{ route('seguimiento.consulta-publica') }}" class="btn btn-nueva-consulta">
                <i class="fas fa-search me-2"></i> Realizar Nueva Consulta
            </a>
            <br>
            <a href="{{ route('login') }}" class="btn btn-link mt-3 text-muted">
                <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesion
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <small>
            <i class="fas fa-shield-alt me-1"></i>
            2026 Direccion Regional de Transportes y Comunicaciones - Apurimac
        </small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
