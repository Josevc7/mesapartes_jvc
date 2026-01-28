{{-- Partial recursivo para mostrar área y sus sub-áreas --}}
@php
    $nivelClass = match($area->nivel) {
        'DIRECCION_REGIONAL' => 'nivel-direccion-regional',
        'OCI' => 'nivel-oci',
        'DIRECCION' => 'nivel-direccion',
        'SUBDIRECCION' => 'nivel-subdireccion',
        'RESIDENCIA' => 'nivel-residencia',
        default => ''
    };
    $tieneSubAreas = $area->subAreas->count() > 0;
@endphp

<li>
    <div class="area-item {{ $nivelClass }} {{ !$area->activo ? 'inactiva' : '' }}">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center flex-grow-1">
                @if($tieneSubAreas)
                    <i class="fas fa-chevron-down toggle-subareas me-2"
                       id="toggle-icon-{{ $area->id_area }}"
                       onclick="toggleSubAreas({{ $area->id_area }})"></i>
                @else
                    <i class="fas fa-circle me-2" style="font-size: 0.4rem; opacity: 0.5;"></i>
                @endif

                <div>
                    <strong>{{ $area->nombre }}</strong>
                    @if(!$area->activo)
                        <span class="badge bg-danger ms-2">Inactiva</span>
                    @endif

                    <div class="mt-1">
                        <small class="{{ in_array($area->nivel, ['DIRECCION_REGIONAL', 'OCI', 'DIRECCION']) ? 'text-white-50' : 'text-muted' }}">
                            @if($area->jefe)
                                <i class="fas fa-user-tie me-1"></i>{{ $area->jefe->name }}
                            @else
                                <i class="fas fa-user-slash me-1"></i>Sin jefe asignado
                            @endif

                            @if($area->funcionarios->count() > 0)
                                <span class="ms-3">
                                    <i class="fas fa-users me-1"></i>{{ $area->funcionarios->count() }} funcionario(s)
                                </span>
                            @endif

                            @if($tieneSubAreas)
                                <span class="ms-3">
                                    <i class="fas fa-sitemap me-1"></i>{{ $area->subAreas->count() }} sub-área(s)
                                </span>
                            @endif
                        </small>
                    </div>
                </div>
            </div>

            <div class="btn-group btn-group-sm">
                <button class="btn btn-sm {{ in_array($area->nivel, ['DIRECCION_REGIONAL', 'OCI', 'DIRECCION']) ? 'btn-light' : 'btn-outline-primary' }}"
                        onclick="agregarSubArea({{ $area->id_area }}, '{{ addslashes($area->nombre) }}')"
                        title="Agregar sub-área">
                    <i class="fas fa-plus"></i>
                </button>
                <button class="btn btn-sm {{ in_array($area->nivel, ['DIRECCION_REGIONAL', 'OCI', 'DIRECCION']) ? 'btn-light' : 'btn-outline-warning' }}"
                        onclick="editarArea({{ $area->id_area }})"
                        title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm {{ in_array($area->nivel, ['DIRECCION_REGIONAL', 'OCI', 'DIRECCION']) ? 'btn-light' : 'btn-outline-secondary' }}"
                        onclick="toggleArea({{ $area->id_area }})"
                        title="{{ $area->activo ? 'Desactivar' : 'Activar' }}">
                    <i class="fas fa-{{ $area->activo ? 'ban' : 'check' }}"></i>
                </button>
                <button class="btn btn-sm {{ in_array($area->nivel, ['DIRECCION_REGIONAL', 'OCI', 'DIRECCION']) ? 'btn-light text-danger' : 'btn-outline-danger' }}"
                        onclick="eliminarArea({{ $area->id_area }}, '{{ addslashes($area->nombre) }}')"
                        title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>

    @if($tieneSubAreas)
        <ul class="area-tree" id="subareas-{{ $area->id_area }}">
            @foreach($area->subAreas as $subArea)
                @include('admin.areas._area-item', ['area' => $subArea, 'nivel' => $nivel + 1])
            @endforeach
        </ul>
    @endif
</li>
