<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega índice compuesto para consultas frecuentes de historial.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historial_expedientes', function (Blueprint $table) {
            // Para consultas: historial de expediente ordenado por fecha
            $table->index(['id_expediente', 'fecha'], 'idx_historial_exp_fecha');
        });
    }

    public function down(): void
    {
        Schema::table('historial_expedientes', function (Blueprint $table) {
            $table->dropIndex('idx_historial_exp_fecha');
        });
    }
};
