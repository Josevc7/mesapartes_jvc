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
        <!-- ENCABEZADO -->
        <div class="modern-form-header">
            <div class="modern-form-header-content">
                <div class="modern-form-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="modern-form-title">
                    <h1>Registrar Nuevo Expediente</h1>
                    <p>Registro, Clasificacion y Derivacion de Expedientes</p>
                </div>
            </div>
        </div>

        <!-- CUERPO DEL FORMULARIO -->
        <div class="modern-form-body">
            @if(session('success') && !session('codigo_expediente'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <div class="flex-grow-1">
                            <strong>Documento registrado exitosamente.</strong> {{ session('success') }}
                            @if(session('codigo_expediente'))
                                @php
                                    $expediente = \App\Models\Expediente::where('codigo_expediente', session('codigo_expediente'))->first();
                                @endphp
                                <span class="ms-2">Codigo: <strong class="text-success">{{ session('codigo_expediente') }}</strong></span>
                                @if($expediente)
                                    <a href="{{ route('mesa-partes.cargo', $expediente->id_expediente) }}"
                                       class="btn btn-sm btn-success ms-2" target="_blank">
                                        <i class="fas fa-print me-1"></i> Imprimir Cargo
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-triangle text-danger me-2 mt-1"></i>
                        <div>
                            <strong>Errores en el formulario:</strong>
                            <ul class="mb-0 ps-3 mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('mesa-partes.store-registrar') }}" enctype="multipart/form-data" id="form-registrar" novalidate>
                @csrf
                <input type="hidden" id="persona_existente_id" name="persona_existente_id" value="">
                <input type="hidden" id="tipo_documento_envio" name="tipo_documento" value="{{ old('tipo_documento', 'DNI') }}">
                <input type="hidden" id="numero_documento_envio" name="numero_documento" value="{{ old('numero_documento') }}">

                <!-- Seccion 1: Identificacion del Solicitante -->
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-user text-primary fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 text-primary fw-bold">1. Identificacion del Solicitante</h5>
                            <p class="text-muted mb-0 small">Busque o registre los datos del ciudadano</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="tipo_persona" class="form-label fw-semibold">
                                <i class="fas fa-user-tag text-primary me-1"></i>Tipo de Persona *
                            </label>
                            <select class="form-select @error('tipo_persona') is-invalid @enderror"
                                    id="tipo_persona" name="tipo_persona" required>
                                <option value="NATURAL" {{ old('tipo_persona', 'NATURAL') == 'NATURAL' ? 'selected' : '' }}>Persona Natural</option>
                                <option value="JURIDICA" {{ old('tipo_persona') == 'JURIDICA' ? 'selected' : '' }}>Persona Juridica</option>
                            </select>
                            @error('tipo_persona')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Campos para Persona Natural -->
                        <div id="campos-natural-doc" class="col-md-9">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="tipo_documento" class="form-label fw-semibold">
                                        <i class="fas fa-id-card text-primary me-1"></i>Tipo Documento *
                                    </label>
                                    <select class="form-select @error('tipo_documento') is-invalid @enderror"
                                            id="tipo_documento">
                                        <option value="DNI" {{ old('tipo_documento', 'DNI') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                        <option value="CE" {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Carne de Extranjeria</option>
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
                                        <i class="fas fa-search text-primary me-1"></i>N° Documento *
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('numero_documento') is-invalid @enderror"
                                               id="numero_documento" value="{{ old('numero_documento') }}"
                                               placeholder="Ingrese documento y presione Enter">
                                        <button type="button" class="btn btn-primary" id="btn-buscar" onclick="buscarPersona()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Presione Enter o el boton para buscar</div>
                                    @error('numero_documento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Campos para Persona Juridica -->
                        <div id="campos-juridica-doc" class="col-md-9" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="numero_documento_juridica" class="form-label fw-semibold">
                                        <i class="fas fa-search text-primary me-1"></i>RUC *
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                               id="numero_documento_juridica"
                                               placeholder="Ingrese RUC (11 digitos)"
                                               maxlength="11">
                                        <button type="button" class="btn btn-primary" id="btn-buscar-juridica" onclick="buscarPersonaJuridica()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Presione Enter o el boton para buscar</div>
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

                <!-- Seccion 2: Datos Personales + Contacto (combinados) -->
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-address-card text-success fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 text-success fw-bold">2. Datos del Solicitante</h5>
                            <p class="text-muted mb-0 small">Informacion personal y de contacto</p>
                        </div>
                    </div>

                    <div id="persona-natural" class="persona-fields">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="nombres" class="form-label">Nombres *</label>
                                <input type="text" class="form-control @error('nombres') is-invalid @enderror"
                                       id="nombres" name="nombres" value="{{ old('nombres') }}">
                                @error('nombres')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="apellido_paterno" class="form-label">Apellido Paterno *</label>
                                <input type="text" class="form-control @error('apellido_paterno') is-invalid @enderror"
                                       id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}">
                                @error('apellido_paterno')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
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
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="razon_social" class="form-label">Razon Social *</label>
                                <input type="text" class="form-control @error('razon_social') is-invalid @enderror"
                                       id="razon_social" name="razon_social" value="{{ old('razon_social') }}">
                                @error('razon_social')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="representante_legal" class="form-label">Representante Legal</label>
                                <input type="text" class="form-control @error('representante_legal') is-invalid @enderror"
                                       id="representante_legal" name="representante_legal" value="{{ old('representante_legal') }}">
                                @error('representante_legal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Datos de contacto integrados -->
                    <div class="row g-3 mt-1">
                        <div class="col-md-3">
                            <label for="telefono" class="form-label">
                                <i class="fas fa-phone text-muted me-1"></i>Telefono
                            </label>
                            <input type="text" class="form-control @error('telefono') is-invalid @enderror"
                                   id="telefono" name="telefono" value="{{ old('telefono') }}">
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope text-muted me-1"></i>Email
                            </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-5">
                            <label for="direccion" class="form-label">
                                <i class="fas fa-map-marker-alt text-muted me-1"></i>Direccion
                            </label>
                            <input type="text" class="form-control @error('direccion') is-invalid @enderror"
                                   id="direccion" name="direccion" value="{{ old('direccion') }}">
                            @error('direccion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Seccion 3: Datos del Documento -->
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-file-alt text-warning fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 text-warning fw-bold">3. Datos del Documento</h5>
                            <p class="text-muted mb-0 small">Registre el documento que ingresa</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="tipo_documento_entrante" class="form-label fw-semibold">
                                <i class="fas fa-file-invoice text-warning me-1"></i>Tipo Documento *
                            </label>
                            <select class="form-select @error('tipo_documento_entrante') is-invalid @enderror"
                                    id="tipo_documento_entrante" name="tipo_documento_entrante" required>
                                <option value="">Seleccione...</option>
                                <option value="SOLICITUD" {{ old('tipo_documento_entrante') == 'SOLICITUD' ? 'selected' : '' }}>Solicitud</option>
                                <option value="FUT" {{ old('tipo_documento_entrante') == 'FUT' ? 'selected' : '' }}>FUT</option>
                                <option value="OFICIO" {{ old('tipo_documento_entrante') == 'OFICIO' ? 'selected' : '' }}>Oficio</option>
                                <option value="INFORME" {{ old('tipo_documento_entrante') == 'INFORME' ? 'selected' : '' }}>Informe</option>
                                <option value="MEMORANDUM" {{ old('tipo_documento_entrante') == 'MEMORANDUM' ? 'selected' : '' }}>Memorandum</option>
                                <option value="CARTA" {{ old('tipo_documento_entrante') == 'CARTA' ? 'selected' : '' }}>Carta</option>
                                <option value="RESOLUCION" {{ old('tipo_documento_entrante') == 'RESOLUCION' ? 'selected' : '' }}>Resolucion</option>
                                <option value="OTROS" {{ old('tipo_documento_entrante') == 'OTROS' ? 'selected' : '' }}>Otros</option>
                            </select>
                            @error('tipo_documento_entrante')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-2">
                            <label for="numero_documento_entrante" class="form-label fw-semibold">
                                <i class="fas fa-hashtag text-warning me-1"></i>N° Documento
                            </label>
                            <input type="text" class="form-control @error('numero_documento_entrante') is-invalid @enderror"
                                   id="numero_documento_entrante" name="numero_documento_entrante" value="{{ old('numero_documento_entrante') }}"
                                   placeholder="Ej: 001-2026">
                            @error('numero_documento_entrante')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label for="asunto_documento" class="form-label fw-semibold">
                                <i class="fas fa-align-left text-warning me-1"></i>Asunto *
                            </label>
                            <input type="text" class="form-control @error('asunto_documento') is-invalid @enderror"
                                   id="asunto_documento" name="asunto_documento" value="{{ old('asunto_documento') }}"
                                   placeholder="Resumen del contenido del documento" required>
                            @error('asunto_documento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-2">
                            <label for="folios" class="form-label fw-semibold">
                                <i class="fas fa-copy text-warning me-1"></i>Folios *
                            </label>
                            <input type="number" class="form-control @error('folios') is-invalid @enderror"
                                   id="folios" name="folios" value="{{ old('folios', 1) }}"
                                   min="1" max="9999" required>
                            @error('folios')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-5">
                            <label for="documento" class="form-label fw-semibold">
                                <i class="fas fa-file-pdf text-warning me-1"></i>Documento PDF
                            </label>
                            <input type="file" class="form-control @error('documento') is-invalid @enderror"
                                   id="documento" name="documento" accept=".pdf">
                            <div class="form-text">Opcional - Max 10MB</div>
                            @error('documento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-2">
                            <label for="plazo_dias" class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt text-warning me-1"></i>Plazo (dias) *
                            </label>
                            <input type="number" class="form-control @error('plazo_dias') is-invalid @enderror"
                                   id="plazo_dias" name="plazo_dias"
                                   value="{{ old('plazo_dias', 15) }}"
                                   min="1" max="365" required>
                            @error('plazo_dias')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label for="observaciones_clasificacion" class="form-label fw-semibold">
                                <i class="fas fa-comment text-warning me-1"></i>Observaciones
                            </label>
                            <input type="text" class="form-control @error('observaciones_clasificacion') is-invalid @enderror"
                                   id="observaciones_clasificacion" name="observaciones_clasificacion"
                                   value="{{ old('observaciones_clasificacion') }}"
                                   placeholder="Observaciones opcionales...">
                            @error('observaciones_clasificacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Seccion 4: Clasificacion y Tipo de Tramite -->
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-tags text-success fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 text-success fw-bold">4. Clasificacion y Derivacion</h5>
                            <p class="text-muted mb-0 small">Area de destino y tipo de tramite</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="id_area" class="form-label fw-semibold">
                                <i class="fas fa-building text-success me-1"></i>Area de Destino *
                            </label>
                            <select class="form-select @error('id_area') is-invalid @enderror"
                                    id="id_area" name="id_area" required>
                                <option value="">Seleccione un area</option>
                                @foreach(\App\Models\Area::where('activo', true)
                                     ->where('nivel', \App\Models\Area::NIVEL_DIRECCION)
                                     ->orderBy('nombre')
                                     ->get() as $area)
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
                                <i class="fas fa-tasks text-success me-1"></i>Tipo de Tramite *
                            </label>
                            <select class="form-select @error('id_tipo_tramite') is-invalid @enderror"
                                    id="id_tipo_tramite" name="id_tipo_tramite" required>
                                <option value="">Primero seleccione un area</option>
                            </select>
                            <div class="form-text">Se carga segun el area</div>
                            @error('id_tipo_tramite')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="prioridad" class="form-label fw-semibold">
                                <i class="fas fa-exclamation-circle text-success me-1"></i>Prioridad *
                            </label>
                            <select class="form-select @error('prioridad') is-invalid @enderror"
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

                <!-- Barra de acciones -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between pt-3 border-top">
                    <a href="{{ route('mesa-partes.index') }}" class="btn btn-outline-secondary px-3">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                    <button type="button" id="btn-registrar" class="btn btn-success px-4 shadow-sm" onclick="validarYEnviar()">
                        <i class="fas fa-check-double me-1"></i>Registrar, Clasificar y Derivar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmacion de registro exitoso -->
@if(session('codigo_expediente'))
    @php
        $expediente = \App\Models\Expediente::where('codigo_expediente', session('codigo_expediente'))->first();
    @endphp
    @if($expediente)
        <div class="modal fade" id="modalCargoExitoso" tabindex="-1" aria-labelledby="modalCargoExitosoLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success text-white border-0 py-2">
                        <h5 class="modal-title" id="modalCargoExitosoLabel">
                            <i class="fas fa-check-circle me-2"></i>Registro Exitoso
                        </h5>
                    </div>
                    <div class="modal-body text-center py-3">
                        <div class="mb-3">
                            <i class="fas fa-file-alt fa-2x text-success mb-2"></i>
                            <h5 class="mb-2">Expediente Registrado</h5>
                            <div class="alert alert-info border-0 mb-2 py-2">
                                <small class="d-block">Codigo de Expediente:</small>
                                <h4 class="text-primary mb-0">{{ session('codigo_expediente') }}</h4>
                            </div>
                            <small class="text-muted">El expediente ha sido registrado, clasificado y derivado exitosamente.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-3 pt-0">
                        <button type="button" class="btn btn-success px-3" onclick="imprimirCargo()">
                            <i class="fas fa-print me-1"></i>Imprimir Cargo
                        </button>
                        <button type="button" class="btn btn-primary px-3" onclick="nuevoRegistro()">
                            <i class="fas fa-plus me-1"></i>Nuevo Registro
                        </button>
                        <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
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
let ultimoDocConsultado = '';
let buscandoAuto = false;

function resetEstadoPersonaPorCambioDocumento() {
    const personaExistenteId = document.getElementById('persona_existente_id');
    if (personaExistenteId) personaExistenteId.value = '';

    const box = document.getElementById('persona-encontrada');
    if (box) box.style.display = 'none';

    document.getElementById('nombres').value = '';
    document.getElementById('apellido_paterno').value = '';
    document.getElementById('apellido_materno').value = '';
    document.getElementById('razon_social').value = '';
    document.getElementById('representante_legal').value = '';
    document.getElementById('telefono').value = '';
    document.getElementById('email').value = '';
    document.getElementById('direccion').value = '';

    deshabilitarCamposPersona(false);
    personaEncontrada = null;
}

document.addEventListener('DOMContentLoaded', function() {
    const tipoPersonaSelect = document.getElementById('tipo_persona');
    const numeroDocumento = document.getElementById('numero_documento');
    const numeroDocumentoJuridica = document.getElementById('numero_documento_juridica');

    numeroDocumento.addEventListener('input', function() {
        if (buscandoAuto) return;

        const tipoDoc = document.getElementById('tipo_documento').value;
        let valor = this.value.trim();

        if (tipoDoc === 'DNI') {
            valor = valor.replace(/\D/g, '').slice(0, 8);
            this.value = valor;
        } else if (tipoDoc === 'RUC') {
            valor = valor.replace(/\D/g, '').slice(0, 11);
            this.value = valor;
        }

        if (valor !== ultimoDocConsultado) {
            resetEstadoPersonaPorCambioDocumento();
            ultimoDocConsultado = '';
        }

        const estaCompleto =
            (tipoDoc === 'DNI' && /^\d{8}$/.test(valor)) ||
            (tipoDoc === 'RUC' && /^\d{11}$/.test(valor));

        if (estaCompleto) {
            if (valor === ultimoDocConsultado) return;

            ultimoDocConsultado = valor;

            document.getElementById('tipo_documento_envio').value = tipoDoc;
            document.getElementById('numero_documento_envio').value = valor;

            buscarPersona();
        }
    });

    numeroDocumento.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarPersona();
        }
    });

    numeroDocumentoJuridica.addEventListener('input', function() {
        const valor = this.value.trim();

        document.getElementById('numero_documento_envio').value = valor;
        document.getElementById('tipo_documento_envio').value = 'RUC';

        if (valor.length === 11 && /^\d{11}$/.test(valor)) {
            limpiarCamposDocumento();
            buscarPersonaJuridica();
        } else {
            document.getElementById('persona-encontrada').style.display = 'none';
            personaEncontrada = null;
        }
    });

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
        alert('Ingrese un numero de documento');
        return;
    }

    if (tipoDoc === 'DNI' && (numeroDoc.length !== 8 || !/^\d{8}$/.test(numeroDoc))) {
        alert('Ingrese un DNI valido de 8 digitos');
        return;
    }

    if (tipoDoc === 'RUC' && (numeroDoc.length !== 11 || !/^\d{11}$/.test(numeroDoc))) {
        alert('Ingrese un RUC valido de 11 digitos');
        return;
    }

    const btnBuscar = document.getElementById('btn-buscar');
    btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btnBuscar.disabled = true;

    fetch(`{{ route('mesa-partes.buscar-persona') }}?q=${numeroDoc}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                if (result.data.length > 0) {
                    mostrarPersonaEncontrada(result.data[0]);
                    usarPersonaExistente();
                } else {
                    const personaEncontradaElement = document.getElementById('persona-encontrada');
                    if (personaEncontradaElement) {
                        personaEncontradaElement.style.display = 'none';
                    }
                }
            } else {
                alert(result.error || 'Error al buscar persona');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexion: ' + error.message);
        })
        .finally(() => {
            btnBuscar.innerHTML = '<i class="fas fa-search"></i>';
            btnBuscar.disabled = false;
        });
}

function buscarPersonaJuridica() {
    const numeroDoc = document.getElementById('numero_documento_juridica').value.trim();

    if (!numeroDoc) {
        alert('Ingrese un numero de RUC');
        return;
    }

    if (numeroDoc.length !== 11 || !/^\d{11}$/.test(numeroDoc)) {
        alert('Ingrese un RUC valido de 11 digitos');
        return;
    }

    const btnBuscar = document.getElementById('btn-buscar-juridica');
    btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btnBuscar.disabled = true;

    document.getElementById('tipo_documento').value = 'RUC';
    document.getElementById('numero_documento').value = numeroDoc;

    fetch(`{{ route('mesa-partes.buscar-persona') }}?q=${numeroDoc}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                if (result.data.length > 0) {
                    mostrarPersonaEncontrada(result.data[0]);
                    usarPersonaExistente();
                } else {
                    const personaEncontradaElement = document.getElementById('persona-encontrada');
                    if (personaEncontradaElement) {
                        personaEncontradaElement.style.display = 'none';
                    }
                }
            } else {
                alert(result.error || 'Error al buscar empresa');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexion: ' + error.message);
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

    buscandoAuto = true;

    const p = personaEncontrada;

    const personaExistenteId = document.getElementById('persona_existente_id');
    if (personaExistenteId) personaExistenteId.value = p.id_persona;

    const tipoDoc = document.getElementById('tipo_documento');
    const numeroDoc = document.getElementById('numero_documento');
    const numeroDocJuridica = document.getElementById('numero_documento_juridica');
    const tipoPersona = document.getElementById('tipo_persona');

    document.getElementById('tipo_documento_envio').value = p.tipo_documento || 'DNI';
    document.getElementById('numero_documento_envio').value = p.numero_documento || '';

    if (tipoDoc) tipoDoc.value = p.tipo_documento || 'DNI';
    if (tipoPersona) tipoPersona.value = p.tipo_persona || 'NATURAL';

    if (p.tipo_persona === 'NATURAL') {
        if (numeroDoc) numeroDoc.value = p.numero_documento || '';

        const nombres = document.getElementById('nombres');
        const apellidoP = document.getElementById('apellido_paterno');
        const apellidoM = document.getElementById('apellido_materno');

        if (nombres) nombres.value = p.nombres || '';
        if (apellidoP) apellidoP.value = p.apellido_paterno || '';
        if (apellidoM) apellidoM.value = p.apellido_materno || '';
    } else {
        if (numeroDocJuridica) numeroDocJuridica.value = p.numero_documento || '';

        const razonSocial = document.getElementById('razon_social');
        const representante = document.getElementById('representante_legal');

        if (razonSocial) razonSocial.value = p.razon_social || '';
        if (representante) representante.value = p.representante_legal || '';
    }

    const telefono = document.getElementById('telefono');
    const email = document.getElementById('email');
    const direccion = document.getElementById('direccion');

    if (telefono) telefono.value = p.telefono || '';
    if (email) email.value = p.email || '';
    if (direccion) direccion.value = p.direccion || '';

    togglePersonaFields();

    const personaEncontradaElement = document.getElementById('persona-encontrada');
    if (personaEncontradaElement) {
        personaEncontradaElement.style.display = 'none';
    }

    deshabilitarCamposPersona(true);

    buscandoAuto = false;
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

// Cargar funcionarios cuando cambia el area
document.addEventListener('DOMContentLoaded', function() {
    const areaSelect = document.getElementById('id_area');
    const funcionarioSelect = document.getElementById('id_funcionario_asignado');

    const tipoTramiteSelect = document.getElementById('id_tipo_tramite');
    const plazoDiasInput = document.getElementById('plazo_dias');

    if (areaSelect) {
        areaSelect.addEventListener('change', function() {
            const areaId = this.value;

            if (funcionarioSelect) {
                funcionarioSelect.innerHTML = '<option value="">Cargando...</option>';
            }

            if (tipoTramiteSelect) {
                tipoTramiteSelect.innerHTML = '<option value="">Cargando...</option>';
            }

            if (!areaId) {
                if (funcionarioSelect) {
                    funcionarioSelect.innerHTML = '<option value="">Seleccione primero un area</option>';
                }
                if (tipoTramiteSelect) {
                    tipoTramiteSelect.innerHTML = '<option value="">Primero seleccione un area</option>';
                }
                return;
            }

            if (funcionarioSelect) {
                fetch(`${window.APP_URL}/api/areas/${areaId}/funcionarios`)
                    .then(response => response.json())
                    .then(data => {
                        funcionarioSelect.innerHTML = '<option value="">Sin asignar (el jefe asignara despues)</option>';

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

            if (tipoTramiteSelect) {
                fetch(`${window.APP_URL}/api/areas/${areaId}/tipos-tramite`)
                    .then(response => response.json())
                    .then(data => {
                        tipoTramiteSelect.innerHTML = '<option value="">Seleccione un tipo de tramite</option>';

                        if (data.tipos_tramite && data.tipos_tramite.length > 0) {
                            data.tipos_tramite.forEach(tipo => {
                                const option = document.createElement('option');
                                option.value = tipo.id_tipo_tramite;
                                option.textContent = tipo.nombre;
                                option.dataset.plazoDias = tipo.plazo_dias;
                                tipoTramiteSelect.appendChild(option);
                            });
                        } else {
                            tipoTramiteSelect.innerHTML = '<option value="">No hay tramites para esta area</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar tipos de tramite:', error);
                        tipoTramiteSelect.innerHTML = '<option value="">Error al cargar tipos de tramite</option>';
                    });
            }
        });
    }

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
        personaNatural.style.display = 'block';
        personaJuridica.style.display = 'none';
        camposNaturalDoc.style.display = 'block';
        camposJuridicaDoc.style.display = 'none';

        document.getElementById('nombres').required = true;
        document.getElementById('apellido_paterno').required = true;
        tipoDocumento.required = true;
        numeroDocumento.required = true;

        document.getElementById('razon_social').required = false;
        document.getElementById('razon_social').value = '';
        document.getElementById('representante_legal').value = '';

        if (numeroDocumentoJuridica.value) {
            numeroDocumento.value = '';
        }
    } else {
        personaNatural.style.display = 'none';
        personaJuridica.style.display = 'block';
        camposNaturalDoc.style.display = 'none';
        camposJuridicaDoc.style.display = 'block';

        document.getElementById('razon_social').required = true;

        document.getElementById('nombres').required = false;
        document.getElementById('apellido_paterno').required = false;
        document.getElementById('nombres').value = '';
        document.getElementById('apellido_paterno').value = '';
        document.getElementById('apellido_materno').value = '';
        tipoDocumento.required = false;
        numeroDocumento.required = false;

        tipoDocumento.value = 'RUC';
        document.getElementById('tipo_documento_envio').value = 'RUC';

        if (numeroDocumento.value && !numeroDocumentoJuridica.value) {
            numeroDocumentoJuridica.value = numeroDocumento.value;
        }
        if (numeroDocumentoJuridica.value) {
            document.getElementById('numero_documento_envio').value = numeroDocumentoJuridica.value;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const tipoPersonaSelect = document.getElementById('tipo_persona');
    if (tipoPersonaSelect) {
        tipoPersonaSelect.addEventListener('change', togglePersonaFields);
        togglePersonaFields();
    }
});

function validarYEnviar() {
    const tipoPersona = document.getElementById('tipo_persona').value;

    if (tipoPersona === 'JURIDICA') {
        const rucValue = document.getElementById('numero_documento_juridica').value.trim();

        if (!rucValue || rucValue.length !== 11) {
            alert('Debe ingresar un RUC valido de 11 digitos');
            document.getElementById('numero_documento_juridica').focus();
            return false;
        }

        const razonSocial = document.getElementById('razon_social').value.trim();
        if (!razonSocial) {
            alert('Debe ingresar la razon social');
            document.getElementById('razon_social').focus();
            return false;
        }

        document.getElementById('tipo_documento_envio').value = 'RUC';
        document.getElementById('numero_documento_envio').value = rucValue;
    } else {
        const numeroDoc = document.getElementById('numero_documento').value.trim();
        if (!numeroDoc) {
            alert('Debe ingresar el numero de documento');
            document.getElementById('numero_documento').focus();
            return false;
        }

        const nombres = document.getElementById('nombres').value.trim();
        if (!nombres) {
            alert('Debe ingresar los nombres');
            document.getElementById('nombres').focus();
            return false;
        }

        const apellidoPaterno = document.getElementById('apellido_paterno').value.trim();
        if (!apellidoPaterno) {
            alert('Debe ingresar el apellido paterno');
            document.getElementById('apellido_paterno').focus();
            return false;
        }

        document.getElementById('tipo_documento_envio').value = document.getElementById('tipo_documento').value;
        document.getElementById('numero_documento_envio').value = numeroDoc;
    }

    const tipoDocEntrante = document.getElementById('tipo_documento_entrante').value;
    if (!tipoDocEntrante) {
        alert('Debe seleccionar el tipo de documento');
        document.getElementById('tipo_documento_entrante').focus();
        return false;
    }

    const asunto = document.getElementById('asunto_documento').value.trim();
    if (!asunto || asunto.length < 10) {
        alert('Debe ingresar el asunto del documento (minimo 10 caracteres)');
        document.getElementById('asunto_documento').focus();
        return false;
    }

    const idArea = document.getElementById('id_area').value;
    if (!idArea) {
        alert('Debe seleccionar el area de destino');
        document.getElementById('id_area').focus();
        return false;
    }

    const idTipoTramite = document.getElementById('id_tipo_tramite').value;
    if (!idTipoTramite) {
        alert('Debe seleccionar el tipo de tramite');
        document.getElementById('id_tipo_tramite').focus();
        return false;
    }

    document.getElementById('form-registrar').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        setTimeout(function() {
            limpiarFormularioCompleto();
        }, 2000);
    @endif

    const numeroDocumentoVisible = document.getElementById('numero_documento');
    if (numeroDocumentoVisible) {
        numeroDocumentoVisible.addEventListener('input', function() {
            document.getElementById('numero_documento_envio').value = this.value;
        });
    }

    const tipoDocumentoVisible = document.getElementById('tipo_documento');
    if (tipoDocumentoVisible) {
        tipoDocumentoVisible.addEventListener('change', function() {
            ultimoDocConsultado = '';
            resetEstadoPersonaPorCambioDocumento();

            document.getElementById('tipo_documento_envio').value = this.value;
            document.getElementById('numero_documento_envio').value = document.getElementById('numero_documento').value.trim();
        });
    }
});

function limpiarFormularioCompleto() {
    document.getElementById('persona_existente_id').value = '';
    document.getElementById('tipo_documento').value = 'DNI';
    document.getElementById('numero_documento').value = '';
    document.getElementById('tipo_persona').value = 'NATURAL';

    document.getElementById('tipo_documento_envio').value = 'DNI';
    document.getElementById('numero_documento_envio').value = '';

    limpiarCamposPersona();
    limpiarCamposDocumento();

    const idArea = document.getElementById('id_area');
    const idTipoTramite = document.getElementById('id_tipo_tramite');
    const prioridad = document.getElementById('prioridad');
    const funcionarioAsignado = document.getElementById('id_funcionario_asignado');
    const plazoDias = document.getElementById('plazo_dias');

    if (idArea) idArea.value = '';
    if (idTipoTramite) idTipoTramite.innerHTML = '<option value="">Primero seleccione un area</option>';
    if (prioridad) prioridad.value = 'normal';
    if (funcionarioAsignado) funcionarioAsignado.innerHTML = '<option value="">Seleccione primero un area</option>';
    if (plazoDias) plazoDias.value = '15';

    document.getElementById('persona-encontrada').style.display = 'none';
    deshabilitarCamposPersona(false);
    togglePersonaFields();

    personaEncontrada = null;
}

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

function nuevoRegistro() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalCargoExitoso'));
    if (modal) {
        modal.hide();
    }

    limpiarFormularioCompleto();

    setTimeout(function() {
        const campoDoc = document.getElementById('numero_documento');
        if (campoDoc) {
            campoDoc.focus();
        }
    }, 300);
}
</script>

<script src="{{ asset('js/validacion-documentos.js') }}"></script>

@if(session('codigo_expediente'))
    @php
        $expediente = \App\Models\Expediente::where('codigo_expediente', session('codigo_expediente'))->first();
    @endphp
    @if($expediente)
        <script>
            window.addEventListener('DOMContentLoaded', function() {
                const modal = new bootstrap.Modal(document.getElementById('modalCargoExitoso'));
                modal.show();
            });

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
