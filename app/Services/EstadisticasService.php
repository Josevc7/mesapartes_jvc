<?php

namespace App\Services;

use App\Models\Expediente;
use App\Models\Derivacion;
use App\Models\Area;
use App\Models\TipoTramite;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EstadisticasService
{
    /**
     * Obtiene estadísticas del dashboard de Mesa de Partes
     */
    public function obtenerEstadisticasMesaPartes(): array
    {
        return [
            'registrados_hoy' => Expediente::whereDate('created_at', today())->count(),
            'pendientes_clasificar' => Expediente::whereIn('estado', ['recepcionado', 'registrado'])->count(),
            'pendientes_derivar' => Expediente::where('estado', 'clasificado')->count(),
            'vencidos' => $this->contarExpedientesVencidos()
        ];
    }

    /**
     * Obtiene estadísticas del dashboard de Jefe de Área
     */
    public function obtenerEstadisticasJefeArea(int $areaId): array
    {
        return [
            'total_expedientes' => Expediente::where('id_area', $areaId)->count(),
            'pendientes' => Expediente::where('id_area', $areaId)
                ->whereIn('estado', ['derivado', 'en_proceso'])
                ->count(),
            'vencidos' => $this->contarExpedientesVencidosPorArea($areaId),
            'resueltos_mes' => Expediente::where('id_area', $areaId)
                ->where('estado', 'resuelto')
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'promedio_atencion' => $this->calcularPromedioAtencionArea($areaId)
        ];
    }

    /**
     * Obtiene estadísticas del dashboard de Funcionario
     */
    public function obtenerEstadisticasFuncionario(int $funcionarioId): array
    {
        return [
            'asignados' => Expediente::where('id_funcionario_asignado', $funcionarioId)
                ->whereIn('estado', ['derivado', 'en_proceso'])
                ->count(),
            'completados_mes' => Expediente::where('id_funcionario_asignado', $funcionarioId)
                ->where('estado', 'resuelto')
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
        return Expediente::whereIn('estado', ['derivado', 'en_proceso'])
            ->whereHas('derivaciones', function($query) {
                $query->where('fecha_limite', '<', now())
                      ->where('estado', 'pendiente');
            })
            ->count();
    }

    /**
     * Cuenta expedientes vencidos por área
     */
    protected function contarExpedientesVencidosPorArea(int $areaId): int
    {
        return Expediente::where('id_area', $areaId)
            ->whereIn('estado', ['derivado', 'en_proceso'])
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
            ->whereIn('estado', ['derivado', 'en_proceso'])
            ->whereHas('derivaciones', function($query) {
                $query->where('fecha_limite', '<', now())
                      ->where('estado', 'pendiente');
            })
            ->count();
    }

    /**
     * Calcula el promedio de días de atención en un área
     */
    protected function calcularPromedioAtencionArea(int $areaId): float
    {
        $resueltos = Expediente::where('id_area', $areaId)
            ->where('estado', 'resuelto')
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
            ->where('estado', 'resuelto')
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
        $query = Expediente::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado');

        if ($areaId) {
            $query->where('id_area', $areaId);
        }

        $datos = $query->get();

        return [
            'labels' => $datos->pluck('estado')->toArray(),
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
     */
    public function obtenerSerieTemporal(int $dias = 30, ?int $areaId = null): array
    {
        $labels = [];
        $registrados = [];
        $derivados = [];

        for ($i = $dias - 1; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $labels[] = $fecha->format('d/m');

            $queryRegistrados = Expediente::whereDate('created_at', $fecha);
            $queryDerivados = Expediente::whereDate('updated_at', $fecha)
                ->where('estado', 'derivado');

            if ($areaId) {
                $queryRegistrados->where('id_area', $areaId);
                $queryDerivados->where('id_area', $areaId);
            }

            $registrados[] = $queryRegistrados->count();
            $derivados[] = $queryDerivados->count();
        }

        return [
            'labels' => $labels,
            'registrados' => $registrados,
            'derivados' => $derivados
        ];
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
            ->where('expedientes.estado', 'resuelto')
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
