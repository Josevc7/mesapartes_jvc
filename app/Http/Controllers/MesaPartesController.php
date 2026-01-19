<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\Area;
use App\Models\TipoTramite;
use App\Models\Derivacion;
use App\Models\Documento;
use App\Models\User;
use App\Models\Persona;
use App\Http\Requests\Expediente\StoreExpedienteRequest;
use App\Http\Requests\Derivacion\StoreDerivacionRequest;
use App\Services\ExpedienteService;
use App\Services\DerivacionService;
use App\Services\EstadisticasService;

class MesaPartesController extends Controller
{
    protected ExpedienteService $expedienteService;
    protected DerivacionService $derivacionService;
    protected EstadisticasService $estadisticasService;

    public function __construct(
        ExpedienteService $expedienteService,
        DerivacionService $derivacionService,
        EstadisticasService $estadisticasService
    ) {
        $this->expedienteService = $expedienteService;
        $this->derivacionService = $derivacionService;
        $this->estadisticasService = $estadisticasService;
    }
    public function index(Request $request)
    {
        $query = Expediente::with([
                'tipoTramite',
                'ciudadano',
                'area',
                'persona',
                'funcionarioAsignado',
                'derivaciones' => fn($q) => $q->latest()->limit(1)
            ]);

        // Filtro por estados (por defecto los activos)
        $estadosDefault = ['recepcionado', 'registrado', 'clasificado', 'derivado', 'en_proceso'];
        if ($request->filled('estado')) {
            if ($request->estado === 'todos') {
                // No filtrar por estado
            } else {
                $query->where('estado', $request->estado);
            }
        } else {
            $query->whereIn('estado', $estadosDefault);
        }

        // Filtro por canal
        if ($request->filled('canal')) {
            $query->where('canal', $request->canal);
        }

        // Filtro por área
        if ($request->filled('area')) {
            $query->where('id_area', $request->area);
        }

        // Filtro por tipo de trámite
        if ($request->filled('tipo_tramite')) {
            $query->where('id_tipo_tramite', $request->tipo_tramite);
        }

        // Filtro por fecha
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Búsqueda general
        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where(function($q) use ($busqueda) {
                $q->where('codigo_expediente', 'like', "%{$busqueda}%")
                  ->orWhere('asunto', 'like', "%{$busqueda}%")
                  ->orWhere('remitente', 'like', "%{$busqueda}%")
                  ->orWhereHas('persona', function($qp) use ($busqueda) {
                      $qp->where('nombres', 'like', "%{$busqueda}%")
                         ->orWhere('apellidos', 'like', "%{$busqueda}%")
                         ->orWhere('razon_social', 'like', "%{$busqueda}%")
                         ->orWhere('numero_documento', 'like', "%{$busqueda}%");
                  });
            });
        }

        // Paginación mejorada: permitir elegir cantidad de registros
        $perPage = $request->input('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : 15;

        $expedientes = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        // Datos para los filtros (con caché de 5 minutos)
        $areas = \Cache::remember('areas_activas', 300, function() {
            return Area::where('activo', true)->orderBy('nombre')->get();
        });

        $tipoTramites = \Cache::remember('tipo_tramites_activos', 300, function() {
            return TipoTramite::where('activo', true)->orderBy('nombre')->get();
        });

        // Estadísticas rápidas (con caché de 1 minuto para datos en tiempo real)
        $estadisticas = \Cache::remember('mesa_partes_estadisticas', 60, function() use ($estadosDefault) {
            return [
                'total' => Expediente::whereIn('estado', $estadosDefault)->count(),
                'pendientes' => Expediente::where('estado', 'recepcionado')->count(),
                'clasificados' => Expediente::where('estado', 'clasificado')->count(),
                'derivados' => Expediente::where('estado', 'derivado')->count(),
                'en_proceso' => Expediente::where('estado', 'en_proceso')->count(),
                'virtuales' => Expediente::where('canal', 'virtual')->where('estado', 'recepcionado')->count(),
            ];
        });

        return view('mesa-partes.index', compact('expedientes', 'areas', 'tipoTramites', 'estadisticas'));
    }

    public function registrar()
    {
        $tipoTramites = TipoTramite::where('activo', true)->orderBy('nombre')->get();
        return view('mesa-partes.registrar', compact('tipoTramites'));
    }

    /**
     * Registra un nuevo expediente en Mesa de Partes
     *
     * REFACTORIZADO: Ahora usa FormRequest y Service
     * ADEMÁS: Clasifica y deriva automáticamente en un solo paso
     */
    public function storeRegistrar(StoreExpedienteRequest $request)
    {
        try {
            \DB::beginTransaction();

            // 1. Registrar el expediente
            $expediente = $this->expedienteService->registrarExpediente(
                $request->validated()
            );

            // 2. Clasificar el expediente automáticamente
            $tipoTramite = TipoTramite::find($request->id_tipo_tramite);
            $area = Area::find($request->id_area);

            $expediente->update([
                'id_area' => $request->id_area,
                'prioridad' => $request->prioridad,
                'estado' => 'derivado' // Cambiar directamente a derivado
            ]);

            $descripcionClasificacion = "Expediente registrado y clasificado automáticamente - Tipo: {$tipoTramite->nombre}, Área: {$area->nombre}, Prioridad: {$request->prioridad}";

            if ($request->observaciones_clasificacion) {
                $descripcionClasificacion .= " - Observaciones: {$request->observaciones_clasificacion}";
            }

            $expediente->agregarHistorial($descripcionClasificacion, auth()->id());

            // 3. Derivar el expediente automáticamente
            $this->derivacionService->derivarExpediente(
                $expediente,
                $request->id_area,
                $request->id_funcionario_asignado,
                $request->plazo_dias ?? 15,
                $request->prioridad_derivacion ?? $request->prioridad,
                $request->observaciones_derivacion
            );

            \DB::commit();

            // Limpiar caché de estadísticas después de crear expediente
            \Cache::forget('mesa_partes_estadisticas');

            return redirect()
                ->route('mesa-partes.registrar')
                ->with('success', 'Expediente registrado, clasificado y derivado correctamente en un solo paso')
                ->with('codigo_expediente', $expediente->codigo_expediente)
                ->with('id_expediente', $expediente->id_expediente);

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al procesar el expediente: ' . $e->getMessage());
        }
    }

    public function show(Expediente $expediente)
    {
        $expediente->load(['documentos', 'derivaciones.funcionarioAsignado', 'derivaciones.areaDestino', 'historial.usuario']);
        return view('mesa-partes.show', compact('expediente'));
    }

    public function clasificar(Expediente $expediente)
    {
        $tipoTramites = TipoTramite::where('activo', true)->orderBy('nombre')->get();
        $areas = Area::where('activo', true)->orderBy('nombre')->get();
        return view('mesa-partes.clasificar', compact('expediente', 'tipoTramites', 'areas'));
    }

    public function updateClasificacion(Request $request, Expediente $expediente)
    {
        $request->validate([
            'id_tipo_tramite' => 'required|exists:tipo_tramites,id_tipo_tramite',
            'id_area' => 'required|exists:areas,id_area',
            'prioridad' => 'required|in:baja,normal,alta,urgente',
            'observaciones_clasificacion' => 'nullable|string|max:500'
        ]);

        $expediente->update([
            'id_tipo_tramite' => $request->id_tipo_tramite,
            'id_area' => $request->id_area,
            'prioridad' => $request->prioridad,
            'estado' => 'clasificado'
        ]);
        
        $tipoTramite = TipoTramite::find($request->id_tipo_tramite);
        $area = Area::find($request->id_area);
        $descripcionHistorial = "Expediente clasificado - Tipo: {$tipoTramite->nombre}, Área: {$area->nombre}, Prioridad: {$request->prioridad}";
        
        if ($request->observaciones_clasificacion) {
            $descripcionHistorial .= " - Observaciones: {$request->observaciones_clasificacion}";
        }
        
        $expediente->agregarHistorial($descripcionHistorial, auth()->user()->id);

        return redirect()->route('mesa-partes.derivar', $expediente)
            ->with('success', 'Expediente clasificado correctamente. Ahora proceda a derivarlo.');
    }

    public function derivar(Expediente $expediente)
    {
        $areas = Area::where('activo', true)->orderBy('nombre')->get();
        $funcionarios = User::where('id_rol', 4)
            ->where('id_area', $expediente->id_area)
            ->where('activo', true)
            ->orderBy('name')
            ->get();
            
        return view('mesa-partes.derivar', compact('expediente', 'areas', 'funcionarios'));
    }

    /**
     * Deriva un expediente a un área y funcionario
     *
     * REFACTORIZADO: Ahora usa StoreDerivacionRequest y DerivacionService
     * ANTES: 34 líneas con validación inline y lógica de negocio
     * DESPUÉS: 14 líneas delegando al Service
     */
    public function storeDerivar(StoreDerivacionRequest $request, Expediente $expediente)
    {
        $this->derivacionService->derivarExpediente(
            $expediente,
            $request->id_area_destino,
            $request->id_funcionario_asignado,
            $request->plazo_dias,
            $request->prioridad,
            $request->observaciones
        );

        return redirect()->route('mesa-partes.index')
            ->with('success', 'Expediente derivado correctamente');
    }

    public function archivar(Expediente $expediente)
    {
        $expediente->update([
            'estado' => 'archivado',
            'fecha_archivo' => now()
        ]);
        
        return redirect()->route('mesa-partes.index')
            ->with('success', 'Expediente archivado correctamente');
    }

