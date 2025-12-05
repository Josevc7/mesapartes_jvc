@extends('layouts.app')

@section('title', 'Consulta Pública')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Consulta Pública de Expedientes</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('seguimiento.buscar') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="codigo_expediente" class="form-label">Número de Expediente *</label>
                            <input type="text" class="form-control @error('codigo_expediente') is-invalid @enderror" 
                                   id="codigo_expediente" name="codigo_expediente" 
                                   value="{{ old('codigo_expediente') }}"
                                   placeholder="Ej: 2025-000001" required>
                            @error('codigo_expediente')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI del Solicitante *</label>
                            <input type="text" class="form-control @error('dni') is-invalid @enderror" 
                                   id="dni" name="dni" 
                                   value="{{ old('dni') }}"
                                   placeholder="Ingrese DNI" maxlength="8" required>
                            @error('dni')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Consultar Expediente
                            </button>
                        </div>
                    </form>
                    
                    <hr>
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Ingrese el número de expediente y DNI para consultar el estado de su trámite
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection