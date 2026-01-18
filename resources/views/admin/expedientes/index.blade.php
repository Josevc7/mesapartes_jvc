@extends('layouts.app')

@section('title', 'Gestión de Expedientes - Administrador')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-folder-open"></i> Gestión de Expedientes</h4>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="buscar" placeholder="Buscar por código, asunto o remitente..." value="{{ request('buscar') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="estado">
                        <option value="">Todos los estados</option>
                        @foreach($estados as $estado)
                        <option value="{{ $estado->slug }}" {{ request('estado') == $estado->slug ? 'selected' : '' }}>
                            {{ $estado->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="id_area">
                        <option value="">Todas las áreas</option>
                        @foreach($areas as $area)
                        <option value="{{ $area->id_area }}" {{ request('id_area') == $area->id_area ? 'selected' : '' }}>
                            {{ $area->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.expedientes') }}" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Limpiar
                    </a>
                </div>
            </form>

            <!-- Tabla de Expedientes -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Asunto</th>
                            <th>Remitente</th>
                            <th>Área</th>
                            <th>Funcionario</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expedientes as $expediente)
                        <tr>
                            <td>
                                <strong>{{ $expediente->codigo_expediente }}</strong>
                                @if($expediente->canal == 'virtual')
                                    <span class="badge bg-info">Virtual</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($expediente->asunto, 40) }}</td>
                            <td>{{ $expediente->remitente ?? $expediente->persona?->nombre_completo ?? 'N/A' }}</td>
                            <td>{{ $expediente->area?->nombre ?? 'Sin asignar' }}</td>
                            <td>{{ $expediente->funcionarioAsignado?->name ?? 'Sin asignar' }}</td>
                            <td>
                                @php
                                    $coloresEstado = [
                                        'recepcionado' => 'secondary',
                                        'registrado' => 'info',
                                        'clasificado' => 'primary',
                                        'derivado' => 'warning',
                                        'en_proceso' => 'warning',
                                        'observado' => 'danger',
                                        'resuelto' => 'success',
                                        'notificado' => 'success',
                                        'archivado' => 'dark'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $coloresEstado[$expediente->estado] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $expediente->estado)) }}
                                </span>
                            </td>
                            <td>{{ $expediente->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.expedientes.show', $expediente->id_expediente) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No se encontraron expedientes</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $expedientes->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
