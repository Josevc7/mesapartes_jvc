<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ESQUEMA LIMPIO Y CONSOLIDADO - MESA DE PARTES JVC
     * Esta migración reemplaza todas las migraciones anteriores desordenadas
     */ 
    //migracion resumida  y optimizada  
    public function up(): void
    {
        // Eliminar todas las tablas existentes para empezar limpio
        Schema::dropIfExists('resoluciones');
        Schema::dropIfExists('observaciones');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('metas');
        Schema::dropIfExists('auditoria');
        Schema::dropIfExists('historial_expedientes');
        Schema::dropIfExists('documentos');
        Schema::dropIfExists('derivacion');
        Schema::dropIfExists('expedientes');
        Schema::dropIfExists('tipo_tramites');
        Schema::dropIfExists('numeracion');
        Schema::dropIfExists('configuraciones');
        Schema::dropIfExists('users');
        Schema::dropIfExists('personas');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('roles');

        // ===== CREAR ESTRUCTURA LIMPIA =====

        // 1. ROLES
        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombre', 50)->unique();
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 2. AREAS
        Schema::create('areas', function (Blueprint $table) {
            $table->id('id_area');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('id_jefe')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 3. PERSONAS
        Schema::create('personas', function (Blueprint $table) {
            $table->id('id_persona');
            $table->enum('tipo_documento', ['DNI', 'CE', 'RUC', 'PASAPORTE'])->default('DNI');
            $table->string('numero_documento', 20)->unique();
            $table->enum('tipo_persona', ['NATURAL', 'JURIDICA'])->default('NATURAL');
            $table->string('nombres', 100)->nullable();
            $table->string('apellido_paterno', 50)->nullable();
            $table->string('apellido_materno', 50)->nullable();
            $table->string('razon_social', 200)->nullable();
            $table->string('representante_legal', 150)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->string('distrito', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['tipo_documento', 'numero_documento']);
        });

        // 4. USUARIOS
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('dni', 20)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->text('direccion')->nullable();
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_area')->nullable();
            $table->unsignedBigInteger('id_persona')->nullable();
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();
            
            $table->foreign('id_rol')->references('id_rol')->on('roles');
            $table->foreign('id_area')->references('id_area')->on('areas');
            $table->foreign('id_persona')->references('id_persona')->on('personas');
            $table->index(['id_rol', 'activo']);
        });

        // Agregar FK de jefe a areas después de crear users
        Schema::table('areas', function (Blueprint $table) {
            $table->foreign('id_jefe')->references('id')->on('users');
        });

        // 5. TIPOS DE TRAMITE
        Schema::create('tipo_tramites', function (Blueprint $table) {
            $table->id('id_tipo_tramite');
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->text('requisitos')->nullable();
            $table->integer('plazo_dias')->default(15);
            $table->unsignedBigInteger('id_area')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->foreign('id_area')->references('id_area')->on('areas');
            $table->index(['activo', 'id_area']);
        });

        // 6. EXPEDIENTES (Tabla central)
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id('id_expediente');
            $table->string('codigo_expediente', 50)->unique();
            $table->text('asunto');
            $table->text('descripcion')->nullable();
            
            // Relaciones principales
            $table->unsignedBigInteger('id_tipo_tramite');
            $table->unsignedBigInteger('id_ciudadano')->nullable(); // FK a personas, no users
            $table->unsignedBigInteger('id_area')->nullable();
            $table->unsignedBigInteger('id_funcionario_asignado')->nullable();
            
            // Datos del remitente solo para casos excepcionales
            $table->string('remitente_externo', 200)->nullable()->comment('Solo si no está en tabla personas');
            
            // Estados y configuración (string en lugar de ENUM)
            $table->string('estado', 20)->default('recepcionado');
            $table->string('prioridad', 20)->default('media');
            $table->string('canal', 20)->default('presencial');
            
            // Control de fechas
            $table->date('fecha_registro');
            $table->datetime('fecha_resolucion')->nullable();
            $table->datetime('fecha_archivo')->nullable();
            
            // Observaciones
            $table->text('observaciones')->nullable();
            $table->text('observaciones_funcionario')->nullable();
            
            $table->timestamps();
            
            // Foreign Keys con reglas CASCADE/SET NULL
            $table->foreign('id_tipo_tramite')->references('id_tipo_tramite')->on('tipo_tramites')->cascadeOnDelete();
            $table->foreign('id_ciudadano')->references('id_persona')->on('personas')->nullOnDelete();
            $table->foreign('id_area')->references('id_area')->on('areas')->nullOnDelete();
            $table->foreign('id_funcionario_asignado')->references('id')->on('users')->nullOnDelete();
            
            // Índices optimizados
            $table->index(['estado', 'fecha_registro']);
            $table->index(['id_area', 'estado']);
            $table->index(['id_funcionario_asignado', 'estado']);
            $table->index('codigo_expediente');
        });

        // 7. DERIVACION
        Schema::create('derivacion', function (Blueprint $table) {
            $table->id('id_derivacion');
            $table->unsignedBigInteger('id_expediente');
            $table->unsignedBigInteger('id_area_origen')->nullable();
            $table->unsignedBigInteger('id_area_destino');
            $table->unsignedBigInteger('id_funcionario_origen')->nullable();
            $table->unsignedBigInteger('id_funcionario_destino')->nullable();
            $table->unsignedBigInteger('id_funcionario_asignado')->nullable();
            
            $table->date('fecha_derivacion');
            $table->date('fecha_recepcion')->nullable();
            $table->date('fecha_limite')->nullable();
            $table->integer('plazo_dias')->default(15);
            
            $table->enum('estado', ['pendiente', 'recibido', 'vencido'])->default('pendiente');
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
            
            $table->foreign('id_expediente')->references('id_expediente')->on('expedientes')->cascadeOnDelete();
            $table->foreign('id_area_origen')->references('id_area')->on('areas')->nullOnDelete();
            $table->foreign('id_area_destino')->references('id_area')->on('areas')->cascadeOnDelete();
            $table->foreign('id_funcionario_origen')->references('id')->on('users')->nullOnDelete();
            $table->foreign('id_funcionario_destino')->references('id')->on('users')->nullOnDelete();
            $table->foreign('id_funcionario_asignado')->references('id')->on('users')->nullOnDelete();
            
            $table->index(['id_expediente', 'fecha_derivacion']);
            $table->index(['id_area_destino', 'estado']);
        });

        // 8. DOCUMENTOS
        Schema::create('documentos', function (Blueprint $table) {
            $table->id('id_documento');
            $table->unsignedBigInteger('id_expediente');
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 500);
            $table->string('tipo_documento', 50);
            $table->string('extension', 10);
            $table->unsignedBigInteger('tamaño_archivo'); // Corregido: unsigned
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->softDeletes(); // SoftDeletes agregado
            
            $table->foreign('id_expediente')->references('id_expediente')->on('expedientes')->cascadeOnDelete();
            $table->index(['id_expediente', 'tipo_documento']);
        });

        // 9. RESOLUCIONES
        Schema::create('resoluciones', function (Blueprint $table) {
            $table->id('id_resolucion');
            $table->unsignedBigInteger('id_expediente');
            $table->string('numero_resolucion', 50)->unique();
            $table->string('tipo_resolucion', 50);
            $table->text('contenido');
            $table->date('fecha_resolucion');
            $table->unsignedBigInteger('id_funcionario');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('id_expediente')->references('id_expediente')->on('expedientes')->cascadeOnDelete();
            $table->foreign('id_funcionario')->references('id')->on('users')->nullOnDelete();
        });

        // 10. AUDITORIA
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id('id_auditoria');
            $table->string('tabla_afectada', 50);
            $table->unsignedBigInteger('id_registro');
            $table->string('accion', 20); // INSERT, UPDATE, DELETE
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            
            $table->foreign('id_usuario')->references('id')->on('users')->nullOnDelete();
            $table->index(['tabla_afectada', 'id_registro']);
        });

        // 11. NOTIFICACIONES
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id('id_notificacion');
            $table->unsignedBigInteger('id_expediente')->nullable();
            $table->unsignedBigInteger('id_usuario');
            $table->string('tipo', 50);
            $table->string('titulo', 200);
            $table->text('mensaje');
            $table->boolean('leida')->default(false);
            $table->timestamps();
            
            $table->foreign('id_expediente')->references('id_expediente')->on('expedientes')->cascadeOnDelete();
            $table->foreign('id_usuario')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['id_usuario', 'leida']);
        });

        // 12. CONFIGURACIONES
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id('id_configuracion');
            $table->string('clave', 100)->unique();
            $table->text('valor');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        // 13. NUMERACION
        Schema::create('numeracion', function (Blueprint $table) {
            $table->id('id_numeracion');
            $table->string('tipo', 50);
            $table->integer('año');
            $table->integer('ultimo_numero')->default(0);
            $table->string('formato', 100);
            $table->timestamps();
            
            $table->unique(['tipo', 'año']);
        });

        // Agregar SoftDeletes a tablas principales
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
        
        Schema::table('areas', function (Blueprint $table) {
            $table->softDeletes();
        });
        
        Schema::table('expedientes', function (Blueprint $table) {
            $table->softDeletes();
        });
        
        Schema::table('derivacion', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('numeracion');
        Schema::dropIfExists('configuraciones');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('auditoria');
        Schema::dropIfExists('resoluciones');
        Schema::dropIfExists('documentos');
        Schema::dropIfExists('derivacion');
        Schema::dropIfExists('expedientes');
        Schema::dropIfExists('tipo_tramites');
        Schema::dropIfExists('users');
        Schema::dropIfExists('personas');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('roles');
    }
};ign('id_funcionario_asignado')->references('id')->on('users');
            
            $table->index(['id_expediente', 'fecha_derivacion']);
            $table->index(['id_funcionario_asignado', 'estado']);
        });

        // 8. DOCUMENTOS ADJUNTOS
        Schema::create('documentos', function (Blueprint $table) {
            $table->id('id_documento');
            $table->unsignedBigInteger('id_expediente');
            $table->string('nombre', 255);
            $table->string('ruta_pdf', 500);
            $table->enum('tipo', ['entrada', 'informe', 'respuesta', 'anexo'])->default('entrada');
            $table->bigInteger('tamaño_archivo')->nullable();
            $table->timestamps();
            
            $table->foreign('id_expediente')->references('id_expediente')->on('expedientes');
            $table->index('id_expediente');
        });

        // 9. RESOLUCIONES OFICIALES
        Schema::create('resoluciones', function (Blueprint $table) {
            $table->id('id_resolucion');
            $table->unsignedBigInteger('id_expediente');
            $table->unsignedBigInteger('id_funcionario_resolutor');
            $table->string('numero_resolucion', 50)->unique();
            $table->enum('tipo_resolucion', ['aprobado', 'rechazado', 'observado']);
            $table->text('fundamento_legal')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('ruta_documento_resolucion', 500)->nullable();
            $table->datetime('fecha_resolucion');
            $table->datetime('fecha_notificacion')->nullable();
            $table->boolean('notificado')->default(false);
            $table->timestamps();
            
            $table->foreign('id_expediente')->references('id_expediente')->on('expedientes');
            $table->foreign('id_funcionario_resolutor')->references('id')->on('users');
            $table->index('id_expediente');
            $table->index('numero_resolucion');
        });

        // ===== TABLAS DE AUDITORÍA =====

        // 10. HISTORIAL DE EXPEDIENTES
        Schema::create('historial_expedientes', function (Blueprint $table) {
            $table->id('id_historial');
            $table->unsignedBigInteger('id_expediente');
            $table->unsignedBigInteger('id_usuario');
            $table->text('descripcion');
            $table->datetime('fecha');
            $table->timestamps();
            
            $table->foreign('id_expediente')->references('id_expediente')->on('expedientes');
            $table->foreign('id_usuario')->references('id')->on('users');
            $table->index(['id_expediente', 'fecha']);
        });

        // 11. AUDITORÍA TÉCNICA
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id('id_auditoria');
            $table->unsignedBigInteger('id_usuario');
            $table->string('accion', 100);
            $table->string('tabla_afectada', 50);
            $table->bigInteger('id_registro');
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->foreign('id_usuario')->references('id')->on('users');
            $table->index(['tabla_afectada', 'id_registro']);
            $table->index(['id_usuario', 'created_at']);
        });

        // ===== TABLAS AUXILIARES =====

        // 12. OBSERVACIONES
        Schema::create('observaciones', function (Blueprint $table) {
            $table->id('id_observacion');
            $table->unsignedBigInteger('id_expediente');
            $table->unsignedBigInteger('id_usuario');
            $table->text('descripcion');
            $table->enum('tipo', ['informacion', 'advertencia', 'error'])->default('informacion');
            $table->boolean('resuelta')->default(false);
            $table->timestamps();
            
            $table->foreign('id_expediente')->references('id_expediente')->on('expedientes');
            $table->foreign('id_usuario')->references('id')->on('users');
            $table->index(['id_expediente', 'resuelta']);
        });

        // 13. NOTIFICACIONES
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id('id_notificacion');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_expediente')->nullable();
            $table->string('titulo', 200);
            $table->text('mensaje');
            $table->enum('tipo', ['info', 'warning', 'success', 'error'])->default('info');
            $table->boolean('leida')->default(false);
            $table->timestamps();
            
            $table->foreign('id_usuario')->references('id')->on('users');
            $table->foreign('id_expediente')->references('id_expediente')->on('expedientes');
            $table->index(['id_usuario', 'leida']);
        });

        // 14. METAS Y OBJETIVOS
        Schema::create('metas', function (Blueprint $table) {
            $table->id('id_meta');
            $table->unsignedBigInteger('id_area');
            $table->string('descripcion');
            $table->integer('cantidad_objetivo');
            $table->integer('cantidad_actual')->default(0);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('estado', ['activa', 'completada', 'vencida'])->default('activa');
            $table->timestamps();
            
            $table->foreign('id_area')->references('id_area')->on('areas');
            $table->index(['id_area', 'estado']);
        });

        // 15. NUMERACIÓN Y CONTROL
        Schema::create('numeracion', function (Blueprint $table) {
            $table->id('id_numeracion');
            $table->year('año');
            $table->integer('ultimo_numero')->default(0);
            $table->string('prefijo', 10)->default('EXP');
            $table->timestamps();
            
            $table->unique('año');
        });

        // 16. CONFIGURACIONES DEL SISTEMA
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id('id_configuracion');
            $table->string('clave', 100)->unique();
            $table->text('valor');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Eliminar en orden inverso por dependencias
        $tables = [
            'configuraciones', 'numeracion', 'metas', 'notificaciones',
            'observaciones', 'auditoria', 'historial_expedientes',
            'resoluciones', 'documentos', 'derivacions', 'expedientes',
            'tipo_tramites', 'users', 'personas', 'areas', 'roles'
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};