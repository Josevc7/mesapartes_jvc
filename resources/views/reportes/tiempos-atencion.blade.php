@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('Reporte de Tiempos de Atención') }}</h4>
                    <a href="{{ route('reportes.index') }}" class="btn btn-secondary">Volver</a>
                </div>

                <div class="card-body">
                    <!-- Filtros -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" 
                                       value="{{ $fechaInicio }}">
                            </div>
                            <div class="col-md-4">
                                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" name="fecha_fin" id="fecha_fin" 
                                       value="{{ $fechaFin }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Filtrar</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Botón de Exportación -->
                    <div class="mb-3">
                        <a href="{{ route('reportes.tiempos-atencion', array_merge(request()->all(), ['export' => 'excel'])) }}" 
                           class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Exportar a Excel
                        </a>
                    </div>

                    <!-- Resumen -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-center bg-info text-white">
                                <div class="card-body">
                                    <h4>{{ $expedientes->count() }}</h4>
                                    <p>Total Expedientes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center bg-success text-white">
                                <div class="card-body">
                                    <h4>{{ $expedientes->where('cumplimiento', 'A Tiempo')->count() }}</h4>
                                    <p>A Tiempo</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center bg-danger text-white">
                                <div class="card-body">
                                    <h4>{{ $expedientes->where('cumplimiento', 'Fuera de Plazo')->count() }}</h4>
                                    <p>Fuera de Plazo</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center bg-warning text-white">
                                <div class="card-body">
                                    <h4>{{ $expedientes->count() > 0 ? round(($expedientes->where('cumplimiento', 'A Tiempo')->count() / $expedientes->count()) * 100, 1) : 0 }}%</h4>
                                    <p>% Cumplimiento</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Resultados -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Tipo Trámite</th>
                                    <th>Ciudadano</th>
                                    <th>Fecha Registro</th>
                                    <th>Fecha Resolución</th>
                                    <th>Plazo (días)</th>
                                    <th>Tiempo Real (días)</th>
                                    <th>Cumplimiento</th>
                                    <th>Diferencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expedientes as $item)
                                <tr>
                                    <td>{{ $item['expediente']->codigo_expediente }}</td>
                                    <td>{{ $item['expediente']->tipoTramite->nombre }}</td>
                                    <td>{{ $item['expediente']->ciudadano->name }}</td>
                                    <td>{{ $item['expediente']->fecha_registro }}</td>
                                    <td>{{ $item['expediente']->updated_at->format('Y-m-d') }}</td>
                                    <td>{{ $item['plazo_establecido'] }}</td>
                                    <td>{{ $item['tiempo_real'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item['cumplimiento'] == 'A Tiempo' ? 'success' : 'danger' }}">
                                            {{ $item['cumplimiento'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-{{ $item['diferencia'] <= 0 ? 'success' : 'danger' }}">
                                            {{ $item['diferencia'] > 0 ? '+' : '' }}{{ $item['diferencia'] }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No se encontraron expedientes en el rango de fechas seleccionado.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection