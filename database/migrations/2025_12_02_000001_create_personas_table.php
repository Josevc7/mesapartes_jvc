<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * NOTA: Esta migración ya fue ejecutada con 'id' como primary key.
     * La migración 2026_01_12_132522_rename_id_to_id_persona_in_personas_table
     * se encarga de renombrar 'id' a 'id_persona'.
     *
     * Si ejecutas migrate:fresh, esta versión creará directamente con id_persona.
     */
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id('id_persona');
            $table->enum('tipo_documento', ['DNI', 'CE', 'RUC', 'PASAPORTE'])->default('DNI');
            $table->string('numero_documento', 20)->unique();
            $table->enum('tipo_persona', ['NATURAL', 'JURIDICA'])->default('NATURAL');
            
            // Persona Natural
            $table->string('nombres', 100)->nullable();
            $table->string('apellido_paterno', 50)->nullable();
            $table->string('apellido_materno', 50)->nullable();
            
            // Persona Jurídica
            $table->string('razon_social', 200)->nullable();
            $table->string('representante_legal', 150)->nullable();
            
            // Datos comunes
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->string('distrito', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('departamento', 100)->nullable();
            
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['tipo_documento', 'numero_documento']);
            $table->index('tipo_persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};