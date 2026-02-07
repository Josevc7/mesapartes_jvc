@extends('layouts.app')

@section('title', 'Validar Documentos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Validar Documentos</h3>
                <span class="badge bg-info">Área: {{ auth()->user()->area->nombre ?? 'Todas las áreas' }}</span>
            </div>
        </div>
    </div>

    <!-- Resumen de Validaciones -->
    <!--<div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['pendientes_validacion'] }}</h4>
                            <p class="mb-0">Pendientes Validación</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['validados_hoy'] }}</h4>
                            <p class="mb-0">Validados Hoy</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['rechazados_hoy'] }}</h4>
                            <p class="mb-0">Rechazados Hoy</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['requieren_autorizacion'] }}</h4>
                            <p class="mb-0">Requieren Autorización</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-key fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>-->

    <!-- Expedientes Pendientes de Validación -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Expedientes Pendientes de Validación</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Asunto</th>
                                    <th>Funcionario</th>
                                    <th>Fecha Resolución</th>
                                    <th>Tipo Validación</th>
                                    <th>Prioridad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expedientesPendientes as $expediente)
                                <tr>
                                    <td><strong>{{ $expediente->codigo_expediente }}</strong></td>
                                    <td>{{ Str::limit($expediente->asunto, 40) }}</td>
                                    <td>{{ $expediente->funcionarioAsignado->name ?? 'N/A' }}</td>
                                    <td>{{ $expediente->fecha_resolucion ? $expediente->fecha_resolucion->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $expediente->requiere_autorizacion_especial ? 'warning' : 'info' }}">
                                            {{ $expediente->requiere_autorizacion_especial ? 'Autorización Especial' : 'Validación Normal' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $expediente->prioridad == 'Urgente' ? 'danger' : 
                                            ($expediente->prioridad == 'Alta' ? 'warning' : 'secondary') 
                                        }}">
                                            {{ $expediente->prioridad }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary"
                                                    onclick="verDetalleValidacion({{ $expediente->id_expediente }})"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalValidacion">
                                                <i class="fas fa-eye"></i> Revisar
                                            </button>
                                            <button class="btn btn-outline-success"
                                                    onclick="validarExpediente({{ $expediente->id_expediente }}, 'aprobar')">
                                                <i class="fas fa-check"></i> Validar
                                            </button>
                                            <button class="btn btn-outline-danger"
                                                    onclick="validarExpediente({{ $expediente->id_expediente }}, 'rechazar')">
                                                <i class="fas fa-times"></i> Rechazar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Validación -->
<div class="modal fade" id="modalValidacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Validar Expediente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoValidacion">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="btnValidar">
                    <i class="fas fa-check"></i> Validar
                </button>
                <button type="button" class="btn btn-danger" id="btnRechazar">
                    <i class="fas fa-times"></i> Rechazar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function verDetalleValidacion(expedienteId) {
    document.getElementById('contenidoValidacion').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>`;

    fetch(`/jefe-area/expedientes/${expedienteId}/detalle-validacion`)
        .then(response => {
            if (!response.ok) throw new Error('Error al cargar los datos');
            return response.json();
        })
        .then(data => {
            const docs = Array.isArray(data.documentos) ? data.documentos : Object.values(data.documentos || {});
            const docsHtml = docs.length > 0
                ? docs.map(doc => `
                    <div class="list-group-item d-flex justify-content-between">
                        <span>${doc.nombre}</span>
                        <a href="${doc.url}" target="_blank" class="btn btn-sm btn-outline-primary">Ver</a>
                    </div>`).join('')
                : '<p class="text-muted">Sin documentos adjuntos</p>';

            document.getElementById('contenidoValidacion').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Información del Expediente</h6>
                        <p><strong>Código:</strong> ${data.codigo_expediente}</p>
                        <p><strong>Asunto:</strong> ${data.asunto}</p>
                        <p><strong>Funcionario:</strong> ${data.funcionario}</p>
                        <p><strong>Fecha Resolución:</strong> ${data.fecha_resolucion}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Documentos de Resolución</h6>
                        <div class="list-group">${docsHtml}</div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6>Observaciones del Funcionario</h6>
                        <p>${data.observaciones_funcionario || 'Sin observaciones'}</p>
                    </div>
                </div>
            `;

            document.getElementById('btnValidar').onclick = () => validarExpediente(expedienteId, 'aprobar');
            document.getElementById('btnRechazar').onclick = () => validarExpediente(expedienteId, 'rechazar');
        })
        .catch(error => {
            document.getElementById('contenidoValidacion').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    No se pudo cargar el detalle del expediente. Intente nuevamente.
                </div>`;
        });
}

function validarExpediente(expedienteId, accion) {
    let observaciones = '';
    if (accion === 'rechazar') {
        observaciones = prompt('Motivo del rechazo:');
        if (!observaciones) return;
    }
    
    fetch(`/jefe-area/expedientes/${expedienteId}/validar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            accion: accion,
            observaciones: observaciones
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}
</script>
@endsection