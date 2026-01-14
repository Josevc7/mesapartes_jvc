<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\Area;
use App\Models\TipoTramite;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index()
    {
        $stats = [
            'total_expedientes' => Expediente::count(),
            'expedientes_mes' => Expediente::whereMonth('created_at', now()->month)->count(),
            'pendientes' => Expediente::whereIn('estado', ['Registrado', 'Clasificado'])->count(),
            'resueltos' => Expediente::where('estado', 'Resuelto')->count()
        ];

        return view('reportes.index', compact('stats'));
    }

    /**
     * Reporte de trámites por fecha
     */
    public function reportePorFecha(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $tipoTramite = $request->get('tipo_tramite');
        $area = $request->get('area');
        $estado = $request->get('estado');

        // Query base
        $query = Expediente::with(['tipoTramite', 'area', 'persona', 'funcionarioAsignado'])
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);

        // Filtros opcionales
        if ($tipoTramite) {
            $query->where('id_tipo_tramite', $tipoTramite);
        }
        if ($area) {
            $query->where('id_area', $area);
        }
        if ($estado) {
            $query->where('estado', $estado);
        }

        $expedientes = $query->orderBy('created_at', 'desc')->paginate(20);

        // Estadísticas del período
        $statsQuery = Expediente::whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);

        if ($tipoTramite) $statsQuery->where('id_tipo_tramite', $tipoTramite);
        if ($area) $statsQuery->where('id_area', $area);
        if ($estado) $statsQuery->where('estado', $estado);

        $estadisticas = [
            'total' => (clone $statsQuery)->count(),
            'por_estado' => Expediente::selectRaw('estado, COUNT(*) as total')
                ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                ->when($tipoTramite, fn($q) => $q->where('id_tipo_tramite', $tipoTramite))
                ->when($area, fn($q) => $q->where('id_area', $area))
                ->groupBy('estado')
                ->get()
                ->pluck('total', 'estado'),
            'por_tipo_tramite' => Expediente::selectRaw('id_tipo_tramite, COUNT(*) as total')
                ->with('tipoTramite:id_tipo_tramite,nombre')
                ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                ->when($area, fn($q) => $q->where('id_area', $area))
                ->when($estado, fn($q) => $q->where('estado', $estado))
                ->groupBy('id_tipo_tramite')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),
            'por_area' => Expediente::selectRaw('id_area, COUNT(*) as total')
                ->with('area:id_area,nombre')
                ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                ->when($tipoTramite, fn($q) => $q->where('id_tipo_tramite', $tipoTramite))
                ->when($estado, fn($q) => $q->where('estado', $estado))
                ->whereNotNull('id_area')
                ->groupBy('id_area')
                ->orderByDesc('total')
                ->get(),
            'por_dia' => Expediente::selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
                ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                ->when($tipoTramite, fn($q) => $q->where('id_tipo_tramite', $tipoTramite))
                ->when($area, fn($q) => $q->where('id_area', $area))
                ->when($estado, fn($q) => $q->where('estado', $estado))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('fecha')
                ->get()
        ];

        // Datos para filtros
        $tiposTramite = TipoTramite::where('activo', true)->orderBy('nombre')->get();
        $areas = Area::where('activo', true)->orderBy('nombre')->get();
        $estados = ['recepcionado', 'registrado', 'clasificado', 'derivado', 'en_proceso', 'resuelto', 'archivado'];

        return view('reportes.por-fecha', compact(
            'expedientes',
            'estadisticas',
            'tiposTramite',
            'areas',
            'estados',
            'fechaInicio',
            'fechaFin',
            'tipoTramite',
            'area',
            'estado'
        ));
    }

    /**
     * Exportar reporte a Excel/CSV
     */
    public function exportarReporte(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $tipoTramite = $request->get('tipo_tramite');
        $area = $request->get('area');
        $estado = $request->get('estado');

        $query = Expediente::with(['tipoTramite', 'area', 'persona', 'funcionarioAsignado'])
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);

        if ($tipoTramite) $query->where('id_tipo_tramite', $tipoTramite);
        if ($area) $query->where('id_area', $area);
        if ($estado) $query->where('estado', $estado);

        $expedientes = $query->orderBy('created_at', 'desc')->get();

        // Generar CSV
        $filename = 'reporte_tramites_' . $fechaInicio . '_' . $fechaFin . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($expedientes) {
            $file = fopen('php://output', 'w');
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados
            fputcsv($file, [
                'Código',
                'Fecha Registro',
                'Asunto',
                'Tipo Trámite',
                'Área',
                'Estado',
                'Prioridad',
                'Remitente',
                'Funcionario Asignado'
            ]);

            // Datos
            foreach ($expedientes as $exp) {
                fputcsv($file, [
                    $exp->codigo_expediente,
                    $exp->created_at->format('d/m/Y H:i'),
                    $exp->asunto,
                    $exp->tipoTramite->nombre ?? 'N/A',
                    $exp->area->nombre ?? 'Sin asignar',
                    ucfirst($exp->estado),
                    ucfirst($exp->prioridad),
                    $exp->persona->nombre_completo ?? 'N/A',
                    $exp->funcionarioAsignado->name ?? 'Sin asignar'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function tramitesPorMes()
    {
        $tramites = Expediente::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('mes')
            ->get();
            
        return response()->json($tramites);
    }

    public function tiemposAtencion()
    {
        $tiempos = Expediente::where('estado', 'Resuelto')
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as promedio_dias')
            ->first();

        return response()->json($tiempos);
    }

    /**
     * Reporte por Tipo de Trámite
     */
    public function reportePorTipoTramite(Request $request)
    {
        $tipoTramiteId = $request->get('tipo_tramite');
        $fechaInicio = $request->get('fecha_inicio', now()->startOfYear()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        // Estadísticas generales por tipo de trámite
        $estadisticasPorTipo = TipoTramite::withCount([
            'expedientes',
            'expedientes as resueltos_count' => fn($q) => $q->where('estado', 'resuelto'),
            'expedientes as pendientes_count' => fn($q) => $q->whereIn('estado', ['recepcionado', 'registrado', 'derivado', 'en_proceso']),
        ])
            ->where('activo', true)
            ->orderByDesc('expedientes_count')
            ->get();

        // Detalle del tipo seleccionado
        $expedientes = collect();
        $tipoSeleccionado = null;
        $estadisticasDetalle = null;

        if ($tipoTramiteId) {
            $tipoSeleccionado = TipoTramite::find($tipoTramiteId);

            $expedientes = Expediente::with(['area', 'persona', 'funcionarioAsignado'])
                ->where('id_tipo_tramite', $tipoTramiteId)
                ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            $estadisticasDetalle = [
                'total' => Expediente::where('id_tipo_tramite', $tipoTramiteId)
                    ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                    ->count(),
                'por_estado' => Expediente::selectRaw('estado, COUNT(*) as total')
                    ->where('id_tipo_tramite', $tipoTramiteId)
                    ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                    ->groupBy('estado')
                    ->get()
                    ->pluck('total', 'estado'),
                'por_area' => Expediente::selectRaw('id_area, COUNT(*) as total')
                    ->with('area:id_area,nombre')
                    ->where('id_tipo_tramite', $tipoTramiteId)
                    ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                    ->whereNotNull('id_area')
                    ->groupBy('id_area')
                    ->orderByDesc('total')
                    ->get(),
                'por_mes' => Expediente::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
                    ->where('id_tipo_tramite', $tipoTramiteId)
                    ->whereYear('created_at', now()->year)
                    ->groupBy('mes')
                    ->orderBy('mes')
                    ->get()
            ];
        }

        $tiposTramite = TipoTramite::where('activo', true)->orderBy('nombre')->get();

        return view('reportes.por-tipo-tramite', compact(
            'estadisticasPorTipo',
            'expedientes',
            'tipoSeleccionado',
            'estadisticasDetalle',
            'tiposTramite',
            'tipoTramiteId',
            'fechaInicio',
            'fechaFin'
        ));
    }

    /**
     * Reporte por Área
     */
    public function reportePorArea(Request $request)
    {
        $areaId = $request->get('area');
        $fechaInicio = $request->get('fecha_inicio', now()->startOfYear()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        // Estadísticas generales por área
        $estadisticasPorArea = Area::withCount([
            'expedientes',
            'expedientes as resueltos_count' => fn($q) => $q->where('estado', 'resuelto'),
            'expedientes as pendientes_count' => fn($q) => $q->whereIn('estado', ['derivado', 'en_proceso']),
            'expedientes as vencidos_count' => fn($q) => $q->whereHas('derivaciones', function($d) {
                $d->where('fecha_limite', '<', now())->where('estado', 'Pendiente');
            }),
        ])
            ->where('activo', true)
            ->orderByDesc('expedientes_count')
            ->get();

        // Detalle del área seleccionada
        $expedientes = collect();
        $areaSeleccionada = null;
        $estadisticasDetalle = null;
        $funcionariosArea = collect();

        if ($areaId) {
            $areaSeleccionada = Area::find($areaId);

            $expedientes = Expediente::with(['tipoTramite', 'persona', 'funcionarioAsignado'])
                ->where('id_area', $areaId)
                ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            $estadisticasDetalle = [
                'total' => Expediente::where('id_area', $areaId)
                    ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                    ->count(),
                'por_estado' => Expediente::selectRaw('estado, COUNT(*) as total')
                    ->where('id_area', $areaId)
                    ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                    ->groupBy('estado')
                    ->get()
                    ->pluck('total', 'estado'),
                'por_tipo' => Expediente::selectRaw('id_tipo_tramite, COUNT(*) as total')
                    ->with('tipoTramite:id_tipo_tramite,nombre')
                    ->where('id_area', $areaId)
                    ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                    ->groupBy('id_tipo_tramite')
                    ->orderByDesc('total')
                    ->get(),
                'promedio_atencion' => Expediente::where('id_area', $areaId)
                    ->where('estado', 'resuelto')
                    ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as promedio')
                    ->value('promedio') ?? 0
            ];

            // Rendimiento de funcionarios del área
            $funcionariosArea = DB::table('users')
                ->select('users.id', 'users.name',
                    DB::raw('COUNT(expedientes.id_expediente) as total_asignados'),
                    DB::raw('SUM(CASE WHEN expedientes.estado = "resuelto" THEN 1 ELSE 0 END) as resueltos'),
                    DB::raw('SUM(CASE WHEN expedientes.estado IN ("derivado", "en_proceso") THEN 1 ELSE 0 END) as pendientes')
                )
                ->leftJoin('expedientes', 'users.id', '=', 'expedientes.id_funcionario_asignado')
                ->where('users.id_area', $areaId)
                ->where('users.id_rol', 4)
                ->groupBy('users.id', 'users.name')
                ->get();
        }

        $areas = Area::where('activo', true)->orderBy('nombre')->get();

        return view('reportes.por-area', compact(
            'estadisticasPorArea',
            'expedientes',
            'areaSeleccionada',
            'estadisticasDetalle',
            'funcionariosArea',
            'areas',
            'areaId',
            'fechaInicio',
            'fechaFin'
        ));
    }

    /**
     * Reporte por Remitente/Persona
     */
    public function reportePorRemitente(Request $request)
    {
        $busqueda = $request->get('busqueda');
        $tipoDocumento = $request->get('tipo_documento');
        $numeroDocumento = $request->get('numero_documento');

        $personas = collect();
        $personaSeleccionada = null;
        $expedientesPersona = collect();

        // Buscar personas
        if ($busqueda || $numeroDocumento) {
            $queryPersonas = \App\Models\Persona::query();

            if ($busqueda) {
                $queryPersonas->where(function($q) use ($busqueda) {
                    $q->where('nombres', 'LIKE', "%{$busqueda}%")
                      ->orWhere('apellido_paterno', 'LIKE', "%{$busqueda}%")
                      ->orWhere('apellido_materno', 'LIKE', "%{$busqueda}%")
                      ->orWhere('razon_social', 'LIKE', "%{$busqueda}%")
                      ->orWhere('numero_documento', 'LIKE', "%{$busqueda}%");
                });
            }

            if ($tipoDocumento) {
                $queryPersonas->where('tipo_documento', $tipoDocumento);
            }

            if ($numeroDocumento) {
                $queryPersonas->where('numero_documento', $numeroDocumento);
            }

            $personas = $queryPersonas->withCount('expedientes')
                ->orderByDesc('expedientes_count')
                ->limit(50)
                ->get();
        }

        // Si se selecciona una persona específica
        $personaId = $request->get('persona_id');
        if ($personaId) {
            $personaSeleccionada = \App\Models\Persona::withCount([
                'expedientes',
                'expedientes as resueltos_count' => fn($q) => $q->where('estado', 'resuelto'),
                'expedientes as pendientes_count' => fn($q) => $q->whereIn('estado', ['recepcionado', 'registrado', 'derivado', 'en_proceso']),
            ])->find($personaId);

            $expedientesPersona = Expediente::with(['tipoTramite', 'area', 'funcionarioAsignado'])
                ->where('id_persona', $personaId)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        // Top remitentes del mes
        $topRemitentes = \App\Models\Persona::withCount(['expedientes' => fn($q) => $q->whereMonth('created_at', now()->month)])
            ->having('expedientes_count', '>', 0)
            ->orderByDesc('expedientes_count')
            ->limit(10)
            ->get();

        return view('reportes.por-remitente', compact(
            'personas',
            'personaSeleccionada',
            'expedientesPersona',
            'topRemitentes',
            'busqueda',
            'tipoDocumento',
            'numeroDocumento',
            'personaId'
        ));
    }
}