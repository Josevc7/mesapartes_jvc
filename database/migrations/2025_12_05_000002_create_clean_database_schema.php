<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ESQUEMA FINAL CONSOLIDADO - MESA DE PARTES JVC
     * Esta migración define la estructura limpia y final de la base de datos
     */
    public function up(): void
    {
        // ===== TABLAS PRINCIPALES =====
        
        // 1. ROLES
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 50)->unique();
                $table->string('descripcion')->nullable();
                $table->timestamps();
            });
        }

        // 2. AREAS
        if (!Schema::hasTable('areas')) {
            Schema::create('areas', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 100);
                $table->text('descripcion')->nullable();
                $table->foreignId('id_jefe')->nullable()->constrained('users');
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        // 3. PERSONAS (Ciudadanos y Representantes)
        if (!Schema::hasTable('personas')) {
            Schema::create('personas', function (Blueprint $table) {
                $table->id();
                $table->enum('tipo_documento', ['DNI', 'CE', 'RUC', 'PASAPORTE'])->default('DNI');
                $table->string('numero_documento', 20)->unique();
                $table->enum('tipo_persona', ['NATURAL', 'JURIDICA'])->default('NATURAL');
                
                // Persona Natural
                $table->string('nombres', 100)->nullable();
                $table->string('apellido_paterno', 50)->nullable();
                $table->string('apellido_materno', 50)->nullable();
                
                // Persona Jurídica
                $table->string('razon_social', 200)->nullable();
                $table->string('representante_legal', 150)->nullable();
                
                // Datos de contacto
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
        }

        // 4. USUARIOS DEL SISTEMA
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->foreignId('id_rol')->constrained('roles');
                $table->foreignId('id_area')->nullable()->constrained('areas');
                $table->foreignId('id_persona')->nullable()->constrained('personas');
                $table->boolean('activo')->default(true);
                $table->rememberToken();
                $table->timestamps();
                
                $table->index(['id_rol', 'activo']);
                $table->index('id_area');
            });
        }

        // 5. TIPOS DE TRÁMITE
        if (!Schema::hasTable('tipo_tramites')) {
            Schema::create('tipo_tramites', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 150);
                $table->text('descripcion')->nullable();
                $table->text('requisitos')->nullable();
                $table->integer('plazo_dias')->default(15);
                $table->foreignId('id_area')->nullable()->constrained('areas');
                $table->boolean('activo')->default(true);
                $table->timestamps();
                
                $table->index(['activo', 'id_area']);
            });
        }

        // ===== TABLA PRINCIPAL: EXPEDIENTES =====
        if (!Schema::hasTable('expedientes')) {
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
                
                // Datos del remitente (casos sin registro)
                $table->string('remitente', 200)->nullable();
                $table->string('dni_remitente', 20)->nullable();
                
                // Estados y configuración
                $table->enum('estado', [
                    'pendiente', 'registrado', 'clasificado', 'derivado', 
                    'en_proceso', 'observado', 'resuelto', 'aprobado', 
                    'rechazado', 'archivado'
                ])->default('pendiente');
                $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
                $table->enum('canal', ['presencial', 'virtual', 'correo'])->default('presencial');
                
                // Control de fechas
                $table->date('fecha_registro');
                $table->datetime('fecha_resolucion')->nullable();
                $table->datetime('fecha_archivo')->nullable();
                
                // Observaciones
                $table->text('observaciones')->nullable();
                $table->text('observaciones_funcionario')->nullable();
                
                $table->timestamps();
                
                // Índices optimizados
                $table->index(['estado', 'fecha_registro']);
                $table->index(['id_area', 'estado']);
                $table->index(['id_funcionario_asignado', 'estado']);
                $table->index('codigo_expediente');
            });
        }

        // ===== TABLAS DE FLUJO =====

        // 6. DERIVACIONES
        if (!Schema::hasTable('derivacions')) {
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
        }

        // 7. DOCUMENTOS ADJUNTOS
        if (!Schema::hasTable('documentos')) {
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
        }

        // 8. RESOLUCIONES OFICIALES
        if (!Schema::hasTable('resoluciones')) {
            Schema::create('resoluciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_expediente')->constrained('expedientes');
                $table->foreignId('id_funcionario_resolutor')->constrained('users');
                $table->string('numero_resolucion', 50)->unique();
                $table->enum('tipo_resolucion', ['aprobado', 'rechazado', 'observado']);
                $table->text('fundamento_legal')->nullable();
                $table->text('observaciones')->nullable();
                $table->string('ruta_documento_resolucion', 500)->nullable();
                $table->datetime('fecha_resolucion');
                $table->datetime('fecha_notificacion')->nullable();
                $table->boolean('notificado')->default(false);
                $table->timestamps();
                
                $table->index('id_expediente');
                $table->index('numero_resolucion');
            });
        }

        // ===== TABLAS DE AUDITORÍA Y CONTROL =====

        // 9. HISTORIAL DE EXPEDIENTES
        if (!Schema::hasTable('historial_expedientes')) {
            Schema::create('historial_expedientes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_expediente')->constrained('expedientes');
                $table->foreignId('id_usuario')->constrained('users');
                $table->text('descripcion');
                $table->datetime('fecha');
                $table->timestamps();
                
                $table->index(['id_expediente', 'fecha']);
            });
        }

        // 10. AUDITORÍA TÉCNICA
        if (!Schema::hasTable('auditoria')) {
            Schema::create('auditoria', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_usuario')->constrained('users');
                $table->string('accion', 100);
                $table->string('tabla_afectada', 50);
                $table->bigInteger('id_registro');
                $table->json('datos_anteriores')->nullable();
                $table->json('datos_nuevos')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();
                
                $table->index(['tabla_afectada', 'id_registro']);
                $table->index(['id_usuario', 'created_at']);
            });
        }

        // ===== TABLAS AUXILIARES =====

        // 11. OBSERVACIONES
        if (!Schema::hasTable('observaciones')) {
            Schema::create('observaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_expediente')->constrained('expedientes');
                $table->foreignId('id_usuario')->constrained('users');
                $table->text('descripcion');
                $table->enum('tipo', ['informacion', 'advertencia', 'error'])->default('informacion');
                $table->boolean('resuelta')->default(false);
                $table->timestamps();
                
                $table->index(['id_expediente', 'resuelta']);
            });
        }

        // 12. NOTIFICACIONES
        if (!Schema::hasTable('notificaciones')) {
            Schema::create('notificaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_usuario')->constrained('users');
                $table->foreignId('id_expediente')->nullable()->constrained('expedientes');
                $table->string('titulo', 200);
                $table->text('mensaje');
                $table->enum('tipo', ['info', 'warning', 'success', 'error'])->default('info');
                $table->boolean('leida')->default(false);
                $table->timestamps();
                
                $table->index(['id_usuario', 'leida']);
            });
        }

        // 13. METAS Y OBJETIVOS
        if (!Schema::hasTable('metas')) {
            Schema::create('metas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_area')->constrained('areas');
                $table->string('descripcion');
                $table->integer('cantidad_objetivo');
                $table->integer('cantidad_actual')->default(0);
                $table->date('fecha_inicio');
                $table->date('fecha_fin');
                $table->enum('estado', ['activa', 'completada', 'vencida'])->default('activa');
                $table->timestamps();
                
                $table->index(['id_area', 'estado']);
            });
        }

        // 14. NUMERACIÓN Y CONTROL
        if (!Schema::hasTable('numeracion')) {
            Schema::create('numeracion', function (Blueprint $table) {
                $table->id();
                $table->year('año');
                $table->integer('ultimo_numero')->default(0);
                $table->string('prefijo', 10)->default('EXP');
                $table->timestamps();
                
                $table->unique('año');
            });
        }

        // 15. CONFIGURACIONES DEL SISTEMA
        if (!Schema::hasTable('configuraciones')) {
            Schema::create('configuraciones', function (Blueprint $table) {
                $table->id();
                $table->string('clave', 100)->unique();
                $table->text('valor');
                $table->string('descripcion')->nullable();
                $table->timestamps();
            });
        }
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