@extends('layouts.app')

@section('title', 'Expedientes Virtuales Pendientes')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>
                        <i class="fas fa-globe text-primary me-2"></i>
                        Expedientes Virtuales Pendientes
                    </h2>
                    <p class="text-muted mb-0">Expedientes ingresados por ciudadanos. Primero debe <strong>recepcionar</strong>, luego podrá <strong>clasificar y derivar</strong>.</p>
                </div>
                <div>
                    <a href="{{ route('mesa-partes.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($expedientes->count() > 0)
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ $expedientes->total() }} Expediente(s) Virtual(es) Pendiente(s)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Fecha Registro</th>
                                    <th>Ciudadano</th>
                                    <th>Tipo Trámite</th>
                                    <th>Asunto</th>
                                    <th>Documentos</th>
                                    <th>Estado</th>
                                    <th width="280">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expedientes as $expediente)
                                <tr>
                                    <td>
                                        <strong class="text-primary">{{ $expediente->codigo_expediente }}</strong>
                                        <br>
                                        <span class="badge bg-info">
                                            <i class="fas fa-globe"></i> Virtual
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $expediente->created_at->format('d/m/Y') }}<br>
                                            {{ $expediente->created_at->format('H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($expediente->persona)
                                            <strong>{{ $expediente->persona->nombre_completo }}</strong><br>
                                            <small class="text-muted">
                                                {{ $expediente->persona->tipo_documento }}: {{ $expediente->persona->numero_documento }}
                                            </small>
                                        @else
                                            {{ $expediente->remitente ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $expediente->tipoTramite->nombre ?? 'Sin tipo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span title="{{ $expediente->asunto }}">
                                            {{ Str::limit($expediente->asunto, 40) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">
                                            <i class="fas fa-file-pdf"></i> {{ $expediente->documentos->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($expediente->estadoExpediente?->slug === 'pendiente_recepcion')
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-hourglass-half"></i> Pendiente de Recepción
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-inbox"></i> Recepcionado
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('mesa-partes.show', $expediente) }}"
                                               class="btn btn-outline-info"
                                               title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if($expediente->estadoExpediente?->slug === 'pendiente_recepcion')
                                                {{-- PASO 1: Recepcionar (obligatorio antes de clasificar) --}}
                                                <form action="{{ route('mesa-partes.recepcionar-virtual', $expediente) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Confirma la recepción de este expediente virtual?\n\nAl recepcionar, usted valida que:\n- Los documentos adjuntos son legibles\n- El formato es correcto\n- Los datos están completos\n- Se cumplen los requisitos mínimos')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning" title="Recepcionar expediente">
                                                        <i class="fas fa-check-circle"></i> Recepcionar
                                                    </button>
                                                </form>
                                            @else
                                                {{-- PASO 2: Clasificar y Derivar (solo después de recepcionar) --}}
                                                <a href="{{ route('mesa-partes.clasificar-virtual', $expediente) }}"
                                                   class="btn btn-success"
                                                   title="Clasificar y Derivar">
                                                    <i class="fas fa-share"></i> Clasificar y Derivar
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $expedientes->links() }}
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                    <h4>No hay expedientes virtuales pendientes</h4>
                    <p class="text-muted">Todos los expedientes ingresados por ciudadanos han sido procesados</p>
                    <a href="{{ route('mesa-partes.dashboard') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-home me-2"></i>
                        Ir al Dashboard
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de confirmación de clasificación exitosa -->
@if(session('id_expediente') && session('codigo_expediente'))
    <div class="modal fade" id="modalCargoExitoso" tabindex="-1" aria-labelledby="modalCargoExitosoLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title" id="modalCargoExitosoLabel">
                        <i class="fas fa-check-circle me-2"></i>¡Clasificación Exitosa!
                    </h5>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-file-alt fa-3x text-success"></i>
                        </div>
                        <h4 class="mb-3">Expediente Clasificado y Derivado</h4>
                        <div class="alert alert-info border-0 mb-3">
                            <p class="mb-1"><strong>Código de Expediente:</strong></p>
                            <h3 class="text-primary mb-0">{{ session('codigo_expediente') }}</h3>
                        </div>
                        <p class="text-muted mb-0">El expediente virtual ha sido clasificado y derivado exitosamente.</p>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-success btn-lg px-4" onclick="imprimirCargoVirtual()">
                        <i class="fas fa-print me-2"></i>Imprimir Cargo
                    </button>
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Script para mostrar modal de confirmación y función de impresión -->
@if(session('id_expediente'))
    <script>
        // Mostrar modal automáticamente al cargar la página
        window.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalCargoExitoso'));
            modal.show();
        });

        // Función para imprimir cargo
        function imprimirCargoVirtual() {
            const cargoUrl = "{{ route('mesa-partes.cargo', session('id_expediente')) }}";
            const width = 900;
            const height = 700;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;

            window.open(
                cargoUrl,
                'CargoPrint',
                `width=${width},height=${height},left=${left},top=${top},toolbar=no,menubar=no,scrollbars=yes,resizable=yes`
            );

            // Cerrar el modal después de abrir la ventana de impresión
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalCargoExitoso'));
            if (modal) {
                modal.hide();
            }
        }
    </script>
@endif
@endsection
