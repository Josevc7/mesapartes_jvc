@extends('layouts.app')

@section('title', 'Registrar Documento')

@push('styles')
<link href="{{ asset('css/ciudadano-form.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-11 col-lg-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white py-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-20 rounded-circle p-3 me-3">
                            <i class="fas fa-plus-circle fa-2x"></i>
                        </div>
                        <div>
                            <h3 class="mb-1 fw-bold">Registrar Documento Entrante</h3>
                            <p class="mb-0 opacity-90">Mesa de Partes - Registro de Expedientes</p>
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
                                    <h6 class="alert-heading mb-1">¡Documento registrado exitosamente!</h6>
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
                                
                                <div class="col-md-5">
                                    <label for="numero_documento" class="form-label fw-semibold">
                                        <i class="fas fa-search text-primary me-2"></i>Número de Documento *
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <input type="text" class="form-control @error('numero_documento') is-invalid @enderror" 
                                               id="numero_documento" name="numero_documento" value="{{ old('numero_documento') }}" 
                                               placeholder="Ingrese documento y presione Enter" required>
                                        <button type="button" class="btn btn-primary" id="btn-buscar" onclick="buscarPersona()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Presione Enter o el botón para buscar si la persona ya existe</div>
                                    @error('numero_documento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
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

                        <!-- Sección 4: Verificación de Documentos -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-file-check text-warning fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-warning fw-bold">4. Verificación de Documentos</h5>
                                    <p class="text-muted mb-0 small">Confirme que el ciudadano presenta todos los documentos requeridos</p>
                                </div>
                            </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <strong>Documentos Obligatorios:</strong>
                            Verificar que el ciudadano presente todos los documentos requeridos antes de registrar el expediente.
                        </div>
                        
                        <div class="two-columns">
                            <div class="adaptive-field">
                                <h6>Documentos Básicos (Obligatorios)</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="doc_dni" name="documentos_verificados[]" value="dni" required>
                                    <label class="form-check-label" for="doc_dni">
                                        <strong>Copia de DNI</strong> - Documento de identidad vigente
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="doc_fut" name="documentos_verificados[]" value="fut" required>
                                    <label class="form-check-label" for="doc_fut">
                                        <strong>FUT (Formulario Único de Trámite)</strong> - Completado y firmado
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="doc_pago" name="documentos_verificados[]" value="pago" required>
                                    <label class="form-check-label" for="doc_pago">
                                        <strong>Comprobante de Pago</strong> - Recibo de derechos de trámite
                                    </label>
                                </div>
                            </div>
                            
                            <div class="adaptive-field">
                                <h6>Documentos Adicionales (Según Trámite)</h6>
                                <div id="documentos-adicionales">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="doc_certificado" name="documentos_adicionales[]" value="certificado_medico">
                                        <label class="form-check-label" for="doc_certificado">
                                            Certificado Médico (para licencias)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="doc_planos" name="documentos_adicionales[]" value="planos">
                                        <label class="form-check-label" for="doc_planos">
                                            Planos de Obra (para permisos viales)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="doc_memoria" name="documentos_adicionales[]" value="memoria_descriptiva">
                                        <label class="form-check-label" for="doc_memoria">
                                            Memoria Descriptiva (para obras)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="doc_seguro" name="documentos_adicionales[]" value="poliza_seguro">
                                        <label class="form-check-label" for="doc_seguro">
                                            Póliza de Seguro (para obras mayores)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observaciones_documentos" class="form-label">Observaciones sobre Documentos</label>
                            <textarea class="form-control" id="observaciones_documentos" name="observaciones_documentos" rows="2" 
                                      placeholder="Ej: Falta certificado médico, DNI vencido, etc."></textarea>
                        </div>
                        
                        </div>
                        
                        <!-- Sección 5: Datos del Trámite -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-clipboard-list text-danger fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-danger fw-bold">5. Datos del Trámite</h5>
                                    <p class="text-muted mb-0 small">Información del expediente y documentos adjuntos</p>
                                </div>
                            </div>
                            
                            <div class="two-columns">
                                <div class="adaptive-field">
                                    <label for="id_tipo_tramite" class="form-label">Tipo de Trámite *</label>
                                    <select class="form-select @error('id_tipo_tramite') is-invalid @enderror" 
                                            id="id_tipo_tramite" name="id_tipo_tramite" required>
                                        <option value="">Seleccione un tipo de trámite</option>
                                        @foreach($tipoTramites as $tipoTramite)
                                            <option value="{{ $tipoTramite->id_tipo_tramite }}" {{ old('id_tipo_tramite') == $tipoTramite->id_tipo_tramite ? 'selected' : '' }}>
                                                {{ $tipoTramite->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_tipo_tramite')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="adaptive-field">
                                    <label for="asunto" class="form-label">Asunto *</label>
                                    <input type="text" class="form-control @error('asunto') is-invalid @enderror" 
                                           id="asunto" name="asunto" value="{{ old('asunto') }}" 
                                           placeholder="Describa brevemente el motivo del trámite" required>
                                    @error('asunto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="adaptive-grid">
                                <div class="adaptive-field">
                                    <label for="documento" class="form-label">Documento PDF *</label>
                                    <input type="file" class="form-control @error('documento') is-invalid @enderror" 
                                           id="documento" name="documento" accept=".pdf" required>
                                    <div class="form-text">Solo archivos PDF, máximo 10MB</div>
                                    @error('documento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="adaptive-field full-width">
                                    <label for="observaciones" class="form-label">Observaciones Iniciales</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                                              placeholder="Observaciones adicionales sobre el trámite...">{{ old('observaciones') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-end pt-4 border-top">
                            <a href="{{ route('mesa-partes.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="fas fa-arrow-left me-2"></i>Volver a Mesa de Partes
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                                <i class="fas fa-save me-2"></i>Registrar Documento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
let personaEncontrada = null;

document.addEventListener('DOMContentLoaded', function() {
    const tipoPersonaSelect = document.getElementById('tipo_persona');
    const personaNatural = document.getElementById('persona-natural');
    const personaJuridica = document.getElementById('persona-juridica');
    const numeroDocumento = document.getElementById('numero_documento');
    
    function togglePersonaFields() {
        if (tipoPersonaSelect.value === 'NATURAL') {
            personaNatural.style.display = 'block';
            personaJuridica.style.display = 'none';
        } else {
            personaNatural.style.display = 'none';
            personaJuridica.style.display = 'block';
        }
    }
    
    tipoPersonaSelect.addEventListener('change', togglePersonaFields);
    togglePersonaFields();
    
    // Búsqueda automática al presionar Enter
    numeroDocumento.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarPersona();
        }
    });
    
    // Limpiar búsqueda al cambiar documento
    numeroDocumento.addEventListener('input', function() {
        document.getElementById('persona-encontrada').style.display = 'none';
        personaEncontrada = null;
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
                    alert('Persona no encontrada. Puede registrar una nueva.');
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
    const tipoPersona = document.getElementById('tipo_persona');
    
    if (tipoDoc) tipoDoc.value = p.tipo_documento || 'DNI';
    if (numeroDoc) numeroDoc.value = p.numero_documento || '';
    if (tipoPersona) tipoPersona.value = p.tipo_persona || 'NATURAL';
    
    // Llenar datos según tipo de persona
    if (p.tipo_persona === 'NATURAL') {
        const nombres = document.getElementById('nombres');
        const apellidoP = document.getElementById('apellido_paterno');
        const apellidoM = document.getElementById('apellido_materno');
        
        if (nombres) nombres.value = p.nombres || '';
        if (apellidoP) apellidoP.value = p.apellido_paterno || '';
        if (apellidoM) apellidoM.value = p.apellido_materno || '';
    } else {
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

function togglePersonaFields() {
    const tipoPersonaSelect = document.getElementById('tipo_persona');
    const personaNatural = document.getElementById('persona-natural');
    const personaJuridica = document.getElementById('persona-juridica');
    
    if (tipoPersonaSelect.value === 'NATURAL') {
        personaNatural.style.display = 'block';
        personaJuridica.style.display = 'none';
    } else {
        personaNatural.style.display = 'none';
        personaJuridica.style.display = 'block';
    }
}

// Mostrar documentos específicos según tipo de trámite
function actualizarDocumentosRequeridos() {
    const tipoTramiteSelect = document.getElementById('id_tipo_tramite');
    const tipoTramiteText = tipoTramiteSelect.options[tipoTramiteSelect.selectedIndex]?.text || '';
    
    // Resetear documentos adicionales
    const checkboxes = document.querySelectorAll('#documentos-adicionales input[type="checkbox"]');
    checkboxes.forEach(cb => {
        cb.checked = false;
        cb.parentElement.style.display = 'none';
    });
    
    // Mostrar documentos según tipo de trámite
    if (tipoTramiteText.toLowerCase().includes('licencia') || tipoTramiteText.toLowerCase().includes('examen')) {
        document.getElementById('doc_certificado').parentElement.style.display = 'block';
    }
    
    if (tipoTramiteText.toLowerCase().includes('obra') || tipoTramiteText.toLowerCase().includes('permiso')) {
        document.getElementById('doc_planos').parentElement.style.display = 'block';
        document.getElementById('doc_memoria').parentElement.style.display = 'block';
        document.getElementById('doc_seguro').parentElement.style.display = 'block';
    }
}

// Agregar event listener para tipo de trámite
document.addEventListener('DOMContentLoaded', function() {
    const tipoTramiteSelect = document.getElementById('id_tipo_tramite');
    if (tipoTramiteSelect) {
        tipoTramiteSelect.addEventListener('change', actualizarDocumentosRequeridos);
    }
});

// Validar que se marquen los 3 documentos básicos
function validarDocumentosBasicos() {
    const docsBasicos = ['doc_dni', 'doc_fut', 'doc_pago'];
    const marcados = docsBasicos.filter(id => document.getElementById(id).checked);
    
    if (marcados.length < 3) {
        alert('Debe verificar los 3 documentos básicos: DNI, FUT y Comprobante de Pago');
        return false;
    }
    return true;
}

// Agregar validación al formulario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validarDocumentosBasicos()) {
                e.preventDefault();
            }
        });
    }
    
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
    
    // Limpiar documentos verificados
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = false);
    
    // Limpiar campos de trámite
    document.getElementById('id_tipo_tramite').value = '';
    document.getElementById('asunto').value = '';
    document.getElementById('documento').value = '';
    document.getElementById('observaciones').value = '';
    document.getElementById('observaciones_documentos').value = '';
    
    // Ocultar alertas
    document.getElementById('persona-encontrada').style.display = 'none';
    
    // Habilitar campos
    deshabilitarCamposPersona(false);
    
    // Actualizar vista
    togglePersonaFields();
    
    personaEncontrada = null;
}
</script>
@endsection
@endsection