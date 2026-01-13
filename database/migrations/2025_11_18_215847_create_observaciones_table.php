<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observaciones', function (Blueprint $table) {
            $table->id('id_observacion');
            $table->unsignedBigInteger('id_expediente');
            $table->foreign('id_expediente')->references('id_expediente')->on('expedientes');
            $table->foreignId('id_usuario')->constrained('users');
            $table->enum('tipo', ['observacion', 'devolucion', 'subsanacion']);
            $table->text('descripcion');
            $table->date('fecha_limite')->nullable();
            $table->enum('estado', ['pendiente', 'subsanado', 'vencido'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observaciones');
    }
};