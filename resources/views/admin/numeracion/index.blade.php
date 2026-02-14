@extends('layouts.app')

@section('title', 'Configuración de Numeración')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-sort-numeric-up"></i> Control de Numeración</h4>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalNumeracion">
                        <i class="fas fa-plus"></i> Nuevo Año
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Año</th>
                                    <th>Prefijo</th>
                                    <th>Último Número</th>
                                    <th>Siguiente</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($numeraciones as $numeracion)
                                <tr class="{{ $numeracion->anio == date('Y') ? 'table-primary' : '' }}">
                                    <td>
                                        <strong>{{ $numeracion->anio }}</strong>
                                        @if($numeracion->anio == date('Y'))
                                            <span class="badge bg-success ms-2">Actual</span>
                                        @endif
                                    </td>
                                    <td>{{ $numeracion->prefijo ?? 'EXP' }}</td>
                                    <td>{{ $numeracion->ultimo_numero }}</td>
                                    <td>
                                        <code>{{ $numeracion->anio }}-{{ str_pad($numeracion->ultimo_numero + 1, 6, '0', STR_PAD_LEFT) }}</code>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editarNumeracion({{ json_encode($numeracion) }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.numeracion.reiniciar', $numeracion->id_numeracion) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Reiniciar numeración a 0? Esta acción es irreversible.')">
                                                <i class="fas fa-redo"></i> Reiniciar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay numeraciones configuradas</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Configuración</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.configuraciones.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Formato de Código</label>
                            <input type="text" class="form-control" name="formato_codigo" value="{{ $configuraciones['formato_codigo'] ?? 'YYYY-XXXXXX' }}" readonly>
                            <small class="text-muted">YYYY = Año, XXXXXX = Número secuencial</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prefijo de Expediente</label>
                            <input type="text" class="form-control" name="prefijo_expediente" value="{{ $configuraciones['prefijo_expediente'] ?? 'EXP' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dígitos del Número</label>
                            <select class="form-select" name="digitos_numero">
                                @for($i = 4; $i <= 8; $i++)
                                <option value="{{ $i }}" {{ ($configuraciones['digitos_numero'] ?? 6) == $i ? 'selected' : '' }}>
                                    {{ $i }} dígitos ({{ str_pad(1, $i, '0', STR_PAD_LEFT) }})
                                </option>
                                @endfor
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Guardar Configuración
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Año Actual:</strong> {{ date('Y') }}</p>
                    <p class="mb-2">
                        <strong>Próximo Código:</strong>
                        @php
                            $numActual = $numeraciones->firstWhere('anio', date('Y'));
                            $siguiente = $numActual ? $numActual->ultimo_numero + 1 : 1;
                        @endphp
                        <code>{{ date('Y') }}-{{ str_pad($siguiente, 6, '0', STR_PAD_LEFT) }}</code>
                    </p>
                    <p class="mb-0"><strong>Total este año:</strong> {{ $numActual->ultimo_numero ?? 0 }} expedientes</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Numeración -->
<div class="modal fade" id="modalNumeracion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNumeracionTitle">Nueva Numeración</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNumeracion" method="POST" action="{{ route('admin.numeracion.store') }}">
                @csrf
                <div id="methodFieldNum"></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Año *</label>
                        <input type="number" class="form-control" name="anio" id="numAnio" min="2020" max="2100" value="{{ date('Y') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Último Número *</label>
                        <input type="number" class="form-control" name="ultimo_numero" id="numUltimoNumero" min="0" value="0" required>
                        <small class="text-muted">El siguiente expediente usará este número + 1</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prefijo</label>
                        <input type="text" class="form-control" name="prefijo" id="numPrefijo" placeholder="EXP">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
function editarNumeracion(numeracion) {
    document.getElementById('modalNumeracionTitle').textContent = 'Editar Numeración';
    document.getElementById('formNumeracion').action = window.APP_URL + '/admin/numeracion/' + numeracion.id_numeracion;
    document.getElementById('methodFieldNum').innerHTML = '<input type="hidden" name="_method" value="PUT">';

    document.getElementById('numAnio').value = numeracion.anio;
    document.getElementById('numAnio').readOnly = true;
    document.getElementById('numUltimoNumero').value = numeracion.ultimo_numero;
    document.getElementById('numPrefijo').value = numeracion.prefijo || '';

    new bootstrap.Modal(document.getElementById('modalNumeracion')).show();
}

document.getElementById('modalNumeracion').addEventListener('hidden.bs.modal', function() {
    document.getElementById('modalNumeracionTitle').textContent = 'Nueva Numeración';
    document.getElementById('formNumeracion').action = '{{ route("admin.numeracion.store") }}';
    document.getElementById('methodFieldNum').innerHTML = '';
    document.getElementById('numAnio').readOnly = false;
    document.getElementById('formNumeracion').reset();
});
</script>
@endsection
@endsection
