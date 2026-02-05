<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Renombra columna año → anio en numeracion.
 *
 * La ñ causa problemas en:
 * - Laravel Eloquent (escapado de nombres)
 * - Exports CSV/Excel
 * - APIs REST
 * - Algunas consolas/terminales
 */
return new class extends Migration
{
    public function up(): void
    {
        // Eliminar índice UNIQUE que usa la columna
        Schema::table('numeracion', function (Blueprint $table) {
            $table->dropUnique('numeracion_año_area_unique');
        });

        // Renombrar columna
        Schema::table('numeracion', function (Blueprint $table) {
            $table->renameColumn('año', 'anio');
        });

        // Recrear índice UNIQUE con nuevo nombre de columna
        Schema::table('numeracion', function (Blueprint $table) {
            $table->unique(['anio', 'id_area'], 'numeracion_anio_area_unique');
        });
    }

    public function down(): void
    {
        Schema::table('numeracion', function (Blueprint $table) {
            $table->dropUnique('numeracion_anio_area_unique');
        });

        Schema::table('numeracion', function (Blueprint $table) {
            $table->renameColumn('anio', 'año');
        });

        Schema::table('numeracion', function (Blueprint $table) {
            $table->unique(['año', 'id_area'], 'numeracion_año_area_unique');
        });
    }
};
