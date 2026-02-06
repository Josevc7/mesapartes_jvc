<?php

namespace App\Services;

use App\Models\User;
use App\Models\Area;
use App\Models\Expediente;
use App\Enums\EstadoExpediente;
use App\Enums\RolUsuario;
use Illuminate\Database\Eloquent\Collection;

/**
 * Servicio para obtener estadísticas de funcionarios
 * Resuelve el problema N+1 que existía en supervision() y reportes()
 */
class FuncionarioEstadisticasService
{
    /**
     * Obtiene funcionarios con estadísticas completas (optimizado)
     * Resuelve problema N+1 con subqueries en lugar de queries dentro de ->map()
     *
     * ANTES: 50 funcionarios = 50+ queries
     * AHORA: 50 funcionarios = 2-3 queries
     */
    public function obtenerFuncionariosConEstadisticas(array $areaIds): Collection
    {
        $estadosPendientes = EstadoExpediente::estadosPendientes();
        $estadosFinalizados = EstadoExpediente::estadosFinalizados();

        return User::whereIn('id_area', $areaIds)
            ->where('id_rol', RolUsuario::FUNCIONARIO->value)
            ->where('activo', true)
            ->with('area')
            // Conteos básicos con withCount
            ->withCount([
                'expedientesAsignados as total_asignados',
                'expedientesAsignados as resueltos' => fn($q) =>
                    $q->whereHas('estadoExpediente', fn($eq) => $eq->whereIn('slug', $estadosFinalizados)),
                'expedientesAsignados as pendientes' => fn($q) =>
                    $q->whereHas('estadoExpediente', fn($eq) => $eq->whereIn('slug', $estadosPendientes)),
                'expedientesAsignados as vencidos' => fn($q) =>
                    $q->whereHas('derivaciones', fn($d) =>
                        $d->where('estado', 'pendiente')
                          ->where('fecha_limite', '<', now())
                    ),
                'expedientesAsignados as resueltos_mes' => fn($q) =>
                    $q->whereHas('estadoExpediente', fn($eq) => $eq->whereIn('slug', ['resuelto', 'aprobado']))
                      ->whereMonth('updated_at', now()->month),
            ])
            // Tiempo promedio con subquery (OPTIMIZACIÓN CLAVE)
            // Evita hacer query individual por cada funcionario
            ->addSelect([
                'tiempo_promedio_dias' => Expediente::selectRaw('AVG(DATEDIFF(fecha_resolucion, created_at))')
                    ->whereColumn('id_funcionario_asignado', 'users.id')
                    ->whereIn('id_estado', \App\Models\EstadoExpediente::whereIn('slug', ['resuelto', 'aprobado'])->pluck('id_estado'))
                    ->whereNotNull('fecha_resolucion')
            ])
            ->get()
            ->map(function($funcionario) {
                // Cálculos derivados (no generan queries adicionales)
                $funcionario->carga_trabajo = $funcionario->pendientes;
                $funcionario->efectividad = $funcionario->total_asignados > 0
                    ? round(($funcionario->resueltos / $funcionario->total_asignados) * 100, 1)
                    : 0;
                $funcionario->tiempo_promedio = round($funcionario->tiempo_promedio_dias ?? 0, 1);
                return $funcionario;
            });
    }

    /**
     * Obtiene funcionarios para filtros/selects (ligero)
     * Solo carga la información necesaria para mostrar en dropdowns
     */
    public function obtenerFuncionariosParaSelect(array $areaIds): Collection
    {
        return User::whereIn('id_area', $areaIds)
            ->where('id_rol', RolUsuario::FUNCIONARIO->value)
            ->where('activo', true)
            ->with('area')
            ->withCount([
                'expedientesAsignados as carga_trabajo' => fn($q) =>
                    $q->whereHas('estadoExpediente', fn($eq) => $eq->whereIn('slug', EstadoExpediente::estadosPendientes()))
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtiene expedientes actuales de un funcionario específico
     * Para usar cuando se necesita detallar los expedientes de un funcionario
     */
    public function obtenerExpedientesActuales(int $funcionarioId, int $limite = 10): Collection
    {
        return Expediente::where('id_funcionario_asignado', $funcionarioId)
            ->whereHas('estadoExpediente', fn($eq) => $eq->whereIn('slug', EstadoExpediente::estadosPendientes()))
            ->orWhere(function($q) use ($funcionarioId) {
                $q->where('id_funcionario_asignado', $funcionarioId)
                  ->whereHas('estadoExpediente', fn($eq) => $eq->where('slug', 'resuelto'));
            })
            ->with(['tipoTramite', 'derivaciones' => fn($q) =>
                $q->where('estado', 'pendiente')->latest()
            ])
            ->orderBy('created_at', 'desc')
            ->take($limite)
            ->get();
    }

    /**
     * Obtiene funcionarios ordenados por carga de trabajo
     * Útil para reasignación automática
     */
    public function obtenerFuncionariosPorCarga(array $areaIds, string $orden = 'asc'): Collection
    {
        return User::whereIn('id_area', $areaIds)
            ->where('id_rol', RolUsuario::FUNCIONARIO->value)
            ->where('activo', true)
            ->with('area')
            ->withCount([
                'expedientesAsignados as carga_trabajo' => fn($q) =>
                    $q->whereHas('estadoExpediente', fn($eq) => $eq->whereIn('slug', EstadoExpediente::estadosPendientes()))
            ])
            ->orderBy('carga_trabajo', $orden)
            ->get();
    }

    /**
     * Obtiene el rendimiento de funcionarios para reportes
     * Incluye métricas adicionales como efectividad y cumplimiento
     */
    public function obtenerRendimientoParaReportes(array $areaIds): Collection
    {
        $estadosFinalizados = EstadoExpediente::estadosFinalizados();

        return User::whereIn('id_area', $areaIds)
            ->where('id_rol', RolUsuario::FUNCIONARIO->value)
            ->where('activo', true)
            ->with('area')
            ->withCount([
                'expedientesAsignados as total_asignados',
                'expedientesAsignados as resueltos' => fn($q) =>
                    $q->whereHas('estadoExpediente', fn($eq) => $eq->whereIn('slug', $estadosFinalizados)),
                'expedientesAsignados as pendientes' => fn($q) =>
                    $q->whereHas('estadoExpediente', fn($eq) => $eq->whereIn('slug', EstadoExpediente::estadosPendientes())),
                'expedientesAsignados as vencidos' => fn($q) =>
                    $q->whereHas('derivaciones', fn($d) =>
                        $d->where('estado', 'pendiente')
                          ->where('fecha_limite', '<', now())
                    ),
            ])
            ->addSelect([
                'tiempo_promedio_dias' => Expediente::selectRaw('AVG(DATEDIFF(fecha_resolucion, created_at))')
                    ->whereColumn('id_funcionario_asignado', 'users.id')
                    ->whereIn('id_estado', \App\Models\EstadoExpediente::whereIn('slug', ['resuelto', 'aprobado'])->pluck('id_estado'))
                    ->whereNotNull('fecha_resolucion')
            ])
            ->get()
            ->map(function($func) {
                $func->efectividad = $func->total_asignados > 0
                    ? round(($func->resueltos / $func->total_asignados) * 100, 1)
                    : 0;
                $func->tiempo_promedio = round($func->tiempo_promedio_dias ?? 0, 1);
                return $func;
            });
    }
}
