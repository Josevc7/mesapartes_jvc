<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Mapeo de estados inconsistentes a normalizados (minúsculas)
     */
    private array $mapeoEstados = [
        'Derivado' => 'derivado',
        'En Proceso' => 'en_proceso',
        'Observado' => 'observado',
        'Resuelto' => 'resuelto',
        'Aprobado' => 'aprobado',
        'Rechazado' => 'rechazado',
        'Registrado' => 'registrado',
        'Clasificado' => 'clasificado',
        'Archivado' => 'archivado',
        'Recepcionado' => 'recepcionado',
        'Notificado' => 'notificado',
        'Asignado' => 'asignado',
        'En Revision' => 'en_revision',
        'En_Proceso' => 'en_proceso',
        'En_Revision' => 'en_revision',
        'Pendiente' => 'pendiente',
    ];

    private array $mapeoPrioridades = [
        'Urgente' => 'urgente',
        'Alta' => 'alta',
        'Normal' => 'normal',
        'Baja' => 'baja',
    ];

    public function up(): void
    {
        $cambios = [
            'expedientes_estados' => 0,
            'expedientes_prioridades' => 0,
            'historial_estados' => 0,
        ];

        // Normalizar estados en expedientes
        foreach ($this->mapeoEstados as $incorrecto => $correcto) {
            $afectados = DB::table('expedientes')
                ->where('estado', $incorrecto)
                ->update(['estado' => $correcto]);
            $cambios['expedientes_estados'] += $afectados;
        }

        // Normalizar prioridades en expedientes
        foreach ($this->mapeoPrioridades as $incorrecto => $correcto) {
            $afectados = DB::table('expedientes')
                ->where('prioridad', $incorrecto)
                ->update(['prioridad' => $correcto]);
            $cambios['expedientes_prioridades'] += $afectados;
        }

        // Normalizar estados en historial_expedientes (si existe la columna)
        if (DB::getSchemaBuilder()->hasColumn('historial_expedientes', 'estado')) {
            foreach ($this->mapeoEstados as $incorrecto => $correcto) {
                $afectados = DB::table('historial_expedientes')
                    ->where('estado', $incorrecto)
                    ->update(['estado' => $correcto]);
                $cambios['historial_estados'] += $afectados;
            }
        }

        // Log de cambios
        Log::info("Migración de normalización de estados completada", [
            'expedientes_estados_normalizados' => $cambios['expedientes_estados'],
            'expedientes_prioridades_normalizadas' => $cambios['expedientes_prioridades'],
            'historial_estados_normalizados' => $cambios['historial_estados'],
        ]);
    }

    public function down(): void
    {
        // No revertir - los valores en minúsculas son correctos
        Log::info("Rollback de normalización de estados - no se realizan cambios (valores normalizados son correctos)");
    }
};
