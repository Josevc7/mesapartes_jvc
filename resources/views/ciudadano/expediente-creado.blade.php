@extends('layouts.app')

@section('title', 'Expediente Creado')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-success">
                <div class="card-header bg-success text-white text-center">
                    <h4><i class="fas fa-check-circle"></i> Expediente Registrado Exitosamente</h4>
                </div>
                <div class="card-body text-center">
                    <div class="alert alert-success">
                        <h5>Su expediente ha sido registrado con el código:</h5>
                        <h2 class="text-primary"><strong>{{ $codigo }}</strong></h2>
                    </div>
                    
                    <p class="mb-4">Guarde este código para consultar el estado de su trámite.</p>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('ciudadano.dashboard') }}" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-home"></i> Ir al Dashboard
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('ciudadano.mis-expedientes') }}" class="btn btn-info btn-lg w-100">
                                <i class="fas fa-list"></i> Ver Mis Expedientes
                            </a>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('ciudadano.registrar-expediente') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-plus"></i> Registrar Otro Expediente
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('ciudadano.seguimiento', $codigo) }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-search"></i> Ver Seguimiento
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection