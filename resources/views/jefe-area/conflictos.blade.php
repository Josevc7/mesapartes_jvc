@extends('layouts.app')

@section('title', 'Resolver Conflictos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Resolver Conflictos y Autorizaciones Especiales</h3>
                <span class="badge bg-warning">{{ $conflictos->count() }} Conflictos Pendientes</span>
            </div>
        </div>
    </div>

    <!-- Tipos de Conflictos -->
     <!--  <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['expedientes_vencidos'] }}</h4>
                            <p class="mb-0">Expedientes Vencidos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['reasignaciones_pendientes'] }}</h4>
                            <p class="mb-0">Reasignaciones</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exchange-alt fa-2x"></i>
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
                            <h4>{{ $estadisticas['autorizaciones_especiales'] }}</h4>
                            <p class="mb-0">Autorizaciones Especiales</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-key fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $estadisticas['observaciones_ciudadano'] }}</h4>
                            <p class="mb-0">Observaciones Ciudadano</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-comment-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>-->

    <!-- Lista de Conflictos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Conflictos y Situaciones Especiales</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Tipo Conflicto</th>
                                    <th>Asunto</th>
                                    <th>Funcionario</th>
                                    <th>Días Vencido</th>
                                    <th>Prioridad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($conflictos as $conflicto)
                                <tr class="table-{{ $conflicto->tipo_conflicto == 'vencido' ? 'danger' : ($conflicto->tipo_conflicto == 'autorizacion' ? 'warning' : 'info') }}">
                                    <td><strong>{{ $conflicto->codigo_expediente }}</strong></td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $conflicto->tipo_conflicto == 'vencido' ? 'danger' : 
                                            ($conflicto->tipo_conflicto == 'autorizacion' ? 'warning' : 'info') 
                                        }}">
                                            {{ ucfirst($conflicto->tipo_conflicto) }}
                                        </span>
                                    </td>
                                    <td>{{ Str::limit($conflicto->asunto, 40) }}</td>
                                    <td>{{ $conflicto->funcionarioAsignado->name ?? 'Sin asignar' }}</td>
                                    <td>
                                        @if($conflicto->dias_vencido > 0)
                                            <span class="text-danger fw-bold">{{ $conflicto->dias_vencido }} días</span>
                                        @else
                                            <span class="text-muted">En plazo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $conflicto->prioridad == 'Urgente' ? 'danger' : 
                                            ($conflicto->prioridad == 'Alta' ? 'warning' : 'secondary') 
                                        }}">
                                            {{ $conflicto->prioridad }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary"
                                                    onclick="verDetalleConflicto({{ $conflicto->id_expediente }})"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalConflicto">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            @if($conflicto->tipo_conflicto == 'vencido')
                                            <button class="btn btn-outline-warning"
                                                    onclick="extenderPlazo({{ $conflicto->id_expediente }})">
                                                <i class="fas fa-clock"></i> Extender
                                            </button>
                                            <button class="btn btn-outline-info"
                                                    onclick="reasignar({{ $conflicto->id_expediente }})">
                                                <i class="fas fa-exchange-alt"></i> Reasignar
                                            </button>
                                            @endif

                                            @if($conflicto->tipo_conflicto == 'autorizacion')
                                            <button class="btn btn-outline-success"
                                                    onclick="autorizarEspecial({{ $conflicto->id_expediente }})">
                                                <i class="fas fa-key"></i> Autorizar
                                            </button>
                                            @endif
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

<!-- Modal Detalle Conflicto -->
<div class="modal fade" id="modalConflicto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del Conflicto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoConflicto">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <div id="accionesConflicto"></div>
            </div>
        </div>
    </div>
</div>

<script>
function verDetalleConflicto(expedienteId) {
    fetch(`/jefe-area/conflictos/${expedienteId}/detalle`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('contenidoConflicto').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Información del Expediente</h6>
                        <p><strong>Código:</strong> ${data.codigo_expediente}</p>
                        <p><strong>Asunto:</strong> ${data.asunto}</p>
                        <p><strong>Funcionario:</strong> ${data.funcionario}</p>
                        <p><strong>Fecha Límite:</strong> ${data.fecha_limite}</p>
                        <p><strong>Días Vencido:</strong> <span class="text-danger">${data.dias_vencido}</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Historial Reciente</h6>
                        <div class="timeline-sm">
                            ${data.historial.map(h => `
                                <div class="timeline-item-sm">
                                    <small class="text-muted">${h.fecha}</small>
                                    <p class="mb-1">${h.descripcion}</p>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6>Motivo del Conflicto</h6>
                        <div class="alert alert-warning">
                            ${data.motivo_conflicto}
                        </div>
                    </div>
                </div>
            `;
            
            // Configurar acciones según tipo de conflicto
            let acciones = '';
            if (data.tipo_conflicto === 'vencido') {
                acciones = `
                    <button class="btn btn-warning" onclick="extenderPlazo(${expedienteId})">
                        <i class="fas fa-clock"></i> Extender Plazo
                    </button>
                    <button class="btn btn-info" onclick="reasignar(${expedienteId})">
                        <i class="fas fa-exchange-alt"></i> Reasignar
                    </button>
                `;
            } else if (data.tipo_conflicto === 'autorizacion') {
                acciones = `
                    <button class="btn btn-success" onclick="autorizarEspecial(${expedienteId})">
                        <i class="fas fa-key"></i> Autorizar
                    </button>
                `;
            }
            
            document.getElementById('accionesConflicto').innerHTML = acciones;
        });
}

function extenderPlazo(expedienteId) {
    const dias = prompt('¿Cuántos días adicionales desea otorgar?', '5');
    if (!dias) return;
    
    const motivo = prompt('Motivo de la extensión:');
    if (!motivo) return;
    
    fetch(`/jefe-area/conflictos/${expedienteId}/extender-plazo`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            dias_adicionales: parseInt(dias),
            motivo: motivo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Plazo extendido correctamente');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function reasignar(expedienteId) {
    // Implementar lógica de reasignación
    alert('Función de reasignación en desarrollo');
}

function autorizarEspecial(expedienteId) {
    const observaciones = prompt('Observaciones de la autorización especial:');
    if (!observaciones) return;
    
    fetch(`/jefe-area/conflictos/${expedienteId}/autorizar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            observaciones: observaciones
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Autorización especial otorgada');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}
</script>
@endsection