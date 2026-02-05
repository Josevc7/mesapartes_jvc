<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Optimiza índices de expedientes:
 * - Elimina índices duplicados/redundantes
 * - Corrige índices mal nombrados (nombre no coincide con columnas)
 * - Crea índices compuestos correctos para consultas frecuentes
 * - Corrige FK id_funcionario_asignado a SET NULL
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Eliminar índices duplicados/mal nombrados
        $this->dropIndexIfExists('expedientes', 'idx_expedientes_codigo'); // duplica UNIQUE
        $this->dropIndexIfExists('expedientes', 'idx_expedientes_estado_fecha'); // mal nombrado
        $this->dropIndexIfExists('expedientes', 'idx_expedientes_area_estado'); // mal nombrado
        $this->dropIndexIfExists('expedientes', 'idx_expedientes_ciudadano_estado'); // mal nombrado

        // 2. Crear índices compuestos correctos
        Schema::table('expedientes', function (Blueprint $table) {
            // Para consultas: expedientes por estado y fecha
            $table->index(['id_estado', 'created_at'], 'idx_exp_estado_fecha');

            // Para consultas: expedientes por área y estado
            $table->index(['id_area', 'id_estado'], 'idx_exp_area_estado');

            // Para consultas: expedientes de ciudadano por estado
            $table->index(['id_ciudadano', 'id_estado'], 'idx_exp_ciudadano_estado');

            // Para consultas: expedientes por prioridad (faltaba)
            $table->index('prioridad', 'idx_exp_prioridad');

            // Para búsquedas por fecha de registro
            $table->index('fecha_registro', 'idx_exp_fecha_registro');
        });

        // 3. Corregir FK id_funcionario_asignado: RESTRICT → SET NULL
        $fkName = $this->getForeignKeyName('expedientes', 'id_funcionario_asignado');
        if ($fkName) {
            Schema::table('expedientes', function (Blueprint $table) use ($fkName) {
                $table->dropForeign($fkName);
            });
            Schema::table('expedientes', function (Blueprint $table) {
                $table->foreign('id_funcionario_asignado')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        // Eliminar índices nuevos
        $this->dropIndexIfExists('expedientes', 'idx_exp_estado_fecha');
        $this->dropIndexIfExists('expedientes', 'idx_exp_area_estado');
        $this->dropIndexIfExists('expedientes', 'idx_exp_ciudadano_estado');
        $this->dropIndexIfExists('expedientes', 'idx_exp_prioridad');
        $this->dropIndexIfExists('expedientes', 'idx_exp_fecha_registro');

        // Restaurar índices anteriores (mal nombrados pero originales)
        Schema::table('expedientes', function (Blueprint $table) {
            $table->index('codigo_expediente', 'idx_expedientes_codigo');
            $table->index('created_at', 'idx_expedientes_estado_fecha');
            $table->index('id_area', 'idx_expedientes_area_estado');
            $table->index('id_ciudadano', 'idx_expedientes_ciudadano_estado');
        });

        // Restaurar FK con RESTRICT
        $fkName = $this->getForeignKeyName('expedientes', 'id_funcionario_asignado');
        if ($fkName) {
            Schema::table('expedientes', function (Blueprint $table) use ($fkName) {
                $table->dropForeign($fkName);
            });
            Schema::table('expedientes', function (Blueprint $table) {
                $table->foreign('id_funcionario_asignado')
                    ->references('id')
                    ->on('users')
                    ->onDelete('restrict');
            });
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        if (count($indexes) > 0) {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }

    private function getForeignKeyName(string $table, string $column): ?string
    {
        $result = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table, $column]);

        return $result?->CONSTRAINT_NAME;
    }
};
