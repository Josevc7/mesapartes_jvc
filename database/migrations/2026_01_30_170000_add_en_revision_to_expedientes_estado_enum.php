<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE expedientes MODIFY COLUMN estado ENUM('pendiente', 'registrado', 'recepcionado', 'clasificado', 'derivado', 'asignado', 'en_proceso', 'en_revision', 'observado', 'resuelto', 'aprobado', 'rechazado', 'archivado') DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE expedientes MODIFY COLUMN estado ENUM('pendiente', 'registrado', 'recepcionado', 'clasificado', 'derivado', 'en_proceso', 'observado', 'resuelto', 'aprobado', 'rechazado', 'archivado') DEFAULT 'pendiente'");
    }
};
