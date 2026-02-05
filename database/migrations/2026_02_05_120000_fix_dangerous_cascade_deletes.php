<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACIÓN CRÍTICA: Eliminar cascadas peligrosas
 *
 * Regla institucional: NO borrar registros, solo usar activo=0
 * Esta migración cambia CASCADE por RESTRICT o SET NULL según corresponda.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. areas(id_area_padre): CASCADE → SET NULL
        // Si se borra un área padre, las subáreas quedan huérfanas pero no se eliminan
        Schema::table('areas', function (Blueprint $table) {
            $table->dropForeign(['id_area_padre']);
            $table->foreign('id_area_padre')
                ->references('id_area')
                ->on('areas')
                ->onDelete('set null');
        });

        // 2. numeracion(id_area): CASCADE → RESTRICT
        // No permitir borrar área si tiene numeración asociada
        Schema::table('numeracion', function (Blueprint $table) {
            $table->dropForeign(['id_area']);
            $table->foreign('id_area')
                ->references('id_area')
                ->on('areas')
                ->onDelete('restrict');
        });

        // 3. transiciones_estado: CASCADE → RESTRICT
        // No permitir borrar estados si tienen transiciones configuradas
        Schema::table('transiciones_estado', function (Blueprint $table) {
            $table->dropForeign(['id_estado_origen']);
            $table->dropForeign(['id_estado_destino']);

            $table->foreign('id_estado_origen')
                ->references('id_estado')
                ->on('estados_expediente')
                ->onDelete('restrict');

            $table->foreign('id_estado_destino')
                ->references('id_estado')
                ->on('estados_expediente')
                ->onDelete('restrict');
        });

        // 4. resoluciones: hacer explícito RESTRICT (son documentos legales)
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->dropForeign(['id_expediente']);
            $table->dropForeign(['id_funcionario_resolutor']);

            $table->foreign('id_expediente')
                ->references('id_expediente')
                ->on('expedientes')
                ->onDelete('restrict');

            $table->foreign('id_funcionario_resolutor')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
        });

        // 5. auditoria: hacer explícito RESTRICT (son registros históricos legales)
        Schema::table('auditoria', function (Blueprint $table) {
            $table->dropForeign(['id_usuario']);

            $table->foreign('id_usuario')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        // Revertir a CASCADE (NO RECOMENDADO en producción)

        Schema::table('areas', function (Blueprint $table) {
            $table->dropForeign(['id_area_padre']);
            $table->foreign('id_area_padre')
                ->references('id_area')
                ->on('areas')
                ->onDelete('cascade');
        });

        Schema::table('numeracion', function (Blueprint $table) {
            $table->dropForeign(['id_area']);
            $table->foreign('id_area')
                ->references('id_area')
                ->on('areas')
                ->onDelete('cascade');
        });

        Schema::table('transiciones_estado', function (Blueprint $table) {
            $table->dropForeign(['id_estado_origen']);
            $table->dropForeign(['id_estado_destino']);

            $table->foreign('id_estado_origen')
                ->references('id_estado')
                ->on('estados_expediente')
                ->onDelete('cascade');

            $table->foreign('id_estado_destino')
                ->references('id_estado')
                ->on('estados_expediente')
                ->onDelete('cascade');
        });

        Schema::table('resoluciones', function (Blueprint $table) {
            $table->dropForeign(['id_expediente']);
            $table->dropForeign(['id_funcionario_resolutor']);

            $table->foreign('id_expediente')
                ->references('id_expediente')
                ->on('expedientes');

            $table->foreign('id_funcionario_resolutor')
                ->references('id')
                ->on('users');
        });

        Schema::table('auditoria', function (Blueprint $table) {
            $table->dropForeign(['id_usuario']);

            $table->foreign('id_usuario')
                ->references('id')
                ->on('users');
        });
    }
};
