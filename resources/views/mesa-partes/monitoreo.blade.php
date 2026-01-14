@extends('layouts.app')

@section('title', 'Monitoreo de Plazos')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2>Monitoreo de Plazos</h2>
        </div>
    </div>

    <!-- Expedientes Vencidos -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5><i class="fas fa-exclamation-triangle"></i> Expedientes Vencidos ({{ $vencidos->total() }})</h5>
                </div>
                <div class="card-body">
                    @if($vencidos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Asunto</th>
                                        <th>Área</th>
                                        <th>Funcionario</th>
                                        <th>Días Vencido</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vencidos as $expediente)
                                    @php
                                        $ultimaDerivacion = $expediente->derivaciones->first();
                                        $diasVencido = $ultimaDerivacion && $ultimaDerivacion->fecha_limite
                                            ? (int) now()->diffInDays($ultimaDerivacion->fecha_limite)
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $expediente->codigo_expediente }}</td>
                                        <td>{{ Str::limit($expediente->asunto, 40) }}</td>
                                        <td>{{ $expediente->area->nombre ?? 'N/A' }}</td>
                                        <td>{{ $expediente->funcionarioAsignado->name ?? 'Sin asignar' }}</td>
                                        <td>
                                            <span class="badge bg-danger">
                                                {{ $diasVencido }} días
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('mesa-partes.show', $expediente) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                            <button class="btn btn-sm btn-warning">Recordatorio</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $vencidos->links() }}
                        </div>
                    @else
                        <p class="text-muted">No hay expedientes vencidos</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Expedientes Por Vencer -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5><i class="fas fa-clock"></i> Por Vencer en 3 Días ({{ $porVencer->total() }})</h5>
                </div>
                <div class="card-body">
                    @if($porVencer->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Asunto</th>
                                        <th>Área</th>
                                        <th>Funcionario</th>
                                        <th>Días Restantes</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($porVencer as $expediente)
                                    @php
                                        $ultimaDerivacion = $expediente->derivaciones->first();
                                        $diasRestantes = $ultimaDerivacion && $ultimaDerivacion->fecha_limite
                                            ? (int) $ultimaDerivacion->fecha_limite->diffInDays(now())
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $expediente->codigo_expediente }}</td>
                                        <td>{{ Str::limit($expediente->asunto, 40) }}</td>
                                        <td>{{ $expediente->area->nombre ?? 'N/A' }}</td>
                                        <td>{{ $expediente->funcionarioAsignado->name ?? 'Sin asignar' }}</td>
                                        <td>
                                            <span class="badge bg-warning">
                                                {{ $diasRestantes }} días
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('mesa-partes.show', $expediente) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $porVencer->links() }}
                        </div>
                    @else
                        <p class="text-muted">No hay expedientes próximos a vencer</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection