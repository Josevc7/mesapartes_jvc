<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corrige el UNIQUE en personas.
 *
 * ANTES: UNIQUE(numero_documento) - INCORRECTO
 *   Un mismo número puede existir en tipos distintos (DNI, PASAPORTE, OTROS)
 *
 * AHORA: UNIQUE(tipo_documento, numero_documento) - CORRECTO
 *   Permite "12345678" como DNI y "12345678" como PASAPORTE (son personas distintas)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            // Eliminar UNIQUE incorrecto
            $table->dropUnique('personas_numero_documento_unique');

            // Crear UNIQUE correcto (tipo + numero)
            $table->unique(['tipo_documento', 'numero_documento'], 'personas_tipo_numero_unique');
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropUnique('personas_tipo_numero_unique');
            $table->unique('numero_documento', 'personas_numero_documento_unique');
        });
    }
};
