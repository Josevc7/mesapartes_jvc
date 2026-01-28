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
            // Verificar si existe jefe_id y renombrarlo a id_jefe
            if (Schema::hasColumn('areas', 'jefe_id')) {
                $table->renameColumn('jefe_id', 'id_jefe');
            } elseif (!Schema::hasColumn('areas', 'id_jefe')) {
                // Si no existe ninguna, crear id_jefe
                $table->unsignedBigInteger('id_jefe')->nullable();
                $table->foreign('id_jefe')->references('id')->on('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            if (Schema::hasColumn('areas', 'id_jefe')) {
                $table->renameColumn('id_jefe', 'jefe_id');
            }
        });
    }
};
