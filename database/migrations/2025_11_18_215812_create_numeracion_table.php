<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('numeracion', function (Blueprint $table) {
            $table->id('id_numeracion');
            $table->year('año');
            $table->integer('ultimo_numero')->default(0);
            $table->timestamps();
            
            $table->unique('año');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numeracion');
    }
};