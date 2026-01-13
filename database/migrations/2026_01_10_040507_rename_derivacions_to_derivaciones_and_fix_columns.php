<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * FASE 1: Correcciones Críticas
     * - Renombrar tabla derivacions → derivaciones
     * - Estandarizar nombres de columnas (id_xxx patrón consistente)
     */
    public function up(): void
    {
        // 1. Primero eliminar las foreign keys existentes
        Schema::table('derivacions', function (Blueprint $table) {
            $table->dropForeign(['id_expediente']);
            $table->dropForeign(['id_origen_area']);
            $table->dropForeign(['id_destino_area']);
            $table->dropForeign(['id_funcionario_asignado']);
            $table->dropForeign(['funcionario_origen_id']);
            $table->dropForeign(['funcionario_destino_id']);
        });

        // 2. Renombrar columnas para estandarizar nomenclatura
        Schema::table('derivacions', function (Blueprint $table) {
            // Estandarizar: id_origen_area → id_area_origen
            $table->renameColumn('id_origen_area', 'id_area_origen');
            // Estandarizar: id_destino_area → id_area_destino
            $table->renameColumn('id_destino_area', 'id_area_destino');
            // Estandarizar: funcionario_origen_id → id_funcionario_origen
            $table->renameColumn('funcionario_origen_id', 'id_funcionario_origen');
            // Estandarizar: funcionario_destino_id → id_funcionario_destino
            $table->renameColumn('funcionario_destino_id', 'id_funcionario_destino');
        });

        // 3. Renombrar la tabla
        Schema::rename('derivacions', 'derivaciones');

        // 4. Recrear las foreign keys con los nuevos nombres Y agregar políticas CASCADE
        Schema::table('derivaciones', function (Blueprint $table) {
            $table->foreign('id_expediente')
                ->references('id_expediente')
                ->on('expedientes')
                ->onDelete('cascade'); // Si se elimina expediente, eliminar derivaciones

            $table->foreign('id_area_origen')
                ->references('id_area')
                ->on('areas')
                ->onDelete('restrict'); // No permitir eliminar área si tiene derivaciones

            $table->foreign('id_area_destino')
                ->references('id_area')
                ->on('areas')
                ->onDelete('restrict');

            $table->foreign('id_funcionario_asignado')
                ->references('id')
                ->on('users')
                ->onDelete('set null'); // Si se elimina funcionario, poner NULL

            $table->foreign('id_funcionario_origen')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('id_funcionario_destino')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Eliminar foreign keys
        Schema::table('derivaciones', function (Blueprint $table) {
            $table->dropForeign(['id_expediente']);
            $table->dropForeign(['id_area_origen']);
            $table->dropForeign(['id_area_destino']);
            $table->dropForeign(['id_funcionario_asignado']);
            $table->dropForeign(['id_funcionario_origen']);
            $table->dropForeign(['id_funcionario_destino']);
        });

        // 2. Renombrar columnas de vuelta
        Schema::table('derivaciones', function (Blueprint $table) {
            $table->renameColumn('id_area_origen', 'id_origen_area');
            $table->renameColumn('id_area_destino', 'id_destino_area');
            $table->renameColumn('id_funcionario_origen', 'funcionario_origen_id');
            $table->renameColumn('id_funcionario_destino', 'funcionario_destino_id');
        });

        // 3. Renombrar tabla de vuelta
        Schema::rename('derivaciones', 'derivacions');

        // 4. Recrear foreign keys originales
        Schema::table('derivacions', function (Blueprint $table) {
            $table->foreign('id_expediente')->references('id_expediente')->on('expedientes');
            $table->foreign('id_origen_area')->references('id_area')->on('areas');
            $table->foreign('id_destino_area')->references('id_area')->on('areas');
            $table->foreign('id_funcionario_asignado')->references('id')->on('users');
            $table->foreign('funcionario_origen_id')->references('id')->on('users');
            $table->foreign('funcionario_destino_id')->references('id')->on('users');
        });
    }
};
