<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->foreignId('jefe_id')->nullable()->constrained('users');
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropForeign(['jefe_id']);
            $table->dropColumn(['jefe_id', 'activo']);
        });
    }
};