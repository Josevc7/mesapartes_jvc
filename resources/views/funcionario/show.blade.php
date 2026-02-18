@extends('layouts.app')

@section('title', 'Detalle del Expediente')

@push('styles')
<style>
    .exp-header {
        background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        border-radius: 10px;
        color: #fff;
        padding: 1.2rem 1.5rem;
        margin-bottom: 1rem;
    }
    .exp-header .badge { font-size: 0.85rem; padding: 6px 12px; }
    .exp-header .codigo { font-size: 1.3rem; font-weight: 700; letter-spacing: 0.5px; }
    .exp-header .meta-item { font-size: 0.88rem; opacity: 0.9; }
    .exp-header .meta-item i { width: 18px; text-align: center; }

    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem 1.2rem; }
    .info-grid .info-item label { font-size: 0.75rem; text-transform: uppercase; color: #6c757d; font-weight: 600; margin-bottom: 0; letter-spacing: 0.3px; }
    .info-grid .info-item p { margin-bottom: 0; font-size: 0.92rem; color: #2c3e50; }
    .info-grid .info-full { grid-column: 1 / -1; }

    .section-card { border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 0.8rem; }
    .section-card .card-header { background: #f8f9fa; padding: 0.6rem 1rem; border-bottom: 1px solid #e9ecef; }
    .section-card .card-header h6 { margin: 0; font-size: 0.88rem; font-weight: 600; color: #495057; }
    .section-card .card-body { padding: 0.8rem 1rem; }

    .action-btn { border-radius: 6px; font-size: 0.85rem; padding: 8px 12px; font-weight: 500; }
    .action-btn i { width: 18px; text-align: center; }

    .doc-table th { font-size: 0.78rem; text-transform: uppercase; color: #6c757d; padding: 0.4rem 0.6rem; }
    .doc-table td { font-size: 0.88rem; padding: 0.4rem 0.6rem; vertical-align: middle; }

    .sidebar-actions .card-body { padding: 0.8rem; }
    .sidebar-actions .btn { margin-bottom: 0.4rem; }
    .sidebar-actions .btn:last-child { margin-bottom: 0; }

    @media (max-width: 768px) {
        .info-grid { grid-template-columns: 1fr; }
        .exp-header { padding: 1rem; }
        .exp-header .codigo { font-size: 1.1rem; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3">
    {{-- Header con código, estado y botón volver --}}
    <div class="exp-header d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="codigo">{{ $expediente->codigo_expediente }}</span>
                @php
                    $badgeClass = match($expediente->estado) {
                        'asignado' => 'primary',
                        'en_proceso' => 'info',
                        'en_revision' => 'warning',
                        'devuelto_jefe' => 'warning',
                        default => 'secondary'
                    };
                @endphp
                <span class="badge bg-{{ $badgeClass }}">{{ $expediente->getEstadoFormateado() }}</span>
            </div>
            <div class="d-flex flex-wrap gap-3">
                <span class="meta-item"><i class="fas fa-user me-1"></i>{{ $expediente->remitente ?? $expediente->ciudadano->name ?? 'N/A' }}</span>
                <span class="meta-item"><i class="fas fa-calendar me-1"></i>{{ $expediente->created_at->format('d/m/Y H:i') }}</span>
                @if($expediente->tipoTramite)
                <span class="meta-item"><i class="fas fa-tag me-1"></i>{{ $expediente->tipoTramite->nombre }}</span>
                @endif
            </div>
        </div>
        <a href="{{ route('funcionario.dashboard') }}" class="btn btn-outline-light btn-sm mt-2 mt-md-0">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>

    <div class="row g-2">
        {{-- Columna principal --}}
        <div class="col-lg-8">
            {{-- Información del Expediente --}}
            <div class="section-card">
                <div class="card-header">
                    <h6><i class="fas fa-file-alt me-2"></i>Información del Expediente</h6>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Remitente</label>
                            <p>{{ $expediente->remitente ?? $expediente->ciudadano->name ?? 'N/A' }}</p>
                        </div>
                        <div class="info-item">
                            <label>Fecha de Registro</label>
                            <p>{{ $expediente->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($expediente->tipoTramite)
                        <div class="info-item">
                            <label>Tipo de Trámite</label>
                            <p>{{ $expediente->tipoTramite->nombre }}</p>
                        </div>
                        @endif
                        @if($expediente->area)
                        <div class="info-item">
                            <label>Área</label>
                            <p>{{ $expediente->area->nombre }}</p>
                        </div>
                        @endif
                        <div class="info-item info-full">
                            <label>Asunto</label>
                            <p>{{ $expediente->asunto }}</p>
                        </div>
                        @if($expediente->observaciones)
                        <div class="info-item info-full">
                            <label>Observaciones Iniciales</label>
                            <p>{{ $expediente->observaciones }}</p>
                        </div>
                        @endif
                        @if($expediente->observaciones_funcionario)
                        <div class="info-item info-full">
                            <label>Observaciones del Funcionario</label>
                            <p>{{ $expediente->observaciones_funcionario }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Documentos --}}
            <div class="section-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6><i class="fas fa-paperclip me-2"></i>Documentos ({{ $expediente->documentos->count() }})</h6>
                </div>
                <div class="card-body">
                    @if($expediente->documentos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm doc-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expediente->documentos as $documento)
                                    <tr>
                                        <td><i class="fas fa-file-pdf text-danger me-1"></i>{{ $documento->nombre }}</td>
                                        <td><span class="badge bg-light text-dark">{{ ucfirst($documento->tipo) }}</span></td>
                                        <td>{{ $documento->created_at->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            <a href="{{ Storage::url($documento->ruta_pdf) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0 small"><i class="fas fa-info-circle me-1"></i>No hay documentos adjuntos</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Columna lateral - Acciones --}}
        <div class="col-lg-4">
            @if($expediente->estado == 'asignado')
            <div class="section-card border-info">
                <div class="card-header" style="background: #d1ecf1;">
                    <h6 class="text-info"><i class="fas fa-inbox me-2"></i>Expediente Asignado</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2"><i class="fas fa-info-circle me-1"></i>El Jefe de Área le ha asignado este expediente.</p>
                    <form method="POST" action="{{ route('funcionario.recibir', $expediente) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-success w-100 action-btn">
                            <i class="fas fa-check-circle me-1"></i> Recibir y Procesar
                        </button>
                    </form>
                </div>
            </div>
            @endif

            @if($expediente->estado == 'en_proceso')
            <div class="section-card sidebar-actions">
                <div class="card-header">
                    <h6><i class="fas fa-bolt me-2"></i>Acciones</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('funcionario.procesar', $expediente) }}" class="btn btn-primary w-100 action-btn">
                        <i class="fas fa-edit me-1"></i> Procesar Expediente
                    </a>

                    @php
                        $tieneDocumentoAdjunto = $expediente->documentos()
                            ->whereIn('tipo', ['informe', 'respuesta', 'resolucion', 'oficio'])
                            ->exists();
                    @endphp

                    <button type="button" class="btn btn-success w-100 action-btn" data-bs-toggle="modal" data-bs-target="#resolverModal">
                        <i class="fas fa-paper-plane me-1"></i> Devolver al Jefe
                    </button>

                    @if(!$tieneDocumentoAdjunto)
                    <div class="alert alert-warning py-1 px-2 small mb-1">
                        <i class="fas fa-exclamation-triangle me-1"></i>Adjunte un documento antes de devolver.
                    </div>
                    @endif

                    <button type="button" class="btn btn-outline-warning w-100 action-btn" data-bs-toggle="modal" data-bs-target="#devolverJefeModal">
                        <i class="fas fa-undo-alt me-1"></i> Devolver (sin resolver)
                    </button>
                    <button type="button" class="btn btn-warning w-100 action-btn" data-bs-toggle="modal" data-bs-target="#solicitarInfoModal">
                        <i class="fas fa-question-circle me-1"></i> Solicitar Info
                    </button>
                    <a href="{{ route('funcionario.historial', $expediente) }}" class="btn btn-outline-info w-100 action-btn">
                        <i class="fas fa-history me-1"></i> Ver Historial
                    </a>
                    <a href="{{ route('funcionario.documentos', $expediente) }}" class="btn btn-outline-secondary w-100 action-btn">
                        <i class="fas fa-folder-open me-1"></i> Gestionar Documentos
                    </a>
                </div>
            </div>

            {{-- Adjuntar Documento --}}
            <div class="section-card">
                <div class="card-header">
                    <h6><i class="fas fa-upload me-2"></i>Adjuntar Documento</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('funcionario.adjuntar-documento', $expediente) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm" name="nombre" placeholder="Nombre del documento" required>
                        </div>
                        <div class="mb-2">
                            <select class="form-select form-select-sm" name="tipo" required>
                                <option value="">Tipo de documento</option>
                                <option value="informe">Informe</option>
                                <option value="respuesta">Respuesta</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <input type="file" class="form-control form-control-sm" name="documento" accept=".pdf,.doc,.docx" required>
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100 action-btn">
                            <i class="fas fa-upload me-1"></i>Adjuntar
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Devolver al Jefe --}}
<div class="modal fade" id="resolverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title mb-0"><i class="fas fa-paper-plane me-2"></i>Devolver al Jefe de Área</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('funcionario.enviar-revision', $expediente) }}">
                @csrf
                @method('PUT')
                <div class="modal-body py-2">
                    <p class="small text-muted mb-2">
                        <i class="fas fa-info-circle me-1"></i>El expediente será enviado al <strong>Jefe de Área</strong> para su revisión.
                    </p>

                    @php
                        $docsAdjuntos = $expediente->documentos()
                            ->whereIn('tipo', ['informe', 'respuesta', 'resolucion', 'oficio'])
                            ->get();
                    @endphp

                    @if($docsAdjuntos->count() > 0)
                    <p class="small fw-bold mb-1">Documentos adjuntos:</p>
                    <ul class="list-group list-group-flush mb-2">
                        @foreach($docsAdjuntos as $doc)
                        <li class="list-group-item py-1 px-2 small">
                            <i class="fas fa-file-pdf text-danger me-1"></i>
                            {{ $doc->nombre }} <span class="badge bg-secondary">{{ ucfirst($doc->tipo) }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="alert alert-danger py-2 small mb-2">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Sin documentos.</strong> Adjunte al menos un documento antes de devolver.
                    </div>
                    @endif
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success" {{ $docsAdjuntos->count() === 0 ? 'disabled' : '' }}>
                        <i class="fas fa-paper-plane me-1"></i> Devolver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Devolver al Jefe (sin documento) --}}
<div class="modal fade" id="devolverJefeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark py-2">
                <h6 class="modal-title mb-0"><i class="fas fa-undo-alt me-2"></i>Devolver al Jefe de Área</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('funcionario.devolver-jefe', $expediente) }}">
                @csrf
                @method('PUT')
                <div class="modal-body py-2">
                    <p class="small text-muted mb-2">
                        <i class="fas fa-info-circle me-1"></i>Use esta opción cuando <strong>no pueda continuar</strong> y necesite la intervención del Jefe.
                    </p>
                    <div class="mb-2">
                        <label class="form-label small fw-bold mb-1">Motivo de la devolución *</label>
                        <select class="form-select form-select-sm" name="motivo_devolucion" required>
                            <option value="">-- Seleccione motivo --</option>
                            <option value="falta_informacion">Falta información o documentación</option>
                            <option value="error_asignacion">Error en la asignación</option>
                            <option value="caso_complejo">Caso complejo que requiere decisión del Jefe</option>
                            <option value="ampliacion_plazo">Se requiere ampliación de plazo</option>
                            <option value="otro">Otro motivo</option>
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label small fw-bold mb-1">Detalle / Observación técnica *</label>
                        <textarea class="form-control form-control-sm" name="observaciones_devolucion" rows="3" required minlength="10"
                                  placeholder="Explique detalladamente por qué devuelve el expediente..."></textarea>
                        <div class="form-text small">Mínimo 10 caracteres</div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-warning">
                        <i class="fas fa-undo-alt me-1"></i> Devolver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Solicitar Info --}}
<div class="modal fade" id="solicitarInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0"><i class="fas fa-question-circle me-2"></i>Solicitar Información</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('funcionario.solicitar-info', $expediente) }}">
                @csrf
                <div class="modal-body py-2">
                    <div class="mb-2">
                        <label class="form-label small fw-bold mb-1">Observaciones *</label>
                        <textarea class="form-control form-control-sm" name="observaciones" rows="3" required></textarea>
                    </div>
                    <div class="mb-1">
                        <label class="form-label small fw-bold mb-1">Plazo de Respuesta (días) *</label>
                        <input type="number" class="form-control form-control-sm" name="plazo_respuesta" min="1" max="30" value="15" required>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-warning">
                        <i class="fas fa-paper-plane me-1"></i> Solicitar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection