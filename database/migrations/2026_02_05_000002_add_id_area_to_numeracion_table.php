<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('numeracion', function (Blueprint $table) {
            // Eliminar el constraint unique existente
            $table->dropUnique(['año']);

            // Agregar columna id_area
            $table->unsignedBigInteger('id_area')->nullable()->after('año');

            // Agregar foreign key
            $table->foreign('id_area')
                ->references('id_area')
                ->on('areas')
                ->onDelete('cascade');

            // Crear nuevo unique compuesto (año + id_area)
            $table->unique(['año', 'id_area'], 'numeracion_año_area_unique');
        });
    }

    public function down(): void
    {
        Schema::table('numeracion', function (Blueprint $table) {
            // Eliminar el unique compuesto
            $table->dropUnique('numeracion_año_area_unique');

            // Eliminar foreign key
            $table->dropForeign(['id_area']);

            // Eliminar columna
            $table->dropColumn('id_area');

            // Restaurar unique original
            $table->unique('año');
        });
    }
};
