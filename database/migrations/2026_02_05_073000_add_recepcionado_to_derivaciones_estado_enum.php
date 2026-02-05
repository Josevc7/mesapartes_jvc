<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega el valor 'recepcionado' al enum del campo estado en derivaciones
     */
    public function up(): void
    {
        // Modificar el enum para incluir 'recepcionado' y 'atendido'
        DB::statement("ALTER TABLE derivaciones MODIFY COLUMN estado ENUM('pendiente', 'recibido', 'recepcionado', 'atendido', 'vencido') DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Primero actualizar registros que tengan los nuevos valores
        DB::statement("UPDATE derivaciones SET estado = 'recibido' WHERE estado IN ('recepcionado', 'atendido')");

        // Revertir al enum original
        DB::statement("ALTER TABLE derivaciones MODIFY COLUMN estado ENUM('pendiente', 'recibido', 'vencido') DEFAULT 'pendiente'");
    }
};
