@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('Reporte de Trámites por Mes') }} - {{ $año }}</h4>
                    <a href="{{ route('reportes.index') }}" class="btn btn-secondary">Volver</a>
                </div>

                <div class="card-body">
                    <!-- Filtros -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="año" class="form-label">Año</label>
                                <select name="año" id="año" class="form-select">
                                    @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                        <option value="{{ $i }}" {{ $año == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Filtrar</button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <a href="{{ route('reportes.tramites-mes', array_merge(request()->all(), ['export' => 'excel'])) }}" 
                                       class="btn btn-success">
                                        <i class="fas fa-file-excel"></i> Exportar Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Gráfico Simple -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <canvas id="tramitesChart" width="400" height="100"></canvas>
                        </div>
                    </div>

                    <!-- Tabla de Datos -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Mes</th>
                                    <th>Cantidad de Trámites</th>
                                    <th>Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $meses = [
                                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                    ];
                                    $total = $datos->sum('total');
                                @endphp
                                @for($mes = 1; $mes <= 12; $mes++)
                                    @php
                                        $cantidad = $datos->where('mes', $mes)->first()?->total ?? 0;
                                        $porcentaje = $total > 0 ? round(($cantidad / $total) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $meses[$mes] }}</td>
                                        <td>{{ $cantidad }}</td>
                                        <td>{{ $porcentaje }}%</td>
                                    </tr>
                                @endfor
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th>Total</th>
                                    <th>{{ $total }}</th>
                                    <th>100%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('tramitesChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        datasets: [{
            label: 'Trámites por Mes',
            data: [
                @for($mes = 1; $mes <= 12; $mes++)
                    {{ $datos->where('mes', $mes)->first()?->total ?? 0 }},
                @endfor
            ],
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endsection