<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('historial_expedientes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_area')->nullable()->after('id_usuario');
            $table->foreign('id_area')->references('id_area')->on('areas')->onDelete('set null');
            $table->string('accion', 50)->nullable()->after('id_area');
            $table->text('detalle')->nullable()->after('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial_expedientes', function (Blueprint $table) {
            $table->dropForeign(['id_area']);
            $table->dropColumn(['id_area', 'accion', 'detalle']);
        });
    }
};
