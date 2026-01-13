<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\User;
use App\Services\EstadisticasService;
use App\Services\DerivacionService;
use App\Http\Requests\Derivacion\ExtenderPlazoRequest;
use App\Http\Requests\Expediente\ValidarExpedienteRequest;
use Carbon\Carbon;

class JefeAreaController extends Controller
{
    protected EstadisticasService $estadisticasService;
    protected DerivacionService $derivacionService;

    public function __construct(
        EstadisticasService $estadisticasService,
        DerivacionService $derivacionService
    ) {
        $this->estadisticasService = $estadisticasService;
        $this->derivacionService = $derivacionService;
    }

    /**
     * Dashboard del Jefe de Área
     *
     * REFACTORIZADO: Ahora usa EstadisticasService
     * ANTES: 17 líneas con queries inline
     * DESPUÉS: 4 líneas delegando al Service
     */
    public function dashboard()
    {
        $areaId = auth()->user()->id_area;
        $stats = $this->estadisticasService->obtenerEstadisticasJefeArea($areaId);

        return view('jefe-area.dashboard', compact('stats'));
    }

    public function expedientes(Request $request)
    {
        $areaId = auth()->user()->id_area;
        
        $query = Expediente::where('id_area', $areaId)
            ->with(['tipoTramite', 'ciudadano', 'funcionarioAsignado', 'derivaciones']);

        if ($request->estado) {
            $query->where('estado', $request->estado);
        }

        if ($request->funcionario) {
            $query->where('id_funcionario_asignado', $request->funcionario);
        }

        $expedientes = $query->orderBy('created_at', 'desc')->paginate(15);
        
        $funcionarios = User::where('id_area', $areaId)
            ->where('id_rol', 4)->get();
            
        return view('jefe-area.expedientes', compact('expedientes', 'funcionarios'));
    }

