@extends('layouts.app')

@section('title', 'Mis Notificaciones')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Mis Notificaciones</h3>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" onclick="marcarTodasLeidas()">
                            <i class="fas fa-check-double"></i> Marcar todas como leídas
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($notificaciones->count() > 0)
                        <div class="list-group">
                            @foreach($notificaciones as $notificacion)
                            <div class="list-group-item {{ $notificacion->leida ? '' : 'list-group-item-info' }}">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        @if(!$notificacion->leida)
                                            <i class="fas fa-circle text-primary" style="font-size: 8px;"></i>
                                        @endif
                                        {{ $notificacion->titulo }}
                                    </h6>
                                    <small class="text-muted">{{ $notificacion->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1">{{ $notificacion->mensaje }}</p>
                                @if($notificacion->expediente_codigo)
                                <small class="text-muted">
                                    Expediente: 
                                    <a href="{{ route('ciudadano.seguimiento', $notificacion->expediente_codigo) }}" 
                                       class="text-decoration-none">
                                        {{ $notificacion->expediente_codigo }}
                                    </a>
                                </small>
                                @endif
                                @if(!$notificacion->leida)
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-secondary" 
                                            onclick="marcarLeida({{ $notificacion->id }})">
                                        <i class="fas fa-check"></i> Marcar como leída
                                    </button>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-3">
                            {{ $notificaciones->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No tienes notificaciones</h5>
                            <p class="text-muted">Aquí aparecerán las actualizaciones de tus expedientes</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function marcarLeida(id) {
    fetch(`${window.APP_URL}/ciudadano/notificaciones/${id}/marcar-leida`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    }).then(() => location.reload());
}

function marcarTodasLeidas() {
    fetch('/ciudadano/notificaciones/marcar-todas-leidas', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    }).then(() => location.reload());
}
</script>
@endsection