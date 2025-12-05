@extends('layouts.app')

@section('title', 'Respaldo y Mantenimiento')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Respaldo de Base de Datos</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Generar respaldo completo de la base de datos del sistema.</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipo de Respaldo</label>
                        <select class="form-select" id="tipoRespaldo">
                            <option value="completo">Respaldo Completo</option>
                            <option value="datos">Solo Datos</option>
                            <option value="estructura">Solo Estructura</option>
                        </select>
                    </div>
                    
                    <button class="btn btn-primary" onclick="generarRespaldo()">
                        <i class="fas fa-download"></i> Generar Respaldo
                    </button>
                    
                    <div id="estadoRespaldo" class="mt-3" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fas fa-spinner fa-spin"></i> Generando respaldo...
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Mantenimiento del Sistema</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-warning" onclick="limpiarCache()">
                            <i class="fas fa-broom"></i> Limpiar Caché
                        </button>
                        
                        <button class="btn btn-info" onclick="optimizarDB()">
                            <i class="fas fa-database"></i> Optimizar Base de Datos
                        </button>
                        
                        <button class="btn btn-secondary" onclick="limpiarLogs()">
                            <i class="fas fa-trash"></i> Limpiar Logs Antiguos
                        </button>
                        
                        <button class="btn btn-success" onclick="verificarIntegridad()">
                            <i class="fas fa-check-circle"></i> Verificar Integridad
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Historial de Respaldos</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Tamaño</th>
                                    <th>Estado</th>
                                    <th>Usuario</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($respaldos as $respaldo)
                                <tr>
                                    <td>{{ $respaldo->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ ucfirst($respaldo->tipo) }}</span>
                                    </td>
                                    <td>{{ $respaldo->tamaño_mb }} MB</td>
                                    <td>
                                        <span class="badge bg-{{ $respaldo->exitoso ? 'success' : 'danger' }}">
                                            {{ $respaldo->exitoso ? 'Exitoso' : 'Error' }}
                                        </span>
                                    </td>
                                    <td>{{ $respaldo->usuario->name }}</td>
                                    <td>
                                        @if($respaldo->exitoso)
                                        <a href="{{ route('soporte.descargar-respaldo', $respaldo->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @endif
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="eliminarRespaldo({{ $respaldo->id }})">
                                            <i class="fas fa-trash"></i>
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

<script>
function generarRespaldo() {
    const tipo = document.getElementById('tipoRespaldo').value;
    document.getElementById('estadoRespaldo').style.display = 'block';
    
    fetch('{{ route("soporte.respaldo") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ tipo: tipo })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('estadoRespaldo').innerHTML = 
            `<div class="alert alert-${data.success ? 'success' : 'danger'}">
                <i class="fas fa-${data.success ? 'check' : 'times'}"></i> ${data.message}
            </div>`;
        
        if (data.success) {
            setTimeout(() => location.reload(), 2000);
        }
    });
}

function limpiarCache() {
    fetch('{{ route("soporte.limpiar-cache") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).then(() => {
        alert('Caché limpiado correctamente');
    });
}
</script>
@endsection