<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expedientes', function (Blueprint $table) {
            $table->index(['estado', 'fecha_registro']);
            $table->index(['ciudadano_id', 'estado']);
            $table->index(['funcionario_asignado_id', 'estado']);
            $table->index(['area_id', 'estado']);
        });

        Schema::table('derivacions', function (Blueprint $table) {
            $table->index(['destino_area_id', 'estado']);
            $table->index(['funcionario_asignado_id', 'estado']);
            $table->index(['fecha_derivacion', 'estado']);
        });

        Schema::table('historial_expedientes', function (Blueprint $table) {
            $table->index(['expediente_id', 'fecha']);
        });

        Schema::table('auditoria', function (Blueprint $table) {
            $table->index(['usuario_id', 'created_at']);
            $table->index(['tabla', 'accion']);
        });
    }

    public function down(): void
    {
        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropIndex(['estado', 'fecha_registro']);
            $table->dropIndex(['ciudadano_id', 'estado']);
            $table->dropIndex(['funcionario_asignado_id', 'estado']);
            $table->dropIndex(['area_id', 'estado']);
        });

        Schema::table('derivacions', function (Blueprint $table) {
            $table->dropIndex(['destino_area_id', 'estado']);
            $table->dropIndex(['funcionario_asignado_id', 'estado']);
            $table->dropIndex(['fecha_derivacion', 'estado']);
        });

        Schema::table('historial_expedientes', function (Blueprint $table) {
            $table->dropIndex(['expediente_id', 'fecha']);
        });

        Schema::table('auditoria', function (Blueprint $table) {
            $table->dropIndex(['usuario_id', 'created_at']);
            $table->dropIndex(['tabla', 'accion']);
        });
    }
};