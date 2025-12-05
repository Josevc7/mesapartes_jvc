@extends('layouts.app')

@section('title', 'Logs del Sistema')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Logs del Sistema</h2>
                <a href="{{ route('soporte.dashboard') }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Últimas 100 entradas del log</h5>
                </div>
                <div class="card-body">
                    @if(count($logs) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha/Hora</th>
                                        <th>Nivel</th>
                                        <th>Canal</th>
                                        <th>Mensaje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_reverse($logs) as $log)
                                    <tr class="{{ 
                                        $log['nivel'] == 'ERROR' ? 'table-danger' : 
                                        ($log['nivel'] == 'WARNING' ? 'table-warning' : '') 
                                    }}">
                                        <td>
                                            <small>{{ $log['fecha'] ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $log['nivel'] == 'ERROR' ? 'danger' : 
                                                ($log['nivel'] == 'WARNING' ? 'warning' : 
                                                ($log['nivel'] == 'INFO' ? 'info' : 'secondary')) 
                                            }}">
                                                {{ $log['nivel'] ?? 'UNKNOWN' }}
                                            </span>
                                        </td>
                                        <td>{{ $log['canal'] ?? 'N/A' }}</td>
                                        <td>
                                            <small>{{ Str::limit($log['mensaje'] ?? '', 100) }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No se encontraron logs o el archivo no existe</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Acciones -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Filtros</h5>
                </div>
                <div class="card-body">
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-outline-danger">Solo Errores</button>
                        <button type="button" class="btn btn-outline-warning">Advertencias</button>
                        <button type="button" class="btn btn-outline-info">Info</button>
                        <button type="button" class="btn btn-outline-secondary">Todos</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-warning">
                            <i class="fas fa-trash"></i> Limpiar Logs Antiguos
                        </button>
                        <button class="btn btn-info">
                            <i class="fas fa-download"></i> Descargar Log Completo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh cada 30 segundos
setTimeout(function() {
    location.reload();
}, 30000);
</script>
@endsection