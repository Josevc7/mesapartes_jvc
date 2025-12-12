@extends('layouts.app')

@section('title', 'Tipos de Trámite')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Tipos de Trámite</h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTramite">
                        <i class="fas fa-plus"></i> Nuevo Tipo
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Área Responsable</th>
                                    <th>Plazo (días)</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tipoTramites as $tipo)
                                <tr>
                                    <td>{{ $tipo->id_tipo_tramite }}</td>
                                    <td>{{ $tipo->nombre }}</td>
                                    <td>{{ $tipo->descripcion }}</td>
                                    <td>{{ $tipo->area->nombre ?? 'Sin asignar' }}</td>
                                    <td>{{ $tipo->plazo_dias }} días</td>
                                    <td>
                                        <span class="badge bg-{{ $tipo->activo ? 'success' : 'danger' }}">
                                            {{ $tipo->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editarTipo({{ $tipo->id_tipo_tramite }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-{{ $tipo->activo ? 'danger' : 'success' }}" 
                                                onclick="toggleTipo({{ $tipo->id_tipo_tramite }})">
                                            <i class="fas fa-{{ $tipo->activo ? 'ban' : 'check' }}"></i>
                                        </button>
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

<script>
function editarTipo(id) {
    fetch(`/admin/tipo-tramites/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            const form = document.querySelector('#formTramite');
            form.querySelector('input[name="nombre"]').value = data.tipoTramite.nombre;
            form.querySelector('textarea[name="descripcion"]').value = data.tipoTramite.descripcion || '';
            form.querySelector('select[name="id_area"]').value = data.tipoTramite.area_id || '';
            form.querySelector('input[name="plazo_dias"]').value = data.tipoTramite.plazo_dias;
            form.querySelector('textarea[name="requisitos"]').value = data.tipoTramite.requisitos || '';
            
            form.action = `/admin/tipo-tramites/${id}`;
            form.innerHTML += '<input type="hidden" name="_method" value="PUT">';
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

// Limpiar modal al cerrarse
document.getElementById('modalTramite').addEventListener('hidden.bs.modal', function () {
    const form = document.querySelector('#formTramite');
    form.reset();
    form.action = '{{ route('admin.tipo-tramites.store') }}';
    document.querySelector('.modal-title').textContent = 'Nuevo Tipo de Trámite';
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) methodInput.remove();
});
</script>
@endsection