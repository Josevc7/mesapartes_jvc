@extends('layouts.app')

@section('title', 'Derivar Expediente')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Derivar Expediente: {{ $expediente->codigo_expediente }}</h4>
                </div>
                <div class="card-body">
                    <!-- Información del Expediente -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Información del Expediente</h6>
                        <p class="mb-1"><strong>Asunto:</strong> {{ $expediente->asunto }}</p>
                        <p class="mb-1"><strong>Remitente:</strong> {{ $expediente->remitente ?? ($expediente->ciudadano->name ?? 'N/A') }}</p>
                        <p class="mb-0"><strong>Área Actual:</strong> {{ auth()->user()->area->nombre }}</p>
                    </div>
                    
                    <form method="POST" action="{{ route('funcionario.derivar', $expediente) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="id_area_destino" class="form-label">Área de Destino *</label>
                            <select class="form-select @error('id_area_destino') is-invalid @enderror"
                                    id="id_area_destino" name="id_area_destino" required onchange="cargarFuncionarios()">
                                <option value="">Seleccionar área</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id_area }}" {{ old('id_area_destino') == $area->id_area ? 'selected' : '' }}>
                                        {{ $area->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_area_destino')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="id_funcionario_destino" class="form-label">Funcionario Específico (opcional)</label>
                            <select class="form-select @error('id_funcionario_destino') is-invalid @enderror" 
                                    id="id_funcionario_destino" name="id_funcionario_destino">
                                <option value="">Asignación automática por el Jefe de Área</option>
                            </select>
                            <div class="form-text">Si no selecciona un funcionario específico, el Jefe de Área asignará el expediente</div>
                            @error('id_funcionario_destino')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="plazo_dias" class="form-label">Plazo de Atención (días) *</label>
                            <input type="number" class="form-control @error('plazo_dias') is-invalid @enderror" 
                                   id="plazo_dias" name="plazo_dias" min="1" max="30" 
                                   value="{{ old('plazo_dias', 15) }}" required>
                            <div class="form-text">Días hábiles para la atención del expediente</div>
                            @error('plazo_dias')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Motivo de la Derivación *</label>
                            <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                      id="observaciones" name="observaciones" rows="4" required 
                                      placeholder="Explique el motivo por el cual deriva este expediente...">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Importante</h6>
                            <ul class="mb-0">
                                <li>Al derivar el expediente, perderá el acceso al mismo</li>
                                <li>El expediente será asignado al área seleccionada</li>
                                <li>Se registrará esta acción en el historial del expediente</li>
                                <li>El plazo comenzará a contar desde la fecha de derivación</li>
                            </ul>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between pt-4 border-top">
                            <a href="{{ route('funcionario.show', $expediente) }}" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="fas fa-arrow-left me-2"></i>Volver al Expediente
                            </a>
                            <button type="submit" class="btn btn-warning btn-lg px-5 shadow-sm">
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
async function cargarFuncionarios() {
    const areaId = document.getElementById('id_area_destino').value;
    const funcionarioSelect = document.getElementById('id_funcionario_destino');
    
    // Limpiar opciones
    funcionarioSelect.innerHTML = '<option value="">Asignación automática por el Jefe de Área</option>';
    
    if (areaId) {
        try {
            const response = await fetch(`${window.APP_URL}/api/funcionarios/${areaId}`);
            const funcionarios = await response.json();
            
            funcionarios.forEach(funcionario => {
                const option = document.createElement('option');
                option.value = funcionario.id;
                option.textContent = funcionario.name;
                funcionarioSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error al cargar funcionarios:', error);
        }
    }
}
</script>
@endsection