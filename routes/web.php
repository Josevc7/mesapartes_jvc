<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MesaPartesController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\JefeAreaController;
use App\Http\Controllers\SoporteController;
use App\Http\Controllers\CiudadanoController;

// NOTA: Las alertas de CSRF son falsas alarmas. 
// Laravel aplica protección CSRF automáticamente a través del middleware 'web'.
// Todas las rutas POST/PUT/DELETE dentro de grupos 'auth' están protegidas.

// Ruta principal
Route::get('/', function () {
    return view('auth.login');
});

// Rutas de autenticación - con rate limiting
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Dashboard principal
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// RUTAS DE CIUDADANO (Ventanilla Virtual)
Route::prefix('ciudadano')->middleware(['auth', 'role:Ciudadano,Administrador'])->group(function () {
    Route::get('/dashboard', [CiudadanoController::class, 'dashboard'])->name('ciudadano.dashboard');
    Route::get('/mis-expedientes', [CiudadanoController::class, 'misExpedientes'])->name('ciudadano.mis-expedientes');
    Route::get('/seguimiento/{codigo}', [CiudadanoController::class, 'seguimiento'])->name('ciudadano.seguimiento');

    // CARGO DE RECEPCIÓN (Acuse de Recibo)
    // Ruta principal unificada para descargar/ver cargo
    Route::get('/cargo/{codigo}', [CiudadanoController::class, 'acuseRecibo'])->name('ciudadano.cargo');

    // Rutas legacy mantenidas por compatibilidad (redirigen a la principal)
    Route::get('/acuse-recibo/{codigo}', function($codigo) {
        return redirect()->route('ciudadano.cargo', $codigo);
    })->name('ciudadano.acuse-recibo');

    Route::get('/descargar-acuse/{codigo}', function($codigo) {
        return redirect()->route('ciudadano.cargo', $codigo);
    })->name('ciudadano.descargar-acuse');

    Route::get('/expedientes/{codigo}/acuse', function($codigo) {
        return redirect()->route('ciudadano.cargo', $codigo);
    })->name('ciudadano.acuse-expediente');

    Route::get('/documento/{id_documento}/descargar', [CiudadanoController::class, 'descargarDocumento'])->name('ciudadano.descargar-documento');
    Route::get('/notificaciones', [CiudadanoController::class, 'notificaciones'])->name('ciudadano.notificaciones');

    // Registrar expedientes
    Route::get('/registrar-expediente', [CiudadanoController::class, 'registrarExpediente'])->name('ciudadano.registrar-expediente');
    Route::post('/enviar-tramite', [CiudadanoController::class, 'storeExpediente'])->name('ciudadano.enviar-tramite');
    Route::get('/enviar-tramite', function() {
        return redirect()->route('ciudadano.registrar-expediente')->with('error', 'Debe usar el formulario para enviar expedientes.');
    });
    Route::get('/expediente-guardado', function() {
        return redirect()->route('ciudadano.dashboard')->with('success', session('mensaje_expediente'));
    })->name('ciudadano.expediente-guardado');
    
    // Notificaciones
    Route::post('/notificaciones/{id}/marcar-leida', [CiudadanoController::class, 'marcarNotificacionLeida'])->name('ciudadano.marcar-notificacion-leida');
    Route::post('/notificaciones/marcar-todas-leidas', [CiudadanoController::class, 'marcarTodasNotificacionesLeidas'])->name('ciudadano.marcar-todas-leidas');
    
    // Eliminar expedientes
    Route::delete('/expedientes/{id}/eliminar', [CiudadanoController::class, 'eliminarExpediente'])->name('ciudadano.eliminar-expediente');
    
    // Seguimiento con DNI
    Route::get('/seguimiento-form', [CiudadanoController::class, 'seguimientoForm'])->name('ciudadano.seguimiento-form');
    Route::get('/buscar-expediente', [CiudadanoController::class, 'seguimientoForm'])->name('ciudadano.buscar-expediente.form');
    Route::post('/buscar-expediente', [CiudadanoController::class, 'buscarExpediente'])->name('ciudadano.buscar-expediente');

    // Observaciones y subsanaciones
    Route::get('/observaciones', [CiudadanoController::class, 'observaciones'])->name('ciudadano.observaciones');
    Route::get('/observaciones/{expediente}', [CiudadanoController::class, 'verObservacion'])->name('ciudadano.ver-observacion');
    Route::post('/observaciones/{expediente}/responder', [CiudadanoController::class, 'responderObservacion'])->name('ciudadano.responder-observacion');
});



