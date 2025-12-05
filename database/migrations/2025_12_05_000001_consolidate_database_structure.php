<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. CONSOLIDAR TABLA EXPEDIENTES
        Schema::dropIfExists('expedientes');
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_expediente', 50)->unique();
            $table->text('asunto');
            
            // Relaciones principales
            $table->foreignId('id_tipo_tramite')->constrained('tipo_tramites');
            $table->foreignId('id_ciudadano')->nullable()->constrained('users');
            $table->foreignId('id_persona')->nullable()->constrained('personas');
            $table->foreignId('id_area')->nullable()->constrained('areas');
            $table->foreignId('id_funcionario_asignado')->nullable()->constrained('users');
            
            // Datos del remitente (para casos sin registro)
            $table->string('remitente', 200)->nullable();
            $table->string('dni_remitente', 20)->nullable();
            
            // Estados y fechas
            $table->enum('estado', [
                'pendiente', 'registrado', 'clasificado', 'derivado', 
                'en_proceso', 'observado', 'resuelto', 'aprobado', 
                'rechazado', 'archivado'
            ])->default('pendiente');
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->enum('canal', ['presencial', 'virtual', 'correo'])->default('presencial');
            
            // Fechas del proceso
            $table->date('fecha_registro');
            $table->datetime('fecha_resolucion')->nullable();
            $table->datetime('fecha_archivo')->nullable();
            
            // Observaciones
            $table->text('observaciones')->nullable();
            $table->text('observaciones_funcionario')->nullable();
            
            $table->timestamps();
            
            // Índices para rendimiento
            $table->index(['estado', 'fecha_registro']);
            $table->index(['id_area', 'estado']);
            $table->index(['id_funcionario_asignado', 'estado']);
            $table->index('codigo_expediente');
        });

        // 2. CONSOLIDAR TABLA DERIVACIONS
        Schema::dropIfExists('derivacions');
        Schema::create('derivacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_expediente')->constrained('expedientes');
            $table->foreignId('id_area_origen')->nullable()->constrained('areas');
            $table->foreignId('id_area_destino')->constrained('areas');
            $table->foreignId('id_funcionario_origen')->nullable()->constrained('users');
            $table->foreignId('id_funcionario_destino')->nullable()->constrained('users');
            $table->foreignId('id_funcionario_asignado')->nullable()->constrained('users');
            
            $table->date('fecha_derivacion');
            $table->date('fecha_recepcion')->nullable();
            $table->date('fecha_limite')->nullable();
            $table->integer('plazo_dias')->default(15);
            
            $table->enum('estado', ['pendiente', 'recibido', 'vencido'])->default('pendiente');
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
            
            $table->index(['id_expediente', 'fecha_derivacion']);
            $table->index(['id_funcionario_asignado', 'estado']);
        });

        // 3. CONSOLIDAR TABLA DOCUMENTOS
        Schema::dropIfExists('documentos');
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_expediente')->constrained('expedientes');
            $table->string('nombre', 255);
            $table->string('ruta_pdf', 500);
            $table->enum('tipo', ['entrada', 'informe', 'respuesta', 'anexo'])->default('entrada');
            $table->bigInteger('tamaño_archivo')->nullable();
            $table->timestamps();
            
            $table->index('id_expediente');
        });

        // 4. CONSOLIDAR TABLA AREAS
        Schema::table('areas', function (Blueprint $table) {
            if (!Schema::hasColumn('areas', 'id_jefe')) {
                $table->foreignId('id_jefe')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn('areas', 'activo')) {
                $table->boolean('activo')->default(true);
            }
        });

        // 5. CONSOLIDAR TABLA TIPO_TRAMITES
        Schema::table('tipo_tramites', function (Blueprint $table) {
            if (!Schema::hasColumn('tipo_tramites', 'id_area')) {
                $table->foreignId('id_area')->nullable()->constrained('areas');
            }
            if (!Schema::hasColumn('tipo_tramites', 'activo')) {
                $table->boolean('activo')->default(true);
            }
            if (!Schema::hasColumn('tipo_tramites', 'requisitos')) {
                $table->text('requisitos')->nullable();
            }
        });

        // 6. CONSOLIDAR TABLA USERS
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'id_rol')) {
                $table->foreignId('id_rol')->constrained('roles');
            }
            if (!Schema::hasColumn('users', 'id_area')) {
                $table->foreignId('id_area')->nullable()->constrained('areas');
            }
            if (!Schema::hasColumn('users', 'id_persona')) {
                $table->foreignId('id_persona')->nullable()->constrained('personas');
            }
            if (!Schema::hasColumn('users', 'activo')) {
                $table->boolean('activo')->default(true);
            }
        });
    }

    public function down(): void
    {
        // Revertir cambios si es necesario
        Schema::dropIfExists('expedientes');
        Schema::dropIfExists('derivacions');
        Schema::dropIfExists('documentos');
    }
};