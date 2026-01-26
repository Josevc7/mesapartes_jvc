@extends('layouts.app')

@section('title', 'Mesa de Partes - Expedientes')

@section('styles')
<style>
/* Estadisticas inline */
.stat-mini {
    padding: 1.8rem 3rem;
    border-radius: 14px;
    font-size: 1.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s, transform 0.15s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    min-width: 140px;
    text-align: center;
}
.stat-mini:hover {
    opacity: 0.9;
    transform: scale(1.05);
}
.stat-mini strong {
    font-weight: 800;
    font-size: 3rem;
    display: block;
    line-height: 1;
    margin-bottom: 5px;
}

/* Filtros */
.filter-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    border-radius: 10px;
}
.filter-card .form-control,
.filter-card .form-select {
    font-size: 0.85rem;
    border-radius: 6px;
}

/* Tabla mejorada */
.table-expedientes {
    font-size: 0.85rem;
}
.table-expedientes th {
    background: linear-gradient(135deg, #343a40 0%, #495057 100%);
    color: white;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.75rem 0.5rem;
    border: none;
    white-space: nowrap;
}
.table-expedientes td {
    padding: 0.6rem 0.5rem;
    vertical-align: middle;
}
.table-expedientes tbody tr {
    transition: background-color 0.2s;
}
.table-expedientes tbody tr:hover {
    background-color: #e3f2fd !important;
}

/* Codigo de expediente */
.codigo-expediente {
    font-family: 'Consolas', monospace;
    font-weight: 600;
    color: #1976d2;
    font-size: 0.8rem;
}

/* Badges mejorados */
.badge-canal {
    font-size: 0.75rem;
    padding: 0.35rem 0.5rem;
    border-radius: 20px;
    color: #0d6efd !important;
    background-color: #e7f1ff !important;
    border: 1px solid #0d6efd;
    display: inline-block;
    font-weight: 600;
}
.badge-virtual {
    color: #0d6efd !important;
    background-color: #e7f1ff !important;
    border: 1px solid #0d6efd;
}
.badge-presencial {
    color: #0d6efd !important;
    background-color: #e7f1ff !important;
    border: 1px solid #0d6efd;
}

/* Documento info */
.documento-info {
    font-size: 0.8rem;
}
.documento-tipo {
    font-size: 0.65rem;
    padding: 0.2rem 0.4rem;
}

/* Solicitante */
.solicitante-nombre {
    font-weight: 500;
    color: #333;
    font-size: 0.85rem;
}
.solicitante-detalle {
    font-size: 0.7rem;
    color: #6c757d;
}

/* Estado con progreso */
.estado-container {
    min-width: 120px;
}
.estado-badge {
    font-size: 0.7rem;
    padding: 0.3rem 0.6rem;
    border-radius: 4px;
}
.estado-progress {
    height: 3px;
    border-radius: 2px;
    margin-top: 4px;
}
.estado-detalle {
    font-size: 0.65rem;
    margin-top: 2px;
}

/* Area */
.area-nombre {
    font-size: 0.8rem;
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Fecha */
.fecha-registro {
    font-size: 0.75rem;
    color: #555;
}
.fecha-hora {
    font-size: 0.65rem;
    color: #888;
}

/* Acciones */
.btn-accion {
    padding: 0.25rem 0.4rem;
    font-size: 0.7rem;
    border-radius: 4px;
    margin: 1px;
}
.btn-accion i {
    font-size: 0.75rem;
}
.acciones-container {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
    justify-content: center;
}

/* Paginacion compacta */
.pagination {
    margin: 0.5rem 0;
}
.pagination .page-link {
    padding: 0.3rem 0.6rem;
    font-size: 0.8rem;
}

/* Responsive */
@media (max-width: 1200px) {
    .table-expedientes {
        font-size: 0.8rem;
    }
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0"><i class="fas fa-inbox text-primary"></i> Mesa de Partes - Gestion de Expedientes</h4>
            <small class="text-muted">Administracion y seguimiento de documentos</small>
        </div>
        <div class="btn-group">
            <a href="{{ route('mesa-partes.registrar') }}" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Registrar Documento
            </a>
            <a href="{{ route('mesa-partes.expedientes-virtuales') }}" class="btn btn-primary">
                <i class="fas fa-globe"></i> Virtuales ({{ $estadisticas['virtuales'] }})
            </a>
            <a href="{{ route('mesa-partes.monitoreo') }}" class="btn btn-warning">
                <i class="fas fa-clock"></i> Monitoreo
            </a>
        </div>
    </div>

    <!-- Estadisticas rapidas (compactas) -->
    <div class="d-flex flex-wrap gap-3 mb-3">
        <span class="stat-mini bg-primary text-white" onclick="filtrarPorEstado('todos')" role="button">
            <strong>{{ $estadisticas['total'] }}</strong> Total
        </span>
        <span class="stat-mini bg-warning text-dark" onclick="filtrarPorEstado('recepcionado')" role="button">
            <strong>{{ $estadisticas['pendientes'] }}</strong> Pendientes
        </span>
        <span class="stat-mini bg-info text-white" onclick="filtrarPorEstado('clasificado')" role="button">
            <strong>{{ $estadisticas['clasificados'] }}</strong> Clasificados
        </span>
        <span class="stat-mini bg-secondary text-white" onclick="filtrarPorEstado('derivado')" role="button">
            <strong>{{ $estadisticas['derivados'] }}</strong> Derivados
        </span>
        <span class="stat-mini bg-success text-white" onclick="filtrarPorEstado('en_proceso')" role="button">
            <strong>{{ $estadisticas['en_proceso'] }}</strong> En Proceso
        </span>
        <span class="stat-mini bg-dark text-white" onclick="filtrarPorCanal('virtual')" role="button">
            <strong>{{ $estadisticas['virtuales'] }}</strong> Virtuales
        </span>
    </div>

    <!-- Filtros -->
    <div class="card filter-card mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('mesa-partes.index') }}" id="filterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-1 small fw-bold">Buscar</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="busqueda" class="form-control"
                                   placeholder="Codigo, asunto, DNI, nombre..."
                                   value="{{ request('busqueda') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1 small fw-bold">Estado</label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">-- Activos --</option>
                            <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>Todos</option>
                            <option value="recepcionado" {{ request('estado') == 'recepcionado' ? 'selected' : '' }}>Recepcionado</option>
                            <option value="clasificado" {{ request('estado') == 'clasificado' ? 'selected' : '' }}>Clasificado</option>
                            <option value="derivado" {{ request('estado') == 'derivado' ? 'selected' : '' }}>Derivado</option>
                            <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                            <option value="resuelto" {{ request('estado') == 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                            <option value="archivado" {{ request('estado') == 'archivado' ? 'selected' : '' }}>Archivado</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label mb-1 small fw-bold">Canal</label>
                        <select name="canal" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="presencial" {{ request('canal') == 'presencial' ? 'selected' : '' }}>Presencial</option>
                            <option value="virtual" {{ request('canal') == 'virtual' ? 'selected' : '' }}>Virtual</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1 small fw-bold">Area</label>
                        <select name="area" class="form-select form-select-sm">
                            <option value="">Todas</option>
                            @foreach($areas as $area)
                            <option value="{{ $area->id_area }}" {{ request('area') == $area->id_area ? 'selected' : '' }}>
                                {{ Str::limit($area->nombre, 25) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1 small fw-bold">Fecha</label>
                        <div class="input-group input-group-sm">
                            <input type="date" name="fecha_desde" class="form-control"
                                   value="{{ request('fecha_desde') }}" title="Desde">
                            <input type="date" name="fecha_hasta" class="form-control"
                                   value="{{ request('fecha_hasta') }}" title="Hasta">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('mesa-partes.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de expedientes -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-expedientes mb-0">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Codigo</th>
                            <th style="width: 80px;">Canal</th>
                            <th style="width: 110px;">Dni</th>
                            <th style="width: 180px;">Solicitante</th>
                            <th style="width: 200px;">Asunto</th>
                            <th style="width: 120px;">Tipo Tramite</th>
                            <th style="width: 130px;">Estado</th>
                            <th style="width: 100px;">Area</th>
                            <th style="width: 80px;">Fecha</th>
                            <th style="width: 150px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expedientes as $expediente)
                        <tr>
                            <!-- Codigo -->
                            <td>
                                <span class="codigo-expediente">{{ $expediente->codigo_expediente }}</span>
                                @if($expediente->prioridad === 'urgente')
                                    <span class="badge bg-danger ms-1" title="Urgente"><i class="fas fa-exclamation"></i></span>
                                @elseif($expediente->prioridad === 'alta')
                                    <span class="badge bg-warning ms-1" title="Alta prioridad"><i class="fas fa-arrow-up"></i></span>
                                @endif
                            </td>

                            <!-- Canal -->
                            <td>
                                @if($expediente->canal == 'virtual')
                                    <span class="badge bg-light text-primary border border-primary">
                                        <i class="fas fa-globe"></i> Virtual
                                    </span>
                                @else
                                    <span class="badge bg-light text-secondary border border-secondary">
                                        <i class="fas fa-building"></i> Presencial
                                    </span>
                                @endif
                            </td>

                            <!-- Documento -->
                            <td class="documento-info">
                                @if($expediente->persona)
                                    <span class="badge documento-tipo bg-secondary">{{ $expediente->persona->tipo_documento }}</span>
                                    <div class="fw-bold">{{ $expediente->persona->numero_documento }}</div>
                                @else
                                    <span class="text-muted">{{ $expediente->dni_remitente ?? 'N/A' }}</span>
                                @endif
                            </td>

                            <!-- Solicitante -->
                            <td>
                                @if($expediente->persona)
                                    <div class="solicitante-nombre" title="{{ $expediente->persona->nombre_completo }}">
                                        {{ Str::limit($expediente->persona->nombre_completo, 25) }}
                                    </div>
                                    @if($expediente->persona->tipo_persona == 'JURIDICA')
                                        <div class="solicitante-detalle">
                                            <i class="fas fa-user-tie"></i> {{ Str::limit($expediente->persona->representante_legal, 20) }}
                                        </div>
                                    @endif
                                    @if($expediente->persona->telefono)
                                        <div class="solicitante-detalle">
                                            <i class="fas fa-phone"></i> {{ $expediente->persona->telefono }}
                                        </div>
                                    @endif
                                @else
                                    <div class="solicitante-nombre">{{ Str::limit($expediente->remitente ?? 'N/A', 25) }}</div>
                                @endif
                            </td>

                            <!-- Asunto -->
                            <td>
                                @php
                                    $asunto_mostrar = $expediente->asunto_documento ?? $expediente->asunto ?? 'Sin asunto';
                                @endphp
                                <span title="{{ $asunto_mostrar }}">
                                    {{ Str::limit($asunto_mostrar, 35) }}
                                </span>
                                @if($expediente->folios)
                                    <div class="solicitante-detalle">
                                        <i class="fas fa-file-alt"></i> {{ $expediente->folios }} folios
                                    </div>
                                @endif
                            </td>

                            <!-- Tipo Tramite -->
                            <td>
                                @if($expediente->tipoTramite)
                                    <span class="badge bg-info" title="{{ $expediente->tipoTramite->nombre }}">
                                        {{ Str::limit($expediente->tipoTramite->nombre, 18) }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark">Sin clasificar</span>
                                @endif
                            </td>

                            <!-- Estado -->
                            <td>
                                <div class="estado-container">
                                    @php
                                        $estadoInteligente = $expediente->estado_inteligente;
                                        $colorEstado = $expediente->getColorEstadoInteligente();
                                        $progreso = match($estadoInteligente) {
                                            'pendiente', 'recepcionado' => 15,
                                            'clasificado' => 35,
                                            'asignado', 'por_recibir' => 55,
                                            'derivado' => 65,
                                            'en_proceso' => 80,
                                            'resuelto', 'aprobado' => 100,
                                            'archivado' => 100,
                                            default => 10
                                        };
                                    @endphp
                                    <span class="badge estado-badge bg-{{ $colorEstado }}">
                                        {{ $expediente->getEstadoFormateadoInteligente() }}
                                    </span>
                                    <div class="progress estado-progress">
                                        <div class="progress-bar bg-{{ $colorEstado }}" style="width: {{ $progreso }}%"></div>
                                    </div>
                                    @if($estadoInteligente === 'por_recibir')
                                        <div class="estado-detalle text-muted"><i class="fas fa-clock"></i> Esperando</div>
                                    @elseif($expediente->funcionarioAsignado)
                                        <div class="estado-detalle text-muted">
                                            <i class="fas fa-user"></i> {{ Str::limit($expediente->funcionarioAsignado->name, 12) }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Area -->
                            <td>
                                @if($expediente->area)
                                    <span class="area-nombre" title="{{ $expediente->area->nombre }}">
                                        {{ Str::limit($expediente->area->nombre, 15) }}
                                    </span>
                                @else
                                    <span class="text-muted small">Sin asignar</span>
                                @endif
                            </td>

                            <!-- Fecha -->
                            <td>
                                <div class="fecha-registro">{{ $expediente->created_at->format('d/m/Y') }}</div>
                                <div class="fecha-hora">{{ $expediente->created_at->format('H:i') }}</div>
                            </td>

                            <!-- Acciones -->
                            <td>
                                <div class="acciones-container">
                                    <!-- Ver -->
                                    <a href="{{ route('mesa-partes.show', $expediente) }}"
                                       class="btn btn-primary btn-accion" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <!-- Imprimir Cargo -->
                                    <button type="button" class="btn btn-success btn-accion"
                                            title="Imprimir Cargo" onclick="abrirCargo('{{ route('mesa-partes.cargo', $expediente) }}')">
                                        <i class="fas fa-print"></i>
                                    </button>

                                    @php $estadoInteligente = $expediente->estado_inteligente; @endphp

                                    @if($estadoInteligente === 'recepcionado')
                                        @if($expediente->canal == 'virtual')
                                        <a href="{{ route('mesa-partes.clasificar-virtual', $expediente) }}"
                                           class="btn btn-info btn-accion" title="Clasificar Virtual">
                                            <i class="fas fa-share"></i>
                                        </a>
                                        @else
                                        <a href="{{ route('mesa-partes.clasificar', $expediente) }}"
                                           class="btn btn-warning btn-accion" title="Clasificar">
                                            <i class="fas fa-tags"></i>
                                        </a>
                                        @endif
                                    @endif

                                    @if($estadoInteligente === 'clasificado')
                                    <a href="{{ route('mesa-partes.derivar', $expediente) }}"
                                       class="btn btn-info btn-accion" title="Derivar">
                                        <i class="fas fa-share"></i>
                                    </a>
                                    @endif

                                    @if(in_array($estadoInteligente, ['asignado', 'por_recibir']))
                                    <a href="{{ route('mesa-partes.derivar', $expediente) }}"
                                       class="btn btn-secondary btn-accion" title="Reasignar">
                                        <i class="fas fa-exchange-alt"></i>
                                    </a>
                                    @endif

                                    @if($expediente->estado === 'resuelto')
                                    <button type="button" class="btn btn-dark btn-accion"
                                            title="Archivar" onclick="archivarExpediente({{ $expediente->id_expediente }})">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No se encontraron expedientes</p>
                                @if(request()->hasAny(['busqueda', 'estado', 'canal', 'area', 'fecha_desde', 'fecha_hasta']))
                                <a href="{{ route('mesa-partes.index') }}" class="btn btn-link">Limpiar filtros</a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($expedientes->hasPages())
            <div class="card-footer bg-light py-2">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted">
                            Mostrando {{ $expedientes->firstItem() }} - {{ $expedientes->lastItem() }} de {{ $expedientes->total() }} expedientes
                        </small>
                        <div class="d-flex align-items-center gap-1">
                            <label class="small text-muted mb-0">Mostrar:</label>
                            <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href = updateQueryString('per_page', this.value)">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>
                    {{ $expedientes->links() }}
                </div>
            </div>

            <script>
            function updateQueryString(key, value) {
                const url = new URL(window.location);
                url.searchParams.set(key, value);
                url.searchParams.set('page', 1); // Reset a la primera página
                return url.toString();
            }
            </script>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Funcion para filtrar por estado
function filtrarPorEstado(estado) {
    const form = document.getElementById('filterForm');
    const estadoSelect = form.querySelector('[name="estado"]');
    estadoSelect.value = estado === 'todos' ? 'todos' : estado;
    form.submit();
}

// Funcion para filtrar por canal
function filtrarPorCanal(canal) {
    const form = document.getElementById('filterForm');
    const canalSelect = form.querySelector('[name="canal"]');
    canalSelect.value = canal;
    form.submit();
}

// Funcion para abrir cargo en ventana popup
function abrirCargo(url) {
    const width = 600;
    const height = 700;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;

    window.open(
        url,
        'CargoPrint',
        `width=${width},height=${height},left=${left},top=${top},toolbar=no,menubar=no,scrollbars=yes,resizable=yes`
    );
}

// Funcion para archivar expediente
function archivarExpediente(expedienteId) {
    if (confirm('Esta seguro de archivar este expediente?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/mesa-partes/expedientes/${expedienteId}/archivar`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PUT';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
