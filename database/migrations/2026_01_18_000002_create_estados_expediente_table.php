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
        // Tabla de estados configurables del expediente
        Schema::create('estados_expediente', function (Blueprint $table) {
            $table->id('id_estado');
            $table->string('nombre', 50);
            $table->string('slug', 50)->unique();
            $table->string('descripcion')->nullable();
            $table->string('color', 20)->default('#6c757d'); // Color para badges
            $table->string('icono', 50)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('es_inicial')->default(false);
            $table->boolean('es_final')->default(false);
            $table->boolean('requiere_accion')->default(true);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tabla de transiciones de estado permitidas
        Schema::create('transiciones_estado', function (Blueprint $table) {
            $table->id('id_transicion');
            $table->unsignedBigInteger('id_estado_origen');
            $table->unsignedBigInteger('id_estado_destino');
            $table->string('nombre_accion', 100)->nullable(); // Ej: "Clasificar", "Derivar"
            $table->json('roles_permitidos')->nullable(); // IDs de roles que pueden hacer esta transición
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_estado_origen')->references('id_estado')->on('estados_expediente')->onDelete('cascade');
            $table->foreign('id_estado_destino')->references('id_estado')->on('estados_expediente')->onDelete('cascade');
            $table->unique(['id_estado_origen', 'id_estado_destino']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transiciones_estado');
        Schema::dropIfExists('estados_expediente');
    }
};
