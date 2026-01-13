<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas', function (Blueprint $table) {
            $table->id('id_meta');
            $table->unsignedBigInteger('id_area');
            $table->foreign('id_area')->references('id_area')->on('areas');
            $table->string('descripcion');
            $table->enum('tipo', ['expedientes', 'tiempo', 'eficiencia', 'satisfaccion']);
            $table->decimal('valor_meta', 10, 2);
            $table->decimal('valor_actual', 10, 2)->default(0);
            $table->enum('periodo', ['mensual', 'trimestral', 'semestral', 'anual']);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas');
    }
};