// Consulta pública (sin autenticación) - con rate limiting
Route::get('/consulta-publica/{codigo}', [CiudadanoController::class, 'consultaPublica'])->name('consulta.publica')->middleware('throttle:10,1');

// Seguimiento legacy (mantener compatibilidad)
Route::prefix('seguimiento')->middleware('auth')->group(function () {
    Route::get('/', [SeguimientoController::class, 'index'])->name('seguimiento.index');
    Route::get('/{codigo}', [SeguimientoController::class, 'show'])->name('seguimiento.show');
});

// Seguimiento público (sin autenticación) - con rate limiting
Route::get('/consulta/{codigo}', [SeguimientoController::class, 'consulta'])->name('seguimiento.consulta')->middleware('throttle:10,1');
Route::get('/consulta', [SeguimientoController::class, 'consultaForm'])->name('seguimiento.form')->middleware('throttle:10,1');
Route::post('/consulta/buscar', [SeguimientoController::class, 'buscar'])->name('seguimiento.buscar')->middleware('throttle:10,1');
Route::get('/seguimiento/consulta', [SeguimientoController::class, 'consultaForm'])->name('seguimiento.consulta-form')->middleware('throttle:10,1');

// API para obtener movimientos de un expediente (usado en consulta pública)
Route::get('/api/seguimiento/{expediente}/movimientos', [SeguimientoController::class, 'getMovimientos'])
    ->name('seguimiento.movimientos')
    ->middleware('throttle:30,1');

// Eliminar expediente desde seguimiento (solo Admin y Mesa de Partes autenticados)
Route::delete('/seguimiento/expedientes/{expediente}', [SeguimientoController::class, 'eliminarExpediente'])
    ->name('seguimiento.eliminar')
    ->middleware(['auth', 'role:Mesa de Partes,Administrador']);

// RUTAS DE MESA DE PARTES (Clasificar y Derivar)
Route::prefix('mesa-partes')->middleware(['auth', 'role:Mesa de Partes,Administrador'])->group(function () {
    // Gestión de expedientes
    Route::get('/expedientes', [MesaPartesController::class, 'index'])->name('mesa-partes.index');
    Route::get('/expedientes/{expediente}', [MesaPartesController::class, 'show'])->name('mesa-partes.show');
    
    // Registro de documentos
    Route::get('/registrar', [MesaPartesController::class, 'registrar'])->name('mesa-partes.registrar');
    Route::post('/registrar', [MesaPartesController::class, 'storeRegistrar'])->name('mesa-partes.store-registrar');
    
    // Clasificación
    Route::get('/expedientes/{expediente}/clasificar', [MesaPartesController::class, 'clasificar'])->name('mesa-partes.clasificar');
    Route::put('/expedientes/{expediente}/clasificar', [MesaPartesController::class, 'updateClasificacion'])->name('mesa-partes.update-clasificacion');
    
    // Derivación
    Route::get('/expedientes/{expediente}/derivar', [MesaPartesController::class, 'derivar'])->name('mesa-partes.derivar');
    Route::post('/expedientes/{expediente}/derivar', [MesaPartesController::class, 'storeDerivar'])->name('mesa-partes.store-derivar');
    
    // Monitoreo y control
    Route::get('/dashboard', [MesaPartesController::class, 'dashboard'])->name('mesa-partes.dashboard');
    Route::get('/monitoreo', [MesaPartesController::class, 'monitoreo'])->name('mesa-partes.monitoreo');

    // CARGO DE RECEPCIÓN
    // Ruta principal unificada
    Route::get('/expedientes/{expediente}/cargo', [MesaPartesController::class, 'acuseRecibo'])->name('mesa-partes.cargo');

    // Ruta legacy mantenida por compatibilidad (redirige a la principal)
    Route::get('/expedientes/{expediente}/acuse-recibo', function($expediente) {
        return redirect()->route('mesa-partes.cargo', $expediente);
    })->name('mesa-partes.acuse-recibo');

    Route::get('/estadisticas', [MesaPartesController::class, 'estadisticas'])->name('mesa-partes.estadisticas');
    Route::get('/numeracion', [MesaPartesController::class, 'numeracion'])->name('mesa-partes.numeracion');
    Route::post('/numeracion/verificar', [MesaPartesController::class, 'verificarNumeracion'])->name('mesa-partes.verificar-numeracion');
    
    // Archivar expedientes
    Route::put('/expedientes/{expediente}/archivar', [MesaPartesController::class, 'archivar'])->name('mesa-partes.archivar');

    // Búsqueda de personas
    Route::get('/buscar-persona', [MesaPartesController::class, 'buscarPersona'])->name('mesa-partes.buscar-persona');

    // Gestión de expedientes virtuales
    Route::get('/expedientes-virtuales', [MesaPartesController::class, 'expedientesVirtuales'])->name('mesa-partes.expedientes-virtuales');
    Route::get('/expedientes/{expediente}/clasificar-virtual', [MesaPartesController::class, 'clasificarVirtual'])->name('mesa-partes.clasificar-virtual');
    Route::post('/expedientes/{expediente}/clasificar-virtual', [MesaPartesController::class, 'storeClasificarVirtual'])->name('mesa-partes.store-clasificar-virtual');
});

