@extends('layouts.app')

@section('title', 'Registrar Documento')

@push('styles')
<link href="{{ asset('css/ciudadano-form.css') }}" rel="stylesheet">
<link href="{{ asset('css/modern-forms.css') }}" rel="stylesheet">
<link href="{{ asset('css/compact-form.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="modern-form-container">
    <div class="modern-form-card">
        <!-- ENCABEZADO MODERNO -->
        <div class="modern-form-header">
            <div class="modern-form-header-content">
                <div class="modern-form-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="modern-form-title">
                    <h1>Registrar Documento Entrante</h1>
                    <p>Mesa de Partes - Registro, Clasificación y Derivación de Expedientes</p>
                </div>
            </div>
        </div>

        <!-- CUERPO DEL FORMULARIO -->
        <div class="modern-form-body">
                    @if(session('success') && !session('codigo_expediente'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-20 rounded-circle p-2 me-3">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading mb-1">¡Documento registrado exitosamente!</h6>
                                    <p class="mb-0">{{ session('success') }}</p>
                                    @if(session('codigo_expediente'))
                                        @php
                                            $expediente = \App\Models\Expediente::where('codigo_expediente', session('codigo_expediente'))->first();
                                        @endphp
                                        <div class="mt-2">
                                            <small class="d-block mb-2">Código de expediente: <strong class="text-success">{{ session('codigo_expediente') }}</strong></small>
                                            @if($expediente)
                                                <a href="{{ route('mesa-partes.cargo', $expediente->id_expediente) }}"
                                                   class="btn btn-sm btn-success"
                                                   target="_blank">
                                                    <i class="fas fa-print me-1"></i> Imprimir Cargo
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm">
                            <div class="d-flex align-items-start">
                                <div class="bg-danger bg-opacity-20 rounded-circle p-2 me-3 mt-1">
                                    <i class="fas fa-exclamation-triangle text-danger"></i>
                                </div>
                                <div>
                                    <h6 class="alert-heading mb-2">Errores en el formulario:</h6>
                                    <ul class="mb-0 ps-3">
                                        @foreach($errors->all() as $error)
                                            <li class="mb-1">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('mesa-partes.store-registrar') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="persona_existente_id" name="persona_existente_id" value="">
                        
                        <!-- Sección 1: Identificación del Solicitante -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-user text-primary fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-primary fw-bold">1. Identificación del Solicitante</h5>
                                    <p class="text-muted mb-0 small">Busque o registre los datos del ciudadano</p>
                                </div>
                            </div>
                            
                            <div class="row g-4">
                                <!-- Tipo de Persona (siempre visible) -->
                                <div class="col-md-3">
                                    <label for="tipo_persona" class="form-label fw-semibold">
                                        <i class="fas fa-user-tag text-primary me-2"></i>Tipo de Persona *
                                    </label>
                                    <select class="form-select form-select-lg @error('tipo_persona') is-invalid @enderror"
                                            id="tipo_persona" name="tipo_persona" required>
                                        <option value="NATURAL" {{ old('tipo_persona', 'NATURAL') == 'NATURAL' ? 'selected' : '' }}>Persona Natural</option>
                                        <option value="JURIDICA" {{ old('tipo_persona') == 'JURIDICA' ? 'selected' : '' }}>Persona Jurídica</option>
                                    </select>
                                    @error('tipo_persona')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Campos para Persona Natural -->
                                <div id="campos-natural-doc" class="col-md-9">
                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <label for="tipo_documento" class="form-label fw-semibold">
                                                <i class="fas fa-id-card text-primary me-2"></i>Tipo de Documento *
                                            </label>
                                            <select class="form-select form-select-lg @error('tipo_documento') is-invalid @enderror"
                                                    id="tipo_documento" name="tipo_documento">
                                                <option value="DNI" {{ old('tipo_documento', 'DNI') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                                <option value="CE" {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Carné de Extranjería</option>
                                                <option value="RUC" {{ old('tipo_documento') == 'RUC' ? 'selected' : '' }}>RUC</option>
                                                <option value="PASAPORTE" {{ old('tipo_documento') == 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                                                <option value="OTROS" {{ old('tipo_documento') == 'OTROS' ? 'selected' : '' }}>Otros</option>
                                            </select>
                                            @error('tipo_documento')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-8">
                                            <label for="numero_documento" class="form-label fw-semibold">
                                                <i class="fas fa-search text-primary me-2"></i>Número de Documento *
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <input type="text" class="form-control @error('numero_documento') is-invalid @enderror"
                                                       id="numero_documento" name="numero_documento" value="{{ old('numero_documento') }}"
                                                       placeholder="Ingrese documento y presione Enter">
                                                <button type="button" class="btn btn-primary" id="btn-buscar" onclick="buscarPersona()">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">Presione Enter o el botón para buscar si la persona ya existe</div>
                                            @error('numero_documento')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Campos para Persona Jurídica -->
                                <div id="campos-juridica-doc" class="col-md-9" style="display: none;">
                                    <div class="row g-4">
                                        <div class="col-md-12">
                                            <label for="numero_documento_juridica" class="form-label fw-semibold">
                                                <i class="fas fa-search text-primary me-2"></i>RUC *
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <input type="text" class="form-control"
                                                       id="numero_documento_juridica"
                                                       placeholder="Ingrese RUC (11 dígitos) y presione Enter"
                                                       maxlength="11">
                                                <button type="button" class="btn btn-primary" id="btn-buscar-juridica" onclick="buscarPersonaJuridica()">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">Presione Enter o el botón para buscar si la empresa ya existe</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="persona-encontrada" class="alert alert-info" style="display: none;">
                            <i class="fas fa-info-circle"></i> <strong>Persona encontrada:</strong> 
                            <span id="persona-info"></span>
                            <button type="button" class="btn btn-sm btn-primary ms-2" onclick="usarPersonaExistente()">Usar datos</button>
                            <button type="button" class="btn btn-sm btn-secondary ms-1" onclick="nuevaPersona()">Registrar nueva</button>
                        </div>

                        <!-- Sección 2: Datos Personales -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-address-card text-success fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-success fw-bold">2. Datos Personales</h5>
                                    <p class="text-muted mb-0 small">Complete la información personal del solicitante</p>
                                </div>
                            </div>
                            
                            <div id="persona-natural" class="persona-fields">
                                <div class="three-columns">
                                    <div class="adaptive-field">
                                        <label for="nombres" class="form-label">Nombres *</label>
                                        <input type="text" class="form-control @error('nombres') is-invalid @enderror" 
                                               id="nombres" name="nombres" value="{{ old('nombres') }}">
                                        @error('nombres')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="adaptive-field">
                                        <label for="apellido_paterno" class="form-label">Apellido Paterno *</label>
                                        <input type="text" class="form-control @error('apellido_paterno') is-invalid @enderror" 
                                               id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}">
                                        @error('apellido_paterno')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="adaptive-field">
                                        <label for="apellido_materno" class="form-label">Apellido Materno</label>
                                        <input type="text" class="form-control @error('apellido_materno') is-invalid @enderror" 
                                               id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno') }}">
                                        @error('apellido_materno')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div id="persona-juridica" class="persona-fields" style="display: none;">
                                <div class="three-columns">
                                    <div class="adaptive-field">
                                        <label for="razon_social" class="form-label">Razón Social *</label>
                                        <input type="text" class="form-control @error('razon_social') is-invalid @enderror" 
                                               id="razon_social" name="razon_social" value="{{ old('razon_social') }}">
                                        @error('razon_social')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="adaptive-field">
                                        <label for="representante_legal" class="form-label">Representante Legal</label>
                                        <input type="text" class="form-control @error('representante_legal') is-invalid @enderror" 
                                               id="representante_legal" name="representante_legal" value="{{ old('representante_legal') }}">
                                        @error('representante_legal')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="adaptive-field">
                                        <!-- Campo vacío para mantener la estructura de 3 columnas -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sección 3: Datos de Contacto -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-phone text-info fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-info fw-bold">3. Datos de Contacto</h5>
                                    <p class="text-muted mb-0 small">Información de contacto para notificaciones</p>
                                </div>
                            </div>
                            
                            <div class="three-columns">
                                <div class="adaptive-field">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                           id="telefono" name="telefono" value="{{ old('telefono') }}">
                                    @error('telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="adaptive-field">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="adaptive-field">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                                           id="direccion" name="direccion" value="{{ old('direccion') }}">
                                    @error('direccion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Sección 4: Datos del Documento -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-file-alt text-warning fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-warning fw-bold">4. Datos del Documento</h5>
                                    <p class="text-muted mb-0 small">Registre el documento que ingresa</p>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-3">
                                    <label for="tipo_documento_entrante" class="form-label fw-semibold">
                                        <i class="fas fa-file-invoice text-warning me-2"></i>Tipo de Documento *
                                    </label>
                                    <select class="form-select form-select-lg @error('tipo_documento_entrante') is-invalid @enderror"
                                            id="tipo_documento_entrante" name="tipo_documento_entrante" required>
                                        <option value="">Seleccione...</option>
                                        <option value="SOLICITUD" {{ old('tipo_documento_entrante') == 'SOLICITUD' ? 'selected' : '' }}>Solicitud</option>
                                        <option value="FUT" {{ old('tipo_documento_entrante') == 'FUT' ? 'selected' : '' }}>FUT (Formulario Único de Trámite)</option>
                                        <option value="OFICIO" {{ old('tipo_documento_entrante') == 'OFICIO' ? 'selected' : '' }}>Oficio</option>
                                        <option value="INFORME" {{ old('tipo_documento_entrante') == 'INFORME' ? 'selected' : '' }}>Informe</option>
                                        <option value="MEMORANDUM" {{ old('tipo_documento_entrante') == 'MEMORANDUM' ? 'selected' : '' }}>Memorándum</option>
                                        <option value="CARTA" {{ old('tipo_documento_entrante') == 'CARTA' ? 'selected' : '' }}>Carta</option>
                                        <option value="RESOLUCION" {{ old('tipo_documento_entrante') == 'RESOLUCION' ? 'selected' : '' }}>Resolución</option>
                                        <option value="OTROS" {{ old('tipo_documento_entrante') == 'OTROS' ? 'selected' : '' }}>Otros</option>
                                    </select>
                                    @error('tipo_documento_entrante')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="numero_documento_entrante" class="form-label fw-semibold">
                                        <i class="fas fa-hashtag text-warning me-2"></i>N° de Documento
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('numero_documento_entrante') is-invalid @enderror"
                                           id="numero_documento_entrante" name="numero_documento_entrante" value="{{ old('numero_documento_entrante') }}"
                                           placeholder="Ej: 001-2026">
                                    @error('numero_documento_entrante')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="asunto_documento" class="form-label fw-semibold">
                                        <i class="fas fa-align-left text-warning me-2"></i>Asunto *
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('asunto_documento') is-invalid @enderror"
                                           id="asunto_documento" name="asunto_documento" value="{{ old('asunto_documento') }}"
                                           placeholder="Resumen del contenido del documento" required>
                                    @error('asunto_documento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-2">
                                    <label for="folios" class="form-label fw-semibold">
                                        <i class="fas fa-copy text-warning me-2"></i>Folios *
                                    </label>
                                    <input type="number" class="form-control form-control-lg @error('folios') is-invalid @enderror"
                                           id="folios" name="folios" value="{{ old('folios', 1) }}"
                                           min="1" max="9999" required>
                                    @error('folios')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="documento" class="form-label fw-semibold">
                                        <i class="fas fa-file-pdf text-warning me-2"></i>Documento PDF
                                    </label>
                                    <input type="file" class="form-control form-control-lg @error('documento') is-invalid @enderror"
                                           id="documento" name="documento" accept=".pdf">
                                    <div class="form-text">Opcional - Máximo 10MB</div>
                                    @error('documento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sección 5: Clasificación y Tipo de Trámite -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-tags text-success fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-success fw-bold">5. Clasificación y Tipo de Trámite</h5>
                                    <p class="text-muted mb-0 small">Seleccione el área de destino y el tipo de trámite correspondiente</p>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label for="id_area" class="form-label fw-semibold">
                                        <i class="fas fa-building text-success me-2"></i>Área de Destino *
                                    </label>
                                    <select class="form-select form-select-lg @error('id_area') is-invalid @enderror"
                                            id="id_area" name="id_area" required>
                                        <option value="">Seleccione un área</option>
                                        @foreach(\App\Models\Area::where('activo', true)->orderBy('nombre')->get() as $area)
                                            <option value="{{ $area->id_area }}" {{ old('id_area') == $area->id_area ? 'selected' : '' }}>
                                                {{ $area->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_area')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="id_tipo_tramite" class="form-label fw-semibold">
                                        <i class="fas fa-tasks text-success me-2"></i>Tipo de Trámite *
                                    </label>
                                    <select class="form-select form-select-lg @error('id_tipo_tramite') is-invalid @enderror"
                                            id="id_tipo_tramite" name="id_tipo_tramite" required>
                                        <option value="">Primero seleccione un área</option>
                                    </select>
                                    <div class="form-text">Se cargará según el área seleccionada</div>
                                    @error('id_tipo_tramite')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="prioridad" class="form-label fw-semibold">
                                        <i class="fas fa-exclamation-circle text-success me-2"></i>Prioridad *
                                    </label>
                                    <select class="form-select form-select-lg @error('prioridad') is-invalid @enderror"
                                            id="prioridad" name="prioridad" required>
                                        <option value="normal" {{ old('prioridad', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="baja" {{ old('prioridad') == 'baja' ? 'selected' : '' }}>Baja</option>
                                        <option value="alta" {{ old('prioridad') == 'alta' ? 'selected' : '' }}>Alta</option>
                                        <option value="urgente" {{ old('prioridad') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                    </select>
                                    @error('prioridad')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Sección 6: Derivación del Expediente -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-share text-warning fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-warning fw-bold">6. Derivación del Expediente</h5>
                                    <p class="text-muted mb-0 small">Asigne el funcionario y establezca plazos</p>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="id_funcionario_asignado" class="form-label fw-semibold">
                                        <i class="fas fa-user-tie text-warning me-2"></i>Funcionario Asignado
                                    </label>
                                    <select class="form-select form-select-lg @error('id_funcionario_asignado') is-invalid @enderror"
                                            id="id_funcionario_asignado" name="id_funcionario_asignado">
                                        <option value="">Sin asignar (el jefe asignará después)</option>
                                    </select>
                                    <div class="form-text">Se cargarán los funcionarios del área seleccionada</div>
                                    @error('id_funcionario_asignado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="plazo_dias" class="form-label fw-semibold">
                                        <i class="fas fa-calendar-alt text-warning me-2"></i>Plazo (días) *
                                    </label>
                                    <input type="number" class="form-control form-control-lg @error('plazo_dias') is-invalid @enderror"
                                           id="plazo_dias" name="plazo_dias" value="{{ old('plazo_dias', 15) }}"
                                           min="1" max="365" required>
                                    <div class="form-text">Se actualiza según el tipo de trámite</div>
                                    @error('plazo_dias')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="observaciones_clasificacion" class="form-label fw-semibold">
                                        <i class="fas fa-comment text-warning me-2"></i>Observaciones
                                    </label>
                                    <textarea class="form-control form-control-lg @error('observaciones_clasificacion') is-invalid @enderror"
                                              id="observaciones_clasificacion" name="observaciones_clasificacion"
                                              rows="1" placeholder="Observaciones...">{{ old('observaciones_clasificacion') }}</textarea>
                                    @error('observaciones_clasificacion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info border-0 shadow-sm mb-4">
                            <div class="d-flex align-items-start">
                                <div class="bg-info bg-opacity-20 rounded-circle p-2 me-3">
                                    <i class="fas fa-info-circle text-info"></i>
                                </div>
                                <div>
                                    <h6 class="alert-heading mb-2">Registro Completo en Un Solo Paso</h6>
                                    <p class="mb-0 small">
                                        Al hacer clic en "Registrar, Clasificar y Derivar", el expediente se creará automáticamente
                                        con todos los datos ingresados y quedará listo para ser atendido por el funcionario asignado.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-end pt-4 border-top">
                            <a href="{{ route('mesa-partes.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="fas fa-arrow-left me-2"></i>Volver a Mesa de Partes
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                                <i class="fas fa-check-double me-2"></i>Registrar, Clasificar y Derivar
                            </button>
                        </div>
                    </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación de registro exitoso -->
@if(session('codigo_expediente'))
    @php
        $expediente = \App\Models\Expediente::where('codigo_expediente', session('codigo_expediente'))->first();
    @endphp
    @if($expediente)
        <div class="modal fade" id="modalCargoExitoso" tabindex="-1" aria-labelledby="modalCargoExitosoLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success text-white border-0">
                        <h5 class="modal-title" id="modalCargoExitosoLabel">
                            <i class="fas fa-check-circle me-2"></i>¡Registro Exitoso!
                        </h5>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="mb-4">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <i class="fas fa-file-alt fa-3x text-success"></i>
                            </div>
                            <h4 class="mb-3">Expediente Registrado Correctamente</h4>
                            <div class="alert alert-info border-0 mb-3">
                                <p class="mb-1"><strong>Código de Expediente:</strong></p>
                                <h3 class="text-primary mb-0">{{ session('codigo_expediente') }}</h3>
                            </div>
                            <p class="text-muted mb-0">El expediente ha sido registrado, clasificado y derivado exitosamente.</p>
                        </div>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-success btn-lg px-4" onclick="imprimirCargo()">
                            <i class="fas fa-print me-2"></i>Imprimir Cargo
                        </button>
                        <button type="button" class="btn btn-primary px-4" onclick="nuevoRegistro()">
                            <i class="fas fa-plus me-2"></i>Nuevo Registro
                        </button>
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif

@section('scripts')
<script>
let personaEncontrada = null;

document.addEventListener('DOMContentLoaded', function() {
    const tipoPersonaSelect = document.getElementById('tipo_persona');
    const numeroDocumento = document.getElementById('numero_documento');
    const numeroDocumentoJuridica = document.getElementById('numero_documento_juridica');

    // Búsqueda automática al completar 8 dígitos del DNI - Persona Natural
    numeroDocumento.addEventListener('input', function() {
        const valor = this.value.trim();

        // Buscar automáticamente cuando tenga exactamente 8 dígitos
        if (valor.length === 8 && /^\d{8}$/.test(valor)) {
            console.log('DNI completo detectado, buscando automáticamente...');
            // Limpiar campos del documento para nuevo registro
            limpiarCamposDocumento();
            buscarPersona();
        } else {
            // Limpiar solo si no tiene 8 dígitos
            document.getElementById('persona-encontrada').style.display = 'none';
            personaEncontrada = null;
        }
    });

    // Búsqueda automática al presionar Enter - Persona Natural
    numeroDocumento.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarPersona();
        }
    });

    // Búsqueda automática al completar 11 dígitos del RUC - Persona Jurídica
    numeroDocumentoJuridica.addEventListener('input', function() {
        const valor = this.value.trim();

        // Buscar automáticamente cuando tenga exactamente 11 dígitos
        if (valor.length === 11 && /^\d{11}$/.test(valor)) {
            console.log('RUC completo detectado, buscando automáticamente...');
            // Limpiar campos del documento para nuevo registro
            limpiarCamposDocumento();
            buscarPersonaJuridica();
        } else {
            // Limpiar solo si no tiene 11 dígitos
            document.getElementById('persona-encontrada').style.display = 'none';
            personaEncontrada = null;
        }
    });

    // Búsqueda automática al presionar Enter - Persona Jurídica
    numeroDocumentoJuridica.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarPersonaJuridica();
        }
    });
});

function buscarPersona() {
    const numeroDoc = document.getElementById('numero_documento').value.trim();
    const tipoDoc = document.getElementById('tipo_documento').value;
    
    if (!numeroDoc) {
        alert('Ingrese un número de documento');
        return;
    }
    
    // Validar según tipo de documento
    if (tipoDoc === 'DNI' && (numeroDoc.length !== 8 || !/^\d{8}$/.test(numeroDoc))) {
        alert('Ingrese un DNI válido de 8 dígitos');
        return;
    }
    
    if (tipoDoc === 'RUC' && (numeroDoc.length !== 11 || !/^\d{11}$/.test(numeroDoc))) {
        alert('Ingrese un RUC válido de 11 dígitos');
        return;
    }
    
    const btnBuscar = document.getElementById('btn-buscar');
    btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btnBuscar.disabled = true;
    
    console.log('Buscando persona con documento:', numeroDoc);
    
    fetch(`{{ route('mesa-partes.buscar-persona') }}?q=${numeroDoc}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            console.log('Resultado de búsqueda:', result);
            if (result.success) {
                if (result.data.length > 0) {
                    console.log('Persona encontrada:', result.data[0]);
                    mostrarPersonaEncontrada(result.data[0]);
                    usarPersonaExistente();
                } else {
                    const personaEncontradaElement = document.getElementById('persona-encontrada');
                    if (personaEncontradaElement) {
                        personaEncontradaElement.style.display = 'none';
                    }
                    // No mostrar alerta en búsqueda automática
                    console.log('Persona no encontrada. Usuario puede registrar una nueva.');
                }
            } else {
                console.error('Error en búsqueda:', result.error);
                alert(result.error || 'Error al buscar persona');
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            alert('Error de conexión: ' + error.message);
        })
        .finally(() => {
            btnBuscar.innerHTML = '<i class="fas fa-search"></i>';
            btnBuscar.disabled = false;
        });
}

function buscarPersonaJuridica() {
    const numeroDoc = document.getElementById('numero_documento_juridica').value.trim();

    if (!numeroDoc) {
        alert('Ingrese un número de RUC');
        return;
    }

    // Validar RUC
    if (numeroDoc.length !== 11 || !/^\d{11}$/.test(numeroDoc)) {
        alert('Ingrese un RUC válido de 11 dígitos');
        return;
    }

    const btnBuscar = document.getElementById('btn-buscar-juridica');
    btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btnBuscar.disabled = true;

    // Sincronizar con el campo oculto de tipo documento
    document.getElementById('tipo_documento').value = 'RUC';
    document.getElementById('numero_documento').value = numeroDoc;

    console.log('Buscando persona jurídica con RUC:', numeroDoc);

       fetch(`{{ route('mesa-partes.buscar-persona') }}?q=${numeroDoc}`)
    // he cambiado para que funcione por ip  y localhost
    //fetch(`${window.APP_URL}/mesa-partes/buscar-persona?q=${encodeURIComponent(numeroDoc)}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            console.log('Resultado de búsqueda:', result);
            if (result.success) {
                if (result.data.length > 0) {
                    console.log('Persona jurídica encontrada:', result.data[0]);
                    mostrarPersonaEncontrada(result.data[0]);
                    usarPersonaExistente();
                } else {
                    const personaEncontradaElement = document.getElementById('persona-encontrada');
                    if (personaEncontradaElement) {
                        personaEncontradaElement.style.display = 'none';
                    }
                    // No mostrar alerta en búsqueda automática
                    console.log('Empresa no encontrada. Usuario puede registrar una nueva.');
                }
            } else {
                console.error('Error en búsqueda:', result.error);
                alert(result.error || 'Error al buscar empresa');
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            alert('Error de conexión: ' + error.message);
        })
        .finally(() => {
            btnBuscar.innerHTML = '<i class="fas fa-search"></i>';
            btnBuscar.disabled = false;
        });
}

function mostrarPersonaEncontrada(persona) {
    personaEncontrada = persona;
    const info = persona.tipo_persona === 'NATURAL' 
        ? `${persona.nombres} ${persona.apellido_paterno} ${persona.apellido_materno || ''}`.trim()
        : persona.razon_social;
    
    const personaInfoElement = document.getElementById('persona-info');
    const personaEncontradaElement = document.getElementById('persona-encontrada');
    
    if (personaInfoElement && personaEncontradaElement) {
        personaInfoElement.textContent = info;
        personaEncontradaElement.style.display = 'block';
    }
}

function usarPersonaExistente() {
    if (!personaEncontrada) return;
    
    const p = personaEncontrada;
    
    // Marcar como persona existente
    const personaExistenteId = document.getElementById('persona_existente_id');
    if (personaExistenteId) personaExistenteId.value = p.id_persona;
    
    // Llenar campos básicos
    const tipoDoc = document.getElementById('tipo_documento');
    const numeroDoc = document.getElementById('numero_documento');
    const numeroDocJuridica = document.getElementById('numero_documento_juridica');
    const tipoPersona = document.getElementById('tipo_persona');

    if (tipoDoc) tipoDoc.value = p.tipo_documento || 'DNI';
    if (tipoPersona) tipoPersona.value = p.tipo_persona || 'NATURAL';

    // Llenar datos según tipo de persona
    if (p.tipo_persona === 'NATURAL') {
        if (numeroDoc) numeroDoc.value = p.numero_documento || '';

        const nombres = document.getElementById('nombres');
        const apellidoP = document.getElementById('apellido_paterno');
        const apellidoM = document.getElementById('apellido_materno');

        if (nombres) nombres.value = p.nombres || '';
        if (apellidoP) apellidoP.value = p.apellido_paterno || '';
        if (apellidoM) apellidoM.value = p.apellido_materno || '';
    } else {
        // Para persona jurídica, llenar el campo de RUC
        if (numeroDocJuridica) numeroDocJuridica.value = p.numero_documento || '';
        if (numeroDoc) numeroDoc.value = p.numero_documento || '';

        const razonSocial = document.getElementById('razon_social');
        const representante = document.getElementById('representante_legal');

        if (razonSocial) razonSocial.value = p.razon_social || '';
        if (representante) representante.value = p.representante_legal || '';
    }
    
    // Llenar datos de contacto
    const telefono = document.getElementById('telefono');
    const email = document.getElementById('email');
    const direccion = document.getElementById('direccion');
    
    if (telefono) telefono.value = p.telefono || '';
    if (email) email.value = p.email || '';
    if (direccion) direccion.value = p.direccion || '';
    
    // Actualizar vista
    togglePersonaFields();
    
    const personaEncontradaElement = document.getElementById('persona-encontrada');
    if (personaEncontradaElement) {
        personaEncontradaElement.style.display = 'none';
    }
    
    // Deshabilitar campos de persona (solo lectura)
    deshabilitarCamposPersona(true);
}

function nuevaPersona() {
    const personaEncontradaElement = document.getElementById('persona-encontrada');
    const personaExistenteIdElement = document.getElementById('persona_existente_id');
    
    if (personaEncontradaElement) {
        personaEncontradaElement.style.display = 'none';
    }
    if (personaExistenteIdElement) {
        personaExistenteIdElement.value = '';
    }
    
    limpiarCamposPersona();
    deshabilitarCamposPersona(false);
    personaEncontrada = null;
}

function limpiarCamposPersona() {
    document.getElementById('nombres').value = '';
    document.getElementById('apellido_paterno').value = '';
    document.getElementById('apellido_materno').value = '';
    document.getElementById('razon_social').value = '';
    document.getElementById('representante_legal').value = '';
    document.getElementById('numero_documento').value = '';
    document.getElementById('numero_documento_juridica').value = '';
    document.getElementById('telefono').value = '';
    document.getElementById('email').value = '';
    document.getElementById('direccion').value = '';
}

function deshabilitarCamposPersona(deshabilitar) {
    const campos = ['nombres', 'apellido_paterno', 'apellido_materno', 'razon_social', 'representante_legal'];
    campos.forEach(campo => {
        const elemento = document.getElementById(campo);
        if (elemento) {
            elemento.readOnly = deshabilitar;
            elemento.style.backgroundColor = deshabilitar ? '#f8f9fa' : '';
        }
    });
}

// Cargar funcionarios cuando cambia el área
document.addEventListener('DOMContentLoaded', function() {
    const areaSelect = document.getElementById('id_area');
    const funcionarioSelect = document.getElementById('id_funcionario_asignado');

    const tipoTramiteSelect = document.getElementById('id_tipo_tramite');
    const plazoDiasInput = document.getElementById('plazo_dias');

    if (areaSelect) {
        areaSelect.addEventListener('change', function() {
            const areaId = this.value;

            // Limpiar select de funcionarios
            if (funcionarioSelect) {
                funcionarioSelect.innerHTML = '<option value="">Cargando...</option>';
            }

            // Limpiar select de tipos de trámite
            if (tipoTramiteSelect) {
                tipoTramiteSelect.innerHTML = '<option value="">Cargando...</option>';
            }

            if (!areaId) {
                if (funcionarioSelect) {
                    funcionarioSelect.innerHTML = '<option value="">Seleccione primero un área</option>';
                }
                if (tipoTramiteSelect) {
                    tipoTramiteSelect.innerHTML = '<option value="">Primero seleccione un área</option>';
                }
                return;
            }

            // Cargar funcionarios del área
            if (funcionarioSelect) {
                  //fetch(`/api/areas/${areaId}/funcionarios`)
                //cambiado para acceder por ip a  funcionario
                  fetch(`${window.APP_URL}/api/areas/${areaId}/funcionarios`)
                    .then(response => response.json())
                    .then(data => {
                        funcionarioSelect.innerHTML = '<option value="">Sin asignar (el jefe asignará después)</option>';

                        if (data.funcionarios && data.funcionarios.length > 0) {
                            data.funcionarios.forEach(funcionario => {
                                const option = document.createElement('option');
                                option.value = funcionario.id;
                                option.textContent = funcionario.name;
                                funcionarioSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar funcionarios:', error);
                        funcionarioSelect.innerHTML = '<option value="">Error al cargar funcionarios</option>';
                    });
            }

            // Cargar tipos de trámite del área
            if (tipoTramiteSelect) {

                  //fetch(`/api/areas/${areaId}/tipos-tramite`)
                //cambiado para acceder por ip 
                  fetch(`${window.APP_URL}/api/areas/${areaId}/tipos-tramite`)
                    .then(response => response.json())
                    .then(data => {
                        tipoTramiteSelect.innerHTML = '<option value="">Seleccione un tipo de trámite</option>';

                        if (data.tipos_tramite && data.tipos_tramite.length > 0) {
                            data.tipos_tramite.forEach(tipo => {
                                const option = document.createElement('option');
                                option.value = tipo.id_tipo_tramite;
                                option.textContent = tipo.nombre;
                                option.dataset.plazoDias = tipo.plazo_dias;
                                tipoTramiteSelect.appendChild(option);
                            });
                        } else {
                            tipoTramiteSelect.innerHTML = '<option value="">No hay trámites para esta área</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar tipos de trámite:', error);
                        tipoTramiteSelect.innerHTML = '<option value="">Error al cargar tipos de trámite</option>';
                    });
            }
        });
    }

    // Actualizar plazo de días al seleccionar tipo de trámite
    if (tipoTramiteSelect && plazoDiasInput) {
        tipoTramiteSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.dataset.plazoDias) {
                plazoDiasInput.value = selectedOption.dataset.plazoDias;
            }
        });
    }
});

function togglePersonaFields() {
    const tipoPersonaSelect = document.getElementById('tipo_persona');
    const personaNatural = document.getElementById('persona-natural');
    const personaJuridica = document.getElementById('persona-juridica');
    const camposNaturalDoc = document.getElementById('campos-natural-doc');
    const camposJuridicaDoc = document.getElementById('campos-juridica-doc');
    const tipoDocumento = document.getElementById('tipo_documento');
    const numeroDocumento = document.getElementById('numero_documento');
    const numeroDocumentoJuridica = document.getElementById('numero_documento_juridica');

    if (tipoPersonaSelect.value === 'NATURAL') {
        // Mostrar campos de persona natural
        personaNatural.style.display = 'block';
        personaJuridica.style.display = 'none';
        camposNaturalDoc.style.display = 'block';
        camposJuridicaDoc.style.display = 'none';

        // Hacer requeridos los campos de persona natural
        document.getElementById('nombres').required = true;
        document.getElementById('apellido_paterno').required = true;
        tipoDocumento.required = true;
        numeroDocumento.required = true;

        // Quitar requeridos de persona jurídica
        document.getElementById('razon_social').required = false;
        document.getElementById('razon_social').value = '';
        document.getElementById('representante_legal').value = '';
        numeroDocumentoJuridica.required = false;

        // Sincronizar el valor del RUC si existe
        if (numeroDocumentoJuridica.value) {
            numeroDocumento.value = '';
        }
    } else {
        // Mostrar campos de persona jurídica
        personaNatural.style.display = 'none';
        personaJuridica.style.display = 'block';
        camposNaturalDoc.style.display = 'none';
        camposJuridicaDoc.style.display = 'block';

        // Hacer requeridos los campos de persona jurídica
        document.getElementById('razon_social').required = true;
        numeroDocumentoJuridica.required = true;

        // Quitar requeridos de persona natural
        document.getElementById('nombres').required = false;
        document.getElementById('apellido_paterno').required = false;
        document.getElementById('nombres').value = '';
        document.getElementById('apellido_paterno').value = '';
        document.getElementById('apellido_materno').value = '';
        tipoDocumento.required = false;
        numeroDocumento.required = false;

        // Cambiar automáticamente a RUC y sincronizar valores
        tipoDocumento.value = 'RUC';
        if (numeroDocumento.value) {
            numeroDocumentoJuridica.value = numeroDocumento.value;
            numeroDocumento.value = '';
        }
    }
}

// Event listener para tipo de persona
document.addEventListener('DOMContentLoaded', function() {
    const tipoPersonaSelect = document.getElementById('tipo_persona');
    if (tipoPersonaSelect) {
        tipoPersonaSelect.addEventListener('change', togglePersonaFields);
        // Ejecutar al cargar para mostrar los campos correctos
        togglePersonaFields();
    }
});

// Agregar validación al formulario
document.addEventListener('DOMContentLoaded', function() {
    // Limpiar formulario después de mensaje de éxito
    @if(session('success'))
        setTimeout(function() {
            limpiarFormularioCompleto();
        }, 2000);
    @endif
});

// Función para limpiar todo el formulario
function limpiarFormularioCompleto() {
    // Limpiar campos de persona
    document.getElementById('persona_existente_id').value = '';
    document.getElementById('tipo_documento').value = 'DNI';
    document.getElementById('numero_documento').value = '';
    document.getElementById('tipo_persona').value = 'NATURAL';

    limpiarCamposPersona();

    // Limpiar campos de datos del documento
    limpiarCamposDocumento();

    // Limpiar campos de trámite y clasificación
    const idArea = document.getElementById('id_area');
    const idTipoTramite = document.getElementById('id_tipo_tramite');
    const prioridad = document.getElementById('prioridad');
    const funcionarioAsignado = document.getElementById('id_funcionario_asignado');
    const plazoDias = document.getElementById('plazo_dias');

    if (idArea) idArea.value = '';
    if (idTipoTramite) idTipoTramite.innerHTML = '<option value="">Primero seleccione un área</option>';
    if (prioridad) prioridad.value = 'normal';
    if (funcionarioAsignado) funcionarioAsignado.innerHTML = '<option value="">Seleccione primero un área</option>';
    if (plazoDias) plazoDias.value = '15';

    // Ocultar alertas
    document.getElementById('persona-encontrada').style.display = 'none';

    // Habilitar campos
    deshabilitarCamposPersona(false);

    // Actualizar visibilidad de campos según tipo de persona
    togglePersonaFields();

    personaEncontrada = null;
}

// Función para limpiar solo campos del documento (cuando cambia el DNI)
function limpiarCamposDocumento() {
    const tipoDocEntrante = document.getElementById('tipo_documento_entrante');
    const numeroDocEntrante = document.getElementById('numero_documento_entrante');
    const asuntoDoc = document.getElementById('asunto_documento');
    const folios = document.getElementById('folios');
    const documento = document.getElementById('documento');

    if (tipoDocEntrante) tipoDocEntrante.value = '';
    if (numeroDocEntrante) numeroDocEntrante.value = '';
    if (asuntoDoc) asuntoDoc.value = '';
    if (folios) folios.value = '1';
    if (documento) documento.value = '';
}

// Función para nuevo registro (cierra modal y limpia formulario)
function nuevoRegistro() {
    // Cerrar el modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalCargoExitoso'));
    if (modal) {
        modal.hide();
    }

    // Limpiar todo el formulario
    limpiarFormularioCompleto();

    // Hacer focus en el campo de DNI
    setTimeout(function() {
        const campoDoc = document.getElementById('numero_documento');
        if (campoDoc) {
            campoDoc.focus();
        }
    }, 300);
}
</script>

<!-- Script de validación de documentos (DNI, RUC, CE, PASAPORTE) -->
<script src="{{ asset('js/validacion-documentos.js') }}"></script>

<!-- Script para mostrar modal de confirmación y función de impresión -->
@if(session('codigo_expediente'))
    @php
        $expediente = \App\Models\Expediente::where('codigo_expediente', session('codigo_expediente'))->first();
    @endphp
    @if($expediente)
        <script>
            // Mostrar modal automáticamente al cargar la página
            window.addEventListener('DOMContentLoaded', function() {
                const modal = new bootstrap.Modal(document.getElementById('modalCargoExitoso'));
                modal.show();
            });

            // Función para imprimir cargo
            function imprimirCargo() {
                const cargoUrl = "{{ route('mesa-partes.cargo', $expediente->id_expediente) }}";
                const width = 900;
                const height = 700;
                const left = (screen.width - width) / 2;
                const top = (screen.height - height) / 2;

                window.open(
                    cargoUrl,
                    'CargoPrint',
                    `width=${width},height=${height},left=${left},top=${top},toolbar=no,menubar=no,scrollbars=yes,resizable=yes`
                );

                // Cerrar el modal después de abrir la ventana de impresión
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalCargoExitoso'));
                if (modal) {
                    modal.hide();
                }
            }
        </script>
    @endif
@endif
@endsection
@endsection