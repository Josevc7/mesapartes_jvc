<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cambia fecha_limite de DATE a DATETIME.
 *
 * MOTIVO: Con DATE, los vencimientos son siempre a medianoche (00:00:00).
 *         Con DATETIME, se puede vencer a las 17:00 del viernes, por ejemplo.
 *
 * Los datos existentes se conservan (MySQL convierte DATE a DATETIME 00:00:00).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('derivaciones', function (Blueprint $table) {
            $table->dateTime('fecha_limite')->nullable()->change();
        });

        Schema::table('observaciones', function (Blueprint $table) {
            $table->dateTime('fecha_limite')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('derivaciones', function (Blueprint $table) {
            $table->date('fecha_limite')->nullable()->change();
        });

        Schema::table('observaciones', function (Blueprint $table) {
            $table->date('fecha_limite')->nullable()->change();
        });
    }
};