// API para cargar funcionarios por área (usado en formulario de registro)
Route::get('/api/areas/{area}/funcionarios', function($areaId) {
    $funcionarios = \App\Models\User::where('id_rol', 4) // Rol: Funcionario
        ->where('id_area', $areaId)
        ->where('activo', true)
        ->orderBy('name')
        ->get(['id', 'name']);

    return response()->json(['funcionarios' => $funcionarios]);
})->middleware('auth');

// API para cargar tipos de trámite por área (usado en formulario de registro)
Route::get('/api/areas/{area}/tipos-tramite', function($areaId) {
    $tiposTramite = \App\Models\TipoTramite::where('id_area', $areaId)
        ->where('activo', true)
        ->orderBy('nombre')
        ->get(['id_tipo_tramite', 'nombre', 'plazo_dias']);

    return response()->json(['tipos_tramite' => $tiposTramite]);
})->middleware('auth');

// RUTAS DE JEFE DE ÁREA (Supervisión)
Route::prefix('jefe-area')->middleware(['auth', 'role:Jefe de Área,Administrador'])->group(function () {
    // Dashboard principal
    Route::get('/dashboard', [JefeAreaController::class, 'dashboard'])->name('jefe-area.dashboard');

    // Gestión de Expedientes
    Route::get('/expedientes', [JefeAreaController::class, 'expedientes'])->name('jefe-area.expedientes');
    Route::get('/expedientes/{expediente}', [JefeAreaController::class, 'showExpediente'])->name('jefe-area.show-expediente');
    Route::post('/expedientes/{expediente}/aprobar', [JefeAreaController::class, 'aprobar'])->name('jefe-area.aprobar');
    Route::post('/expedientes/{expediente}/rechazar', [JefeAreaController::class, 'rechazar'])->name('jefe-area.rechazar');

    // Asignación y Reasignación de expedientes
    Route::post('/expedientes/{expediente}/asignar', [JefeAreaController::class, 'asignarExpediente'])->name('jefe-area.asignar-expediente');
    Route::post('/asignacion-masiva', [JefeAreaController::class, 'asignacionMasiva'])->name('jefe-area.asignacion-masiva');

    // Reportes del área
    Route::get('/reportes', [JefeAreaController::class, 'reportes'])->name('jefe-area.reportes');

    // Control de plazos
    Route::get('/control-plazos', [JefeAreaController::class, 'controlPlazos'])->name('jefe-area.control-plazos');

    // Supervisión avanzada
    Route::get('/supervision', [JefeAreaController::class, 'supervision'])->name('jefe-area.supervision');

    // Metas y KPIs
    Route::get('/metas', [JefeAreaController::class, 'metas'])->name('jefe-area.metas');
    Route::post('/metas', [JefeAreaController::class, 'storeMeta'])->name('jefe-area.metas.store');

    // Validación de documentos
    Route::get('/validar-documentos', [JefeAreaController::class, 'validarDocumentos'])->name('jefe-area.validar-documentos');
    Route::get('/expedientes/{expediente}/detalle-validacion', [JefeAreaController::class, 'detalleValidacion'])->name('jefe-area.detalle-validacion');
    Route::post('/expedientes/{expediente}/validar', [JefeAreaController::class, 'validarExpediente'])->name('jefe-area.validar-expediente');

    // Resolución de conflictos
    Route::get('/conflictos', [JefeAreaController::class, 'conflictos'])->name('jefe-area.conflictos');
    Route::get('/conflictos/{expediente}/detalle', [JefeAreaController::class, 'detalleConflicto'])->name('jefe-area.detalle-conflicto');
    Route::post('/conflictos/{expediente}/extender-plazo', [JefeAreaController::class, 'extenderPlazo'])->name('jefe-area.extender-plazo');
    Route::post('/conflictos/{expediente}/autorizar', [JefeAreaController::class, 'autorizarEspecial'])->name('jefe-area.autorizar-especial');
});