    public function monitoreo()
    {
        $vencidos = Expediente::whereIn('estado', ['derivado', 'en_proceso'])
            ->whereHas('derivaciones', function($q) {
                $q->where('fecha_limite', '<', now())
                  ->where('estado', 'Pendiente');
            })
            ->with([
                'tipoTramite',
                'area',
                'funcionarioAsignado',
                'derivaciones' => function($q) {
                    $q->where('fecha_limite', '<', now())
                      ->where('estado', 'Pendiente')
                      ->orderBy('fecha_limite', 'asc');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'vencidos_page');

        $porVencer = Expediente::whereIn('estado', ['derivado', 'en_proceso'])
            ->whereHas('derivaciones', function($q) {
                $q->whereBetween('fecha_limite', [now(), now()->addDays(3)])
                  ->where('estado', 'Pendiente');
            })
            ->with([
                'tipoTramite',
                'area',
                'funcionarioAsignado',
                'derivaciones' => function($q) {
                    $q->whereBetween('fecha_limite', [now(), now()->addDays(3)])
                      ->where('estado', 'Pendiente')
                      ->orderBy('fecha_limite', 'asc');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'por_vencer_page');

        return view('mesa-partes.monitoreo', compact('vencidos', 'porVencer'));
    }

    public function acuseRecibo(Expediente $expediente)
    {
        return view('mesa-partes.cargo', compact('expediente'));
    }

    /**
     * Dashboard de Mesa de Partes
     *
     * REFACTORIZADO: Ahora usa EstadisticasService
     * ANTES: 26 líneas con queries inline
     * DESPUÉS: 19 líneas con lógica de alertas separada
     */
    public function dashboard()
    {
        $estadisticas = $this->estadisticasService->obtenerEstadisticasMesaPartes();

        $expedientesRecientes = Expediente::with(['ciudadano', 'tipoTramite', 'persona'])
            ->whereIn('estado', ['recepcionado', 'registrado', 'derivado'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $alertas = collect();
        if ($estadisticas['vencidos'] > 0) {
            $alertas->push((object)['tipo' => 'danger', 'titulo' => 'Expedientes Vencidos', 'mensaje' => "Hay {$estadisticas['vencidos']} expedientes vencidos que requieren atención inmediata."]);
        }
        if ($estadisticas['pendientes_clasificar'] > 5) {
            $alertas->push((object)['tipo' => 'warning', 'titulo' => 'Pendientes Clasificar', 'mensaje' => "Hay {$estadisticas['pendientes_clasificar']} expedientes pendientes de clasificar."]);
        }

        return view('mesa-partes.dashboard', compact('estadisticas', 'expedientesRecientes', 'alertas'));
    }

    public function estadisticas()
    {
        // Optimización: Una sola consulta para estadísticas de hoy
        $hoy = today();
        $estadisticasHoy = Expediente::selectRaw("
            SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as registrados_hoy,
            SUM(CASE WHEN DATE(updated_at) = ? AND estado = 'derivado' THEN 1 ELSE 0 END) as derivados_hoy,
            SUM(CASE WHEN DATE(updated_at) = ? AND estado = 'archivado' THEN 1 ELSE 0 END) as archivados_hoy
        ", [$hoy, $hoy, $hoy])->first();

        $estadisticas = [
            'registrados_hoy' => $estadisticasHoy->registrados_hoy ?? 0,
            'clasificados_hoy' => $estadisticasHoy->derivados_hoy ?? 0,
            'derivados_hoy' => $estadisticasHoy->derivados_hoy ?? 0,
            'archivados_hoy' => $estadisticasHoy->archivados_hoy ?? 0
        ];

        $tiposTramiteFrecuentes = TipoTramite::withCount('expedientes')
            ->orderBy('expedientes_count', 'desc')
            ->limit(5)
            ->get();

        $expedientesPendientes = Expediente::with(['ciudadano', 'tipoTramite', 'area'])
            ->whereIn('estado', ['recepcionado'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Optimización: Una sola consulta para los últimos 30 días en lugar de 60 consultas
        $fechaInicio = now()->subDays(29)->startOfDay();
        $datosGrafico = Expediente::selectRaw("
            DATE(created_at) as fecha,
            COUNT(*) as registrados,
            SUM(CASE WHEN estado = 'derivado' THEN 1 ELSE 0 END) as derivados
        ")
            ->where('created_at', '>=', $fechaInicio)
            ->groupBy(\DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get()
            ->keyBy('fecha');

        $graficoLabels = [];
        $graficoRegistrados = [];
        $graficoDerivados = [];

        for ($i = 29; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $fechaStr = $fecha->format('Y-m-d');
            $graficoLabels[] = $fecha->format('d/m');
            $graficoRegistrados[] = $datosGrafico->get($fechaStr)->registrados ?? 0;
            $graficoDerivados[] = $datosGrafico->get($fechaStr)->derivados ?? 0;
        }

        return view('mesa-partes.estadisticas', compact(
            'estadisticas', 'tiposTramiteFrecuentes', 'expedientesPendientes',
            'graficoLabels', 'graficoRegistrados', 'graficoDerivados'
        ));
    }

    public function numeracion()
    {
        $numeracionActual = \App\Models\Numeracion::where('año', date('Y'))->first();
        if (!$numeracionActual) {
            $numeracionActual = \App\Models\Numeracion::create([
                'año' => date('Y'),
                'ultimo_numero' => 0
            ]);
        }
        $numeracion = $numeracionActual;
        
        $estadisticas = [
            'total_expedientes' => Expediente::whereYear('created_at', date('Y'))->count(),
            'este_mes' => Expediente::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count(),
            'hoy' => Expediente::whereDate('created_at', today())->count()
        ];

        $historialNumeracion = \App\Models\Numeracion::orderBy('año', 'desc')->get()->map(function($registro) {
            $registro->total_expedientes = \App\Models\Expediente::whereYear('created_at', $registro->año)->count();
            return $registro;
        });

        return view('mesa-partes.numeracion', compact('numeracion', 'estadisticas', 'historialNumeracion'));
    }

    public function verificarNumeracion()
    {
        $resultado = ['status' => 'OK', 'mensaje' => 'Numeración verificada correctamente'];
        return response()->json($resultado);
    }

    public function buscarPersona(Request $request)
    {
        try {
            $dni = $request->get('q');
            
            if (empty($dni)) {
                return response()->json(['success' => false, 'data' => []]);
            }
            
            $persona = Persona::where('numero_documento', $dni)->first();
            
            if ($persona) {
                return response()->json(['success' => true, 'data' => [$persona]]);
            }
            
            return response()->json(['success' => true, 'data' => []]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Error al buscar persona']);
        }
    }
    
    /**
     * MÉTODO DEPRECADO - Usar acuseRecibo() en su lugar
     * Mantenido por compatibilidad, pero ya no se usa en rutas
     */
    // public function cargoRecepcion(Expediente $expediente)
    // {
    //     $expediente->load(['documentos', 'tipoTramite', 'persona']);
    //     $pdf = \PDF::loadView('pdf.cargo-recepcion', compact('expediente'));
    //     return $pdf->download('CARGO_' . $expediente->codigo_expediente . '.pdf');
    // }

    /**
     * Lista los expedientes virtuales pendientes de clasificación
     */
    public function expedientesVirtuales()
    {
        $expedientes = Expediente::where('canal', 'virtual')
            ->where('estado', 'recepcionado')
            ->with(['persona', 'tipoTramite', 'documentos'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('mesa-partes.expedientes-virtuales', compact('expedientes'));
    }

    /**
     * Muestra el formulario para clasificar y derivar un expediente virtual
     */
    public function clasificarVirtual(Expediente $expediente)
    {
        // Verificar que sea un expediente virtual
        if ($expediente->canal != 'virtual') {
            return redirect()
                ->route('mesa-partes.index')
                ->with('error', 'Este expediente no es virtual. Use el flujo normal de clasificación.');
        }

        // Verificar que esté en estado recepcionado
        if ($expediente->estado != 'recepcionado') {
            return redirect()
                ->route('mesa-partes.index')
                ->with('error', 'Este expediente ya ha sido clasificado.');
        }

        $expediente->load(['persona', 'tipoTramite', 'documentos']);

        return view('mesa-partes.clasificar-virtual', compact('expediente'));
    }

    /**
     * Procesa la clasificación y derivación de un expediente virtual
     */
    public function storeClasificarVirtual(Request $request, Expediente $expediente)
    {
        $request->validate([
            'id_area' => 'required|exists:areas,id_area',
            'prioridad' => 'required|in:baja,normal,alta,urgente',
            'observaciones_clasificacion' => 'nullable|string|max:500',
            'id_funcionario_asignado' => 'nullable|exists:users,id',
            'plazo_dias' => 'required|integer|min:1|max:365',
            'prioridad_derivacion' => 'required|in:baja,normal,alta,urgente',
            'observaciones_derivacion' => 'nullable|string|max:1000',
        ]);

        try {
            \DB::beginTransaction();

            // 1. Clasificar el expediente
            $area = Area::find($request->id_area);

            $expediente->update([
                'id_area' => $request->id_area,
                'prioridad' => $request->prioridad,
                'estado' => 'derivado'
            ]);

            $descripcionClasificacion = "Expediente virtual clasificado - Área: {$area->nombre}, Prioridad: {$request->prioridad}";

            if ($request->observaciones_clasificacion) {
                $descripcionClasificacion .= " - Observaciones: {$request->observaciones_clasificacion}";
            }

            $expediente->agregarHistorial($descripcionClasificacion, auth()->id());

            // 2. Derivar el expediente
            $this->derivacionService->derivarExpediente(
                $expediente,
                $request->id_area,
                $request->id_funcionario_asignado,
                $request->plazo_dias,
                $request->prioridad_derivacion,
                $request->observaciones_derivacion
            );

            \DB::commit();

            return redirect()
                ->route('mesa-partes.expedientes-virtuales')
                ->with('success', "Expediente virtual {$expediente->codigo_expediente} clasificado y derivado correctamente")
                ->with('codigo_expediente', $expediente->codigo_expediente)
                ->with('id_expediente', $expediente->id_expediente);

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al procesar el expediente: ' . $e->getMessage());
        }
    }
}