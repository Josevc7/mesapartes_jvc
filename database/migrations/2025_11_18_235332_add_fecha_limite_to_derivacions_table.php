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
        Schema::table('derivacions', function (Blueprint $table) {
            $table->date('fecha_limite')->nullable();
            $table->foreignId('funcionario_origen_id')->nullable()->constrained('users');
            $table->foreignId('funcionario_destino_id')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('derivacions', function (Blueprint $table) {
            $table->dropForeign(['funcionario_origen_id']);
            $table->dropForeign(['funcionario_destino_id']);
            $table->dropColumn(['fecha_limite', 'funcionario_origen_id', 'funcionario_destino_id']);
        });
    }
};
