@extends('layouts.app')

@section('title', 'Editar Permisos - ' . $rol->nombre)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-user-shield"></i> Editar Permisos: {{ $rol->nombre }}</h4>
                    <a href="{{ route('admin.permisos') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.permisos.actualizar', $rol->id_rol) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Seleccione los permisos que desea asignar al rol <strong>{{ $rol->nombre }}</strong>.
                            Los cambios se aplicarán a todos los usuarios con este rol.
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-outline-primary btn-sm me-2" id="selectAll">
                                    <i class="fas fa-check-double"></i> Seleccionar Todos
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAll">
                                    <i class="fas fa-times"></i> Deseleccionar Todos
                                </button>
                            </div>
                        </div>

                        @foreach($modulos as $modulo)
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <div class="form-check">
                                    <input class="form-check-input modulo-checkbox" type="checkbox" id="modulo{{ $modulo->id_modulo }}" data-modulo="{{ $modulo->id_modulo }}">
                                    <label class="form-check-label fw-bold" for="modulo{{ $modulo->id_modulo }}">
                                        <i class="{{ $modulo->icono ?? 'fas fa-folder' }} me-2"></i>
                                        {{ $modulo->nombre }}
                                    </label>
                                    <small class="text-muted ms-2">{{ $modulo->descripcion }}</small>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($modulo->permisos as $permiso)
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input permiso-checkbox" type="checkbox"
                                                   name="permisos[]" value="{{ $permiso->id_permiso }}"
                                                   id="permiso{{ $permiso->id_permiso }}"
                                                   data-modulo="{{ $modulo->id_modulo }}"
                                                   {{ in_array($permiso->id_permiso, $permisosRol) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="permiso{{ $permiso->id_permiso }}">
                                                {{ $permiso->nombre }}
                                                <br><small class="text-muted">{{ $permiso->slug }}</small>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.permisos') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Permisos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Seleccionar/Deseleccionar todos
    document.getElementById('selectAll').addEventListener('click', function() {
        document.querySelectorAll('.permiso-checkbox').forEach(cb => cb.checked = true);
        updateModuloCheckboxes();
    });

    document.getElementById('deselectAll').addEventListener('click', function() {
        document.querySelectorAll('.permiso-checkbox').forEach(cb => cb.checked = false);
        updateModuloCheckboxes();
    });

    // Seleccionar/Deseleccionar por módulo
    document.querySelectorAll('.modulo-checkbox').forEach(function(moduloCb) {
        moduloCb.addEventListener('change', function() {
            const moduloId = this.dataset.modulo;
            document.querySelectorAll(`.permiso-checkbox[data-modulo="${moduloId}"]`).forEach(cb => {
                cb.checked = this.checked;
            });
        });
    });

    // Actualizar checkbox de módulo cuando cambian los permisos
    document.querySelectorAll('.permiso-checkbox').forEach(function(permisoCb) {
        permisoCb.addEventListener('change', updateModuloCheckboxes);
    });

    function updateModuloCheckboxes() {
        document.querySelectorAll('.modulo-checkbox').forEach(function(moduloCb) {
            const moduloId = moduloCb.dataset.modulo;
            const permisos = document.querySelectorAll(`.permiso-checkbox[data-modulo="${moduloId}"]`);
            const checkedPermisos = document.querySelectorAll(`.permiso-checkbox[data-modulo="${moduloId}"]:checked`);
            moduloCb.checked = permisos.length === checkedPermisos.length;
            moduloCb.indeterminate = checkedPermisos.length > 0 && checkedPermisos.length < permisos.length;
        });
    }

    // Inicializar estado de checkboxes de módulo
    updateModuloCheckboxes();
});
</script>
@endsection
@endsection
