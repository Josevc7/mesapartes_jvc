<?php

namespace App\Services;

use App\Models\Expediente;
use App\Models\User;
use App\Services\AuditoriaService;

class ReasignacionService
{
    public function reasignarPorCarga()
    {
        $areas = \App\Models\Area::all();
        
        foreach ($areas as $area) {
            $funcionarios = User::where('area_id', $area->id)
                ->where('rol_id', 4)
                ->where('activo', true)
                ->withCount(['expedientesAsignados as carga' => function($q) {
                    $q->whereIn('estado', ['Derivado', 'En Proceso']);
                }])
                ->get();

            if ($funcionarios->count() < 2) continue;

            // Encontrar funcionario sobrecargado (más de 10 expedientes)
            $sobrecargado = $funcionarios->where('carga', '>', 10)->first();
            
            // Encontrar funcionario con menos carga
            $menosCarga = $funcionarios->sortBy('carga')->first();

            if ($sobrecargado && $menosCarga && $sobrecargado->id !== $menosCarga->id) {
                $this->redistribuirExpedientes($sobrecargado, $menosCarga);
            }
        }
    }

    private function redistribuirExpedientes($origen, $destino)
    {
        $expedientesParaReasignar = Expediente::where('funcionario_asignado_id', $origen->id)
            ->whereIn('estado', ['Derivado', 'En Proceso'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($expedientesParaReasignar as $expediente) {
            $datosAnteriores = $expediente->toArray();
            
            $expediente->update([
                'funcionario_asignado_id' => $destino->id
            ]);

            $expediente->agregarHistorial(
                "Reasignado automáticamente de {$origen->name} a {$destino->name} por balanceo de carga",
                1 // Sistema
            );

            AuditoriaService::expedienteActualizado($expediente, $datosAnteriores);
        }

        \Log::info("Reasignados {$expedientesParaReasignar->count()} expedientes de {$origen->name} a {$destino->name}");
    }

    public function reasignarPorVencimiento()
    {
        $expedientesVencidos = Expediente::whereIn('estado', ['Derivado', 'En Proceso'])
            ->whereHas('derivaciones', function($q) {
                $q->where('fecha_limite', '<', now()->subDays(2));
            })
            ->with(['funcionarioAsignado', 'area'])
            ->get();

        foreach ($expedientesVencidos as $expediente) {
            if ($expediente->area && $expediente->funcionarioAsignado) {
                $jefeArea = User::where('area_id', $expediente->area_id)
                    ->where('rol_id', 3)
                    ->first();

                if ($jefeArea) {
                    $datosAnteriores = $expediente->toArray();
                    
                    $expediente->update([
                        'funcionario_asignado_id' => $jefeArea->id,
                        'prioridad' => 'Urgente'
                    ]);

                    $expediente->agregarHistorial(
                        "Reasignado automáticamente al Jefe de Área por vencimiento",
                        1 // Sistema
                    );

                    AuditoriaService::expedienteActualizado($expediente, $datosAnteriores);
                }
            }
        }
    }
}