<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('derivaciones', function (Blueprint $table) {
            // Numero de registro interno del area (ej: 2026-DIR-000001)
            $table->string('numero_registro_area', 30)->nullable()->after('id_area_destino');
        });
    }

    public function down(): void
    {
        Schema::table('derivaciones', function (Blueprint $table) {
            $table->dropColumn('numero_registro_area');
        });
    }
};
