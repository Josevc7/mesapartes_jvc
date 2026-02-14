@extends('layouts.app')

@section('title', 'Supervisión Mesa de Partes Virtual')

@section('content')
<div class="container-fluid">
    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ $estadisticas['total'] }}</h3>
                    <small>Total Virtual</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3>{{ $estadisticas['pendientes'] }}</h3>
                    <small>Pendientes</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3>{{ $estadisticas['clasificados'] }}</h3>
                    <small>Clasificados</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h3>{{ $estadisticas['en_proceso'] }}</h3>
                    <small>En Proceso</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $estadisticas['resueltos'] }}</h3>
                    <small>Resueltos</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-desktop"></i> Expedientes de Mesa de Partes Virtual</h4>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-2">
                    <select class="form-select" name="estado">
                        <option value="">Todos los estados</option>
                        <option value="recepcionado" {{ request('estado') == 'recepcionado' ? 'selected' : '' }}>Recepcionado</option>
                        <option value="registrado" {{ request('estado') == 'registrado' ? 'selected' : '' }}>Registrado</option>
                        <option value="clasificado" {{ request('estado') == 'clasificado' ? 'selected' : '' }}>Clasificado</option>
                        <option value="derivado" {{ request('estado') == 'derivado' ? 'selected' : '' }}>Derivado</option>
                        <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                        <option value="observado" {{ request('estado') == 'observado' ? 'selected' : '' }}>Observado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="fecha_desde" value="{{ request('fecha_desde') }}" placeholder="Fecha desde">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="fecha_hasta" value="{{ request('fecha_hasta') }}" placeholder="Fecha hasta">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.mesa-virtual') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-undo"></i> Limpiar
                    </a>
                </div>
            </form>

            <!-- Tabla -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Ciudadano</th>
                            <th>Tipo de Trámite</th>
                            <th>Asunto</th>
                            <th>Documentos</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expedientesVirtuales as $expediente)
                        <tr>
                            <td>
                                <strong>{{ $expediente->codigo_expediente }}</strong>
                            </td>
                            <td>
                                {{ $expediente->persona?->nombre_completo ?? $expediente->remitente ?? 'N/A' }}
                                <br><small class="text-muted">{{ $expediente->persona?->numero_documento ?? $expediente->dni_remitente ?? '' }}</small>
                            </td>
                            <td>{{ $expediente->tipoTramite?->nombre ?? 'Sin asignar' }}</td>
                            <td>{{ Str::limit($expediente->asunto, 30) }}</td>
                            <td>
                                <span class="badge bg-{{ $expediente->documentos->count() > 0 ? 'success' : 'warning' }}">
                                    {{ $expediente->documentos->count() }} archivo(s)
                                </span>
                            </td>
                            <td>
                                @php
                                    $coloresEstado = [
                                        'recepcionado' => 'secondary', 'registrado' => 'info', 'clasificado' => 'primary',
                                        'derivado' => 'warning', 'en_proceso' => 'warning', 'observado' => 'danger',
                                        'resuelto' => 'success', 'archivado' => 'dark'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $coloresEstado[$expediente->estado] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $expediente->estado)) }}
                                </span>
                            </td>
                            <td>{{ $expediente->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.expedientes.show', $expediente->id_expediente) }}" class="btn btn-sm btn-primary" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(in_array($expediente->estado, ['recepcionado', 'registrado']))
                                    <button class="btn btn-sm btn-success" onclick="validarExpediente({{ $expediente->id_expediente }}, 'validar')" title="Validar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="validarExpediente({{ $expediente->id_expediente }}, 'observar')" title="Observar">
                                        <i class="fas fa-exclamation"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="validarExpediente({{ $expediente->id_expediente }}, 'rechazar')" title="Rechazar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No hay expedientes virtuales con los filtros seleccionados</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $expedientesVirtuales->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- Modal de Validación -->
<div class="modal fade" id="modalValidar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalValidarTitle">Validar Expediente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formValidar" method="POST">
                @csrf
                <input type="hidden" name="accion" id="accionInput">
                <div class="modal-body">
                    <div id="alertaValidar" class="alert d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">Observación</label>
                        <textarea class="form-control" name="observacion" id="observacionInput" rows="3" placeholder="Ingrese una observación o motivo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnConfirmar">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
function validarExpediente(expedienteId, accion) {
    const form = document.getElementById('formValidar');
    const accionInput = document.getElementById('accionInput');
    const observacionInput = document.getElementById('observacionInput');
    const btnConfirmar = document.getElementById('btnConfirmar');
    const modalTitle = document.getElementById('modalValidarTitle');
    const alerta = document.getElementById('alertaValidar');

    form.action = `${window.APP_URL}/admin/mesa-virtual/${expedienteId}/validar`;
    accionInput.value = accion;
    observacionInput.value = '';
    alerta.classList.add('d-none');

    switch(accion) {
        case 'validar':
            modalTitle.textContent = 'Validar Expediente';
            btnConfirmar.className = 'btn btn-success';
            btnConfirmar.textContent = 'Validar y Clasificar';
            observacionInput.required = false;
            break;
        case 'observar':
            modalTitle.textContent = 'Observar Expediente';
            btnConfirmar.className = 'btn btn-warning';
            btnConfirmar.textContent = 'Marcar Observación';
            observacionInput.required = true;
            alerta.className = 'alert alert-warning';
            alerta.textContent = 'El ciudadano será notificado de las observaciones para que pueda subsanarlas.';
            alerta.classList.remove('d-none');
            break;
        case 'rechazar':
            modalTitle.textContent = 'Rechazar Expediente';
            btnConfirmar.className = 'btn btn-danger';
            btnConfirmar.textContent = 'Rechazar Expediente';
            observacionInput.required = true;
            alerta.className = 'alert alert-danger';
            alerta.textContent = 'El expediente será archivado y el ciudadano será notificado del rechazo.';
            alerta.classList.remove('d-none');
            break;
    }

    new bootstrap.Modal(document.getElementById('modalValidar')).show();
}
</script>
@endsection
@endsection
