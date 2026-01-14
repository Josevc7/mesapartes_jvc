<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Verifica si un índice existe en una tabla
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Run the migrations.
     * Añade índices para mejorar el rendimiento de consultas frecuentes
     */
    public function up(): void
    {
        // Índices para tabla expedientes
        Schema::table('expedientes', function (Blueprint $table) {
            if (!$this->indexExists('expedientes', 'idx_expedientes_estado')) {
                $table->index('estado', 'idx_expedientes_estado');
            }
            if (!$this->indexExists('expedientes', 'idx_expedientes_created_at')) {
                $table->index('created_at', 'idx_expedientes_created_at');
            }
            if (!$this->indexExists('expedientes', 'idx_expedientes_updated_at')) {
                $table->index('updated_at', 'idx_expedientes_updated_at');
            }
            if (!$this->indexExists('expedientes', 'idx_expedientes_id_area')) {
                $table->index('id_area', 'idx_expedientes_id_area');
            }
            if (!$this->indexExists('expedientes', 'idx_expedientes_funcionario')) {
                $table->index('id_funcionario_asignado', 'idx_expedientes_funcionario');
            }
            if (!$this->indexExists('expedientes', 'idx_expedientes_tipo_tramite')) {
                $table->index('id_tipo_tramite', 'idx_expedientes_tipo_tramite');
            }
            if (!$this->indexExists('expedientes', 'idx_expedientes_estado_fecha')) {
                $table->index(['estado', 'created_at'], 'idx_expedientes_estado_fecha');
            }
            if (!$this->indexExists('expedientes', 'idx_expedientes_area_estado')) {
                $table->index(['id_area', 'estado'], 'idx_expedientes_area_estado');
            }
        });

        // Índices para tabla derivaciones
        Schema::table('derivaciones', function (Blueprint $table) {
            if (!$this->indexExists('derivaciones', 'idx_derivaciones_fecha_limite')) {
                $table->index('fecha_limite', 'idx_derivaciones_fecha_limite');
            }
            if (!$this->indexExists('derivaciones', 'idx_derivaciones_estado')) {
                $table->index('estado', 'idx_derivaciones_estado');
            }
            if (!$this->indexExists('derivaciones', 'idx_derivaciones_expediente')) {
                $table->index('id_expediente', 'idx_derivaciones_expediente');
            }
            if (!$this->indexExists('derivaciones', 'idx_derivaciones_funcionario')) {
                $table->index('id_funcionario_asignado', 'idx_derivaciones_funcionario');
            }
            if (!$this->indexExists('derivaciones', 'idx_derivaciones_vencimiento')) {
                $table->index(['fecha_limite', 'estado'], 'idx_derivaciones_vencimiento');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropIndex('idx_expedientes_estado');
            $table->dropIndex('idx_expedientes_created_at');
            $table->dropIndex('idx_expedientes_updated_at');
            $table->dropIndex('idx_expedientes_id_area');
            $table->dropIndex('idx_expedientes_funcionario');
            $table->dropIndex('idx_expedientes_tipo_tramite');
            $table->dropIndex('idx_expedientes_estado_fecha');
            $table->dropIndex('idx_expedientes_area_estado');
        });

        Schema::table('derivaciones', function (Blueprint $table) {
            $table->dropIndex('idx_derivaciones_fecha_limite');
            $table->dropIndex('idx_derivaciones_estado');
            $table->dropIndex('idx_derivaciones_expediente');
            $table->dropIndex('idx_derivaciones_funcionario');
            $table->dropIndex('idx_derivaciones_vencimiento');
        });
    }
};