// RUTAS INTERNAS (Funcionarios - Resolver y Procesar)
Route::prefix('funcionario')->middleware(['auth', 'role:Funcionario,Administrador'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [FuncionarioController::class, 'dashboard'])->name('funcionario.dashboard');
    
    // Expedientes asignados
    Route::get('/expedientes', [FuncionarioController::class, 'index'])->name('funcionario.index');
    Route::get('/mis-expedientes', [FuncionarioController::class, 'misExpedientes'])->name('funcionario.mis-expedientes');
    Route::get('/expedientes/{expediente}', [FuncionarioController::class, 'show'])->name('funcionario.show');
    
    // Recibir expediente
    Route::put('/expedientes/{expediente}/recibir', [FuncionarioController::class, 'recibir'])->name('funcionario.recibir');
    
    // Procesar expediente
    Route::get('/expedientes/{expediente}/procesar', [FuncionarioController::class, 'procesar'])->name('funcionario.procesar');
    Route::put('/expedientes/{expediente}/procesar', [FuncionarioController::class, 'updateProcesar'])->name('funcionario.update-procesar');
    
    // Resolver expediente
    Route::put('/expedientes/{expediente}/resolver', [FuncionarioController::class, 'resolver'])->name('funcionario.resolver');
    
    // Solicitar información adicional
    Route::get('/expedientes/{expediente}/solicitar-info', [FuncionarioController::class, 'solicitarInfoForm'])->name('funcionario.solicitar-info-form');
    Route::post('/expedientes/{expediente}/solicitar-info', [FuncionarioController::class, 'solicitarInfo'])->name('funcionario.solicitar-info');
    
    // Adjuntar documentos
    Route::post('/expedientes/{expediente}/documentos', [FuncionarioController::class, 'adjuntarDocumento'])->name('funcionario.adjuntar-documento');
    
    // Derivar expedientes
    Route::get('/expedientes/{expediente}/derivar', [FuncionarioController::class, 'derivarForm'])->name('funcionario.derivar-form');
    Route::post('/expedientes/{expediente}/derivar', [FuncionarioController::class, 'derivar'])->name('funcionario.derivar');
    
    // Vistas especializadas
    Route::get('/expedientes/{expediente}/historial', [FuncionarioController::class, 'historial'])->name('funcionario.historial');
    Route::get('/expedientes/{expediente}/documentos', [FuncionarioController::class, 'documentos'])->name('funcionario.documentos');
});

// RUTAS DE ADMINISTRACIÓN
Route::prefix('admin')->middleware(['auth', 'role:Administrador'])->group(function () {
    // Gestión de usuarios
    Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('admin.usuarios');
    Route::get('/usuarios/crear', [AdminController::class, 'crearUsuario'])->name('admin.usuarios.create');
    Route::post('/usuarios', [AdminController::class, 'storeUsuario'])->name('admin.usuarios.store');
    Route::get('/usuarios/{id_user}', [AdminController::class, 'showUsuario'])->name('admin.usuarios.show');
    Route::get('/usuarios/{id_user}/editar', [AdminController::class, 'editUsuario'])->name('admin.usuarios.edit');
    Route::put('/usuarios/{id_user}', [AdminController::class, 'updateUsuario'])->name('admin.usuarios.update');
    Route::delete('/usuarios/{id_user}', [AdminController::class, 'destroyUsuario'])->name('admin.usuarios.destroy');
    
    // Gestión de áreas
    Route::get('/areas', [AdminController::class, 'areas'])->name('admin.areas');
    Route::post('/areas', [AdminController::class, 'storeArea'])->name('admin.areas.store');
    Route::get('/areas/{id_area}/edit', [AdminController::class, 'editArea'])->name('admin.areas.edit');
    Route::put('/areas/{id_area}', [AdminController::class, 'updateArea'])->name('admin.areas.update');
    Route::put('/areas/{id_area}/toggle', [AdminController::class, 'toggleArea'])->name('admin.areas.toggle');
    
    // Gestión de tipos de trámite
    Route::get('/tipo-tramites', [AdminController::class, 'tipoTramites'])->name('admin.tipo-tramites');
    Route::post('/tipo-tramites', [AdminController::class, 'storeTipoTramite'])->name('admin.tipo-tramites.store');
    Route::get('/tipo-tramites/{id_tipo_tramite}/edit', [AdminController::class, 'editTipoTramite'])->name('admin.tipo-tramites.edit');
    Route::put('/tipo-tramites/{id_tipo_tramite}', [AdminController::class, 'updateTipoTramite'])->name('admin.tipo-tramites.update');
    Route::put('/tipo-tramites/{id_tipo_tramite}/toggle', [AdminController::class, 'toggleTipoTramite'])->name('admin.tipo-tramites.toggle');
    
    // Configuraciones
    Route::get('/configuraciones', [AdminController::class, 'configuraciones'])->name('admin.configuraciones');
    Route::post('/configuraciones', [AdminController::class, 'updateConfiguraciones'])->name('admin.configuraciones.update');
    
    // Reportes
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/tramites-mes', [ReporteController::class, 'tramitesPorMes'])->name('reportes.tramites-mes');
    Route::get('/reportes/tiempos-atencion', [ReporteController::class, 'tiemposAtencion'])->name('reportes.tiempos-atencion');
    Route::get('/reportes/por-fecha', [ReporteController::class, 'reportePorFecha'])->name('reportes.por-fecha');
    Route::get('/reportes/por-tipo-tramite', [ReporteController::class, 'reportePorTipoTramite'])->name('reportes.por-tipo-tramite');
    Route::get('/reportes/por-area', [ReporteController::class, 'reportePorArea'])->name('reportes.por-area');
    Route::get('/reportes/por-remitente', [ReporteController::class, 'reportePorRemitente'])->name('reportes.por-remitente');
    Route::get('/reportes/exportar', [ReporteController::class, 'exportarReporte'])->name('reportes.exportar');
    Route::get('/reportes/exportar-pdf', [ReporteController::class, 'exportarPdf'])->name('reportes.exportar-pdf');
    Route::get('/reportes/exportar-area-pdf', [ReporteController::class, 'exportarAreaPdf'])->name('reportes.exportar-area-pdf');
    
    // Gestión de personas
    Route::get('/personas', [AdminController::class, 'personas'])->name('admin.personas');
    Route::post('/personas', [AdminController::class, 'storePersona'])->name('admin.personas.store');
    Route::get('/personas/{id_persona}', [AdminController::class, 'showPersona'])->name('admin.personas.show');
    Route::put('/personas/{id_persona}', [AdminController::class, 'updatePersona'])->name('admin.personas.update');
    Route::delete('/personas/{id_persona}', [AdminController::class, 'destroyPersona'])->name('admin.personas.destroy');
    
    // Auditoría
    Route::get('/auditoria', [AdminController::class, 'auditoria'])->name('admin.auditoria');
    Route::get('/auditoria/{id}/detalles', [AdminController::class, 'auditoriaDetalles'])->name('admin.auditoria.detalles');
    
    // Matriz de Control
    Route::get('/matriz-control', [AdminController::class, 'matrizControl'])->name('admin.matriz-control');
    Route::post('/usuarios/{id_user}/toggle-estado', [AdminController::class, 'toggleEstadoUsuario'])->name('admin.usuarios.toggle-estado');
    
    // Dashboard Administrativo
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // Gestión de Roles
    Route::get('/roles', [AdminController::class, 'roles'])->name('admin.roles');
    Route::post('/roles', [AdminController::class, 'storeRol'])->name('admin.roles.store');
    Route::get('/roles/{id_rol}', [AdminController::class, 'showRol'])->name('admin.roles.show');
    Route::put('/roles/{id_rol}', [AdminController::class, 'updateRol'])->name('admin.roles.update');
    Route::delete('/roles/{id_rol}', [AdminController::class, 'destroyRol'])->name('admin.roles.destroy');

    // Gestión de Permisos
    Route::get('/permisos', [AdminController::class, 'permisos'])->name('admin.permisos');
    Route::get('/permisos/rol/{id_rol}', [AdminController::class, 'editarPermisosRol'])->name('admin.permisos.editar');
    Route::put('/permisos/rol/{id_rol}', [AdminController::class, 'actualizarPermisosRol'])->name('admin.permisos.actualizar');

    // Gestión de Estados del Expediente
    Route::get('/estados', [AdminController::class, 'estados'])->name('admin.estados');
    Route::post('/estados', [AdminController::class, 'storeEstado'])->name('admin.estados.store');
    Route::put('/estados/{id_estado}', [AdminController::class, 'updateEstado'])->name('admin.estados.update');
    Route::put('/estados/{id_estado}/toggle', [AdminController::class, 'toggleEstado'])->name('admin.estados.toggle');
    Route::post('/transiciones', [AdminController::class, 'storeTransicion'])->name('admin.transiciones.store');
    Route::delete('/transiciones/{id_transicion}', [AdminController::class, 'destroyTransicion'])->name('admin.transiciones.destroy');

    // Gestión de Numeración
    Route::get('/numeracion', [AdminController::class, 'numeracion'])->name('admin.numeracion');
    Route::post('/numeracion', [AdminController::class, 'storeNumeracion'])->name('admin.numeracion.store');
    Route::put('/numeracion/{id_numeracion}', [AdminController::class, 'updateNumeracion'])->name('admin.numeracion.update');
    Route::post('/numeracion/{id_numeracion}/reiniciar', [AdminController::class, 'reiniciarNumeracion'])->name('admin.numeracion.reiniciar');

    // Gestión de Expedientes (Admin)
    Route::get('/expedientes', [AdminController::class, 'expedientes'])->name('admin.expedientes');
    Route::get('/expedientes/{id_expediente}', [AdminController::class, 'showExpediente'])->name('admin.expedientes.show');
    Route::put('/expedientes/{id_expediente}/estado', [AdminController::class, 'cambiarEstadoExpediente'])->name('admin.expedientes.cambiar-estado');
    Route::put('/expedientes/{id_expediente}/reasignar', [AdminController::class, 'reasignarExpediente'])->name('admin.expedientes.reasignar');

    // Mesa de Partes Virtual (Supervisión)
    Route::get('/mesa-virtual', [AdminController::class, 'mesaVirtual'])->name('admin.mesa-virtual');
    Route::post('/mesa-virtual/{id_expediente}/validar', [AdminController::class, 'validarExpedienteVirtual'])->name('admin.mesa-virtual.validar');

    // Auditoría Completa
    Route::get('/auditoria-completa', [AdminController::class, 'auditoriaCompleta'])->name('admin.auditoria-completa');
    Route::get('/auditoria/exportar', [AdminController::class, 'exportarAuditoria'])->name('admin.auditoria.exportar');

    // API Funcionarios por Área
    Route::get('/api/funcionarios/{id_area}', [AdminController::class, 'getFuncionariosPorArea'])->name('admin.api.funcionarios');

    // Logs del Sistema
    Route::get('/logs', [AdminController::class, 'logs'])->name('admin.logs');
    Route::get('/logs/{id}/detalles', [AdminController::class, 'logDetalles'])->name('admin.logs.detalles');

    // Estadísticas Globales
    Route::get('/estadisticas', [AdminController::class, 'estadisticas'])->name('admin.estadisticas');
});

