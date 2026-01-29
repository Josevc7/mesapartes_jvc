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
use App\Http\Requests\Derivacion\ExtenderPlazoRequest;
use App\Http\Requests\Expediente\ValidarExpedienteRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

        $stats = $this->estadisticasService->obtenerEstadisticasJefeArea($areaId);

        // Estadísticas adicionales para el dashboard
        $stats['por_aprobar'] = Expediente::where('id_area', $areaId)
            ->where('estado', 'resuelto')
            ->count();

        $stats['sin_asignar'] = Expediente::where('id_area', $areaId)
            ->whereNull('id_funcionario_asignado')
            ->whereIn('estado', ['derivado', 'en_proceso'])
            ->count();

        // Expedientes urgentes
        $stats['urgentes'] = Expediente::where('id_area', $areaId)
            ->where('prioridad', 'urgente')
            ->whereNotIn('estado', ['archivado', 'resuelto', 'aprobado'])
            ->count();

        // Expedientes críticos (vencidos o por vencer)
        $expedientesCriticos = Expediente::where('id_area', $areaId)
            ->whereIn('estado', ['derivado', 'en_proceso'])
            ->with(['funcionarioAsignado', 'derivaciones' => function($q) {
                $q->where('estado', 'pendiente')->latest();
            }])
            ->get()
            ->map(function($exp) {
                $derivacion = $exp->derivaciones->first();
                if ($derivacion && $derivacion->fecha_limite) {
                    $exp->dias_vencido = $derivacion->fecha_limite->isPast()
                        ? (int) $derivacion->fecha_limite->diffInDays(now())
                        : 0;
                    $exp->dias_restantes = $derivacion->fecha_limite->isFuture()
                        ? (int) now()->diffInDays($derivacion->fecha_limite)
                        : 0;
                    $exp->fecha_limite = $derivacion->fecha_limite;
                } else {
                    $exp->dias_vencido = 0;
                    $exp->dias_restantes = 999;
                }
                return $exp;
            })
            ->filter(function($exp) {
                return $exp->dias_vencido > 0 || $exp->dias_restantes <= 3;
            })
            ->sortByDesc('dias_vencido')
            ->take(5);

        // Funcionarios de las subdirecciones con carga de trabajo
        $subdireccionesIds = Area::where('id_area_padre', $areaId)->pluck('id_area')->toArray();

        $funcionarios = User::whereIn('id_area', $subdireccionesIds)
            ->where('id_rol', 4) // Solo Funcionarios
            ->where('activo', true)
            ->with('area')
            ->withCount([
                'expedientesAsignados as pendientes' => function($q) {
                    $q->whereIn('estado', ['derivado', 'en_proceso']);
                }
            ])
            ->get();

        return view('jefe-area.dashboard', compact('stats', 'expedientesCriticos', 'funcionarios'));
    }

    /**
     * Lista de expedientes del área con filtros avanzados
     */
    public function expedientes(Request $request)
    {
        $areaId = auth()->user()->id_area;

        $query = Expediente::where('id_area', $areaId)
            ->with(['tipoTramite', 'ciudadano', 'funcionarioAsignado', 'derivaciones' => function($q) {
                $q->where('estado', 'pendiente')->latest();
            }, 'persona']);

        // Filtro por estado
        if ($request->estado) {
            $query->where('estado', $request->estado);
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

        // Agregar información de plazos a cada expediente
        $expedientes->getCollection()->transform(function($exp) {
            $derivacion = $exp->derivaciones->first();
            if ($derivacion && $derivacion->fecha_limite) {
                $exp->dias_vencido = $derivacion->fecha_limite->isPast()
                    ? (int) $derivacion->fecha_limite->diffInDays(now())
                    : 0;
                $exp->dias_restantes = $derivacion->fecha_limite->isFuture()
                    ? (int) now()->diffInDays($derivacion->fecha_limite)
                    : 0;
                $exp->fecha_limite = $derivacion->fecha_limite;
            } else {
                $exp->dias_vencido = 0;
                $exp->dias_restantes = null;
                $exp->fecha_limite = null;
            }
            return $exp;
        });

        // Funcionarios de las subdirecciones para filtros y asignación
        $subdireccionesIds = Area::where('id_area_padre', $areaId)->pluck('id_area')->toArray();

        $funcionarios = User::whereIn('id_area', $subdireccionesIds)
            ->where('id_rol', 4) // Solo Funcionarios
            ->where('activo', true)
            ->with('area')
            ->withCount([
                'expedientesAsignados as carga_trabajo' => function($q) {
                    $q->whereIn('estado', ['derivado', 'en_proceso']);
                }
            ])
            ->orderBy('name')
            ->get();

        // Tipos de trámite del área
        $tiposTramite = TipoTramite::where('id_area', $areaId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        // Estadísticas rápidas
        $estadisticas = [
            'total' => Expediente::where('id_area', $areaId)->count(),
            'pendientes' => Expediente::where('id_area', $areaId)
                ->whereIn('estado', ['derivado', 'en_proceso'])->count(),
            'resueltos' => Expediente::where('id_area', $areaId)
                ->where('estado', 'resuelto')->count(),
            'sin_asignar' => Expediente::where('id_area', $areaId)
                ->whereNull('id_funcionario_asignado')
                ->whereIn('estado', ['derivado', 'en_proceso'])->count(),
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
        $subdireccionesIds = Area::where('id_area_padre', $areaId)->pluck('id_area')->toArray();

        $funcionarios = User::whereIn('id_area', $subdireccionesIds)
            ->where('id_rol', 4) // Solo Funcionarios
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
        $subdireccionesIds = Area::where('id_area_padre', $areaJefe)->pluck('id_area')->toArray();
        $areasPermitidas = array_merge([$areaJefe], $subdireccionesIds);

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
                $datosActualizar['estado'] = 'asignado';
            }

            $expediente->update($datosActualizar);

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
        $subdireccionesIds = Area::where('id_area_padre', $areaId)->pluck('id_area')->toArray();
        $areasPermitidas = array_merge([$areaId], $subdireccionesIds);

        if (!in_array($funcionario->id_area, $areasPermitidas)) {
            return back()->with('error', 'El funcionario no pertenece a esta área.');
        }

        $count = 0;
        DB::transaction(function() use ($request, $funcionario, $areaId, $areasPermitidas, &$count) {
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
     * Recepcionar expediente derivado al área
     */
    public function recepcionar(Expediente $expediente)
    {
        $this->authorize('update', $expediente);

        // Verificar que el expediente esté en estado derivado
        if ($expediente->estado !== 'derivado') {
            return back()->with('error', 'Solo se pueden recepcionar expedientes en estado derivado.');
        }

        $expediente->update([
            'estado' => 'recepcionado'
        ]);

        $expediente->agregarHistorial(
            'Expediente recepcionado por el área. Fecha/Hora: ' . now()->format('d/m/Y H:i:s') . '. Responsable: ' . auth()->user()->name,
            auth()->user()->id
        );

        return back()->with('success', 'Expediente recepcionado correctamente. Ahora puede asignarlo a un funcionario.');
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

    /**
     * Reportes del área con estadísticas completas
     */
    public function reportes(Request $request)
    {
        $areaId = auth()->user()->id_area;
        $año = $request->get('año', now()->year);
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
            ->where('estado', 'resuelto')
            ->whereYear('updated_at', $año)
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');

        // Rendimiento por funcionarios de subdirecciones
        $subdireccionesIdsReporte = Area::where('id_area_padre', $areaId)->pluck('id_area')->toArray();

        $funcionariosRendimiento = User::whereIn('id_area', $subdireccionesIdsReporte)
            ->where('id_rol', 4) // Solo Funcionarios
            ->where('activo', true)
            ->with('area')
            ->withCount([
                'expedientesAsignados as total_asignados',
                'expedientesAsignados as resueltos' => function($q) {
                    $q->whereIn('estado', ['resuelto', 'aprobado', 'archivado']);
                },
                'expedientesAsignados as pendientes' => function($q) {
                    $q->whereIn('estado', ['derivado', 'en_proceso']);
                },
                'expedientesAsignados as vencidos' => function($q) {
                    $q->whereHas('derivaciones', function($d) {
                        $d->where('estado', 'pendiente')
                          ->where('fecha_limite', '<', now());
                    });
                }
            ])
            ->get()
            ->map(function($func) {
                $func->efectividad = $func->total_asignados > 0
                    ? round(($func->resueltos / $func->total_asignados) * 100, 1)
                    : 0;

                // Calcular tiempo promedio de atención
                $tiempoPromedio = Expediente::where('id_funcionario_asignado', $func->id)
                    ->whereIn('estado', ['resuelto', 'aprobado'])
                    ->whereNotNull('fecha_resolucion')
                    ->selectRaw('AVG(DATEDIFF(fecha_resolucion, created_at)) as promedio')
                    ->first();
                $func->tiempo_promedio = round($tiempoPromedio->promedio ?? 0, 1);

                return $func;
            });

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
            ->whereIn('estado', ['resuelto', 'aprobado'])
            ->whereNotNull('fecha_resolucion')
            ->selectRaw('AVG(DATEDIFF(fecha_resolucion, created_at)) as promedio_dias')
            ->first();

        // Cumplimiento de plazos
        $totalConPlazo = Expediente::where('id_area', $areaId)
            ->whereHas('derivaciones', function($q) {
                $q->whereNotNull('fecha_limite');
            })->count();

        $cumplidosEnPlazo = Expediente::where('id_area', $areaId)
            ->whereIn('estado', ['resuelto', 'aprobado', 'archivado'])
            ->whereHas('derivaciones', function($q) {
                $q->whereNotNull('fecha_limite')
                  ->whereRaw('fecha_resolucion <= fecha_limite');
            })->count();

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
                ->whereIn('estado', ['resuelto', 'aprobado', 'archivado'])
                ->whereYear('updated_at', $año)->count(),
        ];

        return view('jefe-area.reportes', compact('reportes'));
    }

    /**
     * Control de plazos con gestión avanzada
     */
    public function controlPlazos()
    {
        $areaId = auth()->user()->id_area;

        // Estadísticas de plazos
        $stats = [
            'vencidos' => Expediente::where('id_area', $areaId)
                ->whereIn('estado', ['derivado', 'en_proceso'])
                ->whereHas('derivaciones', function($q) {
                    $q->where('estado', 'pendiente')
                      ->where('fecha_limite', '<', now());
                })->count(),
            'por_vencer' => Expediente::where('id_area', $areaId)
                ->whereIn('estado', ['derivado', 'en_proceso'])
                ->whereHas('derivaciones', function($q) {
                    $q->where('estado', 'pendiente')
                      ->whereBetween('fecha_limite', [now(), now()->addDays(3)]);
                })->count(),
            'en_plazo' => Expediente::where('id_area', $areaId)
                ->whereIn('estado', ['derivado', 'en_proceso'])
                ->whereHas('derivaciones', function($q) {
                    $q->where('estado', 'pendiente')
                      ->where('fecha_limite', '>', now()->addDays(3));
                })->count(),
            'sin_asignar' => Expediente::where('id_area', $areaId)
                ->whereNull('id_funcionario_asignado')
                ->whereIn('estado', ['derivado', 'en_proceso'])->count(),
            'por_aprobar' => Expediente::where('id_area', $areaId)
                ->where('estado', 'resuelto')->count()
        ];

        // Expedientes críticos (vencidos y por vencer)
        $expedientesCriticos = Expediente::where('id_area', $areaId)
            ->whereIn('estado', ['derivado', 'en_proceso'])
            ->with(['funcionarioAsignado', 'tipoTramite', 'derivaciones' => function($q) {
                $q->where('estado', 'pendiente')->latest();
            }])
            ->get()
            ->map(function($exp) {
                $derivacion = $exp->derivaciones->first();
                if ($derivacion && $derivacion->fecha_limite) {
                    $exp->dias_vencido = $derivacion->fecha_limite->isPast()
                        ? (int) $derivacion->fecha_limite->diffInDays(now())
                        : 0;
                    $exp->dias_restantes = $derivacion->fecha_limite->isFuture()
                        ? (int) now()->diffInDays($derivacion->fecha_limite)
                        : 0;
                    $exp->fecha_limite = $derivacion->fecha_limite;
                    $exp->plazo_original = $derivacion->plazo_dias;
                } else {
                    $exp->dias_vencido = 0;
                    $exp->dias_restantes = null;
                    $exp->fecha_limite = null;
                    $exp->plazo_original = null;
                }
                return $exp;
            })
            ->filter(function($exp) {
                return $exp->dias_vencido > 0 || ($exp->dias_restantes !== null && $exp->dias_restantes <= 3);
            })
            ->sortByDesc('dias_vencido');

        // Expedientes sin asignar
        $expedientesSinAsignar = Expediente::where('id_area', $areaId)
            ->whereNull('id_funcionario_asignado')
            ->whereIn('estado', ['derivado', 'en_proceso'])
            ->with(['tipoTramite', 'derivaciones' => function($q) {
                $q->where('estado', 'pendiente')->latest();
            }])
            ->get()
            ->map(function($exp) {
                $derivacion = $exp->derivaciones->first();
                if ($derivacion && $derivacion->fecha_limite) {
                    $exp->dias_restantes = $derivacion->fecha_limite->isFuture()
                        ? (int) now()->diffInDays($derivacion->fecha_limite)
                        : -1 * (int) $derivacion->fecha_limite->diffInDays(now());
                    $exp->fecha_limite = $derivacion->fecha_limite;
                }
                return $exp;
            });

        // Funcionarios de subdirecciones disponibles para reasignación
        $subdireccionesIdsPlazos = Area::where('id_area_padre', $areaId)->pluck('id_area')->toArray();

        $funcionarios = User::whereIn('id_area', $subdireccionesIdsPlazos)
            ->where('id_rol', 4) // Solo Funcionarios
            ->where('activo', true)
            ->with('area')
            ->withCount([
                'expedientesAsignados as carga_trabajo' => function($q) {
                    $q->whereIn('estado', ['derivado', 'en_proceso']);
                }
            ])
            ->orderBy('carga_trabajo')
            ->get();

        // Análisis de cumplimiento
        $totalExpedientes = Expediente::where('id_area', $areaId)
            ->whereIn('estado', ['resuelto', 'aprobado', 'archivado'])
            ->count();

        $resueltosEnPlazo = Expediente::where('id_area', $areaId)
            ->whereIn('estado', ['resuelto', 'aprobado', 'archivado'])
            ->whereHas('derivaciones', function($q) {
                $q->whereColumn('fecha_limite', '>=', 'expedientes.fecha_resolucion');
            })->count();

        $cumplimiento = [
            'porcentaje' => $totalExpedientes > 0
                ? round(($resueltosEnPlazo / $totalExpedientes) * 100, 1)
                : 100,
            'total' => $totalExpedientes,
            'en_plazo' => $resueltosEnPlazo
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

        // Funcionarios de subdirecciones
        $subdireccionesIdsSupervision = $areaId ? Area::where('id_area_padre', $areaId)->pluck('id_area')->toArray() : [];

        $queryFuncionarios = User::query();
        if (!$esAdministrador || $areaId) {
            $queryFuncionarios->whereIn('id_area', $subdireccionesIdsSupervision);
        }

        // Funcionarios con estadísticas completas
        $funcionarios = $queryFuncionarios
            ->where('id_rol', 4) // Solo Funcionarios
            ->where('activo', true)
            ->with('area')
            ->withCount([
                'expedientesAsignados as total_asignados',
                'expedientesAsignados as resueltos' => function($q) {
                    $q->whereIn('estado', ['resuelto', 'aprobado', 'archivado']);
                },
                'expedientesAsignados as pendientes' => function($q) {
                    $q->whereIn('estado', ['derivado', 'en_proceso']);
                },
                'expedientesAsignados as vencidos' => function($q) {
                    $q->whereHas('derivaciones', function($d) {
                        $d->where('estado', 'pendiente')
                          ->where('fecha_limite', '<', now());
                    });
                },
                'expedientesAsignados as resueltos_mes' => function($q) {
                    $q->whereIn('estado', ['resuelto', 'aprobado'])
                      ->whereMonth('updated_at', now()->month);
                }
            ])
            ->get()
            ->map(function($funcionario) {
                $funcionario->carga_trabajo = $funcionario->pendientes;
                $funcionario->efectividad = $funcionario->total_asignados > 0
                    ? round(($funcionario->resueltos / $funcionario->total_asignados) * 100, 1)
                    : 0;

                // Calcular tiempo promedio de atención
                $tiempoPromedio = Expediente::where('id_funcionario_asignado', $funcionario->id)
                    ->whereIn('estado', ['resuelto', 'aprobado'])
                    ->whereNotNull('fecha_resolucion')
                    ->selectRaw('AVG(DATEDIFF(fecha_resolucion, created_at)) as promedio')
                    ->first();
                $funcionario->tiempo_promedio = round($tiempoPromedio->promedio ?? 0, 1);

                // Expedientes actuales del funcionario
                $funcionario->expedientes_actuales = Expediente::where('id_funcionario_asignado', $funcionario->id)
                    ->whereIn('estado', ['derivado', 'en_proceso', 'resuelto'])
                    ->with(['tipoTramite', 'derivaciones' => function($q) {
                        $q->where('estado', 'pendiente')->latest();
                    }])
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get()
                    ->map(function($exp) {
                        $derivacion = $exp->derivaciones->first();
                        if ($derivacion && $derivacion->fecha_limite) {
                            $exp->dias_restantes = $derivacion->fecha_limite->isFuture()
                                ? (int) now()->diffInDays($derivacion->fecha_limite)
                                : -1 * (int) $derivacion->fecha_limite->diffInDays(now());
                            $exp->vencido = $derivacion->fecha_limite->isPast();
                        } else {
                            $exp->dias_restantes = null;
                            $exp->vencido = false;
                        }
                        return $exp;
                    });

                return $funcionario;
            })
            ->sortByDesc('carga_trabajo');

        // Estadísticas generales del área (o todas las áreas si es admin)
        $queryEstadisticas = Expediente::query();
        if (!$esAdministrador || $areaId) {
            $queryEstadisticas->where('id_area', $areaId);
        }

        $estadisticasArea = [
            'total_pendientes' => (clone $queryEstadisticas)
                ->whereIn('estado', ['derivado', 'en_proceso'])->count(),
            'total_vencidos' => (clone $queryEstadisticas)
                ->whereIn('estado', ['derivado', 'en_proceso'])
                ->whereHas('derivaciones', function($q) {
                    $q->where('estado', 'pendiente')
                      ->where('fecha_limite', '<', now());
                })->count(),
            'resueltos_mes' => (clone $queryEstadisticas)
                ->whereIn('estado', ['resuelto', 'aprobado'])
                ->whereMonth('updated_at', now()->month)->count(),
            'promedio_carga' => $funcionarios->avg('carga_trabajo'),
            'funcionario_mas_cargado' => $funcionarios->sortByDesc('carga_trabajo')->first(),
            'funcionario_menos_cargado' => $funcionarios->sortBy('carga_trabajo')->first(),
        ];

        // Procesos que requieren autorización especial
        $queryProcesos = Expediente::query();
        if (!$esAdministrador || $areaId) {
            $queryProcesos->where('id_area', $areaId);
        }
        $procesosEspeciales = $queryProcesos
            ->where('estado', 'resuelto')
            ->with(['funcionarioAsignado', 'tipoTramite'])
            ->orderBy('fecha_resolucion', 'asc')
            ->take(10)
            ->get();

        // Alertas del sistema
        $alertas = collect();

        // Funcionarios sobrecargados (más de 10 pendientes)
        $sobrecargados = $funcionarios->filter(fn($f) => $f->carga_trabajo > 10);
        foreach ($sobrecargados as $func) {
            $alertas->push([
                'tipo' => 'danger',
                'mensaje' => "{$func->name} tiene {$func->carga_trabajo} expedientes pendientes (sobrecargado)",
                'icono' => 'exclamation-triangle'
            ]);
        }

        // Expedientes vencidos
        if ($estadisticasArea['total_vencidos'] > 0) {
            $alertas->push([
                'tipo' => 'danger',
                'mensaje' => "{$estadisticasArea['total_vencidos']} expediente(s) vencido(s) requieren atención",
                'icono' => 'clock'
            ]);
        }

        // Expedientes por aprobar
        $queryAprobar = Expediente::query();
        if (!$esAdministrador || $areaId) {
            $queryAprobar->where('id_area', $areaId);
        }
        $porAprobar = $queryAprobar->where('estado', 'resuelto')->count();
        if ($porAprobar > 0) {
            $alertas->push([
                'tipo' => 'info',
                'mensaje' => "{$porAprobar} expediente(s) listo(s) para aprobar",
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

        // Preparar consulta base
        $queryEstadisticas = Expediente::query();
        $queryExpedientes = Expediente::query();

        // Si es administrador sin área, mostrar todas las áreas
        if (!$esAdministrador || $areaId) {
            $queryEstadisticas->where('id_area', $areaId);
            $queryExpedientes->where('id_area', $areaId);
        }

        $estadisticas = [
            'pendientes_validacion' => (clone $queryEstadisticas)->where('estado', 'Resuelto')->count(),
            'validados_hoy' => (clone $queryEstadisticas)->where('estado', 'Aprobado')->whereDate('updated_at', today())->count(),
            'rechazados_hoy' => (clone $queryEstadisticas)->where('estado', 'Rechazado')->whereDate('updated_at', today())->count(),
            'requieren_autorizacion' => 0
        ];

        $expedientesPendientes = $queryExpedientes
            ->where('estado', 'Resuelto')
            ->with(['funcionarioAsignado', 'tipoTramite', 'area'])
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
        
        // Funcionarios de subdirecciones
        $subdireccionesIdsMetas = Area::where('id_area_padre', $areaId)->pluck('id_area')->toArray();

        $rendimientoFuncionarios = User::whereIn('id_area', $subdireccionesIdsMetas)
            ->where('id_rol', 4) // Solo Funcionarios
            ->with('area')
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