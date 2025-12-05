@extends('layouts.app')

@section('title', 'Nuevo Expediente')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Registrar Nuevo Expediente</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('expedientes.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="asunto" class="form-label">Asunto *</label>
                            <input type="text" class="form-control @error('asunto') is-invalid @enderror" 
                                   id="asunto" name="asunto" value="{{ old('asunto') }}" required>
                            @error('asunto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción *</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" name="descripcion" rows="4" required>{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="documento" class="form-label">Documento PDF *</label>
                            <input type="file" class="form-control @error('documento') is-invalid @enderror" 
                                   id="documento" name="documento" accept=".pdf" required>
                            <div class="form-text">Solo archivos PDF, máximo 10MB</div>
                            @error('documento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">Registrar Expediente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection