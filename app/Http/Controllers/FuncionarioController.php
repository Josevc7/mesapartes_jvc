<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\Derivacion;
use App\Models\Documento;
use App\Models\HistorialExpediente;
use App\Services\DerivacionService;

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
            'asignados' => $conteosPorEstado->get('asignado', 0),
            'derivados' => $conteosPorEstado->get('derivado', 0),
            'en_proceso' => $conteosPorEstado->get('en_proceso', 0),
            'resueltos' => $conteosPorEstado->get('resuelto', 0),
            'vencidos' => $vencidos,
        ];
    }

    public function index(Request $request)
    {
        $query = Expediente::where('id_funcionario_asignado', auth()->user()->id)
            ->with(['tipoTramite', 'ciudadano', 'area', 'derivaciones', 'persona']);

        // Filtros
        if ($request->estado) {
            $query->whereHas('estadoExpediente', fn($q) => $q->where('slug', $request->estado));
        } else {
            $query->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['asignado', 'derivado', 'en_proceso', 'observado', 'resuelto']));
        }

        if ($request->prioridad) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->buscar) {
            $query->where(function($q) use ($request) {
                $q->where('codigo_expediente', 'like', '%' . $request->buscar . '%')
                  ->orWhere('asunto', 'like', '%' . $request->buscar . '%');
            });
        }

        $expedientes = $query->orderBy('created_at', 'desc')->paginate(10);
        
        $stats = $this->getEstadisticasFuncionario(auth()->user()->id);
            
        return view('funcionario.mis-expedientes', compact('expedientes', 'stats'));
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
            // Verificar permisos
            $this->authorize('process', $expediente);

            // Verificar que el expediente esté en estado derivado o asignado
            if (!in_array($expediente->estado, ['derivado', 'asignado'])) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'error' => 'El expediente no está listo para recibir. Estado actual: ' . $expediente->estado
                    ], 400);
                }
                return back()->with('error', 'El expediente no está listo para recibir. Estado actual: ' . $expediente->estado);
            }

            // Actualizar estado del expediente
            $expediente->estado = 'en_proceso';
            $expediente->save();

            // Actualizar la última derivación con fecha de recepción y estado
            $ultimaDerivacion = $expediente->derivacionActual();
            if ($ultimaDerivacion) {
                $ultimaDerivacion->update([
                    'fecha_recepcion' => now(),
                    'estado' => 'recibido'
                ]);
            }

            // Agregar al historial con información completa
            $expediente->agregarHistorial(
                'Expediente recepcionado',
                auth()->id(),
                [
                    'accion' => \App\Models\HistorialExpediente::ACCION_RECEPCION,
                    'id_area' => auth()->user()->id_area,
                    'estado' => 'en_proceso',
                    'detalle' => 'Expediente recibido para procesamiento'
                ]
            );

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Expediente recibido correctamente']);
            }
            return back()->with('success', 'Expediente recibido correctamente. Ahora puede procesarlo.');

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'error' => 'No tienes permisos para recibir este expediente. Solo puedes recibir expedientes asignados a ti.'
                ], 403);
            }
            return back()->with('error', 'No tienes permisos para recibir este expediente.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'error' => 'Error interno del servidor: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error al recibir el expediente: ' . $e->getMessage());
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
            // Estructura: expedientes/{año}/{codigo_expediente}/
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
            'procesar' => 'en_proceso',
            'resolver' => 'resuelto',
            'solicitar_info' => 'observado'
        };

        $expediente->update([
            'observaciones_funcionario' => $request->observaciones_funcionario,
            'estado' => $estado
        ]);

        // Registrar en historial con accion correspondiente
        $accionHistorial = match($request->accion) {
            'resolver' => HistorialExpediente::ACCION_RESOLUCION,
            'solicitar_info' => HistorialExpediente::ACCION_OBSERVACION,
            default => HistorialExpediente::ACCION_CAMBIO_ESTADO
        };

        $expediente->agregarHistorial(
            'Procesado por funcionario: ' . $request->observaciones_funcionario,
            auth()->user()->id,
            [
                'accion' => $accionHistorial,
                'estado' => $estado,
                'id_area' => auth()->user()->id_area,
            ]
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

        // Verificar que tenga al menos un documento adjunto (no de entrada)
        $documentosAdjuntos = $expediente->documentos()
            ->whereIn('tipo', ['informe', 'respuesta', 'resolucion', 'oficio'])
            ->count();

        if ($documentosAdjuntos === 0) {
            return back()->with('error', 'Debe adjuntar al menos un documento (informe, respuesta, resolución u oficio) antes de devolver el expediente al Jefe de Área.');
        }

        // Obtener el último documento adjuntado para el historial
        $ultimoDocumento = $expediente->documentos()
            ->whereIn('tipo', ['informe', 'respuesta', 'resolucion', 'oficio'])
            ->latest()
            ->first();

        // Obtener el jefe del área
        $jefeArea = \App\Models\User::where('id_area', auth()->user()->id_area)
            ->where('id_rol', 3) // Rol Jefe de Área
            ->first();

        $expediente->update([
            'estado' => 'en_revision',
            'fecha_resolucion' => now()
        ]);

        // Historial detallado
        $descripcionHistorial = sprintf(
            'Funcionario %s devolvió el expediente al Jefe de Área para revisión. Documento adjunto: %s (%s).',
            auth()->user()->name,
            $ultimoDocumento->nombre ?? 'N/A',
            ucfirst($ultimoDocumento->tipo ?? 'documento')
        );

        $expediente->agregarHistorial(
            $descripcionHistorial,
            auth()->user()->id,
            [
                'accion' => HistorialExpediente::ACCION_RESOLUCION,
                'estado' => 'en_revision',
                'id_area' => auth()->user()->id_area,
                'detalle' => 'Expediente devuelto al Jefe de Área con documento: ' . ($ultimoDocumento->nombre ?? 'N/A'),
                'documento_adjunto' => $ultimoDocumento->nombre ?? null,
                'tipo_documento' => $ultimoDocumento->tipo ?? null,
                'destinatario' => $jefeArea->name ?? 'Jefe de Área'
            ]
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

        // Solo desde en_proceso
        if ($expediente->estado !== 'en_proceso') {
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

        $expediente->estado = 'devuelto_jefe';
        $expediente->save();

        $descripcion = sprintf(
            'Funcionario %s devolvió el expediente al Jefe de Área. Motivo: %s. Detalle: %s',
            auth()->user()->name,
            $motivoTexto,
            $request->observaciones_devolucion
        );

        $expediente->agregarHistorial($descripcion, auth()->id(), [
            'accion' => HistorialExpediente::ACCION_DEVOLUCION_JEFE,
            'estado' => 'devuelto_jefe',
            'id_area' => auth()->user()->id_area,
            'detalle' => "Motivo: {$motivoTexto}",
        ]);

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

        $expediente->observaciones()->create([
            'id_usuario' => auth()->user()->id,
            'tipo' => 'subsanacion',
            'descripcion' => $request->observaciones,
            'fecha_limite' => now()->addDays($request->plazo_respuesta),
            'estado' => 'pendiente'
        ]);

        $expediente->estado = 'observado';
        $expediente->save();

        $expediente->agregarHistorial(
            'Solicitud de informacion adicional: ' . $request->observaciones,
            auth()->user()->id,
            [
                'accion' => HistorialExpediente::ACCION_OBSERVACION,
                'estado' => 'observado',
                'id_area' => auth()->user()->id_area,
            ]
        );

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
            ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'resuelto'))
            ->whereMonth('updated_at', now()->month)
            ->count();

        $expedientesPrioritarios = Expediente::where('id_funcionario_asignado', $userId)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso']))
            ->whereIn('prioridad', ['alta', 'urgente'])
            ->with(['tipoTramite', 'derivaciones'])
            ->orderByRaw("FIELD(prioridad, 'urgente', 'alta')")
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        $rendimiento = [
            'resueltos_mes' => $estadisticas['resueltos_mes'],
            'tiempo_promedio' => Expediente::where('id_funcionario_asignado', $userId)
                ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'resuelto'))
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

    public function misExpedientes()
    {
        $user = auth()->user();
        $esAdministrador = $user->role?->nombre === 'Administrador';

        // Si es administrador, mostrar todos los expedientes; si no, solo los asignados
        $query = Expediente::query();
        if (!$esAdministrador) {
            $query->where('id_funcionario_asignado', $user->id);
        }

        $expedientes = $query
            ->with(['tipoTramite', 'ciudadano', 'area', 'derivaciones', 'persona', 'funcionarioAsignado'])
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso', 'observado']))
            ->get()
            ->map(function($expediente) {
                $derivacion = $expediente->derivaciones->first();

                // Calcular días restantes como número entero
                if ($derivacion && $derivacion->fecha_limite) {
                    $diasRestantes = now()->diffInDays($derivacion->fecha_limite, false);
                    $expediente->dias_restantes = (int) $diasRestantes;
                } else {
                    $expediente->dias_restantes = 0;
                }

                $expediente->fecha_derivacion = $derivacion ? $derivacion->fecha_derivacion : null;
                return $expediente;
            });

        return view('funcionario.mis-expedientes', compact('expedientes'));
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