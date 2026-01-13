@extends('layouts.app')

@section('title', 'Acuse de Recibo')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center bg-primary text-white">
                    <h4><i class="fas fa-certificate"></i> CARGO</h4>
                    <small>Mesa de Partes Digital</small>
                </div>
                <div class="card-body" id="acuse-content">
                    <div class="text-center mb-4">
                        <h5>DIRECCIÓN REGIONAL DE TRANSPORTES Y COMUNICACIONES DE APURÍMAC</h5>
                        <p class="text-muted">Sistema de Mesa de Partes Digital</p>
                        <hr>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="text-center">
                                <h3 class="text-primary">{{ $expediente->codigo_expediente }}</h3>
                                <small class="text-muted">Código del Expediente</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <h5>{{ $expediente->created_at->format('d/m/Y H:i') }}</h5>
                                <small class="text-muted">Fecha y Hora de Registro</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Solicitante:</strong><br>
                            {{ $expediente->ciudadano->name }}
                        </div>
                        <div class="col-md-6">
                            <strong>DNI:</strong><br>
                            {{ $expediente->ciudadano->dni ?? 'No registrado' }}
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Email:</strong><br>
                            {{ $expediente->ciudadano->email }}
                        </div>
                        <div class="col-md-6">
                            <strong>Teléfono:</strong><br>
                            {{ $expediente->ciudadano->telefono ?? 'No registrado' }}
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Asunto:</strong><br>
                        {{ $expediente->asunto }}
                    </div>
                    
                    @if($expediente->tipoTramite)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Tipo de Trámite:</strong><br>
                            {{ $expediente->tipoTramite->nombre }}
                        </div>
                        <div class="col-md-6">
                            <strong>Plazo de Atención:</strong><br>
                            {{ $expediente->tipoTramite->plazo_dias }} días hábiles
                        </div>
                    </div>
                    @endif
                    
                    @if($expediente->area)
                    <div class="mb-3">
                        <strong>Área Responsable:</strong><br>
                        {{ $expediente->area->nombre }}
                    </div>
                    @endif
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Estado Actual:</strong><br>
                            <span class="badge bg-{{ 
                                $expediente->estado == 'Registrado' ? 'secondary' : 
                                ($expediente->estado == 'En Proceso' ? 'info' : 
                                ($expediente->estado == 'Resuelto' ? 'success' : 'warning')) 
                            }} fs-6">
                                {{ $expediente->estado }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Prioridad:</strong><br>
                            <span class="badge bg-{{ 
                                $expediente->prioridad == 'Urgente' ? 'danger' : 
                                ($expediente->prioridad == 'Alta' ? 'warning' : 'secondary') 
                            }} fs-6">
                                {{ $expediente->prioridad }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <strong>Documentos Adjuntos:</strong><br>
                        @if($expediente->documentos->count() > 0)
                            <ul class="list-unstyled">
                                @foreach($expediente->documentos as $documento)
                                <li><i class="fas fa-file-pdf text-danger"></i> {{ $documento->nombre }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">Sin documentos adjuntos</span>
                        @endif
                    </div>
                    
                    <hr>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Información Importante:</h6>
                        <ul class="mb-0">
                            <li><strong>Conserve este cargo</strong> para futuras consultas</li>
                            <li>Puede consultar el estado de su trámite en cualquier momento usando el código: <strong>{{ $expediente->codigo_expediente }}</strong></li>
                            <li>Recibirá notificaciones por email sobre el avance de su expediente</li>
                            <li>El plazo de atención se cuenta desde la fecha de registro</li>
                            <li>Para consultas: <a href="{{ route('seguimiento.form') }}">Sistema de Seguimiento</a></li>
                        </ul>
                    </div>
                    
                    <!-- QR Code para consulta rápida -->
                    <div class="text-center mt-4">
                        <div class="d-inline-block p-3 border">
                            <div id="qrcode"></div>
                            <small class="d-block mt-2 text-muted">Escanee para consulta rápida</small>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4 no-print">
                        <button onclick="window.print()" class="btn btn-primary me-2">
                            <i class="fas fa-print"></i> Imprimir Cargo
                        </button>
                        <button onclick="descargarPDF()" class="btn btn-success me-2">
                            <i class="fas fa-download"></i> Descargar PDF
                        </button>
                        <a href="{{ route('ciudadano.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-home"></i> Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    .card-header { background: #007bff !important; -webkit-print-color-adjust: exact; }
    body { font-size: 12px; }
}

.badge { font-size: 0.9em !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
// Generar QR Code
const qrText = `{{ route('seguimiento.consulta', $expediente->codigo_expediente) }}`;
QRCode.toCanvas(document.getElementById('qrcode'), qrText, {
    width: 120,
    height: 120,
    margin: 1
});

function descargarPDF() {
    // Implementar descarga de PDF
    window.print();
}
</script>
@endsection