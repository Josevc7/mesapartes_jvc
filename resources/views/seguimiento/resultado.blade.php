@extends('layouts.app')

@section('title', 'Resultado de Consulta')

@section('content')
<div class="container-fluid py-4">
    <!-- SECCIÓN 1: LISTADO DE EXPEDIENTES -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-folder-open me-2"></i>
                Expedientes Encontrados ({{ $expedientes->total() }})
            </h5>
            <a href="{{ route('seguimiento.form') }}" class="btn btn-light btn-sm">
                <i class="fas fa-search me-1"></i> Nueva Búsqueda
            </a>
        </div>
        <div class="card-body">
            <!-- Información de búsqueda -->
            <div class="alert alert-light border mb-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <small class="text-muted">
                            <i class="fas fa-filter me-1"></i>
                            Búsqueda: <strong>{{ $codigo_busqueda }}</strong> |
                            Documento: <strong>{{ $tipo_documento }} {{ $documento_busqueda }}</strong>
                        </small>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" id="busquedaRapida"
                               placeholder="Filtrar en resultados...">
                    </div>
                </div>
            </div>

            <!-- Tabla de expedientes -->
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle" id="tablaExpedientes">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 15%">Código</th>
                            <th style="width: 25%">Asunto</th>
                            <th style="width: 12%">Fecha Registro</th>
                            <th style="width: 12%">Estado</th>
                            <th style="width: 8%">Documento</th>
                            <th style="width: 12%">Fecha Envío</th>
                            <th style="width: 16%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expedientes as $expediente)
                        <tr class="expediente-row"
                            data-id="{{ $expediente->id_expediente }}"
                            data-codigo="{{ $expediente->codigo_expediente }}">
                            <td>
                                <strong class="text-primary">{{ $expediente->codigo_expediente }}</strong>
                            </td>
                            <td>
                                <span title="{{ $expediente->asunto }}">
                                    {{ Str::limit($expediente->asunto, 40) }}
                                </span>
                            </td>
                            <td>{{ $expediente->created_at->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $estadoClase = match($expediente->estado) {
                                        'recepcionado', 'registrado', 'clasificado' => 'info',
                                        'derivado' => 'primary',
                                        'en_proceso' => 'warning',
                                        'observado' => 'danger',
                                        'resuelto', 'notificado' => 'success',
                                        'archivado' => 'secondary',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $estadoClase }}">
                                    {{ strtoupper(str_replace('_', ' ', $expediente->estado)) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($expediente->documentos->where('tipo', 'entrada')->first())
                                    @php $docEntrada = $expediente->documentos->where('tipo', 'entrada')->first(); @endphp
                                    <a href="{{ route('documentos.visualizar', $docEntrada->id_documento) }}"
                                       class="btn btn-sm btn-outline-danger" target="_blank"
                                       title="Ver documento PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                {{ $expediente->fecha_registro ? $expediente->fecha_registro->format('d/m/Y') : $expediente->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-primary btn-ver-movimientos"
                                            data-id="{{ $expediente->id_expediente }}"
                                            data-codigo="{{ $expediente->codigo_expediente }}"
                                            title="Ver historial de movimientos">
                                        <i class="fas fa-history me-1"></i> Ver Movimientos
                                    </button>
                                    @auth
                                        @if($expediente->puedeEliminar(auth()->user()))
                                        <button type="button" class="btn btn-danger btn-eliminar"
                                                data-id="{{ $expediente->id_expediente }}"
                                                data-codigo="{{ $expediente->codigo_expediente }}"
                                                title="Eliminar expediente">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    @endauth
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No se encontraron expedientes</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">
                    Mostrando {{ $expedientes->firstItem() ?? 0 }} - {{ $expedientes->lastItem() ?? 0 }}
                    de {{ $expedientes->total() }} expedientes
                </small>
                {{ $expedientes->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: HISTORIAL DE MOVIMIENTOS (Carga dinámica) -->
    <div class="card shadow-sm" id="seccionMovimientos" style="display: none;">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-history me-2"></i>
                Historial de Movimientos - <span id="codigoExpedienteSeleccionado" class="text-warning"></span>
            </h5>
            <button type="button" class="btn btn-outline-light btn-sm" id="btnCerrarMovimientos">
                <i class="fas fa-times"></i> Cerrar
            </button>
        </div>
        <div class="card-body">
            <!-- Loader -->
            <div id="loaderMovimientos" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-muted">Cargando historial de movimientos...</p>
            </div>

            <!-- Error -->
            <div id="errorMovimientos" class="alert alert-danger" style="display: none;">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span id="mensajeError">Error al cargar los movimientos.</span>
            </div>

            <!-- Contenedor de información del expediente -->
            <div id="infoExpediente" style="display: none;">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <small class="text-muted">Asunto:</small>
                                <p class="mb-0 fw-bold" id="asuntoExpediente"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <small class="text-muted">Estado Actual:</small>
                                <p class="mb-0"><span id="estadoExpediente" class="badge"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <small class="text-muted">Área Actual:</small>
                                <p class="mb-0 fw-bold" id="areaExpediente"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenedor de movimientos -->
            <div id="contenedorMovimientos"></div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variable global para almacenar el documento de búsqueda
    const documentoBusqueda = '{{ $documento_busqueda }}';

    // ===================
    // FILTRO RÁPIDO EN TABLA
    // ===================
    const inputBusqueda = document.getElementById('busquedaRapida');
    if (inputBusqueda) {
        inputBusqueda.addEventListener('input', function(e) {
            const filtro = e.target.value.toLowerCase().trim();
            const filas = document.querySelectorAll('#tablaExpedientes tbody tr.expediente-row');

            filas.forEach(fila => {
                const texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(filtro) ? '' : 'none';
            });
        });
    }

    // ===================
    // VER MOVIMIENTOS
    // ===================
    document.querySelectorAll('.btn-ver-movimientos').forEach(btn => {
        btn.addEventListener('click', function() {
            const idExpediente = this.dataset.id;
            const codigo = this.dataset.codigo;
            cargarMovimientos(idExpediente, codigo);
        });
    });

    // Cerrar sección de movimientos
    document.getElementById('btnCerrarMovimientos')?.addEventListener('click', function() {
        document.getElementById('seccionMovimientos').style.display = 'none';
    });

    // ===================
    // FUNCIÓN CARGAR MOVIMIENTOS
    // ===================
    async function cargarMovimientos(idExpediente, codigo) {
        const seccion = document.getElementById('seccionMovimientos');
        const loader = document.getElementById('loaderMovimientos');
        const errorDiv = document.getElementById('errorMovimientos');
        const contenedor = document.getElementById('contenedorMovimientos');
        const infoExpediente = document.getElementById('infoExpediente');
        const tituloExpediente = document.getElementById('codigoExpedienteSeleccionado');

        // Mostrar sección y loader
        seccion.style.display = 'block';
        loader.style.display = 'block';
        errorDiv.style.display = 'none';
        contenedor.innerHTML = '';
        infoExpediente.style.display = 'none';
        tituloExpediente.textContent = codigo;

        // Scroll suave a la sección
        seccion.scrollIntoView({ behavior: 'smooth', block: 'start' });

        // Resaltar fila seleccionada
        document.querySelectorAll('.expediente-row').forEach(row => {
            row.classList.remove('table-active', 'border-primary');
        });
        document.querySelector(`tr[data-id="${idExpediente}"]`)?.classList.add('table-active', 'border-primary');

        try {
            const response = await fetch(
                `/api/seguimiento/${idExpediente}/movimientos?numero_documento=${encodeURIComponent(documentoBusqueda)}`,
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            if (!response.ok) {
                throw new Error(response.status === 404 ? 'Expediente no encontrado' : 'Error al cargar movimientos');
            }

            const data = await response.json();
            renderizarMovimientos(data);

        } catch (error) {
            errorDiv.style.display = 'block';
            document.getElementById('mensajeError').textContent = error.message || 'Error al cargar los movimientos. Intente nuevamente.';
        } finally {
            loader.style.display = 'none';
        }
    }

    // ===================
    // FUNCIÓN RENDERIZAR MOVIMIENTOS
    // ===================
    function renderizarMovimientos(data) {
        const contenedor = document.getElementById('contenedorMovimientos');
        const infoExpediente = document.getElementById('infoExpediente');

        // Mostrar información del expediente
        infoExpediente.style.display = 'block';
        document.getElementById('asuntoExpediente').textContent = data.expediente.asunto || 'Sin asunto';
        document.getElementById('areaExpediente').textContent = data.expediente.area_actual || 'N/A';

        const estadoBadge = document.getElementById('estadoExpediente');
        const estado = data.expediente.estado_actual || 'Pendiente';
        estadoBadge.textContent = estado.toUpperCase();
        estadoBadge.className = 'badge bg-' + getEstadoColor(estado.toLowerCase());

        if (!data.movimientos || data.movimientos.length === 0) {
            contenedor.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Este expediente aún no tiene movimientos/derivaciones registradas.
                </div>
            `;
            return;
        }

        // Construir tabla de movimientos
        let html = `
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-secondary">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 12%">Fecha/Hora</th>
                            <th style="width: 15%">Recepcionado por</th>
                            <th style="width: 15%">Área/Dependencia</th>
                            <th style="width: 10%">Documento</th>
                            <th style="width: 12%">Fecha Recepción</th>
                            <th style="width: 10%">Estado</th>
                            <th style="width: 21%">Indicaciones</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.movimientos.forEach((mov, index) => {
            const estadoClass = getEstadoMovimientoColor(mov.estado);

            html += `
                <tr>
                    <td class="text-center fw-bold">${index + 1}</td>
                    <td>
                        <small>${mov.fecha_movimiento}</small>
                    </td>
                    <td>
                        <i class="fas fa-user text-muted me-1"></i>
                        ${escapeHtml(mov.recepcionado_por)}
                    </td>
                    <td>
                        <strong>${escapeHtml(mov.area_destino)}</strong>
                        ${mov.area_origen !== 'N/A' ? `<br><small class="text-muted">desde: ${escapeHtml(mov.area_origen)}</small>` : ''}
                    </td>
                    <td class="text-center">
                        ${mov.documento
                            ? `<a href="/documentos/${mov.documento.id}/visualizar" class="btn btn-sm btn-outline-danger" target="_blank" title="${escapeHtml(mov.documento.nombre)}">
                                 <i class="fas fa-file-pdf"></i>
                               </a>`
                            : '<span class="text-muted">-</span>'}
                    </td>
                    <td>
                        <small>${mov.fecha_recepcion}</small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-${estadoClass}">${mov.estado}</span>
                    </td>
                    <td>
                        <small>${mov.observaciones !== '-' ? escapeHtml(mov.observaciones) : '<span class="text-muted">Sin indicaciones</span>'}</small>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>

            <div class="alert alert-secondary mt-3 mb-0">
                <div class="row">
                    <div class="col-md-6">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Total de movimientos:</strong> ${data.total_movimientos}
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted">
                            El estado del expediente corresponde al último movimiento registrado.
                        </small>
                    </div>
                </div>
            </div>
        `;

        contenedor.innerHTML = html;
    }

    // ===================
    // ELIMINAR EXPEDIENTE
    // ===================
    document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            const codigo = this.dataset.codigo;

            if (!confirm(`¿Está seguro de eliminar el expediente ${codigo}?\n\nEsta acción no se puede deshacer.`)) {
                return;
            }

            try {
                const response = await fetch(`${window.APP_URL}/seguimiento/expedientes/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.success);
                    location.reload();
                } else {
                    alert(data.error || 'Error al eliminar el expediente');
                }
            } catch (error) {
                alert('Error de conexión al eliminar el expediente');
            }
        });
    });

    // ===================
    // FUNCIONES AUXILIARES
    // ===================
    function getEstadoColor(estado) {
        const colores = {
            'recepcionado': 'info',
            'registrado': 'info',
            'clasificado': 'info',
            'derivado': 'primary',
            'en proceso': 'warning',
            'en_proceso': 'warning',
            'observado': 'danger',
            'resuelto': 'success',
            'notificado': 'success',
            'archivado': 'secondary'
        };
        return colores[estado] || 'secondary';
    }

    function getEstadoMovimientoColor(estado) {
        const colores = {
            'ATENDIDO': 'success',
            'RECIBIDO': 'success',
            'PENDIENTE': 'warning',
            'VENCIDO': 'danger',
            'ARCHIVADO': 'secondary'
        };
        return colores[estado] || 'secondary';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
@endsection

<style>
    .expediente-row {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .expediente-row:hover {
        background-color: #f8f9fa;
    }
    .expediente-row.table-active {
        background-color: #e3f2fd !important;
        border-left: 4px solid #0d6efd;
    }
    #seccionMovimientos {
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .btn-ver-movimientos {
        white-space: nowrap;
    }    
        #seccionMovimientos .card-header,
        #seccionMovimientos .card-header h5,
        #seccionMovimientos .card-header h5 i,
        #seccionMovimientos .card-header span,
        #seccionMovimientos .card-header button {
            color: #ffffff !important;
            opacity: 1 !important;
    }
</style>
@endsection
