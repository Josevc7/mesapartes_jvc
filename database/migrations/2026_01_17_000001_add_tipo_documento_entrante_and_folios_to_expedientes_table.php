<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expedientes', function (Blueprint $table) {
            $table->string('tipo_documento_entrante')->nullable()->after('id_tipo_tramite');
            $table->integer('folios')->default(1)->after('tipo_documento_entrante');
            $table->text('descripcion')->nullable()->after('asunto');
        });
    }

    public function down(): void
    {
        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropColumn(['tipo_documento_entrante', 'folios', 'descripcion']);
        });
    }
};
