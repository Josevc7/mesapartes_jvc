<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resoluciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_expediente')->constrained('expedientes');
            $table->foreignId('id_funcionario_resolutor')->constrained('users');
            $table->string('numero_resolucion', 50)->unique();
            $table->enum('tipo_resolucion', ['aprobado', 'rechazado', 'observado']);
            $table->text('fundamento_legal')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('ruta_documento_resolucion')->nullable();
            $table->datetime('fecha_resolucion');
            $table->datetime('fecha_notificacion')->nullable();
            $table->tinyInteger('notificado')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resoluciones');
    }
};