<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\User;
use App\Models\Area;
use App\Models\Derivacion;
use App\Models\TipoTramite;
use App\Services\EstadisticasService;
use App\Services\DerivacionService;
use App\Services\FuncionarioEstadisticasService;
use App\Http\Requests\Derivacion\ExtenderPlazoRequest;
use App\Http\Requests\Expediente\ValidarExpedienteRequest;
use App\Enums\EstadoExpediente;
use App\Enums\PrioridadExpediente;
use App\Enums\RolUsuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JefeAreaController extends Controller
{
    protected EstadisticasService $estadisticasService;
    protected DerivacionService $derivacionService;
    protected FuncionarioEstadisticasService $funcionarioEstadisticasService;

    public function __construct(
        EstadisticasService $estadisticasService,
        DerivacionService $derivacionService,
        FuncionarioEstadisticasService $funcionarioEstadisticasService
    ) {
        $this->estadisticasService = $estadisticasService;
        $this->derivacionService = $derivacionService;
        $this->funcionarioEstadisticasService = $funcionarioEstadisticasService;
    }

    /**
     * Obtiene IDs de subdirecciones del área del usuario actual
     * Reemplaza el código duplicado: Area::where('id_area_padre', $areaId)->pluck('id_area')->toArray()
     */
    private function getSubdireccionesIds(?int $areaId = null): array
    {
        $areaId = $areaId ?? auth()->user()->id_area;
        return Area::getSubdireccionesIds($areaId);
    }

    /**
     * Obtiene IDs del área y sus subdirecciones
     */
    private function getAreaYSubdireccionesIds(?int $areaId = null): array
    {
        $areaId = $areaId ?? auth()->user()->id_area;
        return Area::getAreaYSubdireccionesIds($areaId);
    }

    /**
     * Dashboard del Jefe de Área con estadísticas completas
     */
    public function dashboard()
    {
        $user = auth()->user();
        $areaId = $user->id_area;

        // Si es administrador sin área asignada, mostrar todas las áreas o redirigir
        if (!$areaId && $user->role?->nombre === 'Administrador') {
            // Tomar la primera área activa para el admin
            $primeraArea = Area::where('activo', true)->first();
            $areaId = $primeraArea?->id_area;

            // Si no hay áreas, mostrar dashboard vacío
            if (!$areaId) {
                $stats = [
                    'total' => 0, 'pendientes' => 0, 'resueltos' => 0, 'vencidos' => 0,
                    'por_aprobar' => 0, 'sin_asignar' => 0, 'urgentes' => 0
                ];
                $expedientesCriticos = collect();
                $funcionarios = collect();
                return view('jefe-area.dashboard', compact('stats', 'expedientesCriticos', 'funcionarios'));
            }
        }

        // Si aún no hay área (usuario sin área y no es admin)
        if (!$areaId) {
            return redirect()->route('dashboard')->with('error', 'No tiene un área asignada.');
        }

        // Incluir área del jefe + subdirecciones para ver expedientes derivados a sub-áreas
        $areasIds = $this->getAreaYSubdireccionesIds($areaId);

        $stats = $this->estadisticasService->obtenerEstadisticasJefeArea($areasIds);

        // Estadísticas adicionales para el dashboard
        $stats['por_aprobar'] = Expediente::whereIn('id_area', $areasIds)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['en_revision', 'resuelto']))
            ->count();

        $stats['sin_asignar'] = Expediente::whereIn('id_area', $areasIds)
            ->whereNull('id_funcionario_asignado')
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso', 'devuelto_jefe']))
            ->count();

        // Expedientes devueltos por funcionarios
        $stats['devueltos'] = Expediente::whereIn('id_area', $areasIds)
            ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'devuelto_jefe'))
            ->count();

        // Expedientes urgentes
        $stats['urgentes'] = Expediente::whereIn('id_area', $areasIds)
            ->where('prioridad', 'urgente')
            ->whereHas('estadoExpediente', fn($q) => $q->whereNotIn('slug', ['archivado', 'en_revision', 'resuelto', 'aprobado']))
            ->count();

        // Expedientes críticos (vencidos o por vencer)
        // OPTIMIZADO: Usa accessors del modelo en lugar de lógica duplicada
        $expedientesCriticos = Expediente::whereIn('id_area', $areasIds)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', EstadoExpediente::estadosPendientes()))
            ->with(['funcionarioAsignado', 'derivaciones' => function($q) {
                $q->where('estado', 'pendiente')->latest();
            }])
            ->get()
            ->filter(fn($exp) => $exp->es_critico)
            ->sortByDesc('dias_vencido')
            ->take(5);

        // Funcionarios de las subdirecciones con carga de trabajo
        // OPTIMIZADO: Usa método helper y servicio
        $subdireccionesIds = $this->getSubdireccionesIds($areaId);
        $funcionarios = $this->funcionarioEstadisticasService->obtenerFuncionariosParaSelect($subdireccionesIds);

        return view('jefe-area.dashboard', compact('stats', 'expedientesCriticos', 'funcionarios'));
    }

    /**
     * Lista de expedientes del área con filtros avanzados
     */
    public function expedientes(Request $request)
    {
        $areaId = auth()->user()->id_area;
        $areasIds = $this->getAreaYSubdireccionesIds($areaId);

        $query = Expediente::whereIn('id_area', $areasIds)
            ->with(['tipoTramite', 'ciudadano', 'funcionarioAsignado', 'derivaciones' => function($q) use ($areasIds) {
                $q->whereIn('id_area_destino', $areasIds)->latest();
            }, 'persona']);

        // Filtro por estado
        if ($request->estado) {
            $query->whereHas('estadoExpediente', fn($q) => $q->where('slug', $request->estado));
        }

        // Filtro por funcionario
        if ($request->funcionario) {
            if ($request->funcionario === 'sin_asignar') {
                $query->whereNull('id_funcionario_asignado');
            } else {
                $query->where('id_funcionario_asignado', $request->funcionario);
            }
        }

        // Filtro por prioridad
        if ($request->prioridad) {
            $query->where('prioridad', $request->prioridad);
        }

        // Filtro por tipo de trámite
        if ($request->tipo_tramite) {
            $query->where('id_tipo_tramite', $request->tipo_tramite);
        }

        // Filtro por fecha
        if ($request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Filtro por vencidos
        if ($request->vencidos) {
            $query->whereHas('derivaciones', function($q) {
                $q->where('estado', 'pendiente')
                  ->where('fecha_limite', '<', now());
            });
        }

        // Filtro por próximos a vencer
        if ($request->por_vencer) {
            $query->whereHas('derivaciones', function($q) {
                $q->where('estado', 'pendiente')
                  ->whereBetween('fecha_limite', [now(), now()->addDays(3)]);
            });
        }

        // Búsqueda por código o asunto
        if ($request->buscar) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('codigo_expediente', 'like', "%{$buscar}%")
                  ->orWhere('asunto', 'like', "%{$buscar}%");
            });
        }

        $expedientes = $query->orderBy('created_at', 'desc')->paginate(15);

        // NOTA: Los plazos ahora se calculan automáticamente via accessors
        // $exp->dias_vencido, $exp->dias_restantes, $exp->fecha_limite_derivacion

        // Funcionarios de las subdirecciones para filtros y asignación
        // OPTIMIZADO: Usa método helper y servicio
        $subdireccionesIds = $this->getSubdireccionesIds($areaId);
        $funcionarios = $this->funcionarioEstadisticasService->obtenerFuncionariosParaSelect($subdireccionesIds);

        // Tipos de trámite del área y subdirecciones
        $tiposTramite = TipoTramite::whereIn('id_area', $areasIds)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        // Estadísticas rápidas
        $estadisticas = [
            'total' => Expediente::whereIn('id_area', $areasIds)->count(),
            'pendientes' => Expediente::whereIn('id_area', $areasIds)
                ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso']))->count(),
            'resueltos' => Expediente::whereIn('id_area', $areasIds)
                ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'resuelto'))->count(),
            'sin_asignar' => Expediente::whereIn('id_area', $areasIds)
                ->whereNull('id_funcionario_asignado')
                ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso', 'devuelto_jefe']))->count(),
            'devueltos' => Expediente::whereIn('id_area', $areasIds)
                ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'devuelto_jefe'))->count(),
        ];

        return view('jefe-area.expedientes', compact('expedientes', 'funcionarios', 'tiposTramite', 'estadisticas'));
    }

    /**
     * Ver detalle de un expediente
     */
    public function showExpediente(Expediente $expediente)
    {
        $this->authorize('view', $expediente);

        $expediente->load([
            'tipoTramite',
            'ciudadano',
            'funcionarioAsignado',
            'derivaciones.funcionarioAsignado',
            'derivaciones.areaDestino',
            'documentos',
            'historial.usuario',
            'observaciones',
            'persona'
        ]);

        // Funcionarios de las subdirecciones
        $areaId = auth()->user()->id_area;
        $subdireccionesIds = $this->getSubdireccionesIds($areaId);

        $funcionarios = User::whereIn('id_area', $subdireccionesIds)
            ->where('id_rol', RolUsuario::FUNCIONARIO->value)
            ->where('activo', true)
            ->with('area')
            ->get();

        return view('jefe-area.show-expediente', compact('expediente', 'funcionarios'));
    }

    /**
     * Asignar expediente a un funcionario
     */
    public function asignarExpediente(Request $request, Expediente $expediente)
    {
        $this->authorize('update', $expediente);

        $request->validate([
            'funcionario_id' => 'required|exists:users,id',
            'observaciones' => 'nullable|string|max:500'
        ]);

        $funcionario = User::findOrFail($request->funcionario_id);

        // Verificar que el funcionario pertenece al área o sus subdirecciones
        $areaJefe = auth()->user()->id_area;
        $areasPermitidas = $this->getAreaYSubdireccionesIds($areaJefe);

        if (!in_array($funcionario->id_area, $areasPermitidas)) {
            return back()->with('error', 'El funcionario no pertenece a esta área.');
        }

        $funcionarioAnterior = $expediente->funcionarioAsignado?->name ?? 'Sin asignar';
        $esNuevaAsignacion = $funcionarioAnterior === 'Sin asignar';

        DB::transaction(function() use ($expediente, $funcionario, $request, $funcionarioAnterior, $esNuevaAsignacion) {
            // Datos a actualizar
            $datosActualizar = [
                'id_funcionario_asignado' => $funcionario->id
            ];

            // Si es primera asignación y viene de recepcionado, cambiar estado a asignado
            if ($esNuevaAsignacion && in_array($expediente->estado, ['recepcionado', 'derivado'])) {
                $expediente->estado = 'asignado';
            }

            // Si viene de devuelto_jefe, reasignar y volver a en_proceso
            if ($expediente->estado === 'devuelto_jefe') {
                $expediente->estado = 'en_proceso';
            }

            $expediente->update($datosActualizar);
            $expediente->save();

            // Actualizar la derivación activa
            $derivacionActiva = $expediente->derivaciones()
                ->where('estado', 'pendiente')
                ->latest()
                ->first();

            if ($derivacionActiva) {
                $derivacionActiva->update([
                    'id_funcionario_asignado' => $funcionario->id
                ]);
            }

            $mensaje = "Asignado a {$funcionario->name} por Jefe de Área.";
            if (!$esNuevaAsignacion) {
                $mensaje = "Reasignado de {$funcionarioAnterior} a {$funcionario->name}.";
            }
            if ($request->observaciones) {
                $mensaje .= " Motivo: {$request->observaciones}";
            }

            $expediente->agregarHistorial($mensaje, auth()->id());
        });

        return back()->with('success', "Expediente asignado correctamente a {$funcionario->name}.");
    }

    /**
     * Reasignar múltiples expedientes (asignación masiva)
     */
    public function asignacionMasiva(Request $request)
    {
        $request->validate([
            'expedientes' => 'required|array|min:1',
            'expedientes.*' => 'exists:expedientes,id_expediente',
            'funcionario_id' => 'required|exists:users,id',
            'motivo' => 'required|string|min:5|max:500'
        ]);

        $funcionario = User::findOrFail($request->funcionario_id);
        $areaId = auth()->user()->id_area;

        // Verificar que el funcionario pertenece al área o sus subdirecciones
        $areasPermitidas = $this->getAreaYSubdireccionesIds($areaId);

        if (!in_array($funcionario->id_area, $areasPermitidas)) {
            return back()->with('error', 'El funcionario no pertenece a esta área.');
        }

        $count = 0;
        DB::transaction(function() use ($request, $funcionario, $areaId, &$count) {
            foreach ($request->expedientes as $expedienteId) {
                $expediente = Expediente::where('id_expediente', $expedienteId)
                    ->where('id_area', $areaId)
                    ->first();

                if ($expediente) {
                    $funcionarioAnterior = $expediente->funcionarioAsignado?->name ?? 'Sin asignar';

                    $expediente->update([
                        'id_funcionario_asignado' => $funcionario->id
                    ]);

                    $derivacionActiva = $expediente->derivaciones()
                        ->where('estado', 'pendiente')
                        ->latest()
                        ->first();

                    if ($derivacionActiva) {
                        $derivacionActiva->update([
                            'id_funcionario_asignado' => $funcionario->id
                        ]);
                    }

                    $expediente->agregarHistorial(
                        "Asignación masiva: de {$funcionarioAnterior} a {$funcionario->name}. Motivo: {$request->motivo}",
                        auth()->id()
                    );

                    $count++;
                }
            }
        });

        return back()->with('success', "{$count} expediente(s) asignado(s) correctamente a {$funcionario->name}.");
    }

    /**
     * Recepcionar un expediente derivado a esta área
     * Genera el número de registro del área
     */
    public function recepcionar(Expediente $expediente)
    {
        $this->authorize('update', $expediente);

        // Verificar que el expediente esté en estado derivado
        if ($expediente->estado !== 'derivado') {
            return back()->with('error', 'Solo se pueden recepcionar expedientes en estado derivado.');
        }

        // Buscar la derivación pendiente para este expediente
        $derivacion = Derivacion::where('id_expediente', $expediente->id_expediente)
            ->where('id_area_destino', auth()->user()->id_area)
            ->where('estado', 'pendiente')
            ->latest()
            ->first();

        if (!$derivacion) {
            return back()->with('error', 'No se encontró una derivación pendiente para este expediente.');
        }

        try {
            $derivacion = $this->derivacionService->recepcionarExpediente($derivacion);

            return back()->with('success',
                'Expediente recepcionado correctamente. Número de registro: ' . $derivacion->numero_registro_area
            );

        } catch (\Exception $e) {
            return back()->with('error', 'Error al recepcionar expediente: ' . $e->getMessage());
        }
    }

    public function aprobar(Expediente $expediente)
    {
        $this->authorize('approve', $expediente);

        $estadoAnterior = $expediente->estado;
        $funcionario = $expediente->funcionarioAsignado;

        $expediente->estado = 'aprobado';
        $expediente->save();

        // Historial detallado
        $descripcion = sprintf(
            'Jefe de Área %s aprobó el expediente. Estado anterior: %s. Funcionario responsable: %s.',
            auth()->user()->name,
            $estadoAnterior,
            $funcionario->name ?? 'N/A'
        );

        $expediente->agregarHistorial($descripcion, auth()->user()->id);

        return back()->with('success', 'Expediente aprobado correctamente. Ahora puede resolver/finalizar el trámite.');
    }

    /**
     * Resolver/Finalizar expediente aprobado
     * Cambia de aprobado → resuelto
     */
    public function resolverExpediente(Request $request, Expediente $expediente)
    {
        $this->authorize('update', $expediente);

        // Solo se puede resolver desde estado aprobado
        if ($expediente->estado !== 'aprobado') {
            return back()->with('error', 'Solo se pueden resolver expedientes en estado aprobado.');
        }

        $request->validate([
            'observaciones_resolucion' => 'nullable|string|max:500'
        ]);

        $expediente->update([
            'estado' => 'resuelto',
            'fecha_resolucion' => now(),
            'observaciones_resolucion' => $request->observaciones_resolucion
        ]);

        $descripcion = sprintf(
            'Jefe de Área %s resolvió/finalizó el expediente. El trámite ha cumplido su finalidad.',
            auth()->user()->name
        );

        if ($request->observaciones_resolucion) {
            $descripcion .= ' Observaciones: ' . $request->observaciones_resolucion;
        }

        $expediente->agregarHistorial($descripcion, auth()->user()->id);

        return back()->with('success', 'Expediente resuelto/finalizado correctamente. Ahora puede archivarlo.');
    }

    /**
     * Archivar expediente resuelto
     * Solo se puede archivar desde estado resuelto
     */
    public function archivar(Expediente $expediente)
    {
        $this->authorize('update', $expediente);

        // Verificar que el expediente esté resuelto
        if ($expediente->estado !== 'resuelto') {
            return back()->with('error', 'Solo se pueden archivar expedientes en estado resuelto.');
        }

        $expediente->update([
            'estado' => 'archivado',
            'fecha_archivo' => now()
        ]);

        $expediente->agregarHistorial(
            'Expediente archivado. Trámite finalizado.',
            auth()->user()->id
        );

        return back()->with('success', 'Expediente archivado correctamente. El trámite ha sido finalizado.');
    }

    public function rechazar(Request $request, Expediente $expediente)
    {
        $request->validate([
            'motivo_rechazo' => 'required|string|min:10'
        ], [
            'motivo_rechazo.required' => 'Debe especificar el motivo del rechazo',
            'motivo_rechazo.min' => 'El motivo debe tener al menos 10 caracteres'
        ]);

        $this->authorize('reject', $expediente);

        try {
            \DB::beginTransaction();

            // Crear observación para el funcionario con el motivo del rechazo
            \App\Models\Observacion::create([
                'id_expediente' => $expediente->id_expediente,
                'id_usuario' => auth()->id(),
                'tipo' => 'rechazo',
                'descripcion' => 'JEFE DE ÁREA RECHAZÓ LA RESOLUCIÓN: ' . $request->motivo_rechazo,
                'estado' => 'pendiente',
                'fecha_limite' => now()->addDays(3) // 3 días para corregir
            ]);

            // Regresar el expediente al funcionario para que lo corrija
            // Cambiar estado a "en_proceso" para que aparezca en su lista
            $expediente->update([
                'estado' => 'en_proceso'
            ]);

            // Registrar en historial con detalle
            $expediente->agregarHistorial(
                'Jefe de Área rechazó la resolución. Motivo: ' . $request->motivo_rechazo . '. Expediente devuelto al funcionario para corrección.',
                auth()->id()
            );

            \DB::commit();

            return back()->with('success', 'Expediente rechazado y devuelto al funcionario ' .
                ($expediente->funcionarioAsignado->name ?? 'asignado') . ' para corrección.');

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'Error al rechazar expediente: ' . $e->getMessage());
        }
    }

    /**
     * Reportes del área con estadísticas completas
     */
    public function reportes(Request $request)
    {
        $areaId = auth()->user()->id_area;
        $año = $request->get('anio', now()->year);
        $mes = $request->get('mes');

        // Expedientes por mes
        $expedientesPorMes = Expediente::where('id_area', $areaId)
            ->selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
            ->whereYear('created_at', $año)
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');

        // Expedientes resueltos por mes
         $resueltosPorMes = Expediente::where('id_area', $areaId)
         ->selectRaw('MONTH(updated_at) as mes, COUNT(*) as total')
         ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto','aprobado','archivado']))
         ->whereYear('updated_at', $año)
         ->groupBy('mes')
         ->get()
         ->keyBy('mes');

        // Rendimiento por funcionarios de subdirecciones
        // OPTIMIZADO: Usa servicio que resuelve N+1 con subqueries
        $subdireccionesIds = $this->getSubdireccionesIds($areaId);
        $funcionariosRendimiento = $this->funcionarioEstadisticasService->obtenerRendimientoParaReportes($subdireccionesIds);

        // Estadísticas por tipo de trámite
        $porTipoTramite = Expediente::where('id_area', $areaId)
            ->whereYear('created_at', $año)
            ->with('tipoTramite')
            ->selectRaw('id_tipo_tramite, COUNT(*) as total')
            ->groupBy('id_tipo_tramite')
            ->get()
            ->map(function($item) {
                return [
                    'nombre' => $item->tipoTramite->nombre ?? 'Sin tipo',
                    'total' => $item->total
                ];
            });

        // Estadísticas por prioridad
        $porPrioridad = Expediente::where('id_area', $areaId)
            ->whereYear('created_at', $año)
            ->selectRaw('prioridad, COUNT(*) as total')
            ->groupBy('prioridad')
            ->get()
            ->keyBy('prioridad');

        // Tiempos promedio generales
         $tiemposPromedio = Expediente::where('id_area', $areaId)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto','aprobado','archivado']))
            ->whereNotNull('fecha_resolucion')
            ->selectRaw('AVG(DATEDIFF(fecha_resolucion, created_at)) as promedio_dias')
            ->first();

        // Cumplimiento de plazos
        $totalConPlazo = Expediente::where('id_area', $areaId)
            ->whereHas('derivaciones', function($q) {
                $q->whereNotNull('fecha_limite');
            })->count();

        $cumplidosEnPlazo = Expediente::where('id_area', $areaId)
           ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto','aprobado','archivado']))
           ->whereNotNull('fecha_resolucion')
           ->whereHas('derivaciones', function($q) {
           $q->whereNotNull('fecha_limite')
           ->whereColumn('fecha_limite', '>=', 'expedientes.fecha_resolucion');
           })
          ->count();

        $cumplimientoPorcentaje = $totalConPlazo > 0
            ? round(($cumplidosEnPlazo / $totalConPlazo) * 100, 1)
            : 100;

        $reportes = [
            'expedientes_por_mes' => $expedientesPorMes,
            'resueltos_por_mes' => $resueltosPorMes,
            'funcionarios_rendimiento' => $funcionariosRendimiento,
            'por_tipo_tramite' => $porTipoTramite,
            'por_prioridad' => $porPrioridad,
            'tiempos_promedio' => $tiemposPromedio,
            'cumplimiento_porcentaje' => $cumplimientoPorcentaje,
            'año_actual' => $año,
            'total_expedientes' => Expediente::where('id_area', $areaId)->whereYear('created_at', $año)->count(),
            'total_resueltos' => Expediente::where('id_area', $areaId)
                ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto','aprobado','archivado']))
                ->whereYear('updated_at', $año)
                ->count(),

        ];

        return view('jefe-area.reportes', compact('reportes'));
    }

    /**
     * Control de plazos con gestión avanzada
     */
     
    public function controlPlazos()
 {
    $areaId = auth()->user()->id_area;

    // Estados “pendientes” (según tus slugs reales)
    $slugsPendientes = ['derivado', 'en_proceso'];

    $stats = [
        'vencidos' => Expediente::where('id_area', $areaId)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', $slugsPendientes))
            ->whereHas('derivaciones', function($q) use ($areaId) {
                $q->where('id_area_destino', $areaId)
                  ->where('estado', 'pendiente')
                  ->whereNotNull('fecha_limite')
                  ->where('fecha_limite', '<', now());
            })->count(),

        'por_vencer' => Expediente::where('id_area', $areaId)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', $slugsPendientes))
            ->whereHas('derivaciones', function($q) use ($areaId) {
                $q->where('id_area_destino', $areaId)
                  ->where('estado', 'pendiente')
                  ->whereNotNull('fecha_limite')
                  ->whereBetween('fecha_limite', [now(), now()->addDays(3)]);
            })->count(),

        'en_plazo' => Expediente::where('id_area', $areaId)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', $slugsPendientes))
            ->whereHas('derivaciones', function($q) use ($areaId) {
                $q->where('id_area_destino', $areaId)
                  ->where('estado', 'pendiente')
                  ->whereNotNull('fecha_limite')
                  ->where('fecha_limite', '>', now()->addDays(3));
            })->count(),

        'sin_asignar' => Expediente::where('id_area', $areaId)
            ->whereNull('id_funcionario_asignado')
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', $slugsPendientes))
            ->count(),

        'por_aprobar' => Expediente::where('id_area', $areaId)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['en_revision','resuelto']))
            ->count(),
    ];

    // Críticos (vencidos / por vencer)
    $expedientesCriticos = Expediente::where('id_area', $areaId)
        ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', $slugsPendientes))
        ->with([
            'funcionarioAsignado',
            'tipoTramite',
            'estadoExpediente',
            'derivaciones' => function($q) use ($areaId) {
                $q->where('id_area_destino', $areaId)
                  ->where('estado', 'pendiente')
                  ->latest();
            }
        ])
        ->get()
        ->filter(fn($exp) => $exp->es_critico)   // tus accessors
        ->sortByDesc('dias_vencido');

    // Sin asignar
    $expedientesSinAsignar = Expediente::where('id_area', $areaId)
        ->whereNull('id_funcionario_asignado')
        ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', $slugsPendientes))
        ->with([
            'tipoTramite',
            'estadoExpediente',
            'derivaciones' => function($q) use ($areaId) {
                $q->where('id_area_destino', $areaId)
                  ->where('estado', 'pendiente')
                  ->latest();
            }
        ])
        ->get();

    // Funcionarios por carga
    $subdireccionesIds = $this->getSubdireccionesIds($areaId);
    $funcionarios = $this->funcionarioEstadisticasService->obtenerFuncionariosPorCarga($subdireccionesIds, 'asc');

    // Cumplimiento (esto depende de cómo guardas “fecha_resolucion”)
    $totalExpedientes = Expediente::where('id_area', $areaId)
        ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto','aprobado','archivado']))
        ->count();

    $resueltosEnPlazo = Expediente::where('id_area', $areaId)
        ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto','aprobado','archivado']))
        ->whereNotNull('fecha_resolucion')
        ->whereHas('derivaciones', function($q) {
            $q->whereNotNull('fecha_limite')
              ->whereColumn('fecha_limite', '>=', 'expedientes.fecha_resolucion');
        })
        ->count();

    $cumplimiento = [
        'porcentaje' => $totalExpedientes > 0 ? round(($resueltosEnPlazo / $totalExpedientes) * 100, 1) : 100,
        'total' => $totalExpedientes,
        'en_plazo' => $resueltosEnPlazo,
    ];

    return view('jefe-area.control-plazos', compact(
        'stats',
        'expedientesCriticos',
        'expedientesSinAsignar',
        'funcionarios',
        'cumplimiento'
    ));
 }

    /**
     * Supervisión avanzada de funcionarios
     */
   
     public function supervision()
 {
    $user = auth()->user();
    $areaId = $user->id_area;
    $esAdministrador = $user->role?->nombre === 'Administrador';

    $slugsPendientes = ['derivado', 'en_proceso'];

    // Funcionarios de subdirecciones
    $subdireccionesIds = $areaId ? $this->getSubdireccionesIds($areaId) : [];

    if ($esAdministrador && !$areaId) {
        $funcionarios = $this->funcionarioEstadisticasService
            ->obtenerFuncionariosConEstadisticas(
                Area::where('activo', true)->pluck('id_area')->toArray()
            );
    } else {
        $funcionarios = $this->funcionarioEstadisticasService
            ->obtenerFuncionariosConEstadisticas($subdireccionesIds);
    }

    // Estadísticas generales del área
    $queryEstadisticas = Expediente::query();

    if (!$esAdministrador || $areaId) {
        $queryEstadisticas->where('id_area', $areaId);
    }

    $estadisticasArea = [
        'total_pendientes' => (clone $queryEstadisticas)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', $slugsPendientes))
            ->count(),

        'total_vencidos' => (clone $queryEstadisticas)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', $slugsPendientes))
            ->whereHas('derivaciones', function($q) {
                $q->where('estado', 'pendiente')
                  ->whereNotNull('fecha_limite')
                  ->where('fecha_limite', '<', now());
            })
            ->count(),

        'resueltos_mes' => (clone $queryEstadisticas)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto','aprobado','archivado']))
            ->whereMonth('updated_at', now()->month)
            ->count(),

        'promedio_carga' => $funcionarios->avg('carga_trabajo'),
        'funcionario_mas_cargado' => $funcionarios->sortByDesc('carga_trabajo')->first(),
        'funcionario_menos_cargado' => $funcionarios->sortBy('carga_trabajo')->first(),
    ];

    // Procesos especiales (esperando aprobación)
    $procesosEspeciales = Expediente::where('id_area', $areaId)
        ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'resuelto'))
        ->with(['funcionarioAsignado', 'tipoTramite'])
        ->orderBy('fecha_resolucion', 'asc')
        ->take(10)
        ->get();

    // Alertas
    $alertas = collect();

    $sobrecargados = $funcionarios->filter(fn($f) => $f->carga_trabajo > 10);
    foreach ($sobrecargados as $func) {
        $alertas->push([
            'tipo' => 'danger',
            'mensaje' => "{$func->name} tiene {$func->carga_trabajo} expedientes pendientes",
            'icono' => 'exclamation-triangle'
        ]);
    }

    if ($estadisticasArea['total_vencidos'] > 0) {
        $alertas->push([
            'tipo' => 'danger',
            'mensaje' => "{$estadisticasArea['total_vencidos']} expediente(s) vencido(s)",
            'icono' => 'clock'
        ]);
    }

    $porAprobar = Expediente::where('id_area', $areaId)
        ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'resuelto'))
        ->count();

    if ($porAprobar > 0) {
        $alertas->push([
            'tipo' => 'info',
            'mensaje' => "{$porAprobar} expediente(s) listos para aprobar",
            'icono' => 'check-circle'
        ]);
    }

    return view('jefe-area.supervision', compact(
        'funcionarios',
        'estadisticasArea',
        'procesosEspeciales',
        'alertas'
    ));
 }



        public function validarDocumentos()
  {
    $user = auth()->user();
    $areaId = $user->id_area;

    $esAdministrador = $user->role?->nombre === 'Administrador';

    $queryEstadisticas = Expediente::query();
    $queryExpedientes  = Expediente::query();

    // Si NO es admin o SI tiene área, filtra por su área
    if (!$esAdministrador || $areaId) {
        $queryEstadisticas->where('id_area', $areaId);
        $queryExpedientes->where('id_area', $areaId);
    }

    $estadisticas = [
        'pendientes_validacion' => (clone $queryEstadisticas)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['en_revision', 'resuelto']))
            ->count(),

        'validados_hoy' => (clone $queryEstadisticas)
            ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'aprobado'))
            ->whereDate('updated_at', today())
            ->count(),

        'rechazados_hoy' => (clone $queryEstadisticas)
            ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'rechazado'))
            ->whereDate('updated_at', today())
            ->count(),

        'requieren_autorizacion' => 0
    ];

    $expedientesPendientes = (clone $queryExpedientes)
        ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['en_revision', 'resuelto']))
        ->with(['funcionarioAsignado', 'tipoTramite', 'area', 'estadoExpediente'])
        ->orderBy('fecha_resolucion', 'asc')
        ->get();

    return view('jefe-area.validar-documentos', compact('estadisticas', 'expedientesPendientes'));
  }

    public function detalleValidacion(Expediente $expediente)
    {
        $this->authorize('view', $expediente);

        $data = [
            'codigo_expediente' => $expediente->codigo_expediente,
            'asunto' => $expediente->asunto,
            'funcionario' => $expediente->funcionarioAsignado->name ?? 'N/A',
            'fecha_resolucion' => $expediente->fecha_resolucion ? $expediente->fecha_resolucion->format('d/m/Y H:i') : 'N/A',
            'observaciones_funcionario' => $expediente->observaciones_funcionario ?? 'Sin observaciones',
            'documentos' => $expediente->documentos->where('tipo', '!=', 'entrada')->values()->map(function($doc) {
                return [
                    'nombre' => $doc->nombre,
                    'url' => asset('storage/' . $doc->ruta_archivo)
                ];
            })
        ];
        
        return response()->json($data);
    }

    /**
     * Valida un expediente (aprobar o rechazar)
     *
     * REFACTORIZADO: Ahora usa ValidarExpedienteRequest
     * ANTES: 26 líneas con validación inline y lógica de negocio
     * DESPUÉS: 18 líneas con FormRequest
     */
    public function validarExpediente(ValidarExpedienteRequest $request, Expediente $expediente)
    {
        $this->authorize('approve', $expediente);

        $accion = $request->input('accion');
        $observaciones = $request->input('observaciones');

        if ($accion === 'aprobar') {
            $expediente->update([
                'estado' => 'aprobado',
                'aprobado_por' => auth()->user()->id,
                'fecha_aprobacion' => now()
            ]);
            $expediente->agregarHistorial('Expediente aprobado por Jefe de Área', auth()->user()->id);
        } else {
            $expediente->update([
                'estado' => 'rechazado',
                'motivo_rechazo' => $observaciones
            ]);
            $expediente->agregarHistorial('Expediente rechazado: ' . $observaciones, auth()->user()->id);
        }

        return response()->json(['success' => true]);
    }

    
        public function conflictos()
     {
       $areaId = auth()->user()->id_area;

       $estadisticas = [
         'expedientes_vencidos' => Expediente::where('id_area', $areaId)
            ->whereHas('derivaciones', function ($q) use ($areaId) {
                $q->where('id_area_destino', $areaId)
                  ->where('estado', 'pendiente')
                  ->whereNotNull('fecha_limite')
                  ->where('fecha_limite', '<', now());
            })
            ->count(),

        'reasignaciones_pendientes' => 0,
        'autorizaciones_especiales' => 0,

        'observaciones_ciudadano' => Expediente::where('id_area', $areaId)
            ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'observado'))
            ->count(),
        ];

      $conflictos = Expediente::where('id_area', $areaId)
        ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', [
            'derivado', 'en_proceso', 'observado'
        ]))
        ->with([
            'funcionarioAsignado',
            'estadoExpediente',
            'derivaciones' => function ($q) use ($areaId) {
                $q->where('id_area_destino', $areaId)
                  ->where('estado', 'pendiente')
                  ->latest();
            }
        ])
        ->get()
        ->map(function ($exp) {
            $derivacion = $exp->derivaciones->first();

            if ($derivacion && $derivacion->fecha_limite && $derivacion->fecha_limite->isPast()) {
                $exp->tipo_conflicto = 'vencido';
                $exp->dias_vencido = (int) $derivacion->fecha_limite->diffInDays(now());
            } else {
                $exp->tipo_conflicto = 'observacion';
                $exp->dias_vencido = 0;
            }

            return $exp;
        });

      return view('jefe-area.conflictos', compact('estadisticas', 'conflictos'));
    }

    public function detalleConflicto(Expediente $expediente)
    {
        $this->authorize('view', $expediente);

        $derivacion = $expediente->derivaciones->first();
        $diasVencido = $derivacion && $derivacion->fecha_limite ?
            ($derivacion->fecha_limite->isPast() ? (int) $derivacion->fecha_limite->diffInDays(now()) : 0) : 0;
        
        $data = [
            'codigo_expediente' => $expediente->codigo_expediente,
            'asunto' => $expediente->asunto,
            'funcionario' => $expediente->funcionarioAsignado->name ?? 'Sin asignar',
            'fecha_limite' => $derivacion ? $derivacion->fecha_limite->format('d/m/Y') : 'N/A',
            'dias_vencido' => $diasVencido,
            'tipo_conflicto' => $diasVencido > 0 ? 'vencido' : 'normal',
            'motivo_conflicto' => $diasVencido > 0 ? 
                "Expediente vencido hace {$diasVencido} días" : 
                'Requiere autorización especial',
            'historial' => $expediente->historial->take(5)->map(function($h) {
                return [
                    'fecha' => $h->created_at->format('d/m H:i'),
                    'descripcion' => $h->descripcion
                ];
            })
        ];
        
        return response()->json($data);
    }

    /**
     * Extiende el plazo de un expediente
     *
     * REFACTORIZADO: Ahora usa ExtenderPlazoRequest y DerivacionService
     * ANTES: 23 líneas con validación inline y lógica de negocio
     * DESPUÉS: 11 líneas delegando al Service
     */
    public function extenderPlazo(ExtenderPlazoRequest $request, Expediente $expediente)
    {
        $this->authorize('extendDeadline', $expediente);

        $this->derivacionService->extenderPlazo(
            $expediente,
            $request->input('dias_adicionales'),
            $request->input('motivo')
        );

        return response()->json(['success' => true]);
    }

    public function autorizarEspecial(Request $request, Expediente $expediente)
    {
        $this->authorize('grantSpecialAuthorization', $expediente);

        $observaciones = $request->input('observaciones');
        
        $expediente->agregarHistorial(
            "Autorización especial otorgada: {$observaciones}", 
            auth()->user()->id
        );
        
        return response()->json(['success' => true]);
    }

    public function metas()
    {
        $areaId = auth()->user()->id_area;

 $kpis = [ 
    'expedientes_mes' => Expediente::where('id_area', $areaId)
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count(),

    'resueltos_mes' => Expediente::where('id_area', $areaId)
        ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto','aprobado','archivado']))
        ->whereMonth('updated_at', now()->month)
        ->whereYear('updated_at', now()->year)
        ->count(),

    'tiempo_promedio' => Expediente::where('id_area', $areaId)
        ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto','aprobado','archivado']))
        ->whereNotNull('fecha_resolucion')
        ->get()
        ->avg(fn($exp) => $exp->created_at->diffInDays($exp->fecha_resolucion)) ?? 0,

    'eficiencia' => 85
 ];

        $metas = \App\Models\Meta::where('id_area', $areaId)->get();

        // Funcionarios de subdirecciones - usando el servicio optimizado
        $subdireccionesIds = $this->getSubdireccionesIds($areaId);

        $rendimientoFuncionarios = User::whereIn('id_area', $subdireccionesIds)
            ->where('id_rol', RolUsuario::FUNCIONARIO->value)
            ->with('area')
            ->withCount(['expedientesAsignados as expedientes_resueltos' => function($q) {
             $q->whereHas('estadoExpediente', fn($qq) => $qq->where('slug', 'resuelto'))
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year);
         }])
            ->addSelect([
                'tiempo_promedio_dias' => Expediente::selectRaw('AVG(DATEDIFF(fecha_resolucion, created_at))')
                    ->whereColumn('id_funcionario_asignado', 'users.id')
                    ->whereHas('estadoExpediente', fn($qq) => $qq->where('slug', 'resuelto'))
                    ->whereNotNull('fecha_resolucion')
            ])
            ->get()
            ->map(function($funcionario) {
                $funcionario->tiempo_promedio = round($funcionario->tiempo_promedio_dias ?? 0, 1);
                return $funcionario;
            });

        return view('jefe-area.metas', compact('kpis', 'metas', 'rendimientoFuncionarios'));
    }

    public function storeMeta(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string',
            'tipo' => 'required|in:expedientes,tiempo,eficiencia,satisfaccion',
            'valor_meta' => 'required|numeric',
            'periodo' => 'required|in:mensual,trimestral,semestral,anual',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio'
        ]);

        \App\Models\Meta::create([
            'id_area' => auth()->user()->id_area,
            'descripcion' => $request->descripcion,
            'tipo' => $request->tipo,
            'valor_meta' => $request->valor_meta,
            'periodo' => $request->periodo,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin
        ]);

        return back()->with('success', 'Meta creada correctamente');
    }

    /**
     * Obtener áreas disponibles para derivación (AJAX)
     */
    public function areasParaDerivacion()
    {
        $areas = $this->derivacionService->obtenerAreasParaDerivacion(auth()->user());
        return response()->json($areas);
    }

    /**
     * Obtener funcionarios de un área (AJAX)
     */
    public function funcionariosDeArea(int $areaId)
    {
        $funcionarios = $this->derivacionService->obtenerFuncionariosParaAsignacion($areaId);
        return response()->json($funcionarios);
    }

    /**
     * Mostrar formulario de derivación
     */
    public function derivarForm(Expediente $expediente)
    {
        $this->authorize('update', $expediente);

        // Obtener todas las áreas activas para derivar
        $areas = Area::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('jefe-area.derivar', compact('expediente', 'areas'));
    }

    /**
     * Derivar expediente a otra área
     */
    public function derivar(Request $request, Expediente $expediente)
    {
        $this->authorize('update', $expediente);

        $request->validate([
            'id_area_destino' => 'required|exists:areas,id_area',
            'id_funcionario_destino' => 'nullable|exists:users,id',
            'observaciones' => 'required|string|min:10|max:500',
            'plazo_dias' => 'required|integer|min:1|max:90',
            'prioridad' => 'nullable|in:baja,normal,alta,urgente'
        ], [
            'id_area_destino.required' => 'Debe seleccionar un área de destino',
            'observaciones.required' => 'Debe ingresar las observaciones de la derivación',
            'observaciones.min' => 'Las observaciones deben tener al menos 10 caracteres',
            'plazo_dias.required' => 'Debe especificar el plazo en días'
        ]);

        try {
            $this->derivacionService->derivarExpediente(
                $expediente,
                $request->id_area_destino,
                $request->id_funcionario_destino,
                $request->plazo_dias,
                $request->prioridad ?? $expediente->prioridad ?? 'normal',
                $request->observaciones
            );

            $area_destino = Area::find($request->id_area_destino);

            return redirect()->route('jefe-area.expedientes')
                ->with('success', 'Expediente derivado correctamente a ' . $area_destino->nombre);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al derivar expediente: ' . $e->getMessage());
        }
    }
}