<?php

namespace App\Services;

use App\Models\Derivacion;
use App\Models\Expediente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DerivacionService
{
    /**
     * Crea una nueva derivación de expediente
     */
    public function derivarExpediente(
        Expediente $expediente,
        int $areaDestinoId,
        ?int $funcionarioAsignadoId,
        int $plazoDias,
        string $prioridad,
        ?string $observaciones = null
    ): Derivacion {
        return DB::transaction(function () use (
            $expediente,
            $areaDestinoId,
            $funcionarioAsignadoId,
            $plazoDias,
            $prioridad,
            $observaciones
        ) {
            $fechaLimite = now()->addDays($plazoDias);

            // Crear la derivación
            $derivacion = Derivacion::create([
                'id_expediente' => $expediente->id_expediente,
                'id_area_origen' => $expediente->id_area ?? auth()->user()->id_area,
                'id_area_destino' => $areaDestinoId,
                'id_funcionario_origen' => auth()->id(),
                'id_funcionario_asignado' => $funcionarioAsignadoId,
                'fecha_derivacion' => now(),
                'plazo_dias' => $plazoDias,
                'fecha_limite' => $fechaLimite,
                'observaciones' => $observaciones,
                'estado' => 'pendiente'
            ]);

            // Actualizar el expediente
            $expediente->update([
                'estado' => 'derivado',
                'id_area' => $areaDestinoId,
                'id_funcionario_asignado' => $funcionarioAsignadoId,
                'prioridad' => $prioridad
            ]);

            // Registrar en historial
            $mensaje = $this->generarMensajeHistorial($derivacion);
            $expediente->agregarHistorial($mensaje, auth()->id());

            return $derivacion;
        });
    }

    /**
     * Extiende el plazo de una derivación
     */
    public function extenderPlazo(
        Expediente $expediente,
        int $diasAdicionales,
        string $motivo
    ): void {
        DB::transaction(function () use ($expediente, $diasAdicionales, $motivo) {
            $derivacion = $expediente->derivaciones()
                ->where('estado', 'pendiente')
                ->latest()
                ->first();

            if (!$derivacion) {
                throw new \Exception('No se encontró una derivación activa');
            }

            $nuevoPlazo = $derivacion->plazo_dias + $diasAdicionales;
            $nuevaFechaLimite = Carbon::parse($derivacion->fecha_derivacion)
                ->addDays($nuevoPlazo);

            $derivacion->update([
                'plazo_dias' => $nuevoPlazo,
                'fecha_limite' => $nuevaFechaLimite
            ]);

            $expediente->agregarHistorial(
                "Plazo extendido {$diasAdicionales} días. Motivo: {$motivo}",
                auth()->id()
            );
        });
    }

    /**
     * Obtiene derivaciones vencidas de un área
     */
    public function obtenerDerivacionesVencidas(int $areaId)
    {
        return Derivacion::where('id_area_destino', $areaId)
            ->where('estado', 'pendiente')
            ->where('fecha_limite', '<', now())
            ->with(['expediente', 'funcionarioAsignado'])
            ->get();
    }

    /**
     * Obtiene derivaciones por vencer (próximos 3 días)
     */
    public function obtenerDerivacionesPorVencer(int $areaId)
    {
        return Derivacion::where('id_area_destino', $areaId)
            ->where('estado', 'pendiente')
            ->whereBetween('fecha_limite', [now(), now()->addDays(3)])
            ->with(['expediente', 'funcionarioAsignado'])
            ->get();
    }

    /**
     * Marca una derivación como atendida
     */
    public function marcarComoAtendida(Derivacion $derivacion): void
    {
        DB::transaction(function () use ($derivacion) {
            $derivacion->update([
                'estado' => 'atendido',
                'fecha_recepcion' => now()
            ]);

            $derivacion->expediente->agregarHistorial(
                'Derivación atendida',
                auth()->id()
            );
        });
    }

    /**
     * Genera mensaje descriptivo para el historial
     */
    protected function generarMensajeHistorial(Derivacion $derivacion): string
    {
        $areaDestino = $derivacion->areaDestino->nombre ?? 'Sin área';
        $mensaje = "Expediente derivado a {$areaDestino}";

        if ($derivacion->funcionarioAsignado) {
            $funcionario = $derivacion->funcionarioAsignado->name;
            $mensaje .= " - Asignado a: {$funcionario}";
        }

        if ($derivacion->plazo_dias) {
            $mensaje .= " - Plazo: {$derivacion->plazo_dias} días";
        }

        return $mensaje;
    }

    /**
     * Reasigna un expediente a otro funcionario
     */
    public function reasignarExpediente(
        Expediente $expediente,
        int $nuevoFuncionarioId,
        string $motivo
    ): void {
        DB::transaction(function () use ($expediente, $nuevoFuncionarioId, $motivo) {
            $funcionarioAnterior = $expediente->funcionarioAsignado?->name ?? 'No asignado';
            $nuevoFuncionario = User::findOrFail($nuevoFuncionarioId);

            $expediente->update([
                'id_funcionario_asignado' => $nuevoFuncionarioId
            ]);

            // Actualizar la derivación activa
            $derivacionActiva = $expediente->derivaciones()
                ->where('estado', 'pendiente')
                ->latest()
                ->first();

            if ($derivacionActiva) {
                $derivacionActiva->update([
                    'id_funcionario_asignado' => $nuevoFuncionarioId
                ]);
            }

            $expediente->agregarHistorial(
                "Reasignado de {$funcionarioAnterior} a {$nuevoFuncionario->name}. Motivo: {$motivo}",
                auth()->id()
            );
        });
    }
}
