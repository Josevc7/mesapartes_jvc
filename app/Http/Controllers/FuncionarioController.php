<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\Derivacion;
use App\Models\Documento;

class FuncionarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Expediente::where('id_funcionario_asignado', auth()->id())
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
            'derivados' => Expediente::where('id_funcionario_asignado', auth()->id())->where('estado', 'derivado')->count(),
            'en_proceso' => Expediente::where('id_funcionario_asignado', auth()->id())->where('estado', 'en_proceso')->count(),
            'vencidos' => Expediente::where('id_funcionario_asignado', auth()->id())
                ->whereHas('derivaciones', function($q) {
                    $q->where('fecha_limite', '<', now());
                })->count(),
            'resueltos' => Expediente::where('id_funcionario_asignado', auth()->id())->where('estado', 'resuelto')->count()
        ];
            
        return view('funcionario.mis-expedientes', compact('expedientes', 'stats'));
    }

    public function show(Expediente $expediente)
    {
        if ($expediente->id_funcionario_asignado !== auth()->id()) {
            abort(403, 'No tienes acceso a este expediente');
        }
        
        $expediente->load(['documentos', 'derivaciones', 'historial', 'tipoTramite', 'area', 'persona']);
        return view('funcionario.show', compact('expediente'));
    }

    public function recibir(Expediente $expediente)
    {
        try {
            // Verificar que el funcionario tenga acceso
            if ($expediente->id_funcionario_asignado !== auth()->id()) {
                return response()->json(['error' => 'No tienes acceso a este expediente'], 403);
            }
            
            // Verificar que el expediente esté en estado derivado
            if ($expediente->estado !== 'derivado') {
                return response()->json(['error' => 'El expediente no está en estado derivado'], 400);
            }
            
            $expediente->update(['estado' => 'en_proceso']);
            
            return response()->json(['success' => true, 'message' => 'Expediente recibido correctamente']);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    public function procesar(Expediente $expediente)
    {
        return view('funcionario.procesar', compact('expediente'));
    }

    public function updateProcesar(Request $request, Expediente $expediente)
    {
        $request->validate([
            'observaciones_funcionario' => 'required|string',
            'documento_respuesta' => 'nullable|file|mimes:pdf|max:10240',
            'accion' => 'required|in:procesar,resolver,solicitar_info'
        ]);

        if ($request->hasFile('documento_respuesta')) {
            $path = $request->file('documento_respuesta')->store('documentos', 'public');
            
            Documento::create([
                'id_expediente' => $expediente->id,
                'nombre' => $request->input('nombre_documento', 'Documento de Respuesta'),
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
            auth()->id()
        );

        return redirect()->route('funcionario.index')
            ->with('success', 'Expediente ' . strtolower($request->accion) . ' correctamente');
    }

    public function resolver(Expediente $expediente)
    {
        $expediente->update([
            'estado' => 'resuelto',
            'fecha_resolucion' => now()
        ]);
        
        $expediente->agregarHistorial('Expediente resuelto', auth()->id());
        
        return redirect()->route('funcionario.index')
            ->with('success', 'Expediente resuelto correctamente');
    }

    public function solicitarInfo(Request $request, Expediente $expediente)
    {
        $request->validate([
            'observaciones' => 'required|string',
            'plazo_respuesta' => 'required|integer|min:1|max:30'
        ]);

        $expediente->observaciones()->create([
            'id_usuario' => auth()->id(),
            'tipo' => 'subsanacion',
            'descripcion' => $request->observaciones,
            'fecha_limite' => now()->addDays($request->plazo_respuesta),
            'estado' => 'pendiente'
        ]);

        $expediente->update(['estado' => 'observado']);
        
        $expediente->agregarHistorial(
            'Solicitud de información adicional: ' . $request->observaciones,
            auth()->id()
        );

        return redirect()->route('funcionario.index')
            ->with('success', 'Solicitud de información enviada');
    }

    public function adjuntarDocumento(Request $request, Expediente $expediente)
    {
        $request->validate([
            'documento' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:informe,respuesta'
        ]);

        $path = $request->file('documento')->store('documentos', 'public');
        
        Documento::create([
            'id_expediente' => $expediente->id,
            'nombre' => $request->nombre,
            'ruta_pdf' => $path,
            'tipo' => strtolower($request->tipo)
        ]);

        $expediente->agregarHistorial(
            'Documento adjuntado: ' . $request->nombre,
            auth()->id()
        );

        return back()->with('success', 'Documento adjuntado correctamente');
    }

    public function historial(Expediente $expediente)
    {
        if ($expediente->id_funcionario_asignado !== auth()->id()) {
            abort(403, 'No tienes acceso a este expediente');
        }
        
        $expediente->load(['historial.usuario.role', 'observaciones']);
        return view('funcionario.historial', compact('expediente'));
    }

    public function documentos(Expediente $expediente)
    {
        if ($expediente->id_funcionario_asignado !== auth()->id()) {
            abort(403, 'No tienes acceso a este expediente');
        }
        
        $expediente->load(['documentos.usuario']);
        return view('funcionario.documentos', compact('expediente'));
    }

    public function dashboard()
    {
        $estadisticas = [
            'derivados' => Expediente::where('id_funcionario_asignado', auth()->id())->where('estado', 'derivado')->count(),
            'en_proceso' => Expediente::where('id_funcionario_asignado', auth()->id())->where('estado', 'en_proceso')->count(),
            'vencidos' => Expediente::where('id_funcionario_asignado', auth()->id())
                ->whereHas('derivaciones', function($q) {
                    $q->where('fecha_limite', '<', now());
                })->count(),
            'resueltos_mes' => Expediente::where('id_funcionario_asignado', auth()->id())
                ->where('estado', 'resuelto')
                ->whereMonth('updated_at', now()->month)
                ->count()
        ];

        $expedientesPrioritarios = Expediente::where('id_funcionario_asignado', auth()->id())
            ->whereIn('estado', ['derivado', 'en_proceso'])
            ->whereIn('prioridad', ['alta', 'urgente'])
            ->with(['tipoTramite', 'derivaciones'])
            ->orderByRaw("FIELD(prioridad, 'urgente', 'alta')")
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        $rendimiento = [
            'resueltos_mes' => $estadisticas['resueltos_mes'],
            'tiempo_promedio' => Expediente::where('id_funcionario_asignado', auth()->id())
                ->where('estado', 'resuelto')
                ->whereNotNull('fecha_resolucion')
                ->get()
                ->avg(function($exp) {
                    return $exp->created_at->diffInDays($exp->fecha_resolucion);
                }) ?? 0,
            'total_asignados' => Expediente::where('id_funcionario_asignado', auth()->id())->count(),
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
        $expedientes = Expediente::where('id_funcionario_asignado', auth()->id())
            ->with(['tipoTramite', 'ciudadano', 'area', 'derivaciones', 'persona'])
            ->whereIn('estado', ['derivado', 'en_proceso', 'observado'])
            ->get()
            ->map(function($expediente) {
                $derivacion = $expediente->derivaciones->first();
                $expediente->dias_restantes = ($derivacion && $derivacion->fecha_limite) ? $derivacion->fecha_limite->diffInDays(now(), false) : 0;
                $expediente->fecha_derivacion = $derivacion ? $derivacion->fecha_derivacion : null;
                return $expediente;
            });

        return view('funcionario.mis-expedientes', compact('expedientes'));
    }

    public function solicitarInfoForm(Expediente $expediente)
    {
        if ($expediente->id_funcionario_asignado !== auth()->id()) {
            abort(403, 'No tienes acceso a este expediente');
        }
        
        return view('funcionario.solicitar-info', compact('expediente'));
    }
    
    public function derivarForm(Expediente $expediente)
    {
        if ($expediente->id_funcionario_asignado !== auth()->id()) {
            abort(403, 'No tienes acceso a este expediente');
        }
        
        $areas = \App\Models\Area::where('activo', true)
            ->where('id', '!=', auth()->user()->id_area)
            ->get();
            
        return view('funcionario.derivar', compact('expediente', 'areas'));
    }
    
    public function derivar(Request $request, Expediente $expediente)
    {
        $request->validate([
            'id_area_destino' => 'required|exists:areas,id',
            'id_funcionario_destino' => 'nullable|exists:users,id',
            'observaciones' => 'required|string|max:500',
            'plazo_dias' => 'required|integer|min:1|max:30'
        ]);
        
        if ($expediente->id_funcionario_asignado !== auth()->id()) {
            abort(403, 'No tienes acceso a este expediente');
        }
        
        // Crear derivación
        \App\Models\Derivacion::create([
            'id_expediente' => $expediente->id,
            'id_area_origen' => auth()->user()->id_area,
            'id_area_destino' => $request->id_area_destino,
            'id_funcionario_origen' => auth()->id(),
            'id_funcionario_destino' => $request->id_funcionario_destino,
            'id_funcionario_asignado' => $request->id_funcionario_destino,
            'fecha_derivacion' => now(),
            'fecha_limite' => now()->addDays($request->plazo_dias),
            'plazo_dias' => $request->plazo_dias,
            'observaciones' => $request->observaciones,
            'estado' => 'pendiente'
        ]);
        
        // Actualizar expediente
        $expediente->update([
            'id_area' => $request->id_area_destino,
            'id_funcionario_asignado' => $request->id_funcionario_destino,
            'estado' => 'derivado'
        ]);
        
        // Registrar en historial
        $area_destino = \App\Models\Area::find($request->id_area_destino);
        $expediente->agregarHistorial(
            "Derivado a {$area_destino->nombre}: {$request->observaciones}",
            auth()->id()
        );
        
        return redirect()->route('funcionario.index')
            ->with('success', 'Expediente derivado correctamente a ' . $area_destino->nombre);
    }
}
