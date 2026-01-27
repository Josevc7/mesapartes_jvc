@extends('layouts.app')

@section('title', 'Gestión de Áreas')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Mensajes de éxito/error -->
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

            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-building me-2"></i>Gestión de Áreas
                    </h4>
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalArea">
                        <i class="fas fa-plus me-1"></i> Nueva Área
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 5%">ID</th>
                                    <th style="width: 20%">Nombre</th>
                                    <th style="width: 30%">Descripción</th>
                                    <th style="width: 20%">Jefe de Área</th>
                                    <th style="width: 10%">Estado</th>
                                    <th style="width: 15%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($areas as $area)
                                <tr>
                                    <td><strong>{{ $area->id_area }}</strong></td>
                                    <td>{{ $area->nombre }}</td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($area->descripcion, 50) ?? 'Sin descripción' }}</small>
                                    </td>
                                    <td>
                                        @if($area->jefe)
                                            <i class="fas fa-user-tie text-primary me-1"></i>{{ $area->jefe->name }}
                                        @else
                                            <span class="text-muted"><i class="fas fa-user-slash me-1"></i>Sin asignar</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $area->activo ? 'success' : 'danger' }}">
                                            {{ $area->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-warning" onclick="editarArea({{ $area->id_area }})" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-{{ $area->activo ? 'secondary' : 'success' }}"
                                                    onclick="toggleArea({{ $area->id_area }})"
                                                    title="{{ $area->activo ? 'Desactivar' : 'Activar' }}">
                                                <i class="fas fa-{{ $area->activo ? 'ban' : 'check' }}"></i>
                                            </button>
                                            <button class="btn btn-danger"
                                                    onclick="eliminarArea({{ $area->id_area }}, '{{ $area->nombre }}')"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No hay áreas registradas</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($areas->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            Mostrando {{ $areas->firstItem() }} - {{ $areas->lastItem() }} de {{ $areas->total() }} áreas
                        </small>
                        {{ $areas->links() }}
                    </div>
                    @endif
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
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-building me-2"></i>Nueva Área
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-tag text-primary me-1"></i>Nombre *
                        </label>
                        <input type="text" class="form-control" name="nombre" id="inputNombre" required
                               placeholder="Ej: Gerencia de Desarrollo Urbano">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-align-left text-primary me-1"></i>Descripción
                        </label>
                        <textarea class="form-control" name="descripcion" id="inputDescripcion" rows="3"
                                  placeholder="Descripción de las funciones del área..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user-tie text-primary me-1"></i>Jefe de Área
                        </label>
                        <select class="form-select" name="id_jefe" id="inputJefe">
                            <option value="">-- Sin asignar --</option>
                            @foreach($jefes as $jefe)
                            <option value="{{ $jefe->id }}">{{ $jefe->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Solo aparecen usuarios con rol "Jefe de Área"</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="formEliminar" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Formulario oculto para toggle -->
<form id="formToggle" method="POST" style="display: none;">
    @csrf
    @method('PUT')
</form>

@endsection

@section('scripts')
<script>
// Editar área
function editarArea(id) {
    fetch(`/admin/areas/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('inputNombre').value = data.area.nombre;
            document.getElementById('inputDescripcion').value = data.area.descripcion || '';
            document.getElementById('inputJefe').value = data.area.id_jefe || '';

            document.getElementById('formArea').action = `/admin/areas/${id}`;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Área';
            document.getElementById('btnGuardar').innerHTML = '<i class="fas fa-save me-1"></i>Actualizar';

            new bootstrap.Modal(document.getElementById('modalArea')).show();
        })
        .catch(error => {
            alert('Error al cargar los datos del área');
            console.error(error);
        });
}

// Toggle estado área
function toggleArea(id) {
    if (confirm('¿Está seguro de cambiar el estado de esta área?')) {
        const form = document.getElementById('formToggle');
        form.action = `/admin/areas/${id}/toggle`;
        form.submit();
    }
}

// Eliminar área
function eliminarArea(id, nombre) {
    if (confirm(`¿Está seguro de eliminar el área "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
        const form = document.getElementById('formEliminar');
        form.action = `/admin/areas/${id}`;
        form.submit();
    }
}

// Limpiar modal al cerrarse
document.getElementById('modalArea').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formArea').reset();
    document.getElementById('formArea').action = '{{ route('admin.areas.store') }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-building me-2"></i>Nueva Área';
    document.getElementById('btnGuardar').innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
});
</script>
@endsection
