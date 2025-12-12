@extends('layouts.app')

@section('title', 'Gestión de Áreas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Gestión de Áreas</h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalArea">
                        <i class="fas fa-plus"></i> Nueva Área
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
                                    <th>Jefe de Área</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($areas as $area)
                                <tr>
                                    <td>{{ $area->id_area }}</td>
                                    <td>{{ $area->nombre }}</td>
                                    <td>{{ $area->descripcion }}</td>
                                    <td>{{ $area->jefe->name ?? 'Sin asignar' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $area->activo ? 'success' : 'danger' }}">
                                            {{ $area->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editarArea({{ $area->id_area }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-{{ $area->activo ? 'danger' : 'success' }}" 
                                                onclick="toggleArea({{ $area->id_area }})">
                                            <i class="fas fa-{{ $area->activo ? 'ban' : 'check' }}"></i>
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

<!-- Modal Nueva/Editar Área -->
<div class="modal fade" id="modalArea" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formArea" method="POST" action="{{ route('admin.areas.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Área</h5>
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
                        <label class="form-label">Jefe de Área</label>
                        <select class="form-select" name="id_jefe">
                            <option value="">Seleccionar...</option>
                            @foreach($jefes as $jefe)
                            <option value="{{ $jefe->id }}">{{ $jefe->name }}</option>
                            @endforeach
                        </select>
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
function editarArea(id) {
    fetch(`/admin/areas/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            document.querySelector('#formArea input[name="nombre"]').value = data.area.nombre;
            document.querySelector('#formArea textarea[name="descripcion"]').value = data.area.descripcion || '';
            document.querySelector('#formArea select[name="id_jefe"]').value = data.area.id_jefe || '';
            
            document.querySelector('#formArea').action = `/admin/areas/${id}`;
            document.querySelector('#formArea').innerHTML += '<input type="hidden" name="_method" value="PUT">';
            document.querySelector('.modal-title').textContent = 'Editar Área';
            
            new bootstrap.Modal(document.getElementById('modalArea')).show();
        });
}

function toggleArea(id) {
    if (confirm('¿Está seguro de cambiar el estado de esta área?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/areas/${id}/toggle`;
        form.innerHTML = `
            @csrf
            <input type="hidden" name="_method" value="PUT">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Limpiar modal al cerrarse
document.getElementById('modalArea').addEventListener('hidden.bs.modal', function () {
    document.querySelector('#formArea').reset();
    document.querySelector('#formArea').action = '{{ route('admin.areas.store') }}';
    document.querySelector('.modal-title').textContent = 'Nueva Área';
    const methodInput = document.querySelector('#formArea input[name="_method"]');
    if (methodInput) methodInput.remove();
});
</script>
@endsection