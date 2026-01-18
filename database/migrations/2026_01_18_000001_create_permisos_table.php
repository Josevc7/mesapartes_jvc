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
        // Tabla de módulos del sistema
        Schema::create('modulos', function (Blueprint $table) {
            $table->id('id_modulo');
            $table->string('nombre', 100);
            $table->string('slug', 100)->unique();
            $table->string('descripcion')->nullable();
            $table->string('icono', 50)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tabla de permisos
        Schema::create('permisos', function (Blueprint $table) {
            $table->id('id_permiso');
            $table->string('nombre', 100);
            $table->string('slug', 100)->unique();
            $table->string('descripcion')->nullable();
            $table->unsignedBigInteger('id_modulo');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_modulo')->references('id_modulo')->on('modulos')->onDelete('cascade');
        });

        // Tabla pivote roles-permisos
        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_permiso');
            $table->timestamps();

            $table->foreign('id_rol')->references('id_rol')->on('roles')->onDelete('cascade');
            $table->foreign('id_permiso')->references('id_permiso')->on('permisos')->onDelete('cascade');
            $table->unique(['id_rol', 'id_permiso']);
        });

        // Agregar campo activo a roles si no existe
        if (!Schema::hasColumn('roles', 'activo')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('activo')->default(true)->after('descripcion');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rol_permiso');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('modulos');
    }
};
