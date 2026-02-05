<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optimiza la tabla areas:
 * - Agrega índices faltantes (activo, nivel)
 * - Corrige FK id_jefe a SET NULL (si se elimina jefe, área no debe fallar)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Agregar índices faltantes (si no existen)
        $this->addIndexIfNotExists('areas', 'idx_areas_activo', 'activo');
        $this->addIndexIfNotExists('areas', 'idx_areas_nivel', 'nivel');

        // Corregir FK id_jefe: RESTRICT → SET NULL
        // Nombre real de la FK: areas_jefe_id_foreign
        Schema::table('areas', function (Blueprint $table) {
            $table->dropForeign('areas_jefe_id_foreign');
            $table->foreign('id_jefe', 'areas_jefe_id_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    private function addIndexIfNotExists(string $table, string $indexName, string $column): void
    {
        $indexes = \DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        if (count($indexes) === 0) {
            Schema::table($table, function (Blueprint $table) use ($indexName, $column) {
                $table->index($column, $indexName);
            });
        }
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropIndex('idx_areas_activo');
            $table->dropIndex('idx_areas_nivel');
        });

        Schema::table('areas', function (Blueprint $table) {
            $table->dropForeign(['id_jefe']);
            $table->foreign('id_jefe')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
        });
    }
};
