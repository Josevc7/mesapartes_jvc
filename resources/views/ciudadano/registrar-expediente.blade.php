@extends('layouts.app')

@section('title', 'Registrar Nuevo Expediente')

@push('styles')
<link href="{{ asset('css/ciudadano-form.css') }}" rel="stylesheet">
<link href="{{ asset('css/modern-forms.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="modern-form-container">
    <div class="modern-form-card">
        <!-- ENCABEZADO MODERNO -->
        <div class="modern-form-header">
            <div class="modern-form-header-content">
                <div class="modern-form-icon">
                    <i class="fas fa-file-plus"></i>
                </div>
                <div class="modern-form-title">
                    <h1>Registrar Nuevo Expediente</h1>
                    <p>Ventanilla Virtual DRTC - Complete todos los campos marcados con (*)</p>
                </div>
            </div>
        </div>

        <!-- CUERPO DEL FORMULARIO -->
        <div class="modern-form-body">
                    @if(session('success'))
                        <div class="alert-modern alert-modern-success">
                            <div class="alert-modern-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">¡Expediente registrado exitosamente!</h6>
                                <p class="mb-0">{{ session('success') }}</p>
                                @if(session('codigo_expediente'))
                                    <div class="mt-2">
                                        <small class="d-block mb-2">Código de expediente: <strong>{{ session('codigo_expediente') }}</strong></small>
                                        <a href="{{ route('ciudadano.acuse-recibo', session('codigo_expediente')) }}"
                                           class="btn-modern btn-modern-success btn-modern-sm"
                                           target="_blank">
                                            <i class="fas fa-download"></i> Descargar Cargo
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert-modern alert-modern-danger">
                            <div class="alert-modern-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <h6 class="mb-2 fw-bold">Errores en el formulario:</h6>
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li class="mb-1">{{ $error }}</li>
                                    @endforeach
                                </ul>
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
                                <!-- Tipo de Persona (siempre visible) -->
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

                                <!-- Campos para Persona Natural -->
                                <div id="campos-natural-doc" class="col-md-8">
                                    <div class="row g-4">
                                        <div class="col-md-5">
                                            <label for="tipo_documento" class="form-label fw-semibold">
                                                <i class="fas fa-id-card text-primary me-2"></i>Tipo de Documento *
                                            </label>
                                            <select class="form-select form-select-lg @error('tipo_documento') is-invalid @enderror"
                                                    id="tipo_documento" name="tipo_documento">
                                                <option value="DNI" {{ old('tipo_documento', 'DNI') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                                <option value="CE" {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Carné de Extranjería</option>
                                                <option value="PASAPORTE" {{ old('tipo_documento') == 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                                            </select>
                                            @error('tipo_documento')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-7">
                                            <label for="numero_documento" class="form-label fw-semibold">
                                                <i class="fas fa-hashtag text-primary me-2"></i>Número de Documento *
                                            </label>
                                            <input type="text" class="form-control form-control-lg @error('numero_documento') is-invalid @enderror"
                                                   id="numero_documento" name="numero_documento" value="{{ old('numero_documento') }}"
                                                   placeholder="Ingrese su número de documento">
                                            @error('numero_documento')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Campos para Persona Jurídica -->
                                <div id="campos-juridica-doc" class="col-md-8" style="display: none;">
                                    <div class="row g-4">
                                        <div class="col-md-12">
                                            <label for="numero_documento_juridica" class="form-label fw-semibold">
                                                <i class="fas fa-building text-primary me-2"></i>RUC *
                                            </label>
                                            <input type="text" class="form-control form-control-lg"
                                                   id="numero_documento_juridica"
                                                   placeholder="Ingrese RUC de 11 dígitos"
                                                   maxlength="11">
                                            <div class="form-text">Ingrese el RUC de la empresa (11 dígitos)</div>
                                        </div>
                                    </div>
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
                                <!-- 1. Tipo de Documento -->
                                <div class="adaptive-field">
                                    <label for="tipo_documento_entrante" class="form-label">Tipo de Documento *</label>
                                    <select class="form-select @error('tipo_documento_entrante') is-invalid @enderror"
                                            id="tipo_documento_entrante" name="tipo_documento_entrante" required>
                                        <option value="">Seleccione...</option>
                                        <option value="SOLICITUD" {{ old('tipo_documento_entrante') == 'SOLICITUD' ? 'selected' : '' }}>Solicitud</option>
                                        <option value="FUT" {{ old('tipo_documento_entrante') == 'FUT' ? 'selected' : '' }}>FUT (Formulario Único de Trámite)</option>
                                        <option value="OFICIO" {{ old('tipo_documento_entrante') == 'OFICIO' ? 'selected' : '' }}>Oficio</option>
                                        <option value="INFORME" {{ old('tipo_documento_entrante') == 'INFORME' ? 'selected' : '' }}>Informe</option>
                                        <option value="MEMORANDUM" {{ old('tipo_documento_entrante') == 'MEMORANDUM' ? 'selected' : '' }}>Memorándum</option>
                                        <option value="CARTA" {{ old('tipo_documento_entrante') == 'CARTA' ? 'selected' : '' }}>Carta</option>
                                        <option value="RESOLUCION" {{ old('tipo_documento_entrante') == 'RESOLUCION' ? 'selected' : '' }}>Resolución</option>
                                    </select>
                                    @error('tipo_documento_entrante')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 2. Asunto del Trámite -->
                                <div class="adaptive-field" style="grid-column: span 2;">
                                    <label for="asunto" class="form-label">Asunto del Trámite *</label>
                                    <input type="text" class="form-control @error('asunto') is-invalid @enderror"
                                           id="asunto" name="asunto" value="{{ old('asunto') }}"
                                           placeholder="Describa brevemente el motivo de su trámite" required>
                                    @error('asunto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="three-columns mt-3">
                                <!-- 3. Folios -->
                                <div class="adaptive-field">
                                    <label for="folios" class="form-label">Folios *</label>
                                    <input type="number" class="form-control @error('folios') is-invalid @enderror"
                                           id="folios" name="folios" value="{{ old('folios', 1) }}"
                                           min="1" max="999" placeholder="Número de hojas" required>
                                    <div class="form-text">Cantidad de hojas del documento</div>
                                    @error('folios')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 4. Tipo de Trámite -->
                                <div class="adaptive-field">
                                    <label for="id_tipo_tramite" class="form-label">Tipo de Trámite *</label>
                                    <select class="form-select @error('id_tipo_tramite') is-invalid @enderror"
                                            id="id_tipo_tramite" name="id_tipo_tramite" required>
                                        <option value="">Seleccionar tipo de trámite</option>
                                        @foreach($tipoTramites as $tipo)
                                            <option value="{{ $tipo->id_tipo_tramite }}"
                                                    data-plazo="{{ $tipo->plazo_dias ?? '' }}"
                                                    data-requisitos="{{ $tipo->requisitos ?? '' }}"
                                                    {{ old('id_tipo_tramite') == $tipo->id_tipo_tramite ? 'selected' : '' }}>
                                                {{ $tipo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_tipo_tramite')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 5. Prioridad -->
                                <div class="adaptive-field">
                                    <label for="prioridad" class="form-label">Prioridad</label>
                                    <select class="form-select" id="prioridad" name="prioridad">
                                        <option value="baja" {{ old('prioridad') == 'baja' ? 'selected' : '' }}>Baja</option>
                                        <option value="normal" {{ old('prioridad', 'normal') == 'normal' ? 'selected' : '' }}>Media</option>
                                        <option value="alta" {{ old('prioridad') == 'alta' ? 'selected' : '' }}>Alta</option>
                                        <option value="urgente" {{ old('prioridad') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                    </select>
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
        const camposNaturalDoc = document.getElementById('campos-natural-doc');
        const camposJuridicaDoc = document.getElementById('campos-juridica-doc');
        const tipoDocumento = document.getElementById('tipo_documento');
        const numeroDocumento = document.getElementById('numero_documento');
        const numeroDocumentoJuridica = document.getElementById('numero_documento_juridica');

        if (tipoPersonaSelect.value === 'NATURAL') {
            camposNatural.style.display = 'block';
            camposJuridica.style.display = 'none';
            camposNaturalDoc.style.display = 'block';
            camposJuridicaDoc.style.display = 'none';

            // Hacer campos naturales requeridos
            document.getElementById('nombres').required = true;
            document.getElementById('apellido_paterno').required = true;
            document.getElementById('razon_social').required = false;
            tipoDocumento.required = true;
            numeroDocumento.required = true;
            numeroDocumentoJuridica.required = false;

            // Limpiar campos de persona jurídica
            document.getElementById('razon_social').value = '';
            const repLegal = document.getElementById('representante_legal');
            if (repLegal) repLegal.value = '';
        } else {
            camposNatural.style.display = 'none';
            camposJuridica.style.display = 'block';
            camposNaturalDoc.style.display = 'none';
            camposJuridicaDoc.style.display = 'block';

            // Hacer campos jurídicos requeridos
            document.getElementById('nombres').required = false;
            document.getElementById('apellido_paterno').required = false;
            document.getElementById('razon_social').required = true;
            tipoDocumento.required = false;
            numeroDocumento.required = false;
            numeroDocumentoJuridica.required = true;

            // Cambiar automáticamente a RUC y sincronizar valores
            tipoDocumento.value = 'RUC';
            if (numeroDocumento.value) {
                numeroDocumentoJuridica.value = numeroDocumento.value;
            }

            // Limpiar campos de persona natural
            document.getElementById('nombres').value = '';
            document.getElementById('apellido_paterno').value = '';
            const apMaterno = document.getElementById('apellido_materno');
            if (apMaterno) apMaterno.value = '';
            numeroDocumento.value = '';
        }
    }
    
    tipoPersonaSelect.addEventListener('change', togglePersonaFields);
    togglePersonaFields(); // Ejecutar al cargar

    // Sincronizar el campo RUC con el campo oculto numero_documento
    const numeroDocumentoJuridica = document.getElementById('numero_documento_juridica');
    const numeroDocumento = document.getElementById('numero_documento');
    const tipoDocumento = document.getElementById('tipo_documento');

    numeroDocumentoJuridica.addEventListener('input', function() {
        // Sincronizar el valor con el campo principal
        numeroDocumento.value = this.value;
        tipoDocumento.value = 'RUC';
    });

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
});
</script>

<!-- Script de validación de documentos (DNI, RUC, CE, PASAPORTE) -->
<script src="{{ asset('js/validacion-documentos.js') }}"></script>
@endsection