@extends('layouts.app')

@section('title', 'Control de Numeración')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Control de Numeración Automática</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5>Estado Actual</h5>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Año Actual:</strong></p>
                                            <h3 class="text-primary">{{ $numeracion->año }}</h3>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Último Número:</strong></p>
                                            <h3 class="text-success">{{ str_pad($numeracion->ultimo_numero, 6, '0', STR_PAD_LEFT) }}</h3>
                                        </div>
                                    </div>
                                    <hr>
                                    <p><strong>Próximo Código:</strong></p>
                                    <h4 class="text-info">{{ $numeracion->año }}-{{ str_pad($numeracion->ultimo_numero + 1, 6, '0', STR_PAD_LEFT) }}</h4>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6>Estadísticas del Año</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <h4 class="text-primary">{{ $estadisticas['total_expedientes'] }}</h4>
                                            <small>Total Expedientes</small>
                                        </div>
                                        <div class="col-4">
                                            <h4 class="text-success">{{ $estadisticas['este_mes'] }}</h4>
                                            <small>Este Mes</small>
                                        </div>
                                        <div class="col-4">
                                            <h4 class="text-info">{{ $estadisticas['hoy'] }}</h4>
                                            <small>Hoy</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Historial de Numeración -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Historial de Numeración</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Año</th>
                                            <th>Último Número</th>
                                            <th>Total Expedientes</th>
                                            <th>Estado</th>
                                            <th>Última Actualización</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($historialNumeracion as $registro)
                                        <tr>
                                            <td>{{ $registro->año }}</td>
                                            <td>{{ str_pad($registro->ultimo_numero, 6, '0', STR_PAD_LEFT) }}</td>
                                            <td>{{ $registro->total_expedientes }}</td>
                                            <td>
                                                <span class="badge bg-{{ $registro->año == date('Y') ? 'success' : 'secondary' }}">
                                                    {{ $registro->año == date('Y') ? 'Activo' : 'Cerrado' }}
                                                </span>
                                            </td>
                                            <td>{{ $registro->updated_at ? $registro->updated_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Configuración -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-header bg-warning">
                                    <h6 class="mb-0">Configuración de Numeración</h6>
                                </div>
                                <div class="card-body">
                                    <div>
                                        <div class="mb-3">
                                            <label class="form-label">Formato de Código</label>
                                            <input type="text" class="form-control" value="YYYY-NNNNNN" readonly>
                                            <small class="text-muted">YYYY = Año, NNNNNN = Número correlativo</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Reinicio Automático</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" checked disabled>
                                                <label class="form-check-label">
                                                    Reiniciar numeración cada año
                                                </label>
                                            </div>
                                        </div>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            La numeración se reinicia automáticamente cada 1 de enero.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Información Importante</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> La numeración es automática y correlativa</li>
                                        <li><i class="fas fa-check text-success"></i> No se pueden duplicar códigos</li>
                                        <li><i class="fas fa-check text-success"></i> Se reinicia cada año automáticamente</li>
                                        <li><i class="fas fa-check text-success"></i> Sistema protegido contra concurrencia</li>
                                    </ul>
                                    
                                    <div class="mt-3">
                                        <button class="btn btn-outline-primary btn-sm" onclick="verificarIntegridad()">
                                            <i class="fas fa-check-double"></i> Verificar Integridad
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function verificarIntegridad() {
    fetch('{{ route("mesa-partes.verificar-numeracion") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Integridad verificada: ' + data.message);
        } else {
            alert('❌ Error encontrado: ' + data.message);
        }
    });
}
</script>
@endsection