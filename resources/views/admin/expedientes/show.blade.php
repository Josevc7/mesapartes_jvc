@extends('layouts.app')

@section('title', 'Expediente ' . $expediente->codigo_expediente)

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('admin.expedientes') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información del Expediente -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-folder-open"></i> {{ $expediente->codigo_expediente }}</h4>
                    @php
                        $coloresEstado = [
                            'recepcionado' => 'secondary', 'registrado' => 'info', 'clasificado' => 'primary',
                            'derivado' => 'warning', 'en_proceso' => 'warning', 'observado' => 'danger',
                            'resuelto' => 'success', 'notificado' => 'success', 'archivado' => 'dark'
                        ];
                    @endphp
                    <span class="badge bg-{{ $coloresEstado[$expediente->estado] ?? 'secondary' }} fs-6">
                        {{ ucfirst(str_replace('_', ' ', $expediente->estado)) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Asunto:</strong>
                            <p>{{ $expediente->asunto }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Tipo de Trámite:</strong>
                            <p>{{ $expediente->tipoTramite?->nombre ?? 'No asignado' }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Remitente:</strong>
                            <p>{{ $expediente->remitente ?? $expediente->persona?->nombre_completo ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>DNI/RUC:</strong>
                            <p>{{ $expediente->dni_remitente ?? $expediente->persona?->numero_documento ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Área Actual:</strong>
                            <p>{{ $expediente->area?->nombre ?? 'Sin asignar' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Funcionario Asignado:</strong>
                            <p>{{ $expediente->funcionarioAsignado?->name ?? 'Sin asignar' }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Canal:</strong>
                            <p>
                                @if($expediente->canal == 'virtual')
                                    <span class="badge bg-info">Virtual</span>
                                @elseif($expediente->canal == 'presencial')
                                    <span class="badge bg-success">Presencial</span>
                                @else
                                    <span class="badge bg-secondary">{{ $expediente->canal }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <strong>Prioridad:</strong>
                            <p>
                                @php
                                    $prioridadColor = ['baja' => 'secondary', 'normal' => 'info', 'alta' => 'warning', 'urgente' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $prioridadColor[$expediente->prioridad] ?? 'secondary' }}">
                                    {{ ucfirst($expediente->prioridad) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <strong>Fecha de Registro:</strong>
                            <p>{{ $expediente->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @if($expediente->descripcion)
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Descripción:</strong>
                            <p>{{ $expediente->descripcion }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Historial -->
            <div class="card mb-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Historial del Expediente</h5>
                </div>
                <div class="card-body">
                    <ul class="timeline">
                        @forelse($expediente->historial->sortByDesc('created_at') as $item)
                        <li class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <i class="fas fa-circle text-primary me-2" style="font-size: 8px;"></i>
                                    {{ $item->descripcion }}
                                </div>
                                <small class="text-muted">
                                    {{ $item->usuario?->name ?? 'Sistema' }} -
                                    {{ $item->created_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </li>
                        @empty
                        <li class="text-muted">Sin historial registrado</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Documentos -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-file-pdf"></i> Documentos Adjuntos</h5>
                </div>
                <div class="card-body">
                    @if($expediente->documentos->count() > 0)
                    <ul class="list-group">
                        @foreach($expediente->documentos as $doc)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                {{ $doc->nombre }}
                            </div>
                            <a href="{{ asset('storage/' . $doc->ruta_pdf) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-muted">No hay documentos adjuntos</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Acciones del Administrador -->
        <div class="col-md-4">
            <!-- Cambiar Estado -->
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> Cambiar Estado</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.expedientes.cambiar-estado', $expediente->id_expediente) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Nuevo Estado</label>
                            <select class="form-select" name="estado" required>
                                @foreach($estados as $estado)
                                <option value="{{ $estado->slug }}" {{ $expediente->estado == $estado->slug ? 'selected' : '' }}>
                                    {{ $estado->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observación</label>
                            <textarea class="form-control" name="observacion" rows="2" placeholder="Motivo del cambio..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-save"></i> Cambiar Estado
                        </button>
                    </form>
                </div>
            </div>

            <!-- Reasignar Expediente -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-user-edit"></i> Reasignar Expediente</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.expedientes.reasignar', $expediente->id_expediente) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Área</label>
                            <select class="form-select" name="id_area" id="selectArea" required>
                                <option value="">Seleccione área...</option>
                                @foreach($areas as $area)
                                <option value="{{ $area->id_area }}" {{ $expediente->id_area == $area->id_area ? 'selected' : '' }}>
                                    {{ $area->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Funcionario</label>
                            <select class="form-select" name="id_funcionario" id="selectFuncionario" required>
                                <option value="">Seleccione funcionario...</option>
                                @foreach($funcionarios as $func)
                                <option value="{{ $func->id }}" data-area="{{ $func->id_area }}" {{ $expediente->id_funcionario_asignado == $func->id ? 'selected' : '' }}>
                                    {{ $func->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motivo de Reasignación</label>
                            <textarea class="form-control" name="observacion" rows="2" placeholder="Motivo de la reasignación..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-exchange-alt"></i> Reasignar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Derivaciones -->
            @if($expediente->derivaciones->count() > 0)
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-share"></i> Derivaciones</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($expediente->derivaciones->sortByDesc('created_at')->take(5) as $derivacion)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>{{ $derivacion->areaDestino?->nombre ?? 'N/A' }}</span>
                                <small class="text-muted">{{ $derivacion->created_at->format('d/m/Y') }}</small>
                            </div>
                            <small class="text-muted">
                                Funcionario: {{ $derivacion->funcionarioDestino?->name ?? 'Sin asignar' }}
                            </small>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@section('scripts')
<script>
document.getElementById('selectArea').addEventListener('change', function() {
    const areaId = this.value;
    const selectFunc = document.getElementById('selectFuncionario');

    // Filtrar funcionarios por área
    Array.from(selectFunc.options).forEach(option => {
        if (option.value === '') return;
        if (option.dataset.area == areaId || !areaId) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });

    // Cargar funcionarios del área seleccionada via AJAX
    if (areaId) {
        fetch(`${window.APP_URL}/admin/api/funcionarios/${areaId}`)
            .then(response => response.json())
            .then(data => {
                selectFunc.innerHTML = '<option value="">Seleccione funcionario...</option>';
                data.funcionarios.forEach(func => {
                    selectFunc.innerHTML += `<option value="${func.id}">${func.name}</option>`;
                });
            });
    }
});
</script>
@endsection
@endsection
