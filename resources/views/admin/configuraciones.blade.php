@extends('layouts.app')

@section('title', 'Configuraciones del Sistema')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Configuraciones del Sistema</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.configuraciones.update') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="nombre_institucion" class="form-label">Nombre de la Institución</label>
                            <input type="text" class="form-control" id="nombre_institucion" 
                                   name="nombre_institucion" value="{{ $configuraciones['nombre_institucion'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" 
                                   name="direccion" value="{{ $configuraciones['direccion'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" 
                                   name="telefono" value="{{ $configuraciones['telefono'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label for="email_institucional" class="form-label">Email Institucional</label>
                            <input type="email" class="form-control" id="email_institucional" 
                                   name="email_institucional" value="{{ $configuraciones['email_institucional'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label for="dias_limite_default" class="form-label">Días Límite por Defecto</label>
                            <input type="number" class="form-control" id="dias_limite_default" 
                                   name="dias_limite_default" value="{{ $configuraciones['dias_limite_default'] ?? '15' }}">
                        </div>

                        <div class="mb-3">
                            <label for="max_size_archivo" class="form-label">Tamaño Máximo de Archivo (MB)</label>
                            <input type="number" class="form-control" id="max_size_archivo" 
                                   name="max_size_archivo" value="{{ $configuraciones['max_size_archivo'] ?? '10' }}">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Guardar Configuraciones</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection