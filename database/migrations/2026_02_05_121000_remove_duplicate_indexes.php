<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Eliminar índices duplicados que inflan la BD y ralentizan escrituras.
 *
 * NOTIFICACIONES: Mantener solo un índice compuesto (id_usuario, leida, created_at)
 * PERSONAS: Mantener solo el UNIQUE en numero_documento
 */
return new class extends Migration
{
    public function up(): void
    {
        // === NOTIFICACIONES ===
        // PRIMERO crear el nuevo índice (la FK necesita un índice que empiece con id_usuario)
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->index(['id_usuario', 'leida', 'created_at'], 'idx_notif_usuario_leida_fecha');
        });

        // LUEGO eliminar los índices duplicados (ahora la FK usará el nuevo índice)
        $this->dropIndexIfExists('notificaciones', 'notificaciones_id_usuario_leida_index');
        $this->dropIndexIfExists('notificaciones', 'idx_notificaciones_usuario');
        $this->dropIndexIfExists('notificaciones', 'idx_notificaciones_usuario_leida');

        // === PERSONAS ===
        // Eliminar índices redundantes (mantener solo el UNIQUE)
        $this->dropIndexIfExists('personas', 'personas_tipo_documento_numero_documento_index');
        $this->dropIndexIfExists('personas', 'idx_personas_documento');
        $this->dropIndexIfExists('personas', 'idx_personas_tipo_numero');
    }

    public function down(): void
    {
        // Restaurar índices anteriores (no recomendado)

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropIndex('idx_notif_usuario_leida_fecha');
            $table->index(['id_usuario', 'leida'], 'notificaciones_id_usuario_leida_index');
            $table->index(['id_usuario'], 'idx_notificaciones_usuario');
            $table->index(['id_usuario', 'leida'], 'idx_notificaciones_usuario_leida');
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->index(['tipo_documento', 'numero_documento'], 'personas_tipo_documento_numero_documento_index');
            $table->index(['numero_documento'], 'idx_personas_documento');
            $table->index(['tipo_documento', 'numero_documento'], 'idx_personas_tipo_numero');
        });
    }

    /**
     * Helper para eliminar índice solo si existe
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);

        if (count($indexes) > 0) {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }
};
