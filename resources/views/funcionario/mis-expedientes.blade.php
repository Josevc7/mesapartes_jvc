@extends('layouts.app')

@section('title', 'Mis Expedientes Asignados')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Mis Expedientes Asignados</h3>
                        <a href="{{ route('funcionario.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                        </a>
                    </div>
                    <div class="card-tools mt-3">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary" onclick="filtrarEstado('todos')">
                                Todos ({{ $expedientes->count() }})
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="filtrarEstado('pendiente')">
                                Pendientes ({{ $expedientes->where('estado', 'derivado')->count() }})
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="filtrarEstado('proceso')">
                                En Proceso ({{ $expedientes->where('estado', 'en_proceso')->count() }})
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="filtrarEstado('vencido')">
                                Vencidos ({{ $expedientes->where('dias_restantes', '<', 0)->count() }})
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="tablaExpedientes">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Asunto</th>
                                    <th>Tipo Trámite</th>
                                    <th>Ciudadano</th>
                                    <th>Estado</th>
                                    <th>Prioridad</th>
                                    <th>Días Restantes</th>
                                    <th>Fecha Asignación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expedientes as $expediente)
                                <tr data-estado="{{ strtolower($expediente->estado) }}" 
                                    data-vencido="{{ $expediente->dias_restantes < 0 ? 'true' : 'false' }}">
                                    <td>
                                        <strong>{{ $expediente->codigo_expediente }}</strong>
                                    </td>
                                    <td>{{ $expediente->asunto }}</td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $expediente->tipoTramite->nombre }}
                                        </span>
                                    </td>
                                    <td>{{ $expediente->remitente ?? ($expediente->ciudadano->name ?? 'N/A') }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $expediente->estado == 'derivado' ? 'warning' : 
                                            ($expediente->estado == 'en_proceso' ? 'info' : 
                                            ($expediente->estado == 'resuelto' ? 'success' : 'secondary')) 
                                        }}">
                                            {{ ucfirst($expediente->estado) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $expediente->prioridad == 'alta' ? 'danger' : 
                                            ($expediente->prioridad == 'normal' ? 'warning' : 'success') 
                                        }}">
                                            {{ ucfirst($expediente->prioridad) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($expediente->dias_restantes < 0)
                                            <span class="text-danger fw-bold">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                Vencido ({{ abs($expediente->dias_restantes) }} días)
                                            </span>
                                        @elseif($expediente->dias_restantes <= 2)
                                            <span class="text-warning fw-bold">
                                                <i class="fas fa-clock"></i>
                                                {{ $expediente->dias_restantes }} días
                                            </span>
                                        @else
                                            <span class="text-success">
                                                {{ $expediente->dias_restantes }} días
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $expediente->fecha_derivacion ? $expediente->fecha_derivacion->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('funcionario.show', $expediente->id_expediente) }}" 
                                               class="btn btn-outline-primary" title="Ver Detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($expediente->estado == 'derivado')
                                            <button class="btn btn-outline-success" 
                                                    onclick="recibirExpediente({{ $expediente->id_expediente }})" 
                                                    title="Recibir">
                                                <i class="fas fa-hand-paper"></i>
                                            </button>
                                            @endif
                                            
                                            @if($expediente->estado == 'en_proceso')
                                            <a href="{{ route('funcionario.procesar', $expediente->id_expediente) }}" 
                                               class="btn btn-outline-info" title="Procesar">
                                                <i class="fas fa-cogs"></i>
                                            </a>
                                            <a href="{{ route('funcionario.derivar-form', $expediente->id_expediente) }}" 
                                               class="btn btn-outline-warning" title="Derivar">
                                                <i class="fas fa-share"></i>
                                            </a>
                                            @endif
                                            
                                            <a href="{{ route('funcionario.historial', $expediente->id_expediente) }}" 
                                               class="btn btn-outline-secondary" title="Historial">
                                                <i class="fas fa-history"></i>
                                            </a>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function filtrarEstado(estado) {
    const filas = document.querySelectorAll('#tablaExpedientes tbody tr');
    
    filas.forEach(fila => {
        const estadoFila = fila.dataset.estado;
        const esVencido = fila.dataset.vencido === 'true';
        
        let mostrar = false;
        
        switch(estado) {
            case 'todos':
                mostrar = true;
                break;
            case 'pendiente':
                mostrar = estadoFila === 'derivado';
                break;
            case 'proceso':
                mostrar = estadoFila === 'en_proceso';
                break;
            case 'vencido':
                mostrar = esVencido;
                break;
        }
        
        fila.style.display = mostrar ? '' : 'none';
    });
    
    // Actualizar botones activos
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

function recibirExpediente(id) {
    Swal.fire({
        title: '¿Recibir Expediente?',
        text: '¿Confirmas que recibes este expediente para su procesamiento?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check"></i> Sí, recibir',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Procesando...',
                text: 'Recibiendo expediente',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading()
                }
            });
            
            fetch(`/funcionario/expedientes/${id}/recibir`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            }).then(response => {
                if (response.ok) {
                    Swal.fire({
                        title: '¡Expediente Recibido!',
                        text: 'El expediente ha sido asignado correctamente',
                        icon: 'success',
                        confirmButtonColor: '#28a745',
                        confirmButtonText: 'Continuar'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo recibir el expediente. Intenta nuevamente.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }).catch(error => {
                Swal.fire({
                    title: 'Error de Conexión',
                    text: 'Verifica tu conexión a internet e intenta nuevamente.',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}
</script>
@endsection