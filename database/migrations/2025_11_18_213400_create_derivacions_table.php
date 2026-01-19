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
        Schema::create('derivacions', function (Blueprint $table) {
            $table->id('id_derivacion');
            $table->unsignedBigInteger('id_expediente');
            $table->foreign('id_expediente')->references('id_expediente')->on('expedientes');
            $table->unsignedBigInteger('id_origen_area')->nullable();
            $table->foreign('id_origen_area')->references('id_area')->on('areas');
            $table->unsignedBigInteger('id_destino_area');
            $table->foreign('id_destino_area')->references('id_area')->on('areas');
            $table->unsignedBigInteger('id_funcionario_asignado')->nullable();
            $table->foreign('id_funcionario_asignado')->references('id')->on('users');
            $table->date('fecha_derivacion');
            $table->date('fecha_recepcion')->nullable();
            $table->enum('estado', ['pendiente', 'recibido', 'vencido'])->default('pendiente');
            $table->integer('plazo_dias');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('derivacions');
    }
};
