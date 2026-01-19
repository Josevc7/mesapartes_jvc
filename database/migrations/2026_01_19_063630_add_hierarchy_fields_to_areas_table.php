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
        Schema::table('areas', function (Blueprint $table) {
            // Campo para establecer jerarquía (área padre)
            $table->unsignedBigInteger('id_area_padre')->nullable()->after('id_area');
            $table->foreign('id_area_padre')->references('id_area')->on('areas')->onDelete('cascade');

            // Campo para nivel jerárquico (Dirección / Subdirección / Residencia)
            $table->enum('nivel', ['DIRECCION_REGIONAL', 'OCI', 'DIRECCION', 'SUBDIRECCION', 'RESIDENCIA'])->default('SUBDIRECCION')->after('id_area_padre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropForeign(['id_area_padre']);
            $table->dropColumn(['id_area_padre', 'nivel']);
        });
    }
};
