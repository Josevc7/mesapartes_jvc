<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Agregar 'anulado' al enum de estado y columnas de auditoría para anulación de derivaciones
     */
    public function up(): void
    {
        // Agregar 'anulado' al enum de estado
        DB::statement("ALTER TABLE derivaciones MODIFY COLUMN estado ENUM('pendiente','recibido','recepcionado','atendido','vencido','anulado') DEFAULT 'pendiente'");

        Schema::table('derivaciones', function (Blueprint $table) {
            $table->text('motivo_anulacion')->nullable()->after('observaciones');
            $table->datetime('fecha_anulacion')->nullable()->after('motivo_anulacion');
            $table->unsignedBigInteger('id_usuario_anulacion')->nullable()->after('fecha_anulacion');
            $table->foreign('id_usuario_anulacion')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('derivaciones', function (Blueprint $table) {
            $table->dropForeign(['id_usuario_anulacion']);
            $table->dropColumn(['motivo_anulacion', 'fecha_anulacion', 'id_usuario_anulacion']);
        });

        DB::statement("ALTER TABLE derivaciones MODIFY COLUMN estado ENUM('pendiente','recibido','recepcionado','atendido','vencido') DEFAULT 'pendiente'");
    }
};
