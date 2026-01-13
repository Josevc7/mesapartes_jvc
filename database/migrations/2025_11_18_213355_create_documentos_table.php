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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id('id_documento');
            $table->unsignedBigInteger('id_expediente');
            $table->foreign('id_expediente')->references('id_expediente')->on('expedientes');
            $table->string('ruta_pdf');
            $table->string('nombre');
            $table->enum('tipo', ['entrada', 'informe', 'respuesta'])->default('entrada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
