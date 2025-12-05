@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('Reporte de Áreas con Retrasos') }}</h4>
                    <a href="{{ route('reportes.index') }}" class="btn btn-secondary">Volver</a>
                </div>

                <div class="card-body">
                    <!-- Resumen General -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-info text-white text-center">
                                <div class="card-body">
                                    <h4>{{ $areasConRetrasos->count() }}</h4>
                                    <p>Total Áreas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white text-center">
                                <div class="card-body">
                                    <h4>{{ $areasConRetrasos->where('vencidos', '>', 0)->count() }}</h4>
                                    <p>Con Retrasos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white text-center">
                                <div class="card-body">
                                    <h4>{{ $areasConRetrasos->sum('vencidos') }}</h4>
                                    <p>Total Vencidos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white text-center">
                                <div class="card-body">
                                    <h4>{{ $areasConRetrasos->sum('total_asignados') }}</h4>
                                    <p>Total Asignados</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Áreas -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Área</th>
                                    <th>Total Asignados</th>
                                    <th>Expedientes Vencidos</th>
                                    <th>% Retraso</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($areasConRetrasos as $item)
                                <tr class="{{ $item['porcentaje_retraso'] > 50 ? 'table-danger' : ($item['porcentaje_retraso'] > 25 ? 'table-warning' : '') }}">
                                    <td>
                                        <strong>{{ $item['area']->nombre }}</strong>
                                        @if($item['area']->descripcion)
                                            <br><small class="text-muted">{{ $item['area']->descripcion }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item['total_asignados'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item['vencidos'] > 0 ? 'danger' : 'success' }}">
                                            {{ $item['vencidos'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $item['porcentaje_retraso'] > 50 ? 'danger' : ($item['porcentaje_retraso'] > 25 ? 'warning' : 'success') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ min($item['porcentaje_retraso'], 100) }}%">
                                                {{ $item['porcentaje_retraso'] }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item['porcentaje_retraso'] == 0)
                                            <span class="badge bg-success">Excelente</span>
                                        @elseif($item['porcentaje_retraso'] <= 10)
                                            <span class="badge bg-info">Bueno</span>
                                        @elseif($item['porcentaje_retraso'] <= 25)
                                            <span class="badge bg-warning">Regular</span>
                                        @elseif($item['porcentaje_retraso'] <= 50)
                                            <span class="badge bg-danger">Malo</span>
                                        @else
                                            <span class="badge bg-dark">Crítico</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item['vencidos'] > 0)
                                            <button class="btn btn-sm btn-outline-primary" onclick="verDetalleArea({{ $item['area']->id }})">
                                                <i class="fas fa-eye"></i> Ver Detalle
                                            </button>
                                        @else
                                            <span class="text-muted">Sin retrasos</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No se encontraron áreas con datos.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Recomendaciones -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-lightbulb"></i> Recomendaciones</h6>
                        <ul class="mb-0">
                            <li><strong>Crítico (>50%):</strong> Requiere intervención inmediata y redistribución de carga</li>
                            <li><strong>Malo (25-50%):</strong> Necesita supervisión y apoyo adicional</li>
                            <li><strong>Regular (10-25%):</strong> Monitoreo constante y mejoras en procesos</li>
                            <li><strong>Bueno (≤10%):</strong> Mantener el rendimiento actual</li>
                            <li><strong>Excelente (0%):</strong> Área modelo para replicar buenas prácticas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalle de área -->
<div class="modal fade" id="detalleAreaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Expedientes Vencidos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleAreaContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function verDetalleArea(areaId) {
    const modal = new bootstrap.Modal(document.getElementById('detalleAreaModal'));
    const content = document.getElementById('detalleAreaContent');
    
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Aquí se haría una llamada AJAX para obtener el detalle
    setTimeout(() => {
        content.innerHTML = `
            <div class="alert alert-info">
                <strong>Funcionalidad en desarrollo</strong><br>
                Aquí se mostrarían los expedientes vencidos del área seleccionada.
            </div>
        `;
    }, 1000);
}
</script>
@endsection