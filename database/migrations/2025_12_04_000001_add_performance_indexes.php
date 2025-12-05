<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expedientes', function (Blueprint $table) {
            $table->index('dni_remitente', 'idx_expedientes_dni_remitente');
            $table->index(['fecha_registro', 'prioridad'], 'idx_expedientes_fecha_registro_prioridad');
        });

        Schema::table('derivacions', function (Blueprint $table) {
            $table->index('fecha_limite', 'idx_derivacions_fecha_limite');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['dni', 'activo'], 'idx_users_dni_activo');
        });
    }

    public function down(): void
    {
        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropIndex('idx_expedientes_dni_remitente');
            $table->dropIndex('idx_expedientes_fecha_registro_prioridad');
        });

        Schema::table('derivacions', function (Blueprint $table) {
            $table->dropIndex('idx_derivacions_fecha_limite');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_dni_activo');
        });
    }
};