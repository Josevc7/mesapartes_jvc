<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega índices para consultas frecuentes en observaciones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observaciones', function (Blueprint $table) {
            // Para consultas: observaciones por expediente y estado
            $table->index(['id_expediente', 'estado'], 'idx_obs_expediente_estado');

            // Para consultas: observaciones pendientes con fecha límite
            $table->index(['estado', 'fecha_limite'], 'idx_obs_estado_fecha');
        });
    }

    public function down(): void
    {
        Schema::table('observaciones', function (Blueprint $table) {
            $table->dropIndex('idx_obs_expediente_estado');
            $table->dropIndex('idx_obs_estado_fecha');
        });
    }
};
