<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    public function up(): void
    {
        // Índices faltantes para expedientes
        Schema::table('expedientes', function (Blueprint $table) {
            // Índice para consultas de ciudadano (muy frecuente)
            if (!$this->indexExists('expedientes', 'idx_expedientes_ciudadano')) {
                $table->index('id_ciudadano', 'idx_expedientes_ciudadano');
            }

            // Índice compuesto para dashboard ciudadano
            if (!$this->indexExists('expedientes', 'idx_expedientes_ciudadano_estado')) {
                $table->index(['id_ciudadano', 'estado'], 'idx_expedientes_ciudadano_estado');
            }

            // Índice para búsqueda por persona
            if (!$this->indexExists('expedientes', 'idx_expedientes_persona')) {
                $table->index('id_persona', 'idx_expedientes_persona');
            }

            // Índice para búsqueda por código (si no existe único)
            if (!$this->indexExists('expedientes', 'idx_expedientes_codigo')) {
                $table->index('codigo_expediente', 'idx_expedientes_codigo');
            }

            // Índice para canal (filtros frecuentes)
            if (!$this->indexExists('expedientes', 'idx_expedientes_canal')) {
                $table->index('canal', 'idx_expedientes_canal');
            }
        });

        // Índices para personas (búsquedas frecuentes)
        Schema::table('personas', function (Blueprint $table) {
            if (!$this->indexExists('personas', 'idx_personas_documento')) {
                $table->index('numero_documento', 'idx_personas_documento');
            }

            if (!$this->indexExists('personas', 'idx_personas_tipo_numero')) {
                $table->index(['tipo_documento', 'numero_documento'], 'idx_personas_tipo_numero');
            }
        });

        // Índices adicionales para derivaciones
        Schema::table('derivaciones', function (Blueprint $table) {
            if (!$this->indexExists('derivaciones', 'idx_derivaciones_fecha_derivacion')) {
                $table->index('fecha_derivacion', 'idx_derivaciones_fecha_derivacion');
            }

            if (!$this->indexExists('derivaciones', 'idx_derivaciones_area_destino')) {
                $table->index('id_area_destino', 'idx_derivaciones_area_destino');
            }
        });

        // Índices para documentos
        Schema::table('documentos', function (Blueprint $table) {
            if (!$this->indexExists('documentos', 'idx_documentos_expediente')) {
                $table->index('id_expediente', 'idx_documentos_expediente');
            }

            if (!$this->indexExists('documentos', 'idx_documentos_tipo')) {
                $table->index('tipo', 'idx_documentos_tipo');
            }
        });

        // Índices para historial
        Schema::table('historial_expedientes', function (Blueprint $table) {
            if (!$this->indexExists('historial_expedientes', 'idx_historial_expediente')) {
                $table->index('id_expediente', 'idx_historial_expediente');
            }
        });

        // Índices para notificaciones
        Schema::table('notificaciones', function (Blueprint $table) {
            if (!$this->indexExists('notificaciones', 'idx_notificaciones_usuario')) {
                $table->index('id_usuario', 'idx_notificaciones_usuario');
            }

            if (!$this->indexExists('notificaciones', 'idx_notificaciones_usuario_leida')) {
                $table->index(['id_usuario', 'leida'], 'idx_notificaciones_usuario_leida');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropIndex('idx_expedientes_ciudadano');
            $table->dropIndex('idx_expedientes_ciudadano_estado');
            $table->dropIndex('idx_expedientes_persona');
            $table->dropIndex('idx_expedientes_codigo');
            $table->dropIndex('idx_expedientes_canal');
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->dropIndex('idx_personas_documento');
            $table->dropIndex('idx_personas_tipo_numero');
        });

        Schema::table('derivaciones', function (Blueprint $table) {
            $table->dropIndex('idx_derivaciones_fecha_derivacion');
            $table->dropIndex('idx_derivaciones_area_destino');
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->dropIndex('idx_documentos_expediente');
            $table->dropIndex('idx_documentos_tipo');
        });

        Schema::table('historial_expedientes', function (Blueprint $table) {
            $table->dropIndex('idx_historial_expediente');
        });

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropIndex('idx_notificaciones_usuario');
            $table->dropIndex('idx_notificaciones_usuario_leida');
        });
    }
};
