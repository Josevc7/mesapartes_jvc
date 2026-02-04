@extends('layouts.app')

@section('title', 'Gestión de Áreas - Organigrama')

@push('styles')
<style>
    .area-tree {
        padding-left: 0;
        list-style: none;
    }
    .area-tree .area-tree {
        padding-left: 2rem;
        border-left: 2px solid #dee2e6;
        margin-left: 1rem;
    }
    .area-item {
        padding: 0.75rem 1rem;
        margin: 0.5rem 0;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        transition: all 0.2s;
    }
    .area-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #0d6efd;
    }
    .area-item.nivel-direccion-regional {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        color: white;
        border: none;
    }
    .area-item.nivel-oci {
        background: linear-gradient(135deg, #6b21a8 0%, #9333ea 100%);
        color: white;
        border: none;
    }
    .area-item.nivel-direccion {
        background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);
        color: white;
        border: none;
    }
    .area-item.nivel-subdireccion {
        background: #f8f9fa;
        border-left: 4px solid #0ea5e9;
    }
    .area-item.nivel-residencia {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
    }
    .area-item.inactiva {
        opacity: 0.6;
        background: #f8d7da !important;
    }
    .nivel-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        border-radius: 0.25rem;
    }
    .toggle-subareas {
        cursor: pointer;
        transition: transform 0.2s;
    }
    .toggle-subareas.collapsed {
        transform: rotate(-90deg);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
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
                        <i class="fas fa-sitemap me-2"></i>Organigrama - Gestión de Áreas
                    </h4>
                    <div>
                        <button type="button" class="btn btn-success me-2" onclick="ejecutarSeeder()">
                            <i class="fas fa-sync me-1"></i> Cargar Organigrama DRTC
                        </button>
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalArea">
                            <i class="fas fa-plus me-1"></i> Nueva Área
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Leyenda de niveles -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <strong><i class="fas fa-info-circle me-2"></i>Niveles Jerárquicos:</strong>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="badge" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);">Dirección Regional</span>
                            <span class="badge" style="background: linear-gradient(135deg, #6b21a8 0%, #9333ea 100%);">OCI</span>
                            <span class="badge" style="background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);">Dirección</span>
                            <span class="badge bg-light text-dark border">Subdirección</span>
                            <span class="badge bg-warning text-dark">Residencia</span>
                        </div>
                    </div>

                    <!-- Árbol de Áreas -->
                    @if($areasRaiz->count() > 0)
                        <ul class="area-tree">
                            @foreach($areasRaiz as $area)
                                @include('admin.areas._area-item', ['area' => $area, 'nivel' => 0])
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-sitemap fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay áreas registradas</h5>
                            <p class="text-muted">Haga clic en "Cargar Organigrama DRTC" para crear la estructura o agregue áreas manualmente.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva/Editar Área -->
<div class="modal fade" id="modalArea" tabindex="-1">
    <div class="modal-dialog modal-lg">
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-layer-group text-primary me-1"></i>Nivel Jerárquico *
                            </label>
                            <select class="form-select" name="nivel" id="inputNivel" required>
                                @foreach($niveles as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-sitemap text-primary me-1"></i>Área Padre
                            </label>
                            <select class="form-select" name="id_area_padre" id="inputAreaPadre">
                                <option value="">-- Ninguna (Área Raíz) --</option>
                                @foreach($todasLasAreas as $areaPadre)
                                    <option value="{{ $areaPadre->id_area }}">
                                        {{ str_repeat('— ', $areaPadre->getProfundidad()) }}{{ $areaPadre->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Seleccione el área superior en la jerarquía</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-tag text-primary me-1"></i>Nombre del Área *
                            </label>
                            <input type="text" class="form-control" name="nombre" id="inputNombre" required
                                   placeholder="Ej: Subdirección de Recursos Humanos">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-font text-primary me-1"></i>Siglas *
                            </label>
                            <input type="text" class="form-control text-uppercase" name="siglas" id="inputSiglas"
                                   required maxlength="20" placeholder="Ej: SDRH"
                                   style="text-transform: uppercase;">
                            <small class="text-muted">Para numeración de documentos</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-align-left text-primary me-1"></i>Descripción
                        </label>
                        <textarea class="form-control" name="descripcion" id="inputDescripcion" rows="2"
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

<!-- Formularios ocultos -->
<form id="formEliminar" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
<form id="formToggle" method="POST" style="display: none;">
    @csrf
    @method('PUT')
</form>

@endsection

@section('scripts')
<script>
// Ejecutar seeder del organigrama
function ejecutarSeeder() {
    if (confirm('¿Desea cargar/actualizar el organigrama de DRTC Apurímac?\n\nEsto creará o actualizará todas las áreas según el organigrama oficial.')) {
        // Mostrar loading
        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Cargando...';

        fetch('{{ route("admin.areas.cargar-organigrama") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al cargar el organigrama: ' + error.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync me-1"></i> Cargar Organigrama DRTC';
        });
    }
}

// Toggle sub-áreas
function toggleSubAreas(id) {
    const subAreas = document.getElementById('subareas-' + id);
    const icon = document.getElementById('toggle-icon-' + id);

    if (subAreas) {
        subAreas.classList.toggle('d-none');
        icon.classList.toggle('collapsed');
    }
}

// Editar área
function editarArea(id) {
    fetch(`/admin/areas/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('inputNombre').value = data.area.nombre;
            document.getElementById('inputSiglas').value = data.area.siglas || '';
            document.getElementById('inputDescripcion').value = data.area.descripcion || '';
            document.getElementById('inputJefe').value = data.area.id_jefe || '';
            document.getElementById('inputNivel').value = data.area.nivel || 'SUBDIRECCION';
            document.getElementById('inputAreaPadre').value = data.area.id_area_padre || '';

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

// Agregar sub-área
function agregarSubArea(idPadre, nombrePadre) {
    document.getElementById('formArea').reset();
    document.getElementById('inputAreaPadre').value = idPadre;
    document.getElementById('inputNivel').value = 'SUBDIRECCION';

    document.getElementById('formArea').action = '{{ route("admin.areas.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Nueva Sub-área de: ' + nombrePadre;
    document.getElementById('btnGuardar').innerHTML = '<i class="fas fa-save me-1"></i>Guardar';

    new bootstrap.Modal(document.getElementById('modalArea')).show();
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
    if (confirm(`¿Está seguro de eliminar el área "${nombre}"?\n\nEsta acción también eliminará todas sus sub-áreas.`)) {
        const form = document.getElementById('formEliminar');
        form.action = `/admin/areas/${id}`;
        form.submit();
    }
}

// Limpiar modal al cerrarse
document.getElementById('modalArea').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formArea').reset();
    document.getElementById('formArea').action = '{{ route("admin.areas.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-building me-2"></i>Nueva Área';
    document.getElementById('btnGuardar').innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
});
</script>
@endsection
