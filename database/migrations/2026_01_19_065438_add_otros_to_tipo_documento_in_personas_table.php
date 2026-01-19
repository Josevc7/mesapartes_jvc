<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar el enum para agregar 'OTROS'
        DB::statement("ALTER TABLE personas MODIFY COLUMN tipo_documento ENUM('DNI', 'CE', 'RUC', 'PASAPORTE', 'OTROS') DEFAULT 'DNI'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el enum removiendo 'OTROS'
        DB::statement("ALTER TABLE personas MODIFY COLUMN tipo_documento ENUM('DNI', 'CE', 'RUC', 'PASAPORTE') DEFAULT 'DNI'");
    }
};
