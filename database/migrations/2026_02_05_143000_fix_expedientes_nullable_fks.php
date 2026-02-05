<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Ajusta ON DELETE para FKs nullable en expedientes.
 *
 * Lógica: Si la columna es nullable, usar SET NULL
 * Excepto id_estado que es crítico para el workflow.
 */
return new class extends Migration
{
    public function up(): void
    {
        // id_area: RESTRICT → SET NULL (si se borra área, expediente queda sin área)
        $this->updateForeignKey('expedientes', 'id_area', 'areas', 'id_area', 'set null');

        // id_ciudadano: RESTRICT → SET NULL (si se borra usuario, expediente persiste)
        $this->updateForeignKey('expedientes', 'id_ciudadano', 'users', 'id', 'set null');

        // id_persona: RESTRICT → SET NULL (si se borra persona, expediente persiste)
        $this->updateForeignKey('expedientes', 'id_persona', 'personas', 'id_persona', 'set null');
    }

    public function down(): void
    {
        // Restaurar RESTRICT
        $this->updateForeignKey('expedientes', 'id_area', 'areas', 'id_area', 'restrict');
        $this->updateForeignKey('expedientes', 'id_ciudadano', 'users', 'id', 'restrict');
        $this->updateForeignKey('expedientes', 'id_persona', 'personas', 'id_persona', 'restrict');
    }

    private function updateForeignKey(
        string $table,
        string $column,
        string $refTable,
        string $refColumn,
        string $onDelete
    ): void {
        $fkName = $this->getForeignKeyName($table, $column);

        if ($fkName) {
            Schema::table($table, function (Blueprint $t) use ($fkName) {
                $t->dropForeign($fkName);
            });
        }

        Schema::table($table, function (Blueprint $t) use ($column, $refTable, $refColumn, $onDelete) {
            $t->foreign($column)
                ->references($refColumn)
                ->on($refTable)
                ->onDelete($onDelete);
        });
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
