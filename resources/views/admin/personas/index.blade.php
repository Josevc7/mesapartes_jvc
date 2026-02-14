@extends('layouts.app')

@section('title', 'Gestión de Personas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Gestión de Personas</h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPersona">
                        <i class="fas fa-plus"></i> Nueva Persona
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Tipo</th>
                                    <th>Nombre/Razón Social</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Expedientes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($personas as $persona)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $persona->tipo_documento }}</span>
                                        {{ $persona->numero_documento }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $persona->tipo_persona == 'NATURAL' ? 'info' : 'warning' }}">
                                            {{ $persona->tipo_persona }}
                                        </span>
                                    </td>
                                    <td>{{ $persona->nombre_completo }}</td>
                                    <td>{{ $persona->telefono }}</td>
                                    <td>{{ $persona->email }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $persona->expedientes->count() }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="verPersona({{ $persona->id_persona }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="editarPersona({{ $persona->id_persona }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($persona->expedientes->count() == 0)
                                        <button class="btn btn-sm btn-danger" onclick="eliminarPersona({{ $persona->id_persona }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        {{ $personas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva/Editar Persona -->
<div class="modal fade" id="modalPersona" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formPersona" method="POST" action="{{ route('admin.personas.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Persona</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Documento</label>
                            <select class="form-select" name="tipo_documento" required>
                                <option value="DNI">DNI</option>
                                <option value="CE">Carné de Extranjería</option>
                                <option value="RUC">RUC</option>
                                <option value="PASAPORTE">Pasaporte</option>
                                <option value="OTROS">Otros</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Número de Documento</label>
                            <input type="text" class="form-control" name="numero_documento" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de Persona</label>
                        <select class="form-select" name="tipo_persona" id="tipo_persona" required>
                            <option value="NATURAL">Persona Natural</option>
                            <option value="JURIDICA">Persona Jurídica</option>
                        </select>
                    </div>

                    <div id="persona-natural">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Nombres</label>
                                <input type="text" class="form-control" name="nombres">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Apellido Paterno</label>
                                <input type="text" class="form-control" name="apellido_paterno">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Apellido Materno</label>
                                <input type="text" class="form-control" name="apellido_materno">
                            </div>
                        </div>
                    </div>

                    <div id="persona-juridica" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Razón Social</label>
                            <input type="text" class="form-control" name="razon_social">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Representante Legal</label>
                            <input type="text" class="form-control" name="representante_legal">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea class="form-control" name="direccion" rows="2"></textarea>
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
function togglePersonaFields() {
    const tipo = document.getElementById('tipo_persona').value;
    const natural = document.getElementById('persona-natural');
    const juridica = document.getElementById('persona-juridica');
    
    if (tipo === 'NATURAL') {
        natural.style.display = 'block';
        juridica.style.display = 'none';
    } else {
        natural.style.display = 'none';
        juridica.style.display = 'block';
    }
}

document.getElementById('tipo_persona').addEventListener('change', togglePersonaFields);

function editarPersona(id) {
    fetch(`${window.APP_URL}/admin/personas/${id}`)
        .then(response => response.json())
        .then(data => {
            const form = document.getElementById('formPersona');
            form.action = `${window.APP_URL}/admin/personas/${id}`;
            form.innerHTML += '<input type="hidden" name="_method" value="PUT">';
            
            // Llenar campos
            form.querySelector('[name="tipo_documento"]').value = data.tipo_documento;
            form.querySelector('[name="numero_documento"]').value = data.numero_documento;
            form.querySelector('[name="tipo_persona"]').value = data.tipo_persona;
            form.querySelector('[name="nombres"]').value = data.nombres || '';
            form.querySelector('[name="apellido_paterno"]').value = data.apellido_paterno || '';
            form.querySelector('[name="apellido_materno"]').value = data.apellido_materno || '';
            form.querySelector('[name="razon_social"]').value = data.razon_social || '';
            form.querySelector('[name="representante_legal"]').value = data.representante_legal || '';
            form.querySelector('[name="telefono"]').value = data.telefono || '';
            form.querySelector('[name="email"]').value = data.email || '';
            form.querySelector('[name="direccion"]').value = data.direccion || '';
            
            togglePersonaFields();
            document.querySelector('.modal-title').textContent = 'Editar Persona';
            new bootstrap.Modal(document.getElementById('modalPersona')).show();
        });
}

function eliminarPersona(id) {
    if (confirm('¿Está seguro de eliminar esta persona?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `${window.APP_URL}/admin/personas/${id}`;
        form.innerHTML = `
            @csrf
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Limpiar modal
document.getElementById('modalPersona').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('formPersona');
    form.reset();
    form.action = '{{ route('admin.personas.store') }}';
    document.querySelector('.modal-title').textContent = 'Nueva Persona';
    const methodInput = form.querySelector('[name="_method"]');
    if (methodInput) methodInput.remove();
});
</script>
@endsection