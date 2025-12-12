@extends('layouts.app')

@section('title', 'Registrar Nuevo Expediente')

@push('styles')
<link href="{{ asset('css/ciudadano-form.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-11">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white py-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-20 rounded-circle p-3 me-3">
                            <i class="fas fa-file-plus fa-2x"></i>
                        </div>
                        <div>
                            <h3 class="mb-1 fw-bold">Registrar Nuevo Expediente</h3>
                            <p class="mb-0 opacity-90">Ventanilla Virtual DRTC - Mesa de Partes Digital</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-20 rounded-circle p-2 me-3">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading mb-1">¡Expediente registrado exitosamente!</h6>
                                    <p class="mb-0">{{ session('success') }}</p>
                                    @if(session('codigo_expediente'))
                                        <small class="d-block mt-1">Código de expediente: <strong class="text-success">{{ session('codigo_expediente') }}</strong></small>
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
                    
                    <form method="POST" action="{{ route('ciudadano.enviar-tramite') }}" enctype="multipart/form-data" id="expedienteForm">
                        @csrf
                        
                        <!-- Sección 1: Identificación del Solicitante -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-user text-primary fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-primary fw-bold">1. Identificación del Solicitante</h5>
                                    <p class="text-muted mb-0 small">Ingrese sus datos de identificación personal</p>
                                </div>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-md-4">
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
                                
                                <div class="col-md-4">
                                    <label for="tipo_documento" class="form-label fw-semibold">
                                        <i class="fas fa-id-card text-primary me-2"></i>Tipo de Documento *
                                    </label>
                                    <select class="form-select form-select-lg @error('tipo_documento') is-invalid @enderror" 
                                            id="tipo_documento" name="tipo_documento" required>
                                        <option value="DNI" {{ old('tipo_documento', 'DNI') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                        <option value="CE" {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Carné de Extranjería</option>
                                        <option value="RUC" {{ old('tipo_documento') == 'RUC' ? 'selected' : '' }}>RUC</option>
                                        <option value="PASAPORTE" {{ old('tipo_documento') == 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                                    </select>
                                    @error('tipo_documento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="numero_documento" class="form-label fw-semibold">
                                        <i class="fas fa-hashtag text-primary me-2"></i>Número de Documento *
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('numero_documento') is-invalid @enderror" 
                                           id="numero_documento" name="numero_documento" value="{{ old('numero_documento') }}" 
                                           placeholder="Ingrese su número de documento" required>
                                    @error('numero_documento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Sección 2: Datos Personales -->
                        <div class="mb-4">
                            <h5 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-address-card"></i> 2. Datos Personales
                            </h5>
                            
                            <!-- Campos para Persona Natural -->
                            <div id="campos-natural" class="persona-fields">
                                <div class="three-columns">
                                    <div class="adaptive-field">
                                        <label for="nombres" class="form-label">Nombres *</label>
                                        <input type="text" class="form-control @error('nombres') is-invalid @enderror" 
                                               id="nombres" name="nombres" value="{{ old('nombres') }}" 
                                               placeholder="Ingrese sus nombres">
                                        @error('nombres')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="adaptive-field">
                                        <label for="apellido_paterno" class="form-label">Apellido Paterno *</label>
                                        <input type="text" class="form-control @error('apellido_paterno') is-invalid @enderror" 
                                               id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" 
                                               placeholder="Apellido paterno">
                                        @error('apellido_paterno')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="adaptive-field">
                                        <label for="apellido_materno" class="form-label">Apellido Materno</label>
                                        <input type="text" class="form-control @error('apellido_materno') is-invalid @enderror" 
                                               id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno') }}" 
                                               placeholder="Apellido materno">
                                        @error('apellido_materno')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Campos para Persona Jurídica -->
                            <div id="campos-juridica" class="persona-fields" style="display: none;">
                                <div class="adaptive-grid">
                                    <div class="adaptive-field">
                                        <label for="razon_social" class="form-label">Razón Social *</label>
                                        <input type="text" class="form-control @error('razon_social') is-invalid @enderror" 
                                               id="razon_social" name="razon_social" value="{{ old('razon_social') }}" 
                                               placeholder="Nombre de la empresa">
                                        @error('razon_social')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="adaptive-field">
                                        <label for="representante_legal" class="form-label">Representante Legal</label>
                                        <input type="text" class="form-control @error('representante_legal') is-invalid @enderror" 
                                               id="representante_legal" name="representante_legal" value="{{ old('representante_legal') }}" 
                                               placeholder="Nombre del representante">
                                        @error('representante_legal')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sección 3: Datos de Contacto -->
                        <div class="mb-4">
                            <h5 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-phone"></i> 3. Datos de Contacto
                            </h5>
                            
                            <div class="three-columns">
                                <div class="adaptive-field">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                           id="telefono" name="telefono" value="{{ old('telefono') }}" 
                                           placeholder="Número de teléfono">
                                    @error('telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="adaptive-field">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" 
                                           placeholder="correo@ejemplo.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="adaptive-field">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                                           id="direccion" name="direccion" value="{{ old('direccion') }}" 
                                           placeholder="Dirección completa">
                                    @error('direccion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Sección 4: Datos del Trámite -->
                        <div class="mb-4">
                            <h5 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-clipboard-list"></i> 4. Datos del Trámite
                            </h5>
                            
                            <div class="three-columns">
                                <div class="adaptive-field">
                                    <label for="id_tipo_tramite" class="form-label">Tipo de Trámite *</label>
                                    <select class="form-select @error('id_tipo_tramite') is-invalid @enderror" 
                                            id="id_tipo_tramite" name="id_tipo_tramite" required>
                                        <option value="">Seleccionar tipo de trámite</option>
                                        @foreach($tipoTramites as $tipo)
                                            <option value="{{ $tipo->id_tipo_tramite }}" 
                                                    data-plazo="{{ $tipo->plazo_dias }}"
                                                    data-requisitos="{{ $tipo->requisitos }}"
                                                    {{ old('id_tipo_tramite') == $tipo->id_tipo_tramite ? 'selected' : '' }}>
                                                {{ $tipo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_tipo_tramite')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="adaptive-field">
                                    <label for="prioridad" class="form-label">Prioridad</label>
                                    <select class="form-select" id="prioridad" name="prioridad">
                                        <option value="baja" {{ old('prioridad') == 'baja' ? 'selected' : '' }}>Baja</option>
                                        <option value="media" {{ old('prioridad', 'media') == 'media' ? 'selected' : '' }}>Media</option>
                                        <option value="alta" {{ old('prioridad') == 'alta' ? 'selected' : '' }}>Alta</option>
                                        <option value="urgente" {{ old('prioridad') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                    </select>
                                </div>
                                
                                <div class="adaptive-field">
                                    <label for="asunto" class="form-label">Asunto del Expediente *</label>
                                    <input type="text" class="form-control @error('asunto') is-invalid @enderror" 
                                           id="asunto" name="asunto" value="{{ old('asunto') }}" 
                                           placeholder="Describa brevemente el motivo de su trámite" required>
                                    @error('asunto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="adaptive-grid">
                                <div class="adaptive-field full-width">
                                    <label for="descripcion" class="form-label">Descripción Detallada</label>
                                    <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                              id="descripcion" name="descripcion" rows="4" 
                                              placeholder="Proporcione detalles adicionales sobre su solicitud">{{ old('descripcion') }}</textarea>
                                    @error('descripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sección 5: Documentos -->
                        <div class="mb-4">
                            <h5 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-file-upload"></i> 5. Documentos
                            </h5>
                            
                            <div class="adaptive-grid">
                                <div class="adaptive-field">
                                    <label for="documento_principal" class="form-label">Documento Principal *</label>
                                    <input type="file" class="form-control @error('documento_principal') is-invalid @enderror" 
                                           id="documento_principal" name="documento_principal" accept=".pdf" required>
                                    <div class="form-text">Solo archivos PDF, máximo 10MB</div>
                                    @error('documento_principal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="adaptive-field">
                                    <label for="documentos_adicionales" class="form-label">Documentos Adicionales</label>
                                    <input type="file" class="form-control" id="documentos_adicionales" 
                                           name="documentos_adicionales[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
                                    <div class="form-text">Archivos PDF, JPG, PNG. Máximo 5 archivos de 5MB cada uno</div>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Requisitos -->
                        <div id="requisitos-info" class="alert alert-info" style="display: none;">
                            <h6><i class="fas fa-info-circle"></i> Requisitos para este trámite:</h6>
                            <div id="lista-requisitos"></div>
                        </div>

                        <!-- Información del Ciudadano -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6>Información del Solicitante</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Nombre:</strong> {{ auth()->user()->name }}</p>
                                        <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>DNI:</strong> {{ auth()->user()->dni ?? 'No registrado' }}</p>
                                        <p><strong>Teléfono:</strong> {{ auth()->user()->telefono ?? 'No registrado' }}</p>
                                    </div>
                                </div>
                                @if(!auth()->user()->dni || !auth()->user()->telefono)
                                <div class="alert alert-warning alert-sm">
                                    <small><i class="fas fa-exclamation-triangle"></i> 
                                    Complete su perfil para un mejor servicio. 
                                    <a href="/perfil">Actualizar datos</a></small>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input @error('acepta_terminos') is-invalid @enderror" 
                                       type="checkbox" value="1" id="acepta_terminos" name="acepta_terminos" required>
                                <label class="form-check-label" for="acepta_terminos">
                                    Acepto los <a href="#" data-bs-toggle="modal" data-bs-target="#modalTerminos">términos y condiciones</a> 
                                    y declaro que la información proporcionada es veraz
                                </label>
                                @error('acepta_terminos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-end pt-4 border-top">
                            <a href="{{ route('ciudadano.dashboard') }}" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                                <i class="fas fa-paper-plane me-2"></i>Enviar Expediente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Términos y Condiciones -->
<div class="modal fade" id="modalTerminos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Términos y Condiciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Condiciones de Uso de la Mesa de Partes Digital</h6>
                <ol>
                    <li>Los documentos enviados deben ser legibles y en formato PDF.</li>
                    <li>La información proporcionada debe ser veraz y completa.</li>
                    <li>El ciudadano es responsable de la autenticidad de los documentos.</li>
                    <li>Los plazos de atención se cuentan desde la fecha de registro.</li>
                    <li>Las notificaciones se enviarán al correo electrónico registrado.</li>
                    <li>El sistema está disponible 24/7 para consultas y seguimiento.</li>
                </ol>
                <p class="text-muted">Al enviar su expediente, acepta estas condiciones y autoriza el tratamiento de sus datos personales conforme a la Ley de Protección de Datos Personales.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Limpiar formulario si hay mensaje de éxito
    @if(session('success'))
        document.getElementById('expedienteForm').reset();
        // Resetear campos dinámicos
        document.getElementById('tipo_persona').value = 'NATURAL';
        document.getElementById('tipo_documento').value = 'DNI';
        document.getElementById('campos-natural').style.display = 'block';
        document.getElementById('campos-juridica').style.display = 'none';
        document.getElementById('requisitos-info').style.display = 'none';
    @endif
    
    const tipoPersonaSelect = document.getElementById('tipo_persona');
    const camposNatural = document.getElementById('campos-natural');
    const camposJuridica = document.getElementById('campos-juridica');
    
    function togglePersonaFields() {
        if (tipoPersonaSelect.value === 'NATURAL') {
            camposNatural.style.display = 'block';
            camposJuridica.style.display = 'none';
            // Hacer campos naturales requeridos
            document.getElementById('nombres').required = true;
            document.getElementById('apellido_paterno').required = true;
            document.getElementById('razon_social').required = false;
        } else {
            camposNatural.style.display = 'none';
            camposJuridica.style.display = 'block';
            // Hacer campos jurídicos requeridos
            document.getElementById('nombres').required = false;
            document.getElementById('apellido_paterno').required = false;
            document.getElementById('razon_social').required = true;
        }
    }
    
    tipoPersonaSelect.addEventListener('change', togglePersonaFields);
    togglePersonaFields(); // Ejecutar al cargar
    
    // Manejar cambio de tipo de trámite
    document.getElementById('id_tipo_tramite').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const requisitos = selectedOption.dataset.requisitos;
        const plazo = selectedOption.dataset.plazo;
        
        if (requisitos && requisitos.trim() !== '') {
            document.getElementById('requisitos-info').style.display = 'block';
            document.getElementById('lista-requisitos').innerHTML = 
                '<p><strong>Plazo de atención:</strong> ' + plazo + ' días hábiles</p>' +
                '<div>' + requisitos.replace(/\n/g, '<br>') + '</div>';
        } else {
            document.getElementById('requisitos-info').style.display = 'none';
        }
    });
    
    // Validar tipo de documento según tipo de persona
    const tipoDocumentoSelect = document.getElementById('tipo_documento');
    tipoPersonaSelect.addEventListener('change', function() {
        if (this.value === 'JURIDICA') {
            // Para personas jurídicas, mostrar solo RUC
            tipoDocumentoSelect.innerHTML = '<option value="RUC">RUC</option>';
        } else {
            // Para personas naturales, mostrar DNI, CE, Pasaporte
            tipoDocumentoSelect.innerHTML = `
                <option value="DNI">DNI</option>
                <option value="CE">Carné de Extranjería</option>
                <option value="PASAPORTE">Pasaporte</option>
            `;
        }
    });
});
</script>
@endsection