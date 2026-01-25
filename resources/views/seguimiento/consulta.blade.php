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
                            <label for="tipo_documento" class="form-label">Tipo de Documento *</label>
                            <select class="form-select @error('tipo_documento') is-invalid @enderror"
                                    id="tipo_documento" name="tipo_documento" required onchange="cambiarTipoDocumento()">
                                <option value="DNI" {{ old('tipo_documento', 'DNI') == 'DNI' ? 'selected' : '' }}>DNI (Persona Natural)</option>
                                <option value="RUC" {{ old('tipo_documento') == 'RUC' ? 'selected' : '' }}>RUC (Persona Jurídica)</option>
                            </select>
                            @error('tipo_documento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="numero_documento" class="form-label">
                                <span id="label_documento">DNI</span> del Solicitante *
                            </label>
                            <input type="text" class="form-control @error('numero_documento') is-invalid @enderror"
                                   id="numero_documento" name="numero_documento"
                                   value="{{ old('numero_documento') }}"
                                   placeholder="Ingrese su documento" maxlength="8" required
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <small id="ayuda_documento" class="form-text text-muted">Ingrese 8 dígitos</small>
                            @error('numero_documento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Consultar Expediente
                            </button>
                        </div>
                    </form>

                    <script>
                        function cambiarTipoDocumento() {
                            const tipo = document.getElementById('tipo_documento').value;
                            const inputDoc = document.getElementById('numero_documento');
                            const labelDoc = document.getElementById('label_documento');
                            const ayudaDoc = document.getElementById('ayuda_documento');

                            if (tipo === 'RUC') {
                                inputDoc.maxLength = 11;
                                inputDoc.placeholder = 'Ingrese RUC';
                                labelDoc.textContent = 'RUC';
                                ayudaDoc.textContent = 'Ingrese 11 dígitos';
                            } else {
                                inputDoc.maxLength = 8;
                                inputDoc.placeholder = 'Ingrese DNI';
                                labelDoc.textContent = 'DNI';
                                ayudaDoc.textContent = 'Ingrese 8 dígitos';
                            }
                            inputDoc.value = '';
                        }

                        document.addEventListener('DOMContentLoaded', function() {
                            cambiarTipoDocumento();
                        });
                    </script>

                    <hr>
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Ingrese el número de expediente y su documento (DNI o RUC) para consultar el estado de su trámite
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection