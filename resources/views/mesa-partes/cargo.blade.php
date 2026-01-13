@extends('layouts.app')

@section('title', 'Cargo')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">
                    <h4>CARGO</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h5>DIRECCIÓN REGIONAL DE TRANSPORTES Y COMUNICACIONES DE APURÍMAC</h5>
                        <p>Mesa de Partes Digital</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Código de Expediente:</strong><br>
                            <span class="fs-4 text-primary">{{ $expediente->codigo_expediente }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha de Registro:</strong><br>
                            {{ $expediente->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <strong>Remitente:</strong><br>
                        {{ $expediente->remitente ?? $expediente->ciudadano->name ?? 'N/A' }}
                    </div>
                    
                    <div class="mb-3">
                        <strong>Asunto:</strong><br>
                        {{ $expediente->asunto }}
                    </div>
                    
                    @if($expediente->tipoTramite)
                    <div class="mb-3">
                        <strong>Tipo de Trámite:</strong><br>
                        {{ $expediente->tipoTramite->nombre }}
                    </div>
                    @endif
                    
                    @if($expediente->area)
                    <div class="mb-3">
                        <strong>Área Asignada:</strong><br>
                        {{ $expediente->area->nombre }}
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <strong>Estado:</strong><br>
                        <span class="badge bg-info">{{ $expediente->estado }}</span>
                    </div>
                    
                    <hr>
                    
                    <div class="alert alert-info">
                        <strong>Importante:</strong> Conserve este comprobante de recibo para futuras consultas. 
                        Puede consultar el estado de su trámite en cualquier momento usando el código de expediente.
                    </div>
                    
                    <div class="text-center mt-4">
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                        <a href="{{ route('mesa-partes.index') }}" class="btn btn-secondary">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .navbar, .card-header { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>
@endsection