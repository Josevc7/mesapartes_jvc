@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Derivar Expediente') }} - {{ $expediente->codigo_expediente }}</h4>
                </div>

                <div class="card-body">
                    <!-- Información del Expediente -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Información del Expediente</h6>
                            <p><strong>Código:</strong> {{ $expediente->codigo_expediente }}</p>
                            <p><strong>Remitente:</strong> {{ $expediente->remitente ?? ($expediente->ciudadano->name ?? 'N/A') }}</p>
                            <p><strong>DNI:</strong> {{ $expediente->dni_remitente ?? ($expediente->ciudadano->dni ?? 'N/A') }}</p>
                            <p><strong>Tipo:</strong> {{ $expediente->tipoTramite->nombre ?? 'Sin clasificar' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Asunto</h6>
                            <p>{{ $expediente->asunto }}</p>
                            <p><strong>Estado:</strong> 
                                <span class="badge bg-{{ $expediente->estado == 'pendiente' ? 'warning' : 'info' }}">
                                    {{ ucfirst($expediente->estado) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <!-- Formulario de Derivación -->
                    <form method="POST" action="{{ route('mesa-partes.store-derivar', $expediente) }}">
                        @csrf

                        <h5 class="mb-3">Datos de Derivación</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="id_area_destino" class="form-label">{{ __('Área Destino') }}</label>
                                <select id="id_area_destino" class="form-select @error('id_area_destino') is-invalid @enderror" 
                                        name="id_area_destino" required>
                                    <option value="">Seleccione un área</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id_area }}" {{ old('id_area_destino') == $area->id_area ? 'selected' : '' }}>
                                            {{ $area->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_area_destino')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="id_funcionario_asignado" class="form-label">{{ __('Funcionario Asignado') }}</label>
                                <select id="id_funcionario_asignado" class="form-select @error('id_funcionario_asignado') is-invalid @enderror" 
                                        name="id_funcionario_asignado">
                                    <option value="">Seleccione un funcionario (opcional)</option>
                                    @foreach($funcionarios as $funcionario)
                                        <option value="{{ $funcionario->id }}" {{ old('id_funcionario_asignado') == $funcionario->id ? 'selected' : '' }}>
                                            {{ $funcionario->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_funcionario_asignado')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="plazo_dias" class="form-label">{{ __('Plazo en Días') }}</label>
                                <input id="plazo_dias" type="number" class="form-control @error('plazo_dias') is-invalid @enderror" 
                                       name="plazo_dias" value="{{ old('plazo_dias', $expediente->tipoTramite->plazo_dias) }}" 
                                       min="1" max="365" required>
                                @error('plazo_dias')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="prioridad" class="form-label">{{ __('Prioridad') }}</label>
                                <select id="prioridad" class="form-select @error('prioridad') is-invalid @enderror" name="prioridad" required>
                                    <option value="baja" {{ old('prioridad', $expediente->prioridad) == 'baja' ? 'selected' : '' }}>Baja</option>
                                    <option value="media" {{ old('prioridad', $expediente->prioridad) == 'media' ? 'selected' : '' }}>Media</option>
                                    <option value="alta" {{ old('prioridad', $expediente->prioridad) == 'alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="urgente" {{ old('prioridad', $expediente->prioridad) == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                </select>
                                @error('prioridad')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="observaciones" class="form-label">{{ __('Observaciones') }}</label>
                            <textarea id="observaciones" class="form-control @error('observaciones') is-invalid @enderror" 
                                      name="observaciones" rows="3">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between pt-4 border-top">
                            <a href="{{ route('mesa-partes.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="fas fa-arrow-left me-2"></i>Volver a Expedientes
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                                <i class="fas fa-share me-2"></i>Derivar Expediente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Cargar funcionarios según área seleccionada
document.getElementById('id_area_destino').addEventListener('change', function() {
    const areaId = this.value;
    const funcionarioSelect = document.getElementById('id_funcionario_asignado');
    
    // Limpiar opciones
    funcionarioSelect.innerHTML = '<option value="">Cargando...</option>';
    
    if (areaId) {
        fetch(`/api/funcionarios/${areaId}`)
            .then(response => response.json())
            .then(data => {
                funcionarioSelect.innerHTML = '<option value="">Seleccione un funcionario (opcional)</option>';
                data.forEach(funcionario => {
                    funcionarioSelect.innerHTML += `<option value="${funcionario.id}">${funcionario.name}</option>`;
                });
            })
            .catch(error => {
                funcionarioSelect.innerHTML = '<option value="">Error al cargar funcionarios</option>';
            });
    } else {
        funcionarioSelect.innerHTML = '<option value="">Seleccione un funcionario (opcional)</option>';
    }
});
</script>
@endsection