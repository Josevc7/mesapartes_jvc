<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_tramites', function (Blueprint $table) {
            $table->foreignId('id_area')->nullable()->constrained('areas');
            $table->text('requisitos')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tipo_tramites', function (Blueprint $table) {
            $table->dropForeign(['id_area']);
            $table->dropColumn(['id_area', 'requisitos']);
        });
    }
};