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
            $table->id();
            $table->foreignId('id_expediente')->constrained('expedientes');
            $table->foreignId('id_origen_area')->nullable()->constrained('areas');
            $table->foreignId('id_destino_area')->constrained('areas');
            $table->foreignId('id_funcionario_asignado')->nullable()->constrained('users');
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
