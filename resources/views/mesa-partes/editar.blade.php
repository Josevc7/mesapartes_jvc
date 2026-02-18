@extends('layouts.app')

@section('title', 'Editar Expediente')

@push('styles')
<link href="{{ asset('css/modern-forms.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="modern-form-container">
    <div class="modern-form-card">
        <!-- ENCABEZADO -->
        <div class="modern-form-header">
            <div class="modern-form-header-content">
                <div class="modern-form-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="modern-form-title">
                    <h1>Editar Expediente</h1>
                    <p>{{ $expediente->codigo_expediente }}</p>
                </div>
            </div>
        </div>

        <!-- CUERPO DEL FORMULARIO -->
        <div class="modern-form-body">
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

            <form method="POST" action="{{ route('mesa-partes.update', $expediente) }}" id="form-editar">
                @csrf
                @method('PUT')

                <!-- Codigo de Expediente (BLOQUEADO) -->
                <div class="mb-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-barcode text-dark me-1"></i>Codigo de Expediente
                            </label>
                            <input type="text" class="form-control fw-bold" value="{{ $expediente->codigo_expediente }}" readonly disabled style="background-color: #e9ecef; font-family: 'Consolas', monospace; font-size: 1.1rem;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-info-circle text-primary me-1"></i>Estado Actual
                            </label>
                            <input type="text" class="form-control" value="{{ $expediente->getEstadoFormateadoInteligente() }}" readonly disabled style="background-color: #e9ecef;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-calendar text-muted me-1"></i>Fecha de Registro
                            </label>
                            <input type="text" class="form-control" value="{{ $expediente->created_at->format('d/m/Y H:i') }}" readonly disabled style="background-color: #e9ecef;">
                        </div>
                    </div>
                </div>

                <!-- Seccion 1: Datos del Solicitante -->
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-user text-primary fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 text-primary fw-bold">1. Datos del Solicitante</h5>
                            <p class="text-muted mb-0 small">Datos del ciudadano o empresa</p>
                        </div>
                    </div>

                    @if($expediente->persona)
                        @if($expediente->persona->tipo_persona === 'NATURAL')
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="tipo_documento_persona" class="form-label fw-semibold">
                                    <i class="fas fa-id-card text-primary me-1"></i>Tipo Documento
                                </label>
                                <select class="form-select" id="tipo_documento_persona" name="tipo_documento_persona">
                                    @foreach(['DNI', 'CE', 'RUC', 'PASAPORTE', 'OTROS'] as $td)
                                        <option value="{{ $td }}" {{ old('tipo_documento_persona', $expediente->persona->tipo_documento) == $td ? 'selected' : '' }}>{{ $td }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="numero_documento_persona" class="form-label fw-semibold">
                                    <i class="fas fa-hashtag text-primary me-1"></i>N° Documento
                                </label>
                                <input type="text" class="form-control @error('numero_documento_persona') is-invalid @enderror"
                                       id="numero_documento_persona" name="numero_documento_persona"
                                       value="{{ old('numero_documento_persona', $expediente->persona->numero_documento) }}">
                                @error('numero_documento_persona')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="nombres" class="form-label fw-semibold">Nombres</label>
                                <input type="text" class="form-control @error('nombres') is-invalid @enderror"
                                       id="nombres" name="nombres"
                                       value="{{ old('nombres', $expediente->persona->nombres) }}">
                                @error('nombres')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label for="apellido_paterno" class="form-label fw-semibold">Apellido Paterno</label>
                                <input type="text" class="form-control @error('apellido_paterno') is-invalid @enderror"
                                       id="apellido_paterno" name="apellido_paterno"
                                       value="{{ old('apellido_paterno', $expediente->persona->apellido_paterno) }}">
                                @error('apellido_paterno')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="apellido_materno" class="form-label fw-semibold">Apellido Materno</label>
                                <input type="text" class="form-control @error('apellido_materno') is-invalid @enderror"
                                       id="apellido_materno" name="apellido_materno"
                                       value="{{ old('apellido_materno', $expediente->persona->apellido_materno) }}">
                                @error('apellido_materno')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="telefono_persona" class="form-label fw-semibold">
                                    <i class="fas fa-phone text-muted me-1"></i>Telefono
                                </label>
                                <input type="text" class="form-control @error('telefono_persona') is-invalid @enderror"
                                       id="telefono_persona" name="telefono_persona"
                                       value="{{ old('telefono_persona', $expediente->persona->telefono) }}">
                                @error('telefono_persona')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label for="email_persona" class="form-label fw-semibold">
                                    <i class="fas fa-envelope text-muted me-1"></i>Email
                                </label>
                                <input type="email" class="form-control @error('email_persona') is-invalid @enderror"
                                       id="email_persona" name="email_persona"
                                       value="{{ old('email_persona', $expediente->persona->email) }}">
                                @error('email_persona')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-8">
                                <label for="direccion_persona" class="form-label fw-semibold">
                                    <i class="fas fa-map-marker-alt text-muted me-1"></i>Direccion
                                </label>
                                <input type="text" class="form-control @error('direccion_persona') is-invalid @enderror"
                                       id="direccion_persona" name="direccion_persona"
                                       value="{{ old('direccion_persona', $expediente->persona->direccion) }}">
                                @error('direccion_persona')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @else
                        {{-- Persona Juridica --}}
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="numero_documento_persona" class="form-label fw-semibold">
                                    <i class="fas fa-hashtag text-primary me-1"></i>RUC
                                </label>
                                <input type="text" class="form-control @error('numero_documento_persona') is-invalid @enderror"
                                       id="numero_documento_persona" name="numero_documento_persona"
                                       value="{{ old('numero_documento_persona', $expediente->persona->numero_documento) }}">
                                <input type="hidden" name="tipo_documento_persona" value="RUC">
                                @error('numero_documento_persona')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5">
                                <label for="razon_social" class="form-label fw-semibold">Razon Social</label>
                                <input type="text" class="form-control @error('razon_social') is-invalid @enderror"
                                       id="razon_social" name="razon_social"
                                       value="{{ old('razon_social', $expediente->persona->razon_social) }}">
                                @error('razon_social')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="representante_legal" class="form-label fw-semibold">Representante Legal</label>
                                <input type="text" class="form-control @error('representante_legal') is-invalid @enderror"
                                       id="representante_legal" name="representante_legal"
                                       value="{{ old('representante_legal', $expediente->persona->representante_legal) }}">
                                @error('representante_legal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-3">
                                <label for="telefono_persona" class="form-label fw-semibold">
                                    <i class="fas fa-phone text-muted me-1"></i>Telefono
                                </label>
                                <input type="text" class="form-control @error('telefono_persona') is-invalid @enderror"
                                       id="telefono_persona" name="telefono_persona"
                                       value="{{ old('telefono_persona', $expediente->persona->telefono) }}">
                                @error('telefono_persona')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="email_persona" class="form-label fw-semibold">
                                    <i class="fas fa-envelope text-muted me-1"></i>Email
                                </label>
                                <input type="email" class="form-control @error('email_persona') is-invalid @enderror"
                                       id="email_persona" name="email_persona"
                                       value="{{ old('email_persona', $expediente->persona->email) }}">
                                @error('email_persona')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5">
                                <label for="direccion_persona" class="form-label fw-semibold">
                                    <i class="fas fa-map-marker-alt text-muted me-1"></i>Direccion
                                </label>
                                <input type="text" class="form-control @error('direccion_persona') is-invalid @enderror"
                                       id="direccion_persona" name="direccion_persona"
                                       value="{{ old('direccion_persona', $expediente->persona->direccion) }}">
                                @error('direccion_persona')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @endif
                    @else
                        {{-- Sin persona asociada, editar remitente directo --}}
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="dni_remitente" class="form-label fw-semibold">
                                    <i class="fas fa-id-card text-primary me-1"></i>DNI Remitente
                                </label>
                                <input type="text" class="form-control @error('dni_remitente') is-invalid @enderror"
                                       id="dni_remitente" name="dni_remitente"
                                       value="{{ old('dni_remitente', $expediente->dni_remitente) }}">
                                @error('dni_remitente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-9">
                                <label for="remitente" class="form-label fw-semibold">
                                    <i class="fas fa-user text-primary me-1"></i>Nombre del Remitente
                                </label>
                                <input type="text" class="form-control @error('remitente') is-invalid @enderror"
                                       id="remitente" name="remitente"
                                       value="{{ old('remitente', $expediente->remitente) }}">
                                @error('remitente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Seccion 2: Datos del Documento -->
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-file-alt text-warning fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 text-warning fw-bold">2. Datos del Documento</h5>
                            <p class="text-muted mb-0 small">Modifique los datos del documento</p>
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
                                @foreach(['SOLICITUD', 'FUT', 'OFICIO', 'INFORME', 'MEMORANDUM', 'CARTA', 'RESOLUCION', 'OTROS'] as $tipo)
                                    <option value="{{ $tipo }}" {{ old('tipo_documento_entrante', $expediente->tipo_documento_entrante) == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                                @endforeach
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
                                   id="numero_documento_entrante" name="numero_documento_entrante"
                                   value="{{ old('numero_documento_entrante', $expediente->numero_documento_entrante) }}">
                            @error('numero_documento_entrante')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label for="asunto" class="form-label fw-semibold">
                                <i class="fas fa-align-left text-warning me-1"></i>Asunto *
                            </label>
                            <input type="text" class="form-control @error('asunto') is-invalid @enderror"
                                   id="asunto" name="asunto"
                                   value="{{ old('asunto', $expediente->asunto) }}" required>
                            @error('asunto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-2">
                            <label for="folios" class="form-label fw-semibold">
                                <i class="fas fa-copy text-warning me-1"></i>Folios
                            </label>
                            <input type="number" class="form-control @error('folios') is-invalid @enderror"
                                   id="folios" name="folios"
                                   value="{{ old('folios', $expediente->folios) }}" min="1" max="9999">
                            @error('folios')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-12">
                            <label for="asunto_documento" class="form-label fw-semibold">
                                <i class="fas fa-file-signature text-warning me-1"></i>Asunto del Documento
                            </label>
                            <input type="text" class="form-control @error('asunto_documento') is-invalid @enderror"
                                   id="asunto_documento" name="asunto_documento"
                                   value="{{ old('asunto_documento', $expediente->asunto_documento) }}">
                            @error('asunto_documento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Seccion 3: Clasificacion y Derivacion -->
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-tags text-success fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 text-success fw-bold">3. Clasificacion y Derivacion</h5>
                            <p class="text-muted mb-0 small">Area de destino y tipo de tramite</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="id_area" class="form-label fw-semibold">
                                <i class="fas fa-building text-success me-1"></i>Area *
                            </label>
                            <select class="form-select @error('id_area') is-invalid @enderror"
                                    id="id_area" name="id_area" required>
                                <option value="">Seleccione un area</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id_area }}" {{ old('id_area', $expediente->id_area) == $area->id_area ? 'selected' : '' }}>
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
                                <option value="">Seleccione...</option>
                                @foreach($tipoTramites as $tipo)
                                    <option value="{{ $tipo->id_tipo_tramite }}" {{ old('id_tipo_tramite', $expediente->id_tipo_tramite) == $tipo->id_tipo_tramite ? 'selected' : '' }}>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
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
                                <option value="baja" {{ old('prioridad', $expediente->prioridad) == 'baja' ? 'selected' : '' }}>Baja</option>
                                <option value="normal" {{ old('prioridad', $expediente->prioridad) == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="alta" {{ old('prioridad', $expediente->prioridad) == 'alta' ? 'selected' : '' }}>Alta</option>
                                <option value="urgente" {{ old('prioridad', $expediente->prioridad) == 'urgente' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            @error('prioridad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="mb-3">
                    <label for="observaciones" class="form-label fw-semibold">
                        <i class="fas fa-comment text-primary me-1"></i>Observaciones
                    </label>
                    <textarea class="form-control @error('observaciones') is-invalid @enderror"
                              id="observaciones" name="observaciones" rows="3"
                              placeholder="Observaciones adicionales...">{{ old('observaciones', $expediente->observaciones) }}</textarea>
                    @error('observaciones')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Documentos adjuntos (informativo) -->
                @if($expediente->documentos->count() > 0)
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-paperclip text-info me-1"></i>Documentos Adjuntos
                    </label>
                    <div class="list-group">
                        @foreach($expediente->documentos as $doc)
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2">
                                <span><i class="fas fa-file-pdf text-danger me-2"></i>{{ $doc->nombre_original ?? $doc->nombre }}</span>
                                <span class="badge bg-secondary">{{ $doc->tipo ?? 'PDF' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Barra de acciones -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between pt-3 border-top">
                    <a href="{{ route('mesa-partes.index') }}" class="btn btn-outline-secondary px-3">
                        <i class="fas fa-arrow-left me-1"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="fas fa-save me-1"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