// RUTAS DE SOPORTE
Route::prefix('soporte')->middleware(['auth', 'role:Soporte,Administrador'])->group(function () {
    Route::get('/dashboard', [SoporteController::class, 'dashboard'])->name('soporte.dashboard');
    Route::get('/logs', [SoporteController::class, 'logs'])->name('soporte.logs');
    Route::get('/usuarios', [SoporteController::class, 'usuarios'])->name('soporte.usuarios');
    Route::get('/respaldo', [SoporteController::class, 'respaldoView'])->name('soporte.respaldo.view');
    Route::get('/monitoreo', [SoporteController::class, 'monitoreo'])->name('soporte.monitoreo');
    Route::post('/usuarios/{id_user}/resetear-password', [SoporteController::class, 'resetearPassword'])->name('soporte.resetear-password');
    Route::post('/usuarios/{id_user}/toggle', [SoporteController::class, 'toggleUsuario'])->name('soporte.toggle-usuario');
    Route::post('/respaldo', [SoporteController::class, 'respaldo'])->name('soporte.respaldo');
    Route::post('/limpiar-cache', [SoporteController::class, 'limpiarCache'])->name('soporte.limpiar-cache');
});

// Rutas de Perfil
Route::middleware('auth')->group(function () {
    Route::get('/perfil', [App\Http\Controllers\PerfilController::class, 'show'])->name('perfil.show');
    Route::get('/perfil/editar', [App\Http\Controllers\PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [App\Http\Controllers\PerfilController::class, 'update'])->name('perfil.update');
});

// RUTAS DE RESOLUCIONES
Route::prefix('resoluciones')->middleware(['auth', 'role:Funcionario,Jefe de Área,Administrador'])->group(function () {
    Route::get('/', [App\Http\Controllers\ResolucionController::class, 'index'])->name('resoluciones.index');
    Route::get('/crear/{expediente}', [App\Http\Controllers\ResolucionController::class, 'create'])->name('resoluciones.create');
    Route::post('/', [App\Http\Controllers\ResolucionController::class, 'store'])->name('resoluciones.store');
    Route::get('/{id_resolucion}', [App\Http\Controllers\ResolucionController::class, 'show'])->name('resoluciones.show');
    Route::patch('/{id_resolucion}/notificar', [App\Http\Controllers\ResolucionController::class, 'notificar'])->name('resoluciones.notificar');
    Route::get('/{id_resolucion}/descargar', [App\Http\Controllers\ResolucionController::class, 'descargar'])->name('resoluciones.descargar');
});

// RUTAS DE DOCUMENTOS (con control de acceso)
Route::prefix('documentos')->middleware('auth')->group(function () {
    Route::get('/', [App\Http\Controllers\DocumentoController::class, 'index'])->name('documentos.index');
    Route::get('/{id_documento}/descargar', [App\Http\Controllers\DocumentoController::class, 'show'])->name('documentos.descargar');
    Route::get('/{id_documento}/visualizar', [App\Http\Controllers\DocumentoController::class, 'visualizar'])->name('documentos.visualizar');
    Route::post('/expediente/{id_expediente}', [App\Http\Controllers\DocumentoController::class, 'store'])->name('documentos.store');
    Route::delete('/{id_documento}', [App\Http\Controllers\DocumentoController::class, 'destroy'])->name('documentos.destroy');
    Route::post('/{id_documento}/validar', [App\Http\Controllers\DocumentoController::class, 'validar'])->name('documentos.validar');
});

// API Routes para consultas AJAX - con validación y autorización
Route::prefix('api')->middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/areas', function() {
        return App\Models\Area::where('activo', true)->select('id_area', 'nombre')->get();
    })->name('api.areas');

    Route::get('/funcionarios/{area}', function($area) {
        // Validar que el área existe
        App\Models\Area::findOrFail($area);

        return App\Models\User::where('id_rol', 4)
            ->where('id_area', $area)
            ->where('activo', true)
            ->select('id', 'name', 'email')
            ->get();
    })->name('api.funcionarios');
});
