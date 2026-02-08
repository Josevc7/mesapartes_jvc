<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mesa de Partes-DRTC')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/modern-style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/adaptive-forms.css') }}" rel="stylesheet">
    <link href="{{ asset('css/logo-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('css/responsive.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <!-- Top Header -->
    <nav class="navbar navbar-dark fixed-top top-navbar">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                @auth
                <button class="btn btn-link text-white d-lg-none me-2 sidebar-toggle" type="button" id="sidebarToggle" aria-label="Abrir menú">
                    <i class="fas fa-bars fa-lg"></i>
                </button>
                @endauth
                <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                    <img src="{{ asset('images/logo1.jpg') }}" alt="Logo DRTC">
                    <span class="fw-bold navbar-title">Mesa de Partes DRTC</span>
                </a>
            </div>

            <div class="d-flex align-items-center">
                @guest
                    <a class="nav-link text-white me-3" href="{{ route('seguimiento.form') }}">Consultar Expediente</a>
                @else
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                            <i class="fas fa-user-circle d-sm-none"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('perfil.show') }}"><i class="fas fa-user"></i> Mi Perfil</a></li>
                            <li><a class="dropdown-item" href="#" onclick="document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                        </ul>
                    </div>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                @endguest
            </div>
        </div>
    </nav>

    <!-- Overlay para cerrar sidebar en móvil -->
    @auth
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    @endauth

    <div class="d-flex app-wrapper">
        <!-- Sidebar -->
        @auth
        <nav class="sidebar" id="sidebar">
            <div class="p-3">
                @if(auth()->user()->role->nombre == 'Ciudadano')
                    <h6 class="text-muted mb-3">MESA DE PARTES VIRTUAL</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('ciudadano.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('ciudadano.registrar-expediente') }}"><i class="fas fa-plus"></i> Nuevo Expediente</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('ciudadano.mis-expedientes') }}"><i class="fas fa-folder"></i> Mis Expedientes</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('panel.seguimiento.index') }}"><i class="fas fa-search-location"></i> Seguimiento</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('ciudadano.notificaciones') }}"><i class="fas fa-bell"></i> Notificaciones</a>
                        </li>
                    </ul>
                @endif

                @if(auth()->user()->role->nombre == 'Administrador')
                    <!-- Mesa de Partes Virtual -->
                    <div class="mb-2">
                        <a class="nav-link text-dark fw-bold" data-bs-toggle="collapse" href="#menuVirtual" role="button">
                            <i class="fas fa-desktop"></i> Mesa de Partes Virtual <i class="fas fa-chevron-down float-end"></i>
                        </a>
                        <div class="collapse" id="menuVirtual">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('ciudadano.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('ciudadano.registrar-expediente') }}"><i class="fas fa-plus"></i> Nuevo Expediente</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('ciudadano.mis-expedientes') }}"><i class="fas fa-folder"></i> Mis Expedientes</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('ciudadano.notificaciones') }}"><i class="fas fa-bell"></i> Notificaciones</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Mesa de Partes -->
                    <div class="mb-2">
                        <a class="nav-link text-dark fw-bold" data-bs-toggle="collapse" href="#menuMesaPartes" role="button">
                            <i class="fas fa-inbox"></i> Mesa de Partes <i class="fas fa-chevron-down float-end"></i>
                        </a>
                        <div class="collapse" id="menuMesaPartes">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('mesa-partes.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('mesa-partes.index') }}"><i class="fas fa-folder"></i> Expedientes</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('mesa-partes.expedientes-virtuales') }}">
                                        <i class="fas fa-globe"></i> Expedientes Virtuales
                                        @php
                                            $pendientesVirtuales = \App\Models\Expediente::where('canal', 'virtual')->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'recepcionado'))->count();
                                        @endphp
                                        @if($pendientesVirtuales > 0)
                                            <span class="badge bg-danger ms-1">{{ $pendientesVirtuales }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('mesa-partes.registrar') }}"><i class="fas fa-plus"></i> Registrar Expediente</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('panel.seguimiento.index') }}"><i class="fas fa-search-location"></i> Seguimiento</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('mesa-partes.monitoreo') }}"><i class="fas fa-monitor"></i> Monitoreo</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('mesa-partes.estadisticas') }}"><i class="fas fa-chart-bar"></i> Estadísticas</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('mesa-partes.numeracion') }}"><i class="fas fa-hashtag"></i> Numeración</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Supervisión -->
                    <div class="mb-2">
                        <a class="nav-link text-dark fw-bold" data-bs-toggle="collapse" href="#menuSupervision" role="button">
                            <i class="fas fa-eye"></i> Jefe de Area <i class="fas fa-chevron-down float-end"></i>
                        </a>
                        <div class="collapse" id="menuSupervision">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('jefe-area.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('jefe-area.expedientes') }}"><i class="fas fa-folder"></i> Expedientes</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('jefe-area.validar-documentos') }}"><i class="fas fa-check"></i> Validar Documentos</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('jefe-area.conflictos') }}"><i class="fas fa-exclamation-triangle"></i> Resolver Conflictos</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('jefe-area.control-plazos') }}"><i class="fas fa-clock"></i> Control de Plazos</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('jefe-area.supervision') }}"><i class="fas fa-eye"></i> Supervisión</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('jefe-area.metas') }}"><i class="fas fa-target"></i> Metas y KPIs</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('jefe-area.reportes') }}"><i class="fas fa-file-alt"></i> Reportes</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('resoluciones.index') }}"><i class="fas fa-gavel"></i> Resoluciones</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Mis Expedientes -->
                    <div class="mb-2">
                        <a class="nav-link text-dark fw-bold" data-bs-toggle="collapse" href="#menuExpedientes" role="button">
                            <i class="fas fa-user-check"></i> Funcionario<i class="fas fa-chevron-down float-end"></i>
                        </a>
                        <div class="collapse" id="menuExpedientes">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('funcionario.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('funcionario.index') }}"><i class="fas fa-folder-open"></i> Todos</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('funcionario.mis-expedientes') }}"><i class="fas fa-user-check"></i> Mis Asignados</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('resoluciones.index') }}"><i class="fas fa-gavel"></i> Resoluciones</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Soporte -->
                    <div class="mb-2">
                        <a class="nav-link text-dark fw-bold" data-bs-toggle="collapse" href="#menuSoporte" role="button">
                            <i class="fas fa-tools"></i> Soporte <i class="fas fa-chevron-down float-end"></i>
                        </a>
                        <div class="collapse" id="menuSoporte">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('soporte.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('soporte.usuarios') }}"><i class="fas fa-users"></i> Usuarios</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('soporte.logs') }}"><i class="fas fa-list"></i> Logs</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="/soporte/respaldo"><i class="fas fa-database"></i> Respaldo</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="/soporte/monitoreo"><i class="fas fa-desktop"></i> Monitoreo</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Administración -->
                    <div class="mb-2">
                        <a class="nav-link text-dark fw-bold" data-bs-toggle="collapse" href="#menuAdmin" role="button">
                            <i class="fas fa-cogs"></i> Administración <i class="fas fa-chevron-down float-end"></i>
                        </a>
                        <div class="collapse" id="menuAdmin">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard Admin</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('admin.usuarios') }}"><i class="fas fa-users"></i> Usuarios</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('admin.roles') }}"><i class="fas fa-user-tag"></i> Roles</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('admin.personas') }}"><i class="fas fa-address-book"></i> Personas</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('admin.areas') }}"><i class="fas fa-building"></i> Áreas</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('admin.tipo-tramites') }}"><i class="fas fa-tags"></i> Tipos de Trámite</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('admin.configuraciones') }}"><i class="fas fa-cog"></i> Configuraciones</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="/admin/auditoria"><i class="fas fa-search"></i> Auditoría</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('admin.logs') }}"><i class="fas fa-list-alt"></i> Logs Sistema</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('admin.estadisticas') }}"><i class="fas fa-chart-bar"></i> Estadísticas</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('reportes.index') }}"><i class="fas fa-chart-line"></i> Reportes</a>
                                </li>
                                <li class="nav-item mb-1">
                                    <a class="nav-link text-dark" href="{{ route('admin.matriz-control') }}"><i class="fas fa-table"></i> Matriz de Control</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif

                @if(in_array(auth()->user()->role->nombre, ['Mesa de Partes']) && auth()->user()->role->nombre != 'Administrador')
                    <h6 class="text-muted mb-3">MESA DE PARTES</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('mesa-partes.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('mesa-partes.index') }}"><i class="fas fa-folder"></i> Expedientes</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('mesa-partes.expedientes-virtuales') }}">
                                <i class="fas fa-globe"></i> Expedientes Virtuales
                                @php
                                    $pendientesVirtuales = \App\Models\Expediente::where('canal', 'virtual')->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'recepcionado'))->count();
                                @endphp
                                @if($pendientesVirtuales > 0)
                                    <span class="badge bg-danger ms-1">{{ $pendientesVirtuales }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('mesa-partes.registrar') }}"><i class="fas fa-plus"></i> Registrar</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('panel.seguimiento.index') }}"><i class="fas fa-search-location"></i> Seguimiento</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('mesa-partes.monitoreo') }}"><i class="fas fa-monitor"></i> Monitoreo</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('mesa-partes.estadisticas') }}"><i class="fas fa-chart-bar"></i> Estadísticas</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('mesa-partes.numeracion') }}"><i class="fas fa-hashtag"></i> Numeración</a>
                        </li>
                    </ul>
                @endif

                @if(in_array(auth()->user()->role->nombre, ['Jefe de Área']) && auth()->user()->role->nombre != 'Administrador')
                    <h6 class="text-muted mb-3 mt-4">SUPERVISIÓN JEFE DE AREA</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('jefe-area.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('jefe-area.expedientes') }}"><i class="fas fa-folder"></i> Expedientes</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('jefe-area.validar-documentos') }}"><i class="fas fa-check"></i> Validar Documentos</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('jefe-area.conflictos') }}"><i class="fas fa-exclamation-triangle"></i> Resolver Conflictos</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('jefe-area.control-plazos') }}"><i class="fas fa-clock"></i> Control de Plazos</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('jefe-area.supervision') }}"><i class="fas fa-eye"></i> Supervisión</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('jefe-area.metas') }}"><i class="fas fa-target"></i> Metas y KPIs</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('jefe-area.reportes') }}"><i class="fas fa-file-alt"></i> Reportes</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('panel.seguimiento.index') }}"><i class="fas fa-search-location"></i> Seguimiento</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('resoluciones.index') }}"><i class="fas fa-gavel"></i> Resoluciones</a>
                        </li>
                    </ul>
                @endif

                @if(in_array(auth()->user()->role->nombre, ['Funcionario']) && auth()->user()->role->nombre != 'Administrador')
                    <h6 class="text-muted mb-3 mt-4">MIS EXPEDIENTES</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('funcionario.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('funcionario.index') }}"><i class="fas fa-folder-open"></i> Todos</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('funcionario.mis-expedientes') }}"><i class="fas fa-user-check"></i> Mis Asignados</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('panel.seguimiento.index') }}"><i class="fas fa-search-location"></i> Seguimiento</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('resoluciones.index') }}"><i class="fas fa-gavel"></i> Resoluciones</a>
                        </li>
                    </ul>
                @endif

                @if(in_array(auth()->user()->role->nombre, ['Soporte']) && auth()->user()->role->nombre != 'Administrador')
                    <h6 class="text-muted mb-3 mt-4">SOPORTE</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('soporte.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('soporte.usuarios') }}"><i class="fas fa-users"></i> Usuarios</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="{{ route('soporte.logs') }}"><i class="fas fa-list"></i> Logs</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="/soporte/respaldo"><i class="fas fa-database"></i> Respaldo</a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-dark" href="/soporte/monitoreo"><i class="fas fa-desktop"></i> Monitoreo</a>
                        </li>
                    </ul>
                @endif
            </div>
        </nav>
        @endauth

        <!-- Main Content -->
        <main class="flex-grow-1 main-content">
            <!-- Breadcrumbs -->
            @hasSection('breadcrumbs')
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    @yield('breadcrumbs')
                </ol>
            </nav>
            @endif
            
            <!-- Page Title -->
            @hasSection('page-title')
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">@yield('page-title')</h1>
                @hasSection('page-actions')
                <div>
                    @yield('page-actions')
                </div>
                @endif
            </div>
            @endif
            
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show fade-in">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show fade-in">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show fade-in">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show fade-in">
                    <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- 👇 PEGA ESTO AQUÍ -->
    <script>
    window.APP_URL = "{{ url('') }}";
    </script>
    
    <!-- Common JavaScript -->
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
        // CSRF token for AJAX requests
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Common AJAX error handler
        function handleAjaxError(error) {
            console.error('AJAX Error:', error);
            Swal.fire({
                title: 'Error de Conexión',
                text: 'Verifica tu conexión a internet e intenta nuevamente.',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        }
    </script>
    
    <!-- Sidebar Toggle Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (sidebarToggle && sidebar && overlay) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('sidebar-open');
                overlay.classList.toggle('active');
                document.body.classList.toggle('sidebar-mobile-open');
            });

            overlay.addEventListener('click', function() {
                sidebar.classList.remove('sidebar-open');
                overlay.classList.remove('active');
                document.body.classList.remove('sidebar-mobile-open');
            });

            // Cerrar sidebar al hacer click en un link (móvil)
            sidebar.querySelectorAll('.nav-link').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        sidebar.classList.remove('sidebar-open');
                        overlay.classList.remove('active');
                        document.body.classList.remove('sidebar-mobile-open');
                    }
                });
            });
        }
    });
    </script>

    @yield('scripts')
</body>
</html>