<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige la columna fecha en historial_expedientes.
 *
 * PROBLEMA: ON UPDATE CURRENT_TIMESTAMP hace que la fecha cambie
 *           cada vez que se modifica el registro.
 *
 * SOLUCIÓN: Solo DEFAULT CURRENT_TIMESTAMP (sin ON UPDATE).
 *           El historial debe ser inmutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Quitar ON UPDATE, mantener solo DEFAULT
        DB::statement("
            ALTER TABLE historial_expedientes
            MODIFY COLUMN fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        // Restaurar ON UPDATE (no recomendado)
        DB::statement("
            ALTER TABLE historial_expedientes
            MODIFY COLUMN fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }
};
