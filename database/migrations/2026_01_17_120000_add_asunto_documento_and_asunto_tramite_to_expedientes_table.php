<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega campos separados para asunto_documento y asunto_tramite
     */
    public function up(): void
    {
        Schema::table('expedientes', function (Blueprint $table) {
            // Asunto específico del documento (lo que se muestra en el cargo)
            $table->text('asunto_documento')->nullable()->after('asunto');

            // Asunto del trámite (relacionado con el tipo de trámite)
            $table->text('asunto_tramite')->nullable()->after('asunto_documento');
        });

        // Migrar datos existentes: copiar asunto actual a asunto_documento
        DB::table('expedientes')->whereNotNull('asunto')->update([
            'asunto_documento' => DB::raw('asunto')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropColumn(['asunto_documento', 'asunto_tramite']);
        });
    }
};
