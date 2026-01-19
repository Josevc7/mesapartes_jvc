<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\Derivacion;
use App\Models\Documento;
use App\Services\DerivacionService;

class FuncionarioController extends Controller
{
    protected DerivacionService $derivacionService;

    public function __construct(DerivacionService $derivacionService)
    {
        $this->derivacionService = $derivacionService;
    }
    public function index(Request $request)
    {
        $query = Expediente::where('id_funcionario_asignado', auth()->user()->id)
            ->with(['tipoTramite', 'ciudadano', 'area', 'derivaciones', 'persona']);

        // Filtros
        if ($request->estado) {
            $query->where('estado', $request->estado);
        } else {
            $query->whereIn('estado', ['derivado', 'en_proceso', 'observado', 'resuelto']);
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
        
        // Estadísticas
        $stats = [
            'derivados' => Expediente::where('id_funcionario_asignado', auth()->user()->id)->where('estado', 'derivado')->count(),
            'en_proceso' => Expediente::where('id_funcionario_asignado', auth()->user()->id)->where('estado', 'en_proceso')->count(),
            'vencidos' => Expediente::where('id_funcionario_asignado', auth()->user()->id)
                ->whereHas('derivaciones', function($q) {
                    $q->where('fecha_limite', '<', now());
                })->count(),
            'resueltos' => Expediente::where('id_funcionario_asignado', auth()->user()->id)->where('estado', 'resuelto')->count()
        ];
            
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

            // Verificar que el expediente esté en estado derivado o Derivado (con mayúscula)
            if (!in_array($expediente->estado, ['derivado', 'Derivado'])) {
                return response()->json([
                    'error' => 'El expediente no está en estado derivado. Estado actual: ' . $expediente->estado
                ], 400);
            }

            // Actualizar estado y agregar al historial
            $expediente->update(['estado' => 'en_proceso']);
            $expediente->agregarHistorial(
                'Expediente recibido por el funcionario para su procesamiento',
                auth()->id()
            );

            return response()->json(['success' => true, 'message' => 'Expediente recibido correctamente']);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'error' => 'No tienes permisos para recibir este expediente. Solo puedes recibir expedientes asignados a ti.'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
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

        // Registrar en historial
        $expediente->agregarHistorial(
            'Procesado por funcionario: ' . $request->observaciones_funcionario,
            auth()->user()->id
        );

        return redirect()->route('funcionario.index')
            ->with('success', 'Expediente ' . strtolower($request->accion) . ' correctamente');
    }

    public function resolver(Expediente $expediente)
    {
        $this->authorize('resolver', $expediente);

        $expediente->update([
            'estado' => 'resuelto',
            'fecha_resolucion' => now()
        ]);

        $expediente->agregarHistorial('Expediente resuelto', auth()->user()->id);

        return redirect()->route('funcionario.index')
            ->with('success', 'Expediente resuelto correctamente');
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

        $expediente->update(['estado' => 'observado']);
        
        $expediente->agregarHistorial(
            'Solicitud de información adicional: ' . $request->observaciones,
            auth()->user()->id
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
            auth()->user()->id
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
        $estadisticas = [
            'derivados' => Expediente::where('id_funcionario_asignado', auth()->user()->id)->where('estado', 'derivado')->count(),
            'en_proceso' => Expediente::where('id_funcionario_asignado', auth()->user()->id)->where('estado', 'en_proceso')->count(),
            'vencidos' => Expediente::where('id_funcionario_asignado', auth()->user()->id)
                ->whereHas('derivaciones', function($q) {
                    $q->where('fecha_limite', '<', now());
                })->count(),
            'resueltos_mes' => Expediente::where('id_funcionario_asignado', auth()->user()->id)
                ->where('estado', 'resuelto')
                ->whereMonth('updated_at', now()->month)
                ->count()
        ];

        $expedientesPrioritarios = Expediente::where('id_funcionario_asignado', auth()->user()->id)
            ->whereIn('estado', ['derivado', 'en_proceso'])
            ->whereIn('prioridad', ['alta', 'urgente'])
            ->with(['tipoTramite', 'derivaciones'])
            ->orderByRaw("FIELD(prioridad, 'urgente', 'alta')")
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        $rendimiento = [
            'resueltos_mes' => $estadisticas['resueltos_mes'],
            'tiempo_promedio' => Expediente::where('id_funcionario_asignado', auth()->user()->id)
                ->where('estado', 'resuelto')
                ->whereNotNull('fecha_resolucion')
                ->get()
                ->avg(function($exp) {
                    return $exp->created_at->diffInDays($exp->fecha_resolucion);
                }) ?? 0,
            'total_asignados' => Expediente::where('id_funcionario_asignado', auth()->user()->id)->count(),
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
            ->whereIn('estado', ['derivado', 'en_proceso', 'observado'])
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