@extends('layouts.app')

@section('title', 'Configuración de Estados')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-exchange-alt"></i> Estados del Expediente</h4>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalEstado">
                        <i class="fas fa-plus"></i> Nuevo Estado
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Orden</th>
                                    <th>Estado</th>
                                    <th>Slug</th>
                                    <th>Color</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($estados as $estado)
                                <tr>
                                    <td>{{ $estado->orden }}</td>
                                    <td>
                                        <i class="{{ $estado->icono ?? 'fas fa-circle' }}" style="color: {{ $estado->color }}"></i>
                                        <strong>{{ $estado->nombre }}</strong>
                                        <br><small class="text-muted">{{ $estado->descripcion }}</small>
                                    </td>
                                    <td><code>{{ $estado->slug }}</code></td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $estado->color }}">
                                            {{ $estado->color }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($estado->es_inicial)
                                            <span class="badge bg-success">Inicial</span>
                                        @endif
                                        @if($estado->es_final)
                                            <span class="badge bg-dark">Final</span>
                                        @endif
                                        @if($estado->requiere_accion)
                                            <span class="badge bg-warning text-dark">Requiere Acción</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($estado->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editarEstado({{ json_encode($estado) }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.estados.toggle', $estado->id_estado) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm {{ $estado->activo ? 'btn-secondary' : 'btn-success' }}">
                                                <i class="fas {{ $estado->activo ? 'fa-ban' : 'fa-check' }}"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No hay estados configurados</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-project-diagram"></i> Transiciones de Estado</h5>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalTransicion">
                        <i class="fas fa-plus"></i> Nueva Transición
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Estado Origen</th>
                                    <th></th>
                                    <th>Estado Destino</th>
                                    <th>Acción</th>
                                    <th>Roles Permitidos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transiciones as $transicion)
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: {{ $transicion->estadoOrigen->color }}">
                                            {{ $transicion->estadoOrigen->nombre }}
                                        </span>
                                    </td>
                                    <td class="text-center"><i class="fas fa-arrow-right"></i></td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $transicion->estadoDestino->color }}">
                                            {{ $transicion->estadoDestino->nombre }}
                                        </span>
                                    </td>
                                    <td>{{ $transicion->nombre_accion ?? '-' }}</td>
                                    <td>
                                        @if($transicion->roles_permitidos)
                                            @foreach($transicion->roles_permitidos as $rolId)
                                                @php $rolNombre = $roles->firstWhere('id_rol', $rolId)?->nombre; @endphp
                                                @if($rolNombre)
                                                    <span class="badge bg-info">{{ $rolNombre }}</span>
                                                @endif
                                            @endforeach
                                        @else
                                            <span class="text-muted">Todos los roles</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.transiciones.destroy', $transicion->id_transicion) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No hay transiciones configuradas</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Estado -->
<div class="modal fade" id="modalEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEstadoTitle">Nuevo Estado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEstado" method="POST" action="{{ route('admin.estados.store') }}">
                @csrf
                <div id="methodField"></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre" id="estadoNombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug *</label>
                        <input type="text" class="form-control" name="slug" id="estadoSlug" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" id="estadoDescripcion" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">Color *</label>
                            <input type="color" class="form-control form-control-color" name="color" id="estadoColor" value="#6c757d" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Orden *</label>
                            <input type="number" class="form-control" name="orden" id="estadoOrden" value="0" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icono (FontAwesome)</label>
                        <input type="text" class="form-control" name="icono" id="estadoIcono" placeholder="fas fa-circle">
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="es_inicial" id="estadoEsInicial" value="1">
                                <label class="form-check-label" for="estadoEsInicial">Es Inicial</label>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="es_final" id="estadoEsFinal" value="1">
                                <label class="form-check-label" for="estadoEsFinal">Es Final</label>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="requiere_accion" id="estadoRequiereAccion" value="1" checked>
                                <label class="form-check-label" for="estadoRequiereAccion">Requiere Acción</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Transición -->
<div class="modal fade" id="modalTransicion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Transición de Estado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.transiciones.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Estado Origen *</label>
                        <select class="form-select" name="id_estado_origen" required>
                            <option value="">Seleccione...</option>
                            @foreach($estados as $estado)
                            <option value="{{ $estado->id_estado }}">{{ $estado->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado Destino *</label>
                        <select class="form-select" name="id_estado_destino" required>
                            <option value="">Seleccione...</option>
                            @foreach($estados as $estado)
                            <option value="{{ $estado->id_estado }}">{{ $estado->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Acción</label>
                        <input type="text" class="form-control" name="nombre_accion" placeholder="Ej: Clasificar, Derivar, Resolver">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Roles Permitidos</label>
                        <div class="row">
                            @foreach($roles as $rol)
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles_permitidos[]" value="{{ $rol->id_rol }}" id="rolTrans{{ $rol->id_rol }}">
                                    <label class="form-check-label" for="rolTrans{{ $rol->id_rol }}">{{ $rol->nombre }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Deje vacío para permitir a todos los roles</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Transición</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
function editarEstado(estado) {
    document.getElementById('modalEstadoTitle').textContent = 'Editar Estado';
    document.getElementById('formEstado').action = window.APP_URL + '/admin/estados/' + estado.id_estado;
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';

    document.getElementById('estadoNombre').value = estado.nombre;
    document.getElementById('estadoSlug').value = estado.slug;
    document.getElementById('estadoDescripcion').value = estado.descripcion || '';
    document.getElementById('estadoColor').value = estado.color;
    document.getElementById('estadoOrden').value = estado.orden;
    document.getElementById('estadoIcono').value = estado.icono || '';
    document.getElementById('estadoEsInicial').checked = estado.es_inicial;
    document.getElementById('estadoEsFinal').checked = estado.es_final;
    document.getElementById('estadoRequiereAccion').checked = estado.requiere_accion;

    new bootstrap.Modal(document.getElementById('modalEstado')).show();
}

document.getElementById('modalEstado').addEventListener('hidden.bs.modal', function() {
    document.getElementById('modalEstadoTitle').textContent = 'Nuevo Estado';
    document.getElementById('formEstado').action = '{{ route("admin.estados.store") }}';
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('formEstado').reset();
});

// Auto-generar slug
document.getElementById('estadoNombre').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .replace(/[áàäâ]/g, 'a').replace(/[éèëê]/g, 'e').replace(/[íìïî]/g, 'i')
        .replace(/[óòöô]/g, 'o').replace(/[úùüû]/g, 'u').replace(/ñ/g, 'n')
        .replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
    document.getElementById('estadoSlug').value = slug;
});
</script>
@endsection
@endsection
