<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Corrige FK peligrosa en derivaciones:
 * - id_expediente: CASCADE → RESTRICT
 *
 * Regla: No borrar expedientes si tienen derivaciones (son registros históricos)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Obtener nombre real de la FK
        $fkName = $this->getForeignKeyName('derivaciones', 'id_expediente');

        if ($fkName) {
            Schema::table('derivaciones', function (Blueprint $table) use ($fkName) {
                $table->dropForeign($fkName);
            });

            Schema::table('derivaciones', function (Blueprint $table) {
                $table->foreign('id_expediente')
                    ->references('id_expediente')
                    ->on('expedientes')
                    ->onDelete('restrict');
            });
        }
    }

    public function down(): void
    {
        $fkName = $this->getForeignKeyName('derivaciones', 'id_expediente');

        if ($fkName) {
            Schema::table('derivaciones', function (Blueprint $table) use ($fkName) {
                $table->dropForeign($fkName);
            });

            Schema::table('derivaciones', function (Blueprint $table) {
                $table->foreign('id_expediente')
                    ->references('id_expediente')
                    ->on('expedientes')
                    ->onDelete('cascade');
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
