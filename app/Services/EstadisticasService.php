<?php

namespace App\Services;

use App\Models\Expediente;
use App\Models\Derivacion;
use App\Models\Area;
use App\Models\TipoTramite;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class EstadisticasService
{
    /**
     * Obtiene estadísticas del dashboard de Mesa de Partes
     * Con caché de 5 minutos para mejorar rendimiento
     */
    public function obtenerEstadisticasMesaPartes(): array
    {
        return Cache::remember('estadisticas_mesa_partes', 300, function () {
            $hoy = today();

            // Una sola consulta optimizada para todas las estadísticas
            $idsPendientes = \App\Models\EstadoExpediente::whereIn('slug', ['pendiente_recepcion', 'recepcionado', 'registrado'])->pluck('id_estado')->toArray();
            $idClasificado = \App\Models\EstadoExpediente::where('slug', 'clasificado')->value('id_estado');
            $stats = Expediente::selectRaw("
                SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as registrados_hoy,
                SUM(CASE WHEN id_estado IN (" . implode(',', $idsPendientes) . ") THEN 1 ELSE 0 END) as pendientes_clasificar,
                SUM(CASE WHEN id_estado = ? THEN 1 ELSE 0 END) as pendientes_derivar
            ", [$hoy, $idClasificado])->first();

            return [
                'registrados_hoy' => (int) ($stats->registrados_hoy ?? 0),
                'pendientes_clasificar' => (int) ($stats->pendientes_clasificar ?? 0),
                'pendientes_derivar' => (int) ($stats->pendientes_derivar ?? 0),
                'vencidos' => $this->contarExpedientesVencidos()
            ];
        });
    }

    /**
     * Obtiene estadísticas del dashboard de Jefe de Área
     * @param array $areasIds IDs del área principal + subdirecciones
     */
    public function obtenerEstadisticasJefeArea(array $areasIds): array
    {
        return [
            'total_expedientes' => Expediente::whereIn('id_area', $areasIds)->count(),
            'pendientes' => Expediente::whereIn('id_area', $areasIds)
                ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso']))
                ->count(),
            'vencidos' => $this->contarExpedientesVencidosPorArea($areasIds),
            'resueltos_mes' => Expediente::whereIn('id_area', $areasIds)
                ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'resuelto'))
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'promedio_atencion' => $this->calcularPromedioAtencionArea($areasIds)
        ];
    }

    /**
     * Obtiene estadísticas del dashboard de Funcionario
     */
    public function obtenerEstadisticasFuncionario(int $funcionarioId): array
    {
        return [
            'asignados' => Expediente::where('id_funcionario_asignado', $funcionarioId)
                ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso']))
                ->count(),
            'completados_mes' => Expediente::where('id_funcionario_asignado', $funcionarioId)
                ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'resuelto'))
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'vencidos' => $this->contarExpedientesVencidosPorFuncionario($funcionarioId),
            'promedio_atencion' => $this->calcularPromedioAtencionFuncionario($funcionarioId)
        ];
    }

    /**
     * Cuenta expedientes vencidos en general
     */
    protected function contarExpedientesVencidos(): int
    {
        return Expediente::whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso']))
            ->whereHas('derivaciones', function($query) {
                $query->where('fecha_limite', '<', now())
                      ->where('estado', 'pendiente');
            })
            ->count();
    }

    /**
     * Cuenta expedientes vencidos por área(s)
     */
    protected function contarExpedientesVencidosPorArea(array $areasIds): int
    {
        return Expediente::whereIn('id_area', $areasIds)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso']))
            ->whereHas('derivaciones', function($query) {
                $query->where('fecha_limite', '<', now())
                      ->where('estado', 'pendiente');
            })
            ->count();
    }

    /**
     * Cuenta expedientes vencidos por funcionario
     */
    protected function contarExpedientesVencidosPorFuncionario(int $funcionarioId): int
    {
        return Expediente::where('id_funcionario_asignado', $funcionarioId)
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso']))
            ->whereHas('derivaciones', function($query) {
                $query->where('fecha_limite', '<', now())
                      ->where('estado', 'pendiente');
            })
            ->count();
    }

    /**
     * Calcula el promedio de días de atención en área(s)
     */
    protected function calcularPromedioAtencionArea(array $areasIds): float
    {
        $resueltos = Expediente::whereIn('id_area', $areasIds)
            ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'resuelto'))
            ->whereNotNull('fecha_resolucion')
            ->select(
                DB::raw('AVG(DATEDIFF(fecha_resolucion, fecha_registro)) as promedio')
            )
            ->value('promedio');

        return round($resueltos ?? 0, 1);
    }

    /**
     * Calcula el promedio de días de atención de un funcionario
     */
    protected function calcularPromedioAtencionFuncionario(int $funcionarioId): float
    {
        $resueltos = Expediente::where('id_funcionario_asignado', $funcionarioId)
            ->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'resuelto'))
            ->whereNotNull('fecha_resolucion')
            ->select(
                DB::raw('AVG(DATEDIFF(fecha_resolucion, fecha_registro)) as promedio')
            )
            ->value('promedio');

        return round($resueltos ?? 0, 1);
    }

    /**
     * Obtiene datos para gráfico de expedientes por estado
     */
    public function obtenerGraficoEstados(?int $areaId = null): array
    {
        $query = Expediente::join('estados_expediente', 'expedientes.id_estado', '=', 'estados_expediente.id_estado')
            ->select('estados_expediente.nombre as estado_nombre', DB::raw('count(*) as total'))
            ->groupBy('estados_expediente.nombre');

        if ($areaId) {
            $query->where('expedientes.id_area', $areaId);
        }

        $datos = $query->get();

        return [
            'labels' => $datos->pluck('estado_nombre')->toArray(),
            'values' => $datos->pluck('total')->toArray()
        ];
    }

    /**
     * Obtiene datos para gráfico de expedientes por tipo de trámite
     */
    public function obtenerGraficoTipoTramites(?int $areaId = null, int $limit = 10): array
    {
        $query = TipoTramite::withCount('expedientes')
            ->orderBy('expedientes_count', 'desc')
            ->limit($limit);

        if ($areaId) {
            $query->whereHas('expedientes', function($q) use ($areaId) {
                $q->where('id_area', $areaId);
            });
        }

        $datos = $query->get();

        return [
            'labels' => $datos->pluck('nombre')->toArray(),
            'values' => $datos->pluck('expedientes_count')->toArray()
        ];
    }

    /**
     * Obtiene serie temporal de expedientes registrados
     * Optimizado: Una sola consulta en lugar de 60 consultas en loop
     */
    public function obtenerSerieTemporal(int $dias = 30, ?int $areaId = null): array
    {
        $cacheKey = "serie_temporal_{$dias}_" . ($areaId ?? 'all');

        return Cache::remember($cacheKey, 300, function () use ($dias, $areaId) {
            $fechaInicio = now()->subDays($dias - 1)->startOfDay();

            $idDerivado = \App\Models\EstadoExpediente::where('slug', 'derivado')->value('id_estado');
            $query = Expediente::selectRaw("
                DATE(created_at) as fecha,
                COUNT(*) as registrados,
                SUM(CASE WHEN id_estado = ? THEN 1 ELSE 0 END) as derivados
            ", [$idDerivado])->where('created_at', '>=', $fechaInicio);

            if ($areaId) {
                $query->where('id_area', $areaId);
            }

            $datosGrafico = $query->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('fecha')
                ->get()
                ->keyBy('fecha');

            $labels = [];
            $registrados = [];
            $derivados = [];

            for ($i = $dias - 1; $i >= 0; $i--) {
                $fecha = now()->subDays($i);
                $fechaStr = $fecha->format('Y-m-d');
                $labels[] = $fecha->format('d/m');
                $registrados[] = (int) ($datosGrafico->get($fechaStr)->registrados ?? 0);
                $derivados[] = (int) ($datosGrafico->get($fechaStr)->derivados ?? 0);
            }

            return [
                'labels' => $labels,
                'registrados' => $registrados,
                'derivados' => $derivados
            ];
        });
    }

    /**
     * Obtiene top funcionarios por expedientes resueltos
     */
    public function obtenerTopFuncionarios(int $areaId, int $limit = 5): array
    {
        return DB::table('users')
            ->select(
                'users.name',
                DB::raw('COUNT(expedientes.id_expediente) as total_resueltos')
            )
            ->join('expedientes', 'users.id', '=', 'expedientes.id_funcionario_asignado')
            ->where('users.id_area', $areaId)
            ->join('estados_expediente', 'expedientes.id_estado', '=', 'estados_expediente.id_estado')
            ->where('estados_expediente.slug', 'resuelto')
            ->whereMonth('expedientes.updated_at', now()->month)
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_resueltos')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Obtiene estadísticas de cumplimiento de plazos por área
     */
    public function obtenerCumplimientoPlazos(int $areaId): array
    {
        $totalDerivaciones = Derivacion::where('id_area_destino', $areaId)
            ->where('estado', 'atendido')
            ->count();

        $dentroDelPlazo = Derivacion::where('id_area_destino', $areaId)
            ->where('estado', 'atendido')
            ->whereRaw('fecha_recepcion <= fecha_limite')
            ->count();

        $porcentajeCumplimiento = $totalDerivaciones > 0
            ? round(($dentroDelPlazo / $totalDerivaciones) * 100, 1)
            : 0;

        return [
            'total_derivaciones' => $totalDerivaciones,
            'dentro_del_plazo' => $dentroDelPlazo,
            'fuera_del_plazo' => $totalDerivaciones - $dentroDelPlazo,
            'porcentaje_cumplimiento' => $porcentajeCumplimiento
        ];
    }
}