    public function aprobar(Expediente $expediente)
    {
        $this->authorize('approve', $expediente);

        $expediente->update([
            'estado' => 'aprobado',
            'aprobado_por' => auth()->user()->id,
            'fecha_aprobacion' => now()
        ]);
        
        $expediente->agregarHistorial('Expediente aprobado por Jefe de Área', auth()->user()->id);
        
        return back()->with('success', 'Expediente aprobado correctamente');
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

    public function reportes()
    {
        $areaId = auth()->user()->id_area;
        
        $reportes = [
            'expedientes_por_mes' => Expediente::where('id_area', $areaId)
                ->selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
                ->whereYear('created_at', now()->year)
                ->groupBy('mes')->get(),
            'funcionarios_rendimiento' => User::where('id_area', $areaId)
                ->where('id_rol', 4)
                ->withCount(['expedientesAsignados as total', 
                    'expedientesAsignados as resueltos' => function($q) {
                        $q->where('estado', 'Resuelto');
                    }])->get(),
            'tiempos_promedio' => Expediente::where('id_area', $areaId)
                ->where('estado', 'Resuelto')
                ->selectRaw('AVG(DATEDIFF(fecha_resolucion, created_at)) as promedio_dias')
                ->first()
        ];
        
        return view('jefe-area.reportes', compact('reportes'));
    }

    public function controlPlazos()
    {
        $areaId = auth()->user()->id_area;
        
        $stats = [
            'vencidos' => Expediente::where('id_area', $areaId)
                ->whereHas('derivaciones', function($q) {
                    $q->where('fecha_limite', '<', now());
                })->count(),
            'por_vencer' => Expediente::where('id_area', $areaId)
                ->whereHas('derivaciones', function($q) {
                    $q->whereBetween('fecha_limite', [now(), now()->addDays(3)]);
                })->count(),
            'en_plazo' => Expediente::where('id_area', $areaId)
                ->whereIn('estado', ['Derivado', 'En Proceso'])
                ->whereHas('derivaciones', function($q) {
                    $q->where('fecha_limite', '>', now()->addDays(3));
                })->count(),
            'sin_asignar' => Expediente::where('id_area', $areaId)
                ->whereNull('id_funcionario_asignado')->count(),
            'por_aprobar' => Expediente::where('id_area', $areaId)
                ->where('estado', 'Resuelto')->count()
        ];
        
        $expedientes_criticos = Expediente::where('id_area', $areaId)
            ->whereIn('estado', ['Derivado', 'En Proceso'])
            ->with(['funcionarioAsignado', 'derivaciones'])
            ->get()
            ->map(function($exp) {
                $derivacion = $exp->derivaciones->first();
                if ($derivacion && $derivacion->fecha_limite) {
                    $exp->dias_vencido = $derivacion->fecha_limite->isPast() ? (int) $derivacion->fecha_limite->diffInDays(now()) : 0;
                    $exp->dias_restantes = $derivacion->fecha_limite->isFuture() ? (int) now()->diffInDays($derivacion->fecha_limite) : 0;
                }
                return $exp;
            })
            ->filter(function($exp) {
                return $exp->dias_vencido > 0 || $exp->dias_restantes <= 3;
            });
        
        return view('jefe-area.control-plazos', compact('stats', 'expedientes_criticos'));
    }

    public function supervision()
    {
        $areaId = auth()->user()->id_area;
        
        $funcionarios = User::where('id_area', $areaId)
            ->where('id_rol', 4)
            ->withCount([
                'expedientesAsignados as asignados',
                'expedientesAsignados as resueltos' => function($q) {
                    $q->where('estado', 'Resuelto');
                },
                'expedientesAsignados as pendientes' => function($q) {
                    $q->whereIn('estado', ['Derivado', 'En Proceso']);
                }
            ])
            ->get()
            ->map(function($funcionario) {
                $funcionario->carga_trabajo = $funcionario->pendientes;
                $funcionario->efectividad = $funcionario->asignados > 0 ? 
                    round(($funcionario->resueltos / $funcionario->asignados) * 100) : 0;
                return $funcionario;
            });
        
        return view('jefe-area.supervision', compact('funcionarios'));
    }

    public function validarDocumentos()
    {
        $areaId = auth()->user()->id_area;
        
        $estadisticas = [
            'pendientes_validacion' => Expediente::where('id_area', $areaId)->where('estado', 'Resuelto')->count(),
            'validados_hoy' => Expediente::where('id_area', $areaId)->where('estado', 'Aprobado')->whereDate('updated_at', today())->count(),
            'rechazados_hoy' => Expediente::where('id_area', $areaId)->where('estado', 'Rechazado')->whereDate('updated_at', today())->count(),
            'requieren_autorizacion' => 0
        ];
        
        $expedientesPendientes = Expediente::where('id_area', $areaId)
            ->where('estado', 'Resuelto')
            ->with(['funcionarioAsignado', 'tipoTramite'])
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
            'documentos' => $expediente->documentos->where('tipo', '!=', 'entrada')->map(function($doc) {
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
                ->whereHas('derivaciones', function($q) {
                    $q->where('fecha_limite', '<', now());
                })->count(),
            'reasignaciones_pendientes' => 0,
            'autorizaciones_especiales' => 0,
            'observaciones_ciudadano' => Expediente::where('id_area', $areaId)
                ->where('estado', 'Observado')->count()
        ];
        
        $conflictos = Expediente::where('id_area', $areaId)
            ->whereIn('estado', ['Derivado', 'En Proceso', 'Observado'])
            ->with(['funcionarioAsignado', 'derivaciones'])
            ->get()
            ->map(function($exp) {
                $derivacion = $exp->derivaciones->first();

                // Verificar si está vencido
                if ($derivacion && $derivacion->fecha_limite && $derivacion->fecha_limite->isPast()) {
                    $exp->tipo_conflicto = 'vencido';
                    $exp->dias_vencido = (int) $derivacion->fecha_limite->diffInDays(now());
                }
                // Verificar si requiere autorización especial (prioridad Urgente sin clasificar)
                elseif ($exp->prioridad === 'Urgente' && $exp->estado === 'Registrado') {
                    $exp->tipo_conflicto = 'autorizacion';
                    $exp->dias_vencido = 0;
                }
                // Por defecto es observación
                else {
                    $exp->tipo_conflicto = 'observacion';
                    $exp->dias_vencido = 0;
                }
                return $exp;
            })
            ->filter(function($exp) {
                return in_array($exp->tipo_conflicto, ['vencido', 'autorizacion', 'observacion']);
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
            'expedientes_mes' => Expediente::where('id_area', $areaId)->whereMonth('created_at', now()->month)->count(),
            'resueltos_mes' => Expediente::where('id_area', $areaId)->where('estado', 'Resuelto')->whereMonth('updated_at', now()->month)->count(),
            'tiempo_promedio' => Expediente::where('id_area', $areaId)->where('estado', 'Resuelto')
                ->whereNotNull('fecha_resolucion')
                ->get()
                ->avg(function($exp) {
                    return $exp->created_at->diffInDays($exp->fecha_resolucion);
                }) ?? 0,
            'eficiencia' => 85
        ];
        
        $metas = \App\Models\Meta::where('id_area', $areaId)->get();
        
        $rendimientoFuncionarios = User::where('id_area', $areaId)
            ->where('id_rol', 4)
            ->withCount(['expedientesAsignados as expedientes_resueltos' => function($q) {
                $q->where('estado', 'Resuelto')->whereMonth('updated_at', now()->month);
            }])
            ->get()
            ->map(function($funcionario) {
                $funcionario->tiempo_promedio = Expediente::where('id_funcionario_asignado', $funcionario->id)
                    ->where('estado', 'Resuelto')
                    ->whereNotNull('fecha_resolucion')
                    ->get()
                    ->avg(function($exp) {
                        return $exp->created_at->diffInDays($exp->fecha_resolucion);
                    }) ?? 0;
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
}