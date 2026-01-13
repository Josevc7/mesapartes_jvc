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
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id('id_expediente');
            $table->string('codigo_expediente')->unique();
            $table->text('asunto');
            $table->unsignedBigInteger('id_tipo_tramite');
            $table->foreign('id_tipo_tramite')->references('id_tipo_tramite')->on('tipo_tramites');
            $table->foreignId('id_ciudadano')->constrained('users');
            $table->date('fecha_registro');
            $table->enum('estado', ['pendiente', 'derivado', 'en_proceso', 'resuelto', 'archivado'])->default('pendiente');
            $table->enum('prioridad', ['baja', 'normal', 'alta', 'urgente'])->default('normal');
            $table->enum('canal', ['presencial', 'virtual'])->default('presencial');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expedientes');
    }
};
