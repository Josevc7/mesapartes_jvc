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
        // Paso 1: Eliminar las foreign keys existentes que referencian personas.id
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_persona']);
        });

        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropForeign(['id_persona']);
        });

        // Paso 2: Renombrar la columna id a id_persona en la tabla personas
        Schema::table('personas', function (Blueprint $table) {
            $table->renameColumn('id', 'id_persona');
        });

        // Paso 3: Recrear las foreign keys con la nueva referencia
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_persona')->references('id_persona')->on('personas')->onDelete('set null');
        });

        Schema::table('expedientes', function (Blueprint $table) {
            $table->foreign('id_persona')->references('id_persona')->on('personas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Paso 1: Eliminar las foreign keys
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_persona']);
        });

        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropForeign(['id_persona']);
        });

        // Paso 2: Renombrar id_persona de vuelta a id
        Schema::table('personas', function (Blueprint $table) {
            $table->renameColumn('id_persona', 'id');
        });

        // Paso 3: Recrear las foreign keys con la referencia original
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_persona')->references('id')->on('personas')->onDelete('set null');
        });

        Schema::table('expedientes', function (Blueprint $table) {
            $table->foreign('id_persona')->references('id')->on('personas')->onDelete('set null');
        });
    }
};
