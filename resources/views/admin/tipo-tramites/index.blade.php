@extends('layouts.app')

@section('title', 'Tipos de Trámite')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- Alertas --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Tipos de Trámite</h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTramite">
                        <i class="fas fa-plus"></i> Nuevo Tipo
                    </button>
                </div>
                <div class="card-body">
                    {{-- Filtros --}}
                    <form method="GET" action="{{ route('admin.tipo-tramites') }}" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Filtrar por Área</label>
                            <select class="form-select" name="id_area" onchange="this.form.submit()">
                                <option value="">-- Todas las áreas --</option>
                                @foreach($areas as $area)
                                <option value="{{ $area->id_area }}" {{ request('id_area') == $area->id_area ? 'selected' : '' }}>
                                    {{ $area->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Buscar por nombre</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre del trámite...">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            @if(request('id_area') || request('buscar'))
                            <a href="{{ route('admin.tipo-tramites') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Limpiar filtros
                            </a>
                            @endif
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Área Responsable</th>
                                    <th>Plazo (días)</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tipoTramites as $tipo)
                                <tr>
                                    <td>{{ $tipo->id_tipo_tramite }}</td>
                                    <td>{{ $tipo->nombre }}</td>
                                    <td>{{ Str::limit($tipo->descripcion, 50) }}</td>
                                    <td>
                                        <span class="badge bg-info text-dark">{{ $tipo->area->nombre ?? 'Sin asignar' }}</span>
                                    </td>
                                    <td>{{ $tipo->plazo_dias }} días</td>
                                    <td>
                                        <span class="badge bg-{{ $tipo->activo ? 'success' : 'danger' }}">
                                            {{ $tipo->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-warning" onclick="editarTipo({{ $tipo->id_tipo_tramite }})" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-{{ $tipo->activo ? 'secondary' : 'success' }}"
                                                    onclick="toggleTipo({{ $tipo->id_tipo_tramite }})" title="{{ $tipo->activo ? 'Desactivar' : 'Activar' }}">
                                                <i class="fas fa-{{ $tipo->activo ? 'ban' : 'check' }}"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="eliminarTipo({{ $tipo->id_tipo_tramite }}, '{{ addslashes($tipo->nombre) }}')" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        No se encontraron tipos de trámite
                                        @if(request('id_area') || request('buscar'))
                                            con los filtros aplicados.
                                            <br><a href="{{ route('admin.tipo-tramites') }}">Ver todos</a>
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            Mostrando {{ $tipoTramites->firstItem() ?? 0 }} - {{ $tipoTramites->lastItem() ?? 0 }} de {{ $tipoTramites->total() }} registros
                        </small>
                        {{ $tipoTramites->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo/Editar Tipo -->
<div class="modal fade" id="modalTramite" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formTramite" method="POST" action="{{ route('admin.tipo-tramites.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Tipo de Trámite</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Área Responsable</label>
                        <select class="form-select" name="id_area" required>
                            <option value="">Seleccionar...</option>
                            @foreach($areas as $area)
                            <option value="{{ $area->id_area }}">{{ $area->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Plazo (días)</label>
                        <input type="number" class="form-control" name="plazo_dias" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Requisitos</label>
                        <textarea class="form-control" name="requisitos" rows="4"
                                  placeholder="Lista de requisitos necesarios..."></textarea>
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

<!-- Form oculto para eliminar -->
<form id="formEliminar" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="_method" value="DELETE">
</form>

<script>
function editarTipo(id) {
    fetch(`/admin/tipo-tramites/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            const form = document.querySelector('#formTramite');
            form.querySelector('input[name="nombre"]').value = data.tipoTramite.nombre;
            form.querySelector('textarea[name="descripcion"]').value = data.tipoTramite.descripcion || '';
            form.querySelector('select[name="id_area"]').value = data.tipoTramite.id_area || '';
            form.querySelector('input[name="plazo_dias"]').value = data.tipoTramite.plazo_dias;
            form.querySelector('textarea[name="requisitos"]').value = data.tipoTramite.requisitos || '';

            form.action = `/admin/tipo-tramites/${id}`;
            if (!form.querySelector('input[name="_method"]')) {
                form.insertAdjacentHTML('beforeend', '<input type="hidden" name="_method" value="PUT">');
            }
            document.querySelector('.modal-title').textContent = 'Editar Tipo de Trámite';

            new bootstrap.Modal(document.getElementById('modalTramite')).show();
        });
}

function toggleTipo(id) {
    if (confirm('¿Está seguro de cambiar el estado de este tipo de trámite?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/tipo-tramites/${id}/toggle`;
        form.innerHTML = `
            @csrf
            <input type="hidden" name="_method" value="PUT">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function eliminarTipo(id, nombre) {
    if (confirm(`¿Está seguro de eliminar el tipo de trámite "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
        const form = document.getElementById('formEliminar');
        form.action = `/admin/tipo-tramites/${id}`;
        form.submit();
    }
}

// Limpiar modal al cerrarse
document.getElementById('modalTramite').addEventListener('hidden.bs.modal', function () {
    const form = document.querySelector('#formTramite');
    form.reset();
    form.action = '{{ route("admin.tipo-tramites.store") }}';
    document.querySelector('.modal-title').textContent = 'Nuevo Tipo de Trámite';
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) methodInput.remove();
});
</script>
@endsection
