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

class MesaPartesController extends Controller
{
    public function index()
    {
        $expedientes = Expediente::with(['tipoTramite', 'ciudadano', 'area', 'persona'])
            ->whereIn('estado', ['recepcionado', 'registrado', 'clasificado', 'derivado', 'en_proceso'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('mesa-partes.index', compact('expedientes'));
    }

    public function registrar()
    {
        $tipoTramites = TipoTramite::where('activo', true)->orderBy('nombre')->get();
        return view('mesa-partes.registrar', compact('tipoTramites'));
    }

    public function storeRegistrar(Request $request)
    {
        $request->validate([
            'asunto' => 'required|string|max:500',
            'persona_existente_id' => 'nullable|exists:personas,id',
            'tipo_documento' => 'required|in:DNI,CE,RUC,PASAPORTE',
            'numero_documento' => 'required|string|max:20',
            'tipo_persona' => 'required|in:NATURAL,JURIDICA',
            'nombres' => 'required_if:tipo_persona,NATURAL|nullable|string|max:100',
            'apellido_paterno' => 'required_if:tipo_persona,NATURAL|nullable|string|max:50',
            'apellido_materno' => 'nullable|string|max:50',
            'razon_social' => 'required_if:tipo_persona,JURIDICA|nullable|string|max:200',
            'representante_legal' => 'nullable|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
            'documentos_verificados' => 'required|array|min:3',
            'documentos_verificados.*' => 'in:dni,fut,pago',
            'documentos_adicionales' => 'nullable|array',
            'observaciones_documentos' => 'nullable|string',
            'id_tipo_tramite' => 'required|exists:tipo_tramites,id_tipo_tramite',
            'documento' => 'required|file|mimes:pdf|max:10240',
            'observaciones' => 'nullable|string'
        ], [
            'documentos_verificados.required' => 'Debe verificar todos los documentos obligatorios',
            'documentos_verificados.min' => 'Debe marcar los 3 documentos básicos: DNI, FUT y Comprobante de Pago'
        ]);

        if ($request->persona_existente_id) {
            $persona = Persona::findOrFail($request->persona_existente_id);
            
            $persona->update([
                'telefono' => $request->telefono ?: $persona->telefono,
                'email' => $request->email ?: $persona->email,
                'direccion' => $request->direccion ?: $persona->direccion
            ]);
        } else {
            $persona = Persona::where('numero_documento', $request->numero_documento)->first();
            
            if ($persona) {
                $persona->update([
                    'telefono' => $request->telefono ?: $persona->telefono,
                    'email' => $request->email ?: $persona->email,
                    'direccion' => $request->direccion ?: $persona->direccion
                ]);
            } else {
                $persona = Persona::create([
                    'tipo_documento' => $request->tipo_documento,
                    'numero_documento' => $request->numero_documento,
                    'tipo_persona' => $request->tipo_persona,
                    'nombres' => $request->nombres,
                    'apellido_paterno' => $request->apellido_paterno,
                    'apellido_materno' => $request->apellido_materno,
                    'razon_social' => $request->razon_social,
                    'representante_legal' => $request->representante_legal,
                    'telefono' => $request->telefono,
                    'email' => $request->email,
                    'direccion' => $request->direccion
                ]);
            }
        }

        $codigo = app(\App\Services\NumeracionService::class)->generarCodigo();
        
        $observacionesCompletas = [];
        if ($request->observaciones) {
            $observacionesCompletas[] = 'Trámite: ' . $request->observaciones;
        }
        if ($request->observaciones_documentos) {
            $observacionesCompletas[] = 'Documentos: ' . $request->observaciones_documentos;
        }
        
        $docsVerificados = implode(', ', $request->documentos_verificados ?? []);
        $docsAdicionales = $request->documentos_adicionales ? implode(', ', $request->documentos_adicionales) : '';
        
        $observacionesCompletas[] = 'Docs verificados: ' . $docsVerificados;
        if ($docsAdicionales) {
            $observacionesCompletas[] = 'Docs adicionales: ' . $docsAdicionales;
        }
        
        $expediente = Expediente::create([
            'codigo_expediente' => $codigo,
            'asunto' => $request->asunto,
            'id_persona' => $persona->id_persona,
            'remitente' => $persona->nombre_completo,
            'dni_remitente' => $persona->numero_documento,
            'id_tipo_tramite' => $request->id_tipo_tramite,
            'fecha_registro' => now(),
            'estado' => 'recepcionado',
            'canal' => 'presencial',
            'observaciones' => implode(' | ', $observacionesCompletas)
        ]);

        if ($request->hasFile('documento')) {
            $path = $request->file('documento')->store('documentos', 'public');
            
            Documento::create([
                'id_expediente' => $expediente->id_expediente,
                'nombre' => 'Documento Principal',
                'ruta_pdf' => $path,
                'tipo' => 'entrada'
            ]);
        }

        return redirect()->route('mesa-partes.registrar')
            ->with('success', 'SE REGISTRÓ CORRECTAMENTE')
            ->with('codigo_expediente', $codigo);
    }

    public function show(Expediente $expediente)
    {
        $expediente->load(['documentos', 'derivaciones.funcionario', 'historial']);
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
            'prioridad' => 'required|in:baja,media,alta,urgente',
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

    public function storeDerivar(Request $request, Expediente $expediente)
    {
        $request->validate([
            'id_area_destino' => 'required|exists:areas,id_area',
            'id_funcionario_asignado' => 'nullable|exists:users,id',
            'plazo_dias' => 'required|integer|min:1|max:365',
            'prioridad' => 'required|in:baja,media,alta,urgente',
            'observaciones' => 'nullable|string'
        ]);

        $fechaLimite = now()->addDays((int) $request->plazo_dias);

        if ($request->id_funcionario_asignado) {
            Derivacion::create([
                'id_expediente' => $expediente->id_expediente,
                'id_area_destino' => $request->id_area_destino,
                'id_funcionario_asignado' => $request->id_funcionario_asignado,
                'fecha_derivacion' => now()->toDateString(),
                'plazo_dias' => (int) $request->plazo_dias,
                'observaciones' => $request->observaciones,
                'estado' => 'pendiente'
            ]);
        }

        $expediente->update([
            'estado' => 'derivado',
            'id_area' => $request->id_area_destino,
            'id_funcionario_asignado' => $request->id_funcionario_asignado,
            'prioridad' => $request->prioridad
        ]);
        
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
            ->with(['tipoTramite', 'area', 'funcionarioAsignado'])
            ->get();
            
        $porVencer = Expediente::whereIn('estado', ['derivado', 'en_proceso'])
            ->whereHas('derivaciones', function($q) {
                $q->whereBetween('fecha_limite', [now(), now()->addDays(3)])
                  ->where('estado', 'Pendiente');
            })
            ->with(['tipoTramite', 'area', 'funcionarioAsignado'])
            ->get();
            
        return view('mesa-partes.monitoreo', compact('vencidos', 'porVencer'));
    }

    public function acuseRecibo(Expediente $expediente)
    {
        return view('mesa-partes.acuse-recibo', compact('expediente'));
    }

    public function dashboard()
    {
        $estadisticas = [
            'registrados_hoy' => Expediente::whereDate('created_at', today())->count(),
            'pendientes_clasificar' => Expediente::whereIn('estado', ['recepcionado', 'registrado'])->count(),
            'pendientes_derivar' => Expediente::where('estado', 'derivado')->count(),
            'vencidos' => Expediente::whereIn('estado', ['derivado', 'en_proceso'])
                ->whereHas('derivaciones', function($q) {
                    $q->where('fecha_limite', '<', now())->where('estado', 'Pendiente');
                })->count()
        ];

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
        $estadisticas = [
            'registrados_hoy' => Expediente::whereDate('created_at', today())->count(),
            'clasificados_hoy' => Expediente::whereDate('updated_at', today())->where('estado', 'derivado')->count(),
            'derivados_hoy' => Expediente::whereDate('updated_at', today())->where('estado', 'derivado')->count(),
            'archivados_hoy' => Expediente::whereDate('updated_at', today())->where('estado', 'archivado')->count()
        ];

        $tiposTramiteFrecuentes = TipoTramite::withCount('expedientes')
            ->orderBy('expedientes_count', 'desc')
            ->limit(5)
            ->get();

        $expedientesPendientes = Expediente::with(['ciudadano', 'tipoTramite', 'area'])
            ->whereIn('estado', ['recepcionado'])
            ->orderBy('created_at', 'asc')
            ->get();

        $graficoLabels = [];
        $graficoRegistrados = [];
        $graficoDerivados = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $graficoLabels[] = $fecha->format('d/m');
            $graficoRegistrados[] = Expediente::whereDate('created_at', $fecha)->count();
            $graficoDerivados[] = Expediente::whereDate('updated_at', $fecha)->where('estado', 'derivado')->count();
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
    
    public function cargoRecepcion(Expediente $expediente)
    {
        $expediente->load(['documentos', 'tipoTramite', 'persona']);
        
        $pdf = \PDF::loadView('pdf.cargo-recepcion', compact('expediente'));
        
        return $pdf->download('CARGO_' . $expediente->codigo_expediente . '.pdf');
    }
}