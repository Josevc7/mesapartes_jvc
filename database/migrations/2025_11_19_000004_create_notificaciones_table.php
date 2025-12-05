<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('users');
            $table->string('titulo');
            $table->text('mensaje');
            $table->string('expediente_codigo')->nullable();
            $table->enum('tipo', ['info', 'warning', 'success', 'danger'])->default('info');
            $table->boolean('leida')->default(false);
            $table->timestamps();
            
            $table->index(['id_usuario', 'leida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};