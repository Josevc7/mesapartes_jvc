@extends('layouts.app')

@section('title', 'Clasificar y Derivar Expediente Virtual')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-success text-white py-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="bg-white bg-opacity-20 rounded-circle p-3 me-3">
                                <i class="fas fa-globe fa-2x"></i>
                            </div>
                            <div>
                                <h3 class="mb-1 fw-bold">Clasificar y Derivar Expediente Virtual</h3>
                                <p class="mb-0 opacity-90">Expediente: {{ $expediente->codigo_expediente }}</p>
                            </div>
                        </div>
                        <a href="{{ route('mesa-partes.expedientes-virtuales') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
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

                    <!-- Información del Expediente Virtual -->
                    <div class="alert alert-info border-0 mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold">
                                    <i class="fas fa-user me-2"></i>
                                    Información del Ciudadano
                                </h6>
                                @if($expediente->persona)
                                    <p class="mb-1"><strong>Nombre:</strong> {{ $expediente->persona->nombre_completo }}</p>
                                    <p class="mb-1"><strong>Documento:</strong> {{ $expediente->persona->tipo_documento }} - {{ $expediente->persona->numero_documento }}</p>
                                    @if($expediente->persona->telefono)
                                    <p class="mb-1"><strong>Teléfono:</strong> {{ $expediente->persona->telefono }}</p>
                                    @endif
                                    @if($expediente->persona->email)
                                    <p class="mb-0"><strong>Email:</strong> {{ $expediente->persona->email }}</p>
                                    @endif
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Información del Trámite
                                </h6>
                                <p class="mb-1"><strong>Código:</strong> {{ $expediente->codigo_expediente }}</p>
                                <p class="mb-1"><strong>Tipo:</strong> {{ $expediente->tipoTramite->nombre ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Fecha:</strong> {{ $expediente->created_at->format('d/m/Y H:i') }}</p>
                                <p class="mb-1"><strong>Canal:</strong> <span class="badge bg-primary">Virtual</span></p>
                                <p class="mb-0"><strong>Documentos:</strong> {{ $expediente->documentos->count() }} adjunto(s)</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="fw-bold">
                                    <i class="fas fa-align-left me-2"></i>
                                    Asunto
                                </h6>
                                <p class="mb-0">{{ $expediente->asunto }}</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('mesa-partes.store-clasificar-virtual', $expediente) }}">
                        @csrf

                        <!-- Clasificación del Expediente -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-tags text-warning fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-warning fw-bold">1. Clasificación del Expediente</h5>
                                    <p class="text-muted mb-0 small">Asigne el área y prioridad</p>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="id_area" class="form-label fw-semibold">
                                        <i class="fas fa-building text-warning me-2"></i>Área de Destino *
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

                                <div class="col-md-6">
                                    <label for="prioridad" class="form-label fw-semibold">
                                        <i class="fas fa-exclamation-circle text-warning me-2"></i>Prioridad *
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

                                <div class="col-md-12">
                                    <label for="observaciones_clasificacion" class="form-label fw-semibold">
                                        <i class="fas fa-comment text-warning me-2"></i>Observaciones de Clasificación
                                    </label>
                                    <textarea class="form-control @error('observaciones_clasificacion') is-invalid @enderror"
                                              id="observaciones_clasificacion" name="observaciones_clasificacion"
                                              rows="2" placeholder="Observaciones sobre la clasificación...">{{ old('observaciones_clasificacion') }}</textarea>
                                    @error('observaciones_clasificacion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Derivación del Expediente -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-share text-primary fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-primary fw-bold">2. Derivación del Expediente</h5>
                                    <p class="text-muted mb-0 small">Asigne el funcionario y establezca plazos</p>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="id_funcionario_asignado" class="form-label fw-semibold">
                                        <i class="fas fa-user-tie text-primary me-2"></i>Funcionario Asignado
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
                                        <i class="fas fa-calendar-alt text-primary me-2"></i>Plazo (días) *
                                    </label>
                                    <input type="number" class="form-control form-control-lg @error('plazo_dias') is-invalid @enderror"
                                           id="plazo_dias" name="plazo_dias" value="{{ old('plazo_dias', 15) }}"
                                           min="1" max="365" required>
                                    <div class="form-text">Días hábiles</div>
                                    @error('plazo_dias')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="prioridad_derivacion" class="form-label fw-semibold">
                                        <i class="fas fa-flag text-primary me-2"></i>Prioridad *
                                    </label>
                                    <select class="form-select form-select-lg @error('prioridad_derivacion') is-invalid @enderror"
                                            id="prioridad_derivacion" name="prioridad_derivacion" required>
                                        <option value="normal" {{ old('prioridad_derivacion', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="baja" {{ old('prioridad_derivacion') == 'baja' ? 'selected' : '' }}>Baja</option>
                                        <option value="alta" {{ old('prioridad_derivacion') == 'alta' ? 'selected' : '' }}>Alta</option>
                                        <option value="urgente" {{ old('prioridad_derivacion') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                    </select>
                                    @error('prioridad_derivacion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label for="observaciones_derivacion" class="form-label fw-semibold">
                                        <i class="fas fa-comment-dots text-primary me-2"></i>Observaciones de Derivación
                                    </label>
                                    <textarea class="form-control @error('observaciones_derivacion') is-invalid @enderror"
                                              id="observaciones_derivacion" name="observaciones_derivacion"
                                              rows="3" placeholder="Instrucciones o observaciones para el funcionario...">{{ old('observaciones_derivacion') }}</textarea>
                                    @error('observaciones_derivacion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success border-0 shadow-sm mb-4">
                            <div class="d-flex align-items-start">
                                <div class="bg-success bg-opacity-20 rounded-circle p-2 me-3">
                                    <i class="fas fa-info-circle text-success"></i>
                                </div>
                                <div>
                                    <h6 class="alert-heading mb-2">Expediente Virtual</h6>
                                    <p class="mb-0 small">
                                        Este expediente fue ingresado por el ciudadano vía plataforma web.
                                        Al clasificar y derivar, quedará listo para ser atendido por el funcionario asignado.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 justify-content-end pt-4 border-top">
                            <a href="{{ route('mesa-partes.expedientes-virtuales') }}" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                                <i class="fas fa-check-double me-2"></i>Clasificar y Derivar
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
// Cargar funcionarios cuando cambia el área
document.addEventListener('DOMContentLoaded', function() {
    const areaSelect = document.getElementById('id_area');
    const funcionarioSelect = document.getElementById('id_funcionario_asignado');

    if (areaSelect && funcionarioSelect) {
        areaSelect.addEventListener('change', function() {
            const areaId = this.value;

            funcionarioSelect.innerHTML = '<option value="">Cargando...</option>';

            if (!areaId) {
                funcionarioSelect.innerHTML = '<option value="">Seleccione primero un área</option>';
                return;
            }

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
        });
    }
});
</script>
@endsection
@endsection
