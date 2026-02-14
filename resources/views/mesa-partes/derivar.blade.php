@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2">
                    <h5 class="mb-0"><i class="fas fa-share text-primary me-1"></i>Derivar Expediente - {{ $expediente->codigo_expediente }}</h5>
                </div>

                <div class="card-body py-2">
                    <!-- Resumen del Expediente -->
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <div class="bg-light rounded p-2" style="font-size: 0.82rem;">
                                <div class="row">
                                    <div class="col-4 text-muted">Codigo:</div>
                                    <div class="col-8"><strong class="text-primary">{{ $expediente->codigo_expediente }}</strong></div>
                                    <div class="col-4 text-muted">Remitente:</div>
                                    <div class="col-8">{{ Str::limit($expediente->remitente ?? ($expediente->ciudadano->name ?? 'N/A'), 30) }}</div>
                                    <div class="col-4 text-muted">DNI:</div>
                                    <div class="col-8">{{ $expediente->dni_remitente ?? ($expediente->ciudadano->dni ?? 'N/A') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light rounded p-2" style="font-size: 0.82rem;">
                                <div class="row">
                                    <div class="col-4 text-muted">Tipo:</div>
                                    <div class="col-8">{{ $expediente->tipoTramite->nombre ?? 'Sin clasificar' }}</div>
                                    <div class="col-4 text-muted">Asunto:</div>
                                    <div class="col-8">{{ Str::limit($expediente->asunto, 40) }}</div>
                                    <div class="col-4 text-muted">Estado:</div>
                                    <div class="col-8">
                                        <span class="badge bg-{{ $expediente->estado == 'pendiente' ? 'warning' : 'info' }}">
                                            {{ ucfirst($expediente->estado) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de Derivacion -->
                    <form method="POST" action="{{ route('mesa-partes.store-derivar', $expediente) }}">
                        @csrf

                        <h6 class="text-muted mb-2" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.3rem;">
                            <i class="fas fa-paper-plane me-1"></i>Datos de Derivacion
                        </h6>

                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label for="id_area_destino" class="form-label" style="font-size: 0.82rem;">Area Destino *</label>
                                <select id="id_area_destino" class="form-select form-select-sm @error('id_area_destino') is-invalid @enderror"
                                        name="id_area_destino" required>
                                    <option value="">Seleccione un area</option>
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
                                <label for="id_funcionario_asignado" class="form-label" style="font-size: 0.82rem;">Funcionario Asignado</label>
                                <select id="id_funcionario_asignado" class="form-select form-select-sm @error('id_funcionario_asignado') is-invalid @enderror"
                                        name="id_funcionario_asignado">
                                    <option value="">Seleccione (opcional)</option>
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

                        <div class="row g-3 mb-2">
                            <div class="col-md-3">
                                <label for="plazo_dias" class="form-label" style="font-size: 0.82rem;">Plazo (dias) *</label>
                                <input id="plazo_dias" type="number" class="form-control form-control-sm @error('plazo_dias') is-invalid @enderror"
                                       name="plazo_dias" value="{{ old('plazo_dias', $expediente->tipoTramite->plazo_dias) }}"
                                       min="1" max="365" required>
                                @error('plazo_dias')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="prioridad" class="form-label" style="font-size: 0.82rem;">Prioridad *</label>
                                <select id="prioridad" class="form-select form-select-sm @error('prioridad') is-invalid @enderror" name="prioridad" required>
                                    <option value="baja" {{ old('prioridad', $expediente->prioridad) == 'baja' ? 'selected' : '' }}>Baja</option>
                                    <option value="normal" {{ old('prioridad', $expediente->prioridad) == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="alta" {{ old('prioridad', $expediente->prioridad) == 'alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="urgente" {{ old('prioridad', $expediente->prioridad) == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                </select>
                                @error('prioridad')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="observaciones" class="form-label" style="font-size: 0.82rem;">Observaciones</label>
                                <input id="observaciones" type="text" class="form-control form-control-sm @error('observaciones') is-invalid @enderror"
                                       name="observaciones" value="{{ old('observaciones') }}" placeholder="Observaciones opcionales...">
                                @error('observaciones')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between pt-2 border-top">
                            <a href="{{ route('mesa-partes.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Volver
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                <i class="fas fa-share me-1"></i>Derivar Expediente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('id_area_destino').addEventListener('change', function() {
    const areaId = this.value;
    const funcionarioSelect = document.getElementById('id_funcionario_asignado');

    funcionarioSelect.innerHTML = '<option value="">Cargando...</option>';

    if (areaId) {
        fetch(`${window.APP_URL}/api/funcionarios/${areaId}`)
            .then(response => response.json())
            .then(data => {
                funcionarioSelect.innerHTML = '<option value="">Seleccione (opcional)</option>';
                data.forEach(funcionario => {
                    funcionarioSelect.innerHTML += `<option value="${funcionario.id}">${funcionario.name}</option>`;
                });
            })
            .catch(error => {
                funcionarioSelect.innerHTML = '<option value="">Error al cargar</option>';
            });
    } else {
        funcionarioSelect.innerHTML = '<option value="">Seleccione (opcional)</option>';
    }
});
</script>
@endsection
