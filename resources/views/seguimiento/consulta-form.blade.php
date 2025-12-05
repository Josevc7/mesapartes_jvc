@extends('layouts.app')

@section('title', 'Consulta de Expediente')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">
                    <h3 class="card-title">Consulta Pública de Expedientes</h3>
                    <p class="text-muted mb-0">Ingresa el código de tu expediente para consultar su estado</p>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('seguimiento.consulta', ['codigo' => 'PLACEHOLDER']) }}" 
                          onsubmit="return consultarExpediente(event)">
                        <div class="mb-4">
                            <label for="codigo" class="form-label">Código del Expediente</label>
                            <input type="text" 
                                   class="form-control form-control-lg text-center" 
                                   id="codigo" 
                                   name="codigo" 
                                   placeholder="Ej: 2024-000001"
                                   pattern="[0-9]{4}-[0-9]{6}"
                                   title="Formato: YYYY-NNNNNN (Ej: 2024-000001)"
                                   required>
                            <div class="form-text">
                                El código tiene el formato: Año-Número (Ej: 2024-000001)
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search"></i> Consultar Expediente
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                                <h6>Consulta 24/7</h6>
                                <small class="text-muted">Disponible las 24 horas</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                                <h6>Información Segura</h6>
                                <small class="text-muted">Solo con tu código</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <i class="fas fa-mobile-alt fa-2x text-info mb-2"></i>
                                <h6>Desde Cualquier Lugar</h6>
                                <small class="text-muted">Web y móvil</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">¿Cómo obtener mi código de expediente?</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-file-alt text-primary"></i> Acuse de Recibo</h6>
                            <p class="small text-muted">
                                El código aparece en el acuse de recibo que se genera al registrar tu documento.
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-envelope text-info"></i> Correo Electrónico</h6>
                            <p class="small text-muted">
                                También lo recibes por email si proporcionaste tu correo al registrar el expediente.
                            </p>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>¿No encuentras tu código?</strong> 
                        Acércate a la Mesa de Partes con tu DNI para obtener la información de tus expedientes.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function consultarExpediente(event) {
    event.preventDefault();
    const codigo = document.getElementById('codigo').value.trim();
    
    if (!codigo) {
        alert('Por favor ingresa el código del expediente');
        return false;
    }
    
    // Redirigir a la consulta con el código
    window.location.href = `{{ route('seguimiento.consulta', '') }}/${codigo}`;
    return false;
}

// Auto-formatear el código mientras se escribe
document.getElementById('codigo').addEventListener('input', function(e) {
    let value = e.target.value.replace(/[^0-9]/g, '');
    
    if (value.length >= 4) {
        value = value.substring(0, 4) + '-' + value.substring(4, 10);
    }
    
    e.target.value = value;
});
</script>
@endsection