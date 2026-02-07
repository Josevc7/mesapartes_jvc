<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\Documento;
use App\Models\HistorialExpediente;
use App\Services\DerivacionService;
use App\Enums\EstadoExpediente;
use Illuminate\Support\Facades\DB;

class FuncionarioController extends Controller
{
    protected DerivacionService $derivacionService;

    public function __construct(DerivacionService $derivacionService)
    {
        $this->derivacionService = $derivacionService;
    }

    /**
     * Estadísticas del funcionario en una sola query agrupada
     * Reemplaza 5+ queries individuales por 2 queries (groupBy + vencidos)
     */
    private function getEstadisticasFuncionario(int $userId): array
    {
        $conteosPorEstado = Expediente::where('id_funcionario_asignado', $userId)
            ->join('estados_expediente', 'expedientes.id_estado', '=', 'estados_expediente.id_estado')
            ->selectRaw('estados_expediente.slug, COUNT(*) as total')
            ->groupBy('estados_expediente.slug')
            ->pluck('total', 'slug');

        $vencidos = Expediente::where('id_funcionario_asignado', $userId)
            ->whereHas('derivaciones', fn($q) => $q->where('fecha_limite', '<', now()))
            ->count();

        return [
            'asignados' => $conteosPorEstado->get(EstadoExpediente::ASIGNADO->value, 0),
            'derivados' => $conteosPorEstado->get(EstadoExpediente::DERIVADO->value, 0),
            'en_proceso' => $conteosPorEstado->get(EstadoExpediente::EN_PROCESO->value, 0),
            'resueltos' => $conteosPorEstado->get(EstadoExpediente::RESUELTO->value, 0),
            'vencidos' => $vencidos,
        ];
    }

    /**
     * Centraliza transición de estado: update + historial en transacción
     */
    private function cambiarEstado(
        Expediente $expediente,
        EstadoExpediente $nuevoEstado,
        string $descripcion,
        string $accionHistorial,
        array $extraData = [],
        ?string $detalle = null
    ): void {
        DB::transaction(function() use ($expediente, $nuevoEstado, $descripcion, $accionHistorial, $extraData, $detalle) {
            $expediente->update(array_merge(
                ['estado' => $nuevoEstado->value],
                $extraData
            ));

            $expediente->agregarHistorial($descripcion, auth()->id(), [
                'accion' => $accionHistorial,
                'estado' => $nuevoEstado->value,
                'id_area' => auth()->user()->id_area,
                'detalle' => $detalle,
            ]);
        });
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $esAdministrador = $user->role?->nombre === 'Administrador';

        $query = Expediente::query();

        if (!$esAdministrador) {
            $query->where('id_funcionario_asignado', $user->id);
        }

        $query->with(['tipoTramite', 'ciudadano', 'area', 'derivaciones', 'persona', 'funcionarioAsignado']);

        // Filtros
        if ($request->estado) {
            $query->whereHas('estadoExpediente', fn($q) => $q->where('slug', $request->estado));
        } else {
            $query->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', [
                EstadoExpediente::ASIGNADO->value,
                EstadoExpediente::DERIVADO->value,
                EstadoExpediente::EN_PROCESO->value,
                EstadoExpediente::OBSERVADO->value,
                EstadoExpediente::RESUELTO->value,
            ]));
        }

        if ($request->prioridad) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->buscar) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('codigo_expediente', 'like', "%{$buscar}%")
                  ->orWhere('asunto', 'like', "%{$buscar}%");
            });
        }

        $expedientes = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function($expediente) {
                $derivacion = $expediente->derivaciones->sortByDesc('created_at')->first();

                if ($derivacion && $derivacion->fecha_limite) {
                    $expediente->dias_restantes = (int) now()->diffInDays($derivacion->fecha_limite, false);
                } else {
                    $expediente->dias_restantes = 0;
                }

                $expediente->fecha_derivacion = $derivacion?->fecha_derivacion;
                return $expediente;
            });

        return view('funcionario.mis-expedientes', compact('expedientes'));
    }

    public function show(Expediente $expediente)
    {
        $this->authorize('process', $expediente);
        
        $expediente->load(['documentos', 'derivaciones', 'historial', 'tipoTramite', 'area', 'persona']);
        return view('funcionario.show', compact('expediente'));
    }

    public function recibir(Expediente $expediente)
    {
        try {
            $this->authorize('process', $expediente);

            $estadosValidos = [EstadoExpediente::DERIVADO->value, EstadoExpediente::ASIGNADO->value];
            if (!in_array($expediente->estado, $estadosValidos)) {
                $msg = 'El expediente no está listo para recibir. Estado actual: ' . $expediente->estado;
                return request()->expectsJson()
                    ? response()->json(['error' => $msg], 400)
                    : back()->with('error', $msg);
            }

            DB::transaction(function() use ($expediente) {
                $expediente->update(['estado' => EstadoExpediente::EN_PROCESO->value]);

                $ultimaDerivacion = $expediente->derivacionActual();
                $ultimaDerivacion?->update(['fecha_recepcion' => now(), 'estado' => 'recibido']);

                $expediente->agregarHistorial('Expediente recepcionado', auth()->id(), [
                    'accion' => HistorialExpediente::ACCION_RECEPCION,
                    'estado' => EstadoExpediente::EN_PROCESO->value,
                    'id_area' => auth()->user()->id_area,
                    'detalle' => 'Expediente recibido para procesamiento',
                ]);
            });

            return request()->expectsJson()
                ? response()->json(['success' => true, 'message' => 'Expediente recibido correctamente'])
                : back()->with('success', 'Expediente recibido correctamente. Ahora puede procesarlo.');

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $msg = 'No tienes permisos para recibir este expediente.';
            return request()->expectsJson()
                ? response()->json(['error' => $msg], 403)
                : back()->with('error', $msg);
        } catch (\Exception $e) {
            return request()->expectsJson()
                ? response()->json(['error' => 'Error: ' . $e->getMessage()], 500)
                : back()->with('error', 'Error al recibir el expediente: ' . $e->getMessage());
        }
    }

    public function procesar(Expediente $expediente)
    {
        $this->authorize('process', $expediente);
        return view('funcionario.procesar', compact('expediente'));
    }

    public function updateProcesar(Request $request, Expediente $expediente)
    {
        $this->authorize('process', $expediente);

        $request->validate([
            'observaciones_funcionario' => 'required|string',
            'documento_respuesta' => 'nullable|file|mimes:pdf|max:10240',
            'accion' => 'required|in:procesar,resolver,solicitar_info'
        ]);

        if ($request->hasFile('documento_respuesta')) {
            $año = $expediente->created_at->year;
            $carpeta = "expedientes/{$año}/{$expediente->codigo_expediente}";

            $archivo = $request->file('documento_respuesta');
            $nombreOriginal = $archivo->getClientOriginalName();
            $nombreLimpio = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreOriginal);

            $path = $archivo->storeAs($carpeta, $nombreLimpio, 'public');

            Documento::create([
                'id_expediente' => $expediente->id_expediente,
                'nombre' => $request->input('nombre_documento', pathinfo($nombreOriginal, PATHINFO_FILENAME)),
                'ruta_pdf' => $path,
                'tipo' => 'respuesta'
            ]);
        }

        $estado = match($request->accion) {
            'procesar' => EstadoExpediente::EN_PROCESO,
            'resolver' => EstadoExpediente::RESUELTO,
            'solicitar_info' => EstadoExpediente::OBSERVADO,
        };

        $accionHistorial = match($request->accion) {
            'resolver' => HistorialExpediente::ACCION_RESOLUCION,
            'solicitar_info' => HistorialExpediente::ACCION_OBSERVACION,
            default => HistorialExpediente::ACCION_CAMBIO_ESTADO,
        };

        $this->cambiarEstado(
            $expediente,
            $estado,
            'Procesado por funcionario: ' . $request->observaciones_funcionario,
            $accionHistorial,
            ['observaciones_funcionario' => $request->observaciones_funcionario]
        );

        return redirect()->route('funcionario.index')
            ->with('success', 'Expediente ' . strtolower($request->accion) . ' correctamente');
    }

    /**
     * Devolver expediente al Jefe de Área para revisión
     * Requiere al menos un documento adjunto (informe/respuesta)
     */
    public function enviarARevision(Expediente $expediente)
    {
        $this->authorize('enviarARevision', $expediente);

        $documentosAdjuntos = $expediente->documentos()
            ->whereIn('tipo', ['informe', 'respuesta', 'resolucion', 'oficio']);

        if ($documentosAdjuntos->count() === 0) {
            return back()->with('error', 'Debe adjuntar al menos un documento (informe, respuesta, resolución u oficio) antes de devolver el expediente al Jefe de Área.');
        }

        $ultimoDocumento = $documentosAdjuntos->latest()->first();

        $descripcion = sprintf(
            'Funcionario %s devolvió el expediente al Jefe de Área para revisión. Documento adjunto: %s (%s).',
            auth()->user()->name,
            $ultimoDocumento->nombre ?? 'N/A',
            ucfirst($ultimoDocumento->tipo ?? 'documento')
        );

        $this->cambiarEstado(
            $expediente,
            EstadoExpediente::EN_REVISION,
            $descripcion,
            HistorialExpediente::ACCION_RESOLUCION,
            ['fecha_resolucion' => now()],
            'Documento: ' . ($ultimoDocumento->nombre ?? 'N/A')
        );

        return redirect()->route('funcionario.index')
            ->with('success', 'Expediente devuelto al Jefe de Área para revisión.');
    }

    /**
     * Devolver expediente al Jefe de Área (sin necesidad de documento)
     * Para casos: falta información, error de asignación, caso complejo, ampliación de plazo
     */
    public function devolverAlJefe(Request $request, Expediente $expediente)
    {
        $this->authorize('process', $expediente);

        if ($expediente->estado !== EstadoExpediente::EN_PROCESO->value) {
            return back()->with('error', 'Solo se pueden devolver expedientes en estado "En Proceso".');
        }

        $request->validate([
            'motivo_devolucion' => 'required|string|in:falta_informacion,error_asignacion,caso_complejo,ampliacion_plazo,otro',
            'observaciones_devolucion' => 'required|string|min:10|max:500',
        ], [
            'motivo_devolucion.required' => 'Debe seleccionar un motivo de devolución.',
            'observaciones_devolucion.required' => 'Debe detallar el motivo de la devolución.',
            'observaciones_devolucion.min' => 'La observación debe tener al menos 10 caracteres.',
        ]);

        $motivosTexto = [
            'falta_informacion' => 'Falta información o documentación',
            'error_asignacion' => 'Error en la asignación (no corresponde)',
            'caso_complejo' => 'Caso complejo que requiere decisión del Jefe',
            'ampliacion_plazo' => 'Se requiere ampliación de plazo',
            'otro' => 'Otro motivo',
        ];

        $motivoTexto = $motivosTexto[$request->motivo_devolucion] ?? $request->motivo_devolucion;

        $descripcion = sprintf(
            'Funcionario %s devolvió el expediente al Jefe de Área. Motivo: %s. Detalle: %s',
            auth()->user()->name,
            $motivoTexto,
            $request->observaciones_devolucion
        );

        $this->cambiarEstado(
            $expediente,
            EstadoExpediente::DEVUELTO_JEFE,
            $descripcion,
            HistorialExpediente::ACCION_DEVOLUCION_JEFE,
            [],
            "Motivo: {$motivoTexto}"
        );

        return redirect()->route('funcionario.index')
            ->with('success', 'Expediente devuelto al Jefe de Área correctamente.');
    }

    public function solicitarInfo(Request $request, Expediente $expediente)
    {
        $this->authorize('process', $expediente);

        $request->validate([
            'observaciones' => 'required|string',
            'plazo_respuesta' => 'required|integer|min:1|max:30'
        ]);

        DB::transaction(function() use ($expediente, $request) {
            $expediente->observaciones()->create([
                'id_usuario' => auth()->id(),
                'tipo' => 'subsanacion',
                'descripcion' => $request->observaciones,
                'fecha_limite' => now()->addDays($request->plazo_respuesta),
                'estado' => 'pendiente'
            ]);

            $this->cambiarEstado(
                $expediente,
                EstadoExpediente::OBSERVADO,
                'Solicitud de información adicional: ' . $request->observaciones,
                HistorialExpediente::ACCION_OBSERVACION
            );
        });

        return redirect()->route('funcionario.index')
            ->with('success', 'Solicitud de información enviada');
    }

    public function adjuntarDocumento(Request $request, Expediente $expediente)
    {
        $this->authorize('process', $expediente);

        $request->validate([
            'documento' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:informe,respuesta'
        ]);

        // Estructura: expedientes/{año}/{codigo_expediente}/
        $año = $expediente->created_at->year;
        $carpeta = "expedientes/{$año}/{$expediente->codigo_expediente}";

        $archivo = $request->file('documento');
        $nombreOriginal = $archivo->getClientOriginalName();
        $nombreLimpio = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreOriginal);

        $path = $archivo->storeAs($carpeta, $nombreLimpio, 'public');

        Documento::create([
            'id_expediente' => $expediente->id_expediente,
            'nombre' => $request->nombre,
            'ruta_pdf' => $path,
            'tipo' => strtolower($request->tipo)
        ]);

        $expediente->agregarHistorial(
            'Documento adjuntado: ' . $request->nombre,
            auth()->user()->id,
            [
                'accion' => HistorialExpediente::ACCION_ADJUNTO,
                'estado' => $expediente->estado,
                'id_area' => auth()->user()->id_area,
            ]
        );

        return back()->with('success', 'Documento adjuntado correctamente');
    }

    public function historial(Expediente $expediente)
    {
        $this->authorize('process', $expediente);
        
        $expediente->load(['historial.usuario', 'observaciones']);
        return view('funcionario.historial', compact('expediente'));
    }

    public function documentos(Expediente $expediente)
    {
        $this->authorize('process', $expediente);
        
        $expediente->load(['documentos.usuario']);
        return view('funcionario.documentos', compact('expediente'));
    }

    public function dashboard()
    {
        $userId = auth()->user()->id;
        $estadisticas = $this->getEstadisticasFuncionario($userId);

        // Resueltos este mes (específico del dashboard)
        $estadisticas['resueltos_mes'] = Expediente::where('id_funcionario_asignado', $userId)
            ->whereHas('estadoExpediente', fn($q) => $q->where('slug', EstadoExpediente::RESUELTO->value))
            ->whereMonth('updated_at', now()->month)
            ->count();

        $expedientesPrioritarios = Expediente::where('id_funcionario_asignado', $userId)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', [EstadoExpediente::DERIVADO->value, EstadoExpediente::EN_PROCESO->value]))
            ->whereIn('prioridad', ['alta', 'urgente'])
            ->with(['tipoTramite', 'derivaciones'])
            ->orderByRaw("FIELD(prioridad, 'urgente', 'alta')")
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        $rendimiento = [
            'resueltos_mes' => $estadisticas['resueltos_mes'],
            'tiempo_promedio' => Expediente::where('id_funcionario_asignado', $userId)
                ->whereHas('estadoExpediente', fn($q) => $q->where('slug', EstadoExpediente::RESUELTO->value))
                ->whereNotNull('fecha_resolucion')
                ->get()
                ->avg(function($exp) {
                    return $exp->created_at->diffInDays($exp->fecha_resolucion);
                }) ?? 0,
            'total_asignados' => Expediente::where('id_funcionario_asignado', $userId)->count(),
            'pendientes' => $estadisticas['derivados'] + $estadisticas['en_proceso']
        ];

        $alertas = collect();
        if ($estadisticas['vencidos'] > 0) {
            $alertas->push((object)['tipo' => 'danger', 'titulo' => 'Expedientes Vencidos', 'mensaje' => "Tienes {$estadisticas['vencidos']} expedientes vencidos que requieren atención inmediata."]);
        }
        if ($estadisticas['derivados'] > 5) {
            $alertas->push((object)['tipo' => 'warning', 'titulo' => 'Expedientes por Recibir', 'mensaje' => "Tienes {$estadisticas['derivados']} expedientes pendientes de recibir."]);
        }

        return view('funcionario.dashboard', compact('estadisticas', 'expedientesPrioritarios', 'rendimiento', 'alertas'));
    }


    public function solicitarInfoForm(Expediente $expediente)
    {
        $this->authorize('process', $expediente);
        
        return view('funcionario.solicitar-info', compact('expediente'));
    }
    
    public function derivarForm(Expediente $expediente)
    {
        $this->authorize('process', $expediente);
        
        $areas = \App\Models\Area::where('activo', true)
            ->where('id_area', '!=', auth()->user()->id_area)
            ->get();
            
        return view('funcionario.derivar', compact('expediente', 'areas'));
    }
    
    /**
     * Derivar expediente a otra área
     * REFACTORIZADO: Usa DerivacionService para evitar duplicación de código
     */
    public function derivar(Request $request, Expediente $expediente)
    {
        $request->validate([
            'id_area_destino' => 'required|exists:areas,id_area',
            'id_funcionario_destino' => 'nullable|exists:users,id',
            'observaciones' => 'required|string|max:500',
            'plazo_dias' => 'required|integer|min:1|max:30'
        ]);

        // Verificar permisos
        $this->authorize('process', $expediente);

        try {
            // Usar el servicio de derivación
            // Parámetros: expediente, areaDestino, funcionario, plazoDias, prioridad, observaciones
            $derivacion = $this->derivacionService->derivarExpediente(
                $expediente,
                $request->id_area_destino,
                $request->id_funcionario_destino,
                $request->plazo_dias,
                $request->prioridad ?? $expediente->prioridad ?? 'normal', // Usar prioridad del expediente o normal por defecto
                $request->observaciones
            );

            $area_destino = \App\Models\Area::find($request->id_area_destino);

            return redirect()->route('funcionario.index')
                ->with('success', 'Expediente derivado correctamente a ' . $area_destino->nombre);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al derivar expediente: ' . $e->getMessage());
        }
    }
}