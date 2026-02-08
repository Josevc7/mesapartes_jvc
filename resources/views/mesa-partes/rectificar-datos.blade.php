@extends('layouts.app')

@section('title', 'Rectificar Datos - ' . $expediente->codigo_expediente)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">

            {{-- Encabezado --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-edit text-warning me-2"></i>Rectificar Datos del Expediente</h2>
                    <p class="text-muted mb-0">
                        Corrija los datos incorrectos. El estado del expediente
                        (<strong>{{ ucfirst(str_replace('_', ' ', $expediente->estado)) }}</strong>)
                        no se modificará.
                    </p>
                </div>
                <a href="{{ route('mesa-partes.show', $expediente) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('mesa-partes.store-rectificacion', $expediente) }}">
                @csrf

                {{-- Motivo de rectificación (obligatorio) --}}
                <div class="card mb-4 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Motivo de Rectificación</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Todos los cambios quedarán registrados en el historial con los valores anteriores y nuevos.
                        </div>
                        <div class="mb-0">
                            <label for="motivo_rectificacion" class="form-label fw-bold">Motivo *</label>
                            <textarea class="form-control @error('motivo_rectificacion') is-invalid @enderror"
                                      id="motivo_rectificacion" name="motivo_rectificacion" rows="2" required minlength="10"
                                      placeholder="Ej: Error en digitación del DNI y nombre del ciudadano">{{ old('motivo_rectificacion') }}</textarea>
                            @error('motivo_rectificacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Datos del expediente --}}
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Datos del Expediente</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Código</label>
                                    <input type="text" class="form-control" value="{{ $expediente->codigo_expediente }}" disabled>
                                    <div class="form-text">El código no se puede modificar.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="asunto" class="form-label fw-bold">Asunto *</label>
                                    <textarea class="form-control @error('asunto') is-invalid @enderror"
                                              id="asunto" name="asunto" rows="3" required>{{ old('asunto', $expediente->asunto) }}</textarea>
                                    @error('asunto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="id_tipo_tramite" class="form-label fw-bold">Tipo de Trámite *</label>
                                    <select class="form-select @error('id_tipo_tramite') is-invalid @enderror"
                                            id="id_tipo_tramite" name="id_tipo_tramite" required>
                                        @foreach($tipoTramites as $tipo)
                                            <option value="{{ $tipo->id_tipo_tramite }}"
                                                {{ old('id_tipo_tramite', $expediente->id_tipo_tramite) == $tipo->id_tipo_tramite ? 'selected' : '' }}>
                                                {{ $tipo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_tipo_tramite')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="folios" class="form-label fw-bold">Folios</label>
                                        <input type="number" class="form-control @error('folios') is-invalid @enderror"
                                               id="folios" name="folios" min="1" max="999"
                                               value="{{ old('folios', $expediente->folios) }}">
                                        @error('folios')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="prioridad" class="form-label fw-bold">Prioridad *</label>
                                        <select class="form-select @error('prioridad') is-invalid @enderror"
                                                id="prioridad" name="prioridad" required>
                                            @foreach(['baja', 'normal', 'alta', 'urgente'] as $p)
                                                <option value="{{ $p }}" {{ old('prioridad', $expediente->prioridad) === $p ? 'selected' : '' }}>
                                                    {{ ucfirst($p) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Datos de la persona/ciudadano --}}
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Datos del Solicitante</h5>
                            </div>
                            <div class="card-body">
                                @if($expediente->persona)
                                    <div class="mb-3">
                                        <label class="form-label">Tipo de Persona</label>
                                        <input type="text" class="form-control" value="{{ $expediente->persona->tipo_persona }}" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Tipo Documento</label>
                                        <input type="text" class="form-control" value="{{ $expediente->persona->tipo_documento }}" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="numero_documento" class="form-label fw-bold">N° Documento</label>
                                        <input type="text" class="form-control @error('numero_documento') is-invalid @enderror"
                                               id="numero_documento" name="numero_documento"
                                               value="{{ old('numero_documento', $expediente->persona->numero_documento) }}">
                                        @error('numero_documento')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    @if($expediente->persona->tipo_persona === 'NATURAL')
                                        <div class="mb-3">
                                            <label for="nombres" class="form-label fw-bold">Nombres</label>
                                            <input type="text" class="form-control @error('nombres') is-invalid @enderror"
                                                   id="nombres" name="nombres"
                                                   value="{{ old('nombres', $expediente->persona->nombres) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="apellido_paterno" class="form-label fw-bold">Apellido Paterno</label>
                                            <input type="text" class="form-control @error('apellido_paterno') is-invalid @enderror"
                                                   id="apellido_paterno" name="apellido_paterno"
                                                   value="{{ old('apellido_paterno', $expediente->persona->apellido_paterno) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="apellido_materno" class="form-label fw-bold">Apellido Materno</label>
                                            <input type="text" class="form-control @error('apellido_materno') is-invalid @enderror"
                                                   id="apellido_materno" name="apellido_materno"
                                                   value="{{ old('apellido_materno', $expediente->persona->apellido_materno) }}">
                                        </div>
                                    @else
                                        <div class="mb-3">
                                            <label for="razon_social" class="form-label fw-bold">Razón Social</label>
                                            <input type="text" class="form-control @error('razon_social') is-invalid @enderror"
                                                   id="razon_social" name="razon_social"
                                                   value="{{ old('razon_social', $expediente->persona->razon_social) }}">
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        <label for="telefono" class="form-label fw-bold">Teléfono</label>
                                        <input type="text" class="form-control" id="telefono" name="telefono"
                                               value="{{ old('telefono', $expediente->persona->telefono) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="email_persona" class="form-label fw-bold">Email</label>
                                        <input type="email" class="form-control" id="email_persona" name="email_persona"
                                               value="{{ old('email_persona', $expediente->persona->email) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="direccion" class="form-label fw-bold">Dirección</label>
                                        <input type="text" class="form-control" id="direccion" name="direccion"
                                               value="{{ old('direccion', $expediente->persona->direccion) }}">
                                    </div>
                                @else
                                    <div class="alert alert-secondary">
                                        <i class="fas fa-info-circle me-2"></i>No hay datos de persona asociados a este expediente.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Botones de acción --}}
                <div class="card">
                    <div class="card-body d-flex justify-content-between">
                        <a href="{{ route('mesa-partes.show', $expediente) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="fas fa-check-circle me-1"></i> Confirmar Rectificación
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
