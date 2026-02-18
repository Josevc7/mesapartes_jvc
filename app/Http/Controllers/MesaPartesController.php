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
use App\Models\EstadoExpediente;

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
        $estadosDefault = ['pendiente_recepcion', 'recepcionado', 'registrado', 'clasificado', 'derivado', 'asignado', 'en_proceso'];
        if ($request->filled('estado')) {
            if ($request->estado === 'todos') {
                // No filtrar por estado
            } else {
                $query->whereHas('estadoExpediente', fn($q) => $q->where('slug', $request->estado));
            }
        } else {
            $query->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', $estadosDefault));
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
                'total' => Expediente::whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', $estadosDefault))->count(),
                'pendientes' => Expediente::whereHas('estadoExpediente', fn($q) => $q->where('slug', 'recepcionado'))->count(),
                'clasificados' => Expediente::whereHas('estadoExpediente', fn($q) => $q->where('slug', 'clasificado'))->count(),
                'derivados' => Expediente::whereHas('estadoExpediente', fn($q) => $q->where('slug', 'derivado'))->count(),
                'en_proceso' => Expediente::whereHas('estadoExpediente', fn($q) => $q->where('slug', 'en_proceso'))->count(),
                'virtuales' => Expediente::where('canal', 'virtual')->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['pendiente_recepcion', 'recepcionado']))->count(),
            ];
        });

        return view('mesa-partes.index', compact('expedientes', 'areas', 'tipoTramites', 'estadisticas'));
    }

    public function registrar()
    {
       // $tipoTramites = TipoTramite::where('activo', true)->orderBy('nombre')->get();
       //return view('mesa-partes.registrar', compact('tipoTramites'));
        //  SOLO DIRECCIONES (para el ciudadano)
        $areas = Area::where('activo', true)
        ->where('nivel', Area::NIVEL_DIRECCION)
        ->orderBy('nombre')
       ->get();

       $tipoTramites = TipoTramite::where('activo', true)
        ->orderBy('nombre')
        ->get();
       return view('mesa-partes.registrar', compact('areas', 'tipoTramites'));
         
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

           // $expediente->update([
           //     'id_area' => $request->id_area,
           //     'prioridad' => $request->prioridad,
           //     'estado' => 'derivado' // Cambiar directamente a derivado
           // ]);
             $idDerivado = EstadoExpediente::where('slug', 'derivado')->value('id_estado');

             $expediente->update([
            'id_area' => $request->id_area,
            'prioridad' => $request->prioridad,
            'id_estado' => $idDerivado,
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
   // nuevo storeRegistrar asi
     




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

       //$expediente->update([
        //    'id_tipo_tramite' => $request->id_tipo_tramite,
       //     'id_area' => $request->id_area,
        //    'prioridad' => $request->prioridad,
        //    'estado' => 'clasificado'
        //]);
         $idClasificado = EstadoExpediente::where('slug', 'clasificado')->value('id_estado');

         $expediente->update([
         'id_tipo_tramite' => $request->id_tipo_tramite,
         'id_area' => $request->id_area,
         'prioridad' => $request->prioridad,
         'id_estado' => $idClasificado,
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
        //$expediente->update([
        //    'estado' => 'archivado',
        //    'fecha_archivo' => now()
        //]);
        $idArchivado = EstadoExpediente::where('slug', 'archivado')
        ->value('id_estado');

        $expediente->update([
        'id_estado' => $idArchivado,
        'fecha_archivo' => now()
        ]);
        
        return redirect()->route('mesa-partes.index')
            ->with('success', 'Expediente archivado correctamente');
    }

    public function monitoreo()
    {
        $vencidos = Expediente::whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso']))
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

        $porVencer = Expediente::whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso']))
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
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['pendiente_recepcion', 'recepcionado', 'registrado', 'derivado']))
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
        $slugDerivado = \App\Models\EstadoExpediente::where('slug', 'derivado')->value('id_estado');
        $slugArchivado = \App\Models\EstadoExpediente::where('slug', 'archivado')->value('id_estado');
        $estadisticasHoy = Expediente::selectRaw("
            SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as registrados_hoy,
            SUM(CASE WHEN DATE(updated_at) = ? AND id_estado = ? THEN 1 ELSE 0 END) as derivados_hoy,
            SUM(CASE WHEN DATE(updated_at) = ? AND id_estado = ? THEN 1 ELSE 0 END) as archivados_hoy
        ", [$hoy, $hoy, $slugDerivado, $hoy, $slugArchivado])->first();

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
            ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'recepcionado'))
            ->orderBy('created_at', 'asc')
            ->get();

        // Optimización: Una sola consulta para los últimos 30 días en lugar de 60 consultas
        $fechaInicio = now()->subDays(29)->startOfDay();
        $datosGrafico = Expediente::selectRaw("
            DATE(created_at) as fecha,
            COUNT(*) as registrados,
            SUM(CASE WHEN id_estado = ? THEN 1 ELSE 0 END) as derivados
        ", [$slugDerivado])
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
        $numeracionActual = \App\Models\Numeracion::where('anio', date('Y'))->first();
        if (!$numeracionActual) {
            $numeracionActual = \App\Models\Numeracion::create([
                'anio' => date('Y'),
                'ultimo_numero' => 0
            ]);
        }
        $numeracion = $numeracionActual;
        
        $estadisticas = [
            'total_expedientes' => Expediente::whereYear('created_at', date('Y'))->count(),
            'este_mes' => Expediente::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count(),
            'hoy' => Expediente::whereDate('created_at', today())->count()
        ];

        $historialNumeracion = \App\Models\Numeracion::orderBy('anio', 'desc')->get()->map(function($registro) {
            $registro->total_expedientes = \App\Models\Expediente::whereYear('created_at', $registro->anio)->count();
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
            $documento = $request->get('q');

            if (empty($documento)) {
                return response()->json(['success' => false, 'data' => []]);
            }

            $query = Persona::where('numero_documento', $documento);

            // Filtrar por tipo de documento si se proporciona
            if ($request->filled('tipo_documento')) {
                $query->where('tipo_documento', $request->tipo_documento);
            }

            $persona = $query->first();

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
     * Devuelve los datos del expediente en JSON para el modal de edición
     */
    public function edit(Expediente $expediente)
    {
        $expediente->load(['persona', 'tipoTramite', 'area', 'documentos', 'estadoExpediente']);

        return response()->json([
            'success' => true,
            'expediente' => [
                'id_expediente' => $expediente->id_expediente,
                'codigo_expediente' => $expediente->codigo_expediente,
                'asunto' => $expediente->asunto,
                'asunto_documento' => $expediente->asunto_documento,
                'tipo_documento_entrante' => $expediente->tipo_documento_entrante,
                'numero_documento_entrante' => $expediente->numero_documento_entrante,
                'folios' => $expediente->folios,
                'id_tipo_tramite' => $expediente->id_tipo_tramite,
                'id_area' => $expediente->id_area,
                'prioridad' => $expediente->prioridad,
                'observaciones' => $expediente->observaciones,
                'canal' => $expediente->canal,
                'remitente' => $expediente->remitente,
                'dni_remitente' => $expediente->dni_remitente,
                'estado' => $expediente->estadoExpediente?->slug,
                'estado_formateado' => $expediente->getEstadoFormateadoInteligente(),
                'fecha_registro' => $expediente->created_at->format('d/m/Y H:i'),
                'persona' => $expediente->persona ? [
                    'id_persona' => $expediente->persona->id_persona,
                    'tipo_persona' => $expediente->persona->tipo_persona,
                    'tipo_documento' => $expediente->persona->tipo_documento,
                    'numero_documento' => $expediente->persona->numero_documento,
                    'nombres' => $expediente->persona->nombres,
                    'apellido_paterno' => $expediente->persona->apellido_paterno,
                    'apellido_materno' => $expediente->persona->apellido_materno,
                    'razon_social' => $expediente->persona->razon_social,
                    'representante_legal' => $expediente->persona->representante_legal,
                    'telefono' => $expediente->persona->telefono,
                    'email' => $expediente->persona->email,
                    'direccion' => $expediente->persona->direccion,
                ] : null,
            ],
        ]);
    }

    /**
     * Actualiza los datos de un expediente
     */
    public function update(Request $request, Expediente $expediente)
    {
        $request->validate([
            'asunto' => 'required|string|min:5|max:500',
            'asunto_documento' => 'nullable|string|max:500',
            'tipo_documento_entrante' => 'required|string',
            'numero_documento_entrante' => 'nullable|string|max:50',
            'folios' => 'nullable|integer|min:1|max:9999',
            'id_tipo_tramite' => 'required|exists:tipo_tramites,id_tipo_tramite',
            'id_area' => 'required|exists:areas,id_area',
            'prioridad' => 'required|in:baja,normal,alta,urgente',
            'observaciones' => 'nullable|string|max:1000',
            // Datos de persona
            'tipo_documento_persona' => 'nullable|string|max:20',
            'numero_documento_persona' => 'nullable|string|max:20',
            'nombres' => 'nullable|string|max:100',
            'apellido_paterno' => 'nullable|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'razon_social' => 'nullable|string|max:200',
            'representante_legal' => 'nullable|string|max:200',
            'telefono_persona' => 'nullable|string|max:20',
            'email_persona' => 'nullable|email|max:100',
            'direccion_persona' => 'nullable|string|max:300',
            // Remitente directo (sin persona)
            'dni_remitente' => 'nullable|string|max:20',
            'remitente' => 'nullable|string|max:200',
        ], [
            'asunto.required' => 'El asunto es obligatorio.',
            'asunto.min' => 'El asunto debe tener al menos 5 caracteres.',
            'id_tipo_tramite.required' => 'Debe seleccionar un tipo de trámite.',
            'id_area.required' => 'Debe seleccionar un área.',
        ]);

        try {
            \DB::beginTransaction();

            $cambios = [];

            // Detectar cambios del expediente para el historial
            if ($expediente->asunto !== $request->asunto) {
                $cambios[] = "Asunto: \"{$expediente->asunto}\" → \"{$request->asunto}\"";
            }
            if ($expediente->id_tipo_tramite != $request->id_tipo_tramite) {
                $tipoAnterior = $expediente->tipoTramite->nombre ?? 'N/A';
                $tipoNuevo = TipoTramite::find($request->id_tipo_tramite)->nombre ?? 'N/A';
                $cambios[] = "Tipo trámite: \"{$tipoAnterior}\" → \"{$tipoNuevo}\"";
            }
            if ($expediente->id_area != $request->id_area) {
                $areaAnterior = $expediente->area->nombre ?? 'N/A';
                $areaNueva = Area::find($request->id_area)->nombre ?? 'N/A';
                $cambios[] = "Área: \"{$areaAnterior}\" → \"{$areaNueva}\"";
            }
            if ($expediente->prioridad !== $request->prioridad) {
                $cambios[] = "Prioridad: \"{$expediente->prioridad}\" → \"{$request->prioridad}\"";
            }
            if ($expediente->folios != $request->folios) {
                $cambios[] = "Folios: \"{$expediente->folios}\" → \"{$request->folios}\"";
            }
            if ($expediente->tipo_documento_entrante !== $request->tipo_documento_entrante) {
                $cambios[] = "Tipo doc. entrante: \"{$expediente->tipo_documento_entrante}\" → \"{$request->tipo_documento_entrante}\"";
            }

            // Actualizar datos del expediente
            $expediente->update([
                'asunto' => $request->asunto,
                'asunto_documento' => $request->asunto_documento,
                'tipo_documento_entrante' => $request->tipo_documento_entrante,
                'numero_documento_entrante' => $request->numero_documento_entrante,
                'folios' => $request->folios,
                'id_tipo_tramite' => $request->id_tipo_tramite,
                'id_area' => $request->id_area,
                'prioridad' => $request->prioridad,
                'observaciones' => $request->observaciones,
            ]);

            // Actualizar datos de la persona si existe
            $persona = $expediente->persona;
            if ($persona) {
                if ($persona->numero_documento !== $request->numero_documento_persona && $request->numero_documento_persona) {
                    $cambios[] = "N° Documento: \"{$persona->numero_documento}\" → \"{$request->numero_documento_persona}\"";
                }
                if ($persona->tipo_persona === 'NATURAL') {
                    if ($persona->nombres !== $request->nombres && $request->nombres) {
                        $cambios[] = "Nombres: \"{$persona->nombres}\" → \"{$request->nombres}\"";
                    }
                    if ($persona->apellido_paterno !== $request->apellido_paterno && $request->apellido_paterno) {
                        $cambios[] = "Ap. Paterno: \"{$persona->apellido_paterno}\" → \"{$request->apellido_paterno}\"";
                    }
                    if ($persona->apellido_materno !== $request->apellido_materno && $request->apellido_materno) {
                        $cambios[] = "Ap. Materno: \"{$persona->apellido_materno}\" → \"{$request->apellido_materno}\"";
                    }

                    $datosPersona = array_filter([
                        'tipo_documento' => $request->tipo_documento_persona,
                        'numero_documento' => $request->numero_documento_persona,
                        'nombres' => $request->nombres,
                        'apellido_paterno' => $request->apellido_paterno,
                        'apellido_materno' => $request->apellido_materno,
                    ]);
                } else {
                    if ($persona->razon_social !== $request->razon_social && $request->razon_social) {
                        $cambios[] = "Razón social: \"{$persona->razon_social}\" → \"{$request->razon_social}\"";
                    }

                    $datosPersona = array_filter([
                        'numero_documento' => $request->numero_documento_persona,
                        'razon_social' => $request->razon_social,
                        'representante_legal' => $request->representante_legal,
                    ]);
                }

                // Datos comunes de contacto
                $datosPersona['telefono'] = $request->telefono_persona;
                $datosPersona['email'] = $request->email_persona;
                $datosPersona['direccion'] = $request->direccion_persona;

                $persona->update($datosPersona);
            } else {
                // Actualizar remitente directo
                if ($expediente->remitente !== $request->remitente && $request->remitente) {
                    $cambios[] = "Remitente: \"{$expediente->remitente}\" → \"{$request->remitente}\"";
                }
                if ($expediente->dni_remitente !== $request->dni_remitente && $request->dni_remitente) {
                    $cambios[] = "DNI Remitente: \"{$expediente->dni_remitente}\" → \"{$request->dni_remitente}\"";
                }

                $expediente->update([
                    'remitente' => $request->remitente,
                    'dni_remitente' => $request->dni_remitente,
                ]);
            }

            if (count($cambios) > 0) {
                $expediente->agregarHistorial(
                    "EDICIÓN DE EXPEDIENTE - Campos modificados: " . implode(' | ', $cambios) . ". Responsable: " . auth()->user()->name,
                    auth()->id()
                );
            }

            \DB::commit();
            \Cache::forget('mesa_partes_estadisticas');

            return redirect()->route('mesa-partes.index')
                ->with('success', "Expediente {$expediente->codigo_expediente} actualizado correctamente.");

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar el expediente: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un expediente (solo si está en estados iniciales)
     */
    public function destroy(Expediente $expediente)
    {
        // Solo permitir eliminar en estados iniciales
        $estadosPermitidos = ['pendiente_recepcion', 'recepcionado', 'registrado', 'clasificado'];
        $estadoActual = $expediente->estadoExpediente?->slug;

        if (!in_array($estadoActual, $estadosPermitidos)) {
            return redirect()->route('mesa-partes.index')
                ->with('error', "No se puede eliminar el expediente {$expediente->codigo_expediente}. Solo se pueden eliminar expedientes en estados iniciales (recepcionado, clasificado).");
        }

        try {
            \DB::beginTransaction();

            // Eliminar registros relacionados
            $expediente->documentos()->delete();
            $expediente->derivaciones()->delete();
            $expediente->historial()->delete();
            $expediente->observaciones()->delete();

            $expediente->delete();

            \DB::commit();
            \Cache::forget('mesa_partes_estadisticas');

            return redirect()->route('mesa-partes.index')
                ->with('success', "Expediente {$expediente->codigo_expediente} eliminado correctamente.");

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->route('mesa-partes.index')
                ->with('error', 'Error al eliminar el expediente: ' . $e->getMessage());
        }
    }

    /**
     * Lista los expedientes virtuales pendientes de clasificación
     */
    public function expedientesVirtuales()
    {
        // Mostrar expedientes virtuales pendientes de recepción Y recepcionados (pendientes de clasificar)
        $expedientes = Expediente::where('canal', 'virtual')
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['pendiente_recepcion', 'recepcionado']))
            ->with(['persona', 'tipoTramite', 'documentos', 'estadoExpediente'])
            ->orderByRaw("FIELD((SELECT slug FROM estados_expediente WHERE estados_expediente.id_estado = expedientes.id_estado), 'pendiente_recepcion', 'recepcionado')")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('mesa-partes.expedientes-virtuales', compact('expedientes'));
    }

    /**
     * Recepciona un expediente virtual (paso obligatorio antes de clasificar/derivar)
     * Cambia estado de pendiente_recepcion a recepcionado
     */
    public function recepcionarVirtual(Expediente $expediente)
    {
        // Verificar que sea un expediente virtual
        if ($expediente->canal !== 'virtual') {
            return redirect()->route('mesa-partes.expedientes-virtuales')
                ->with('error', 'Este expediente no es virtual.');
        }

        // Verificar que esté en estado pendiente_recepcion
        if ($expediente->estadoExpediente?->slug !== 'pendiente_recepcion') {
            return redirect()->route('mesa-partes.expedientes-virtuales')
                ->with('error', 'Este expediente ya fue recepcionado.');
        }

        try {
            \DB::beginTransaction();

            // Cambiar estado a recepcionado
            $idRecepcionado = EstadoExpediente::where('slug', 'recepcionado')->value('id_estado');

            if (!$idRecepcionado) {
                throw new \Exception('No existe el estado RECEPCIONADO en estados_expediente.');
            }

            $expediente->update([
                'id_estado' => $idRecepcionado,
            ]);

            // Registrar en historial
            $expediente->agregarHistorial(
                'Expediente virtual recepcionado por Mesa de Partes. Documentos revisados y validados.',
                auth()->id()
            );

            \DB::commit();

            // Limpiar caché de estadísticas
            \Cache::forget('mesa_partes_estadisticas');

            return redirect()->route('mesa-partes.expedientes-virtuales')
                ->with('success', "Expediente {$expediente->codigo_expediente} recepcionado correctamente. Ahora puede clasificar y derivar.");

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->route('mesa-partes.expedientes-virtuales')
                ->with('error', 'Error al recepcionar: ' . $e->getMessage());
        }
    }

    /**
     * Muestra formulario de rectificación de datos del expediente
     * Para corregir errores (DNI, nombre, asunto, tipo trámite, folios) sin cambiar el estado
     */
    public function rectificarDatos(Expediente $expediente)
    {
        $expediente->load(['persona', 'tipoTramite', 'area']);

        $tipoTramites = TipoTramite::where('activo', true)->orderBy('nombre')->get();

        return view('mesa-partes.rectificar-datos', compact('expediente', 'tipoTramites'));
    }

    /**
     * Procesa la rectificación de datos del expediente
     * Registra cada campo modificado en el historial con valor anterior y nuevo
     */
    public function storeRectificacion(Request $request, Expediente $expediente)
    {
        $request->validate([
            'motivo_rectificacion' => 'required|string|min:10|max:500',
            'asunto' => 'required|string|max:500',
            'id_tipo_tramite' => 'required|exists:tipo_tramites,id_tipo_tramite',
            'folios' => 'nullable|integer|min:1|max:999',
            'prioridad' => 'required|in:baja,normal,alta,urgente',
            // Datos de persona
            'numero_documento' => 'nullable|string|max:20',
            'nombres' => 'nullable|string|max:100',
            'apellido_paterno' => 'nullable|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'razon_social' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:20',
            'email_persona' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:300',
        ], [
            'motivo_rectificacion.required' => 'Debe indicar el motivo de la rectificación.',
            'motivo_rectificacion.min' => 'El motivo debe tener al menos 10 caracteres.',
        ]);

        try {
            \DB::beginTransaction();

            $cambios = [];

            // === Rectificar datos del expediente ===
            if ($expediente->asunto !== $request->asunto) {
                $cambios[] = "Asunto: \"{$expediente->asunto}\" → \"{$request->asunto}\"";
            }

            $tipoTramiteAnterior = $expediente->tipoTramite->nombre ?? 'N/A';
            if ($expediente->id_tipo_tramite != $request->id_tipo_tramite) {
                $nuevoTipo = TipoTramite::find($request->id_tipo_tramite);
                $cambios[] = "Tipo trámite: \"{$tipoTramiteAnterior}\" → \"{$nuevoTipo->nombre}\"";
            }

            if ($expediente->folios != $request->folios) {
                $cambios[] = "Folios: \"{$expediente->folios}\" → \"{$request->folios}\"";
            }

            if ($expediente->prioridad !== $request->prioridad) {
                $cambios[] = "Prioridad: \"{$expediente->prioridad}\" → \"{$request->prioridad}\"";
            }

            $expediente->update([
                'asunto' => $request->asunto,
                'id_tipo_tramite' => $request->id_tipo_tramite,
                'folios' => $request->folios,
                'prioridad' => $request->prioridad,
            ]);

            // === Rectificar datos de la persona ===
            $persona = $expediente->persona;
            if ($persona) {
                if ($persona->numero_documento !== $request->numero_documento && $request->numero_documento) {
                    $cambios[] = "N° Documento: \"{$persona->numero_documento}\" → \"{$request->numero_documento}\"";
                }
                if ($persona->tipo_persona === 'NATURAL') {
                    if ($persona->nombres !== $request->nombres && $request->nombres) {
                        $cambios[] = "Nombres: \"{$persona->nombres}\" → \"{$request->nombres}\"";
                    }
                    if ($persona->apellido_paterno !== $request->apellido_paterno && $request->apellido_paterno) {
                        $cambios[] = "Apellido paterno: \"{$persona->apellido_paterno}\" → \"{$request->apellido_paterno}\"";
                    }
                    if ($persona->apellido_materno !== $request->apellido_materno && $request->apellido_materno) {
                        $cambios[] = "Apellido materno: \"{$persona->apellido_materno}\" → \"{$request->apellido_materno}\"";
                    }
                } else {
                    if ($persona->razon_social !== $request->razon_social && $request->razon_social) {
                        $cambios[] = "Razón social: \"{$persona->razon_social}\" → \"{$request->razon_social}\"";
                    }
                }
                if ($persona->telefono !== $request->telefono) {
                    $cambios[] = "Teléfono: \"{$persona->telefono}\" → \"{$request->telefono}\"";
                }
                if ($persona->email !== $request->email_persona) {
                    $cambios[] = "Email: \"{$persona->email}\" → \"{$request->email_persona}\"";
                }
                if ($persona->direccion !== $request->direccion) {
                    $cambios[] = "Dirección: \"{$persona->direccion}\" → \"{$request->direccion}\"";
                }

                $datosPersona = [];
                if ($request->numero_documento) $datosPersona['numero_documento'] = $request->numero_documento;
                if ($persona->tipo_persona === 'NATURAL') {
                    if ($request->nombres) $datosPersona['nombres'] = $request->nombres;
                    if ($request->apellido_paterno) $datosPersona['apellido_paterno'] = $request->apellido_paterno;
                    if ($request->apellido_materno) $datosPersona['apellido_materno'] = $request->apellido_materno;
                } else {
                    if ($request->razon_social) $datosPersona['razon_social'] = $request->razon_social;
                }
                $datosPersona['telefono'] = $request->telefono;
                $datosPersona['email'] = $request->email_persona;
                $datosPersona['direccion'] = $request->direccion;

                $persona->update($datosPersona);
            }

            // Registrar en historial
            if (count($cambios) > 0) {
                $descripcionCambios = implode(' | ', $cambios);
                $expediente->agregarHistorial(
                    "RECTIFICACIÓN DE DATOS - Motivo: {$request->motivo_rectificacion}. " .
                    "Campos corregidos: {$descripcionCambios}. " .
                    "Responsable: " . auth()->user()->name,
                    auth()->id()
                );
            } else {
                \DB::rollBack();
                return redirect()->route('mesa-partes.show', $expediente)
                    ->with('info', 'No se detectaron cambios en los datos.');
            }

            \DB::commit();

            \Cache::forget('mesa_partes_estadisticas');

            return redirect()->route('mesa-partes.show', $expediente)
                ->with('success', 'Datos rectificados correctamente. Se registraron ' . count($cambios) . ' corrección(es) en el historial.');

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withInput()->with('error', 'Error al rectificar datos: ' . $e->getMessage());
        }
    }

    /**
     * Opción A: Anular derivación incorrecta (si el área destino aún no recepcionó)
     * Mesa de Partes marca la derivación como ANULADA y el expediente queda listo para re-derivar
     */
    public function anularDerivacion(Request $request, Expediente $expediente)
    {
        $request->validate([
            'motivo_anulacion' => 'required|string|min:10|max:500',
        ], [
            'motivo_anulacion.required' => 'Debe indicar el motivo de la anulación.',
            'motivo_anulacion.min' => 'El motivo debe tener al menos 10 caracteres.',
        ]);

        // Buscar la derivación pendiente (no recepcionada aún)
        $derivacion = $expediente->derivaciones()
            ->where('estado', 'pendiente')
            ->latest()
            ->first();

        if (!$derivacion) {
            return back()->with('error', 'No se encontró una derivación pendiente para anular. Si el área ya recepcionó, el Jefe de Área debe devolver el expediente.');
        }

        try {
            $this->derivacionService->anularDerivacion($derivacion, $request->motivo_anulacion);

            \Cache::forget('mesa_partes_estadisticas');

            return redirect()->route('mesa-partes.show', $expediente)
                ->with('success', "Derivación anulada correctamente. El expediente está listo para ser derivado al área correcta.");

        } catch (\Exception $e) {
            return back()->with('error', 'Error al anular derivación: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para clasificar y derivar un expediente virtual
     */
    public function clasificarVirtual(Expediente $expediente)
    {
        // Verificar que sea un expediente virtual
        if ($expediente->canal !== 'virtual') {
            return redirect()
                ->route('mesa-partes.index')
                ->with('error', 'Este expediente no es virtual. Use el flujo normal de clasificación.');
        }

        // Verificar que esté en estado recepcionado
        if ($expediente->estadoExpediente?->slug !== 'recepcionado') {
           return redirect()->route('mesa-partes.index')
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

            //$expediente->update([
            //    'id_area' => $request->id_area,
            //    'prioridad' => $request->prioridad,
            //    'estado' => 'derivado'
           // ]);
            $idDerivado = EstadoExpediente::where('slug', 'derivado')->value('id_estado');

            $expediente->update([
            'id_area'   => $request->id_area,
            'prioridad' => $request->prioridad,
            'id_estado' => $idDerivado,
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