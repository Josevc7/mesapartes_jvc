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
        Schema::table('documentos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_derivacion')->nullable()->after('id_expediente');
            $table->foreign('id_derivacion')
                  ->references('id_derivacion')
                  ->on('derivaciones')
                  ->onDelete('set null');
            $table->index('id_derivacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['id_derivacion']);
            $table->dropIndex(['id_derivacion']);
            $table->dropColumn('id_derivacion');
        });
    }
};
