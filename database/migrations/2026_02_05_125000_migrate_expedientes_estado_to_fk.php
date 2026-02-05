<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Unifica la gestión de estados: elimina ENUM, usa FK a estados_expediente.
 *
 * VENTAJAS:
 * - Estados configurables desde la BD (sin migraciones para agregar nuevos)
 * - Transiciones controladas via transiciones_estado
 * - Metadatos del estado (color, icono, orden) centralizados
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar estados faltantes a la tabla estados_expediente
        $estadosFaltantes = [
            ['slug' => 'pendiente', 'nombre' => 'Pendiente', 'descripcion' => 'Expediente pendiente de atención', 'color' => '#ffc107', 'es_inicial' => true, 'orden' => 0],
            ['slug' => 'en_revision', 'nombre' => 'En Revisión', 'descripcion' => 'Expediente en revisión', 'color' => '#17a2b8', 'orden' => 45],
            ['slug' => 'aprobado', 'nombre' => 'Aprobado', 'descripcion' => 'Expediente aprobado', 'color' => '#28a745', 'es_final' => true, 'orden' => 80],
            ['slug' => 'rechazado', 'nombre' => 'Rechazado', 'descripcion' => 'Expediente rechazado', 'color' => '#dc3545', 'es_final' => true, 'orden' => 85],
        ];

        foreach ($estadosFaltantes as $estado) {
            $exists = DB::table('estados_expediente')->where('slug', $estado['slug'])->exists();
            if (!$exists) {
                DB::table('estados_expediente')->insert(array_merge($estado, [
                    'activo' => true,
                    'requiere_accion' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 2. Agregar columna id_estado a expedientes
        Schema::table('expedientes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_estado')->nullable()->after('estado');
        });

        // 3. Migrar datos: ENUM → FK
        $mapeo = DB::table('estados_expediente')->pluck('id_estado', 'slug');

        DB::table('expedientes')->orderBy('id_expediente')->chunk(100, function ($expedientes) use ($mapeo) {
            foreach ($expedientes as $exp) {
                $idEstado = $mapeo[$exp->estado] ?? null;
                if ($idEstado) {
                    DB::table('expedientes')
                        ->where('id_expediente', $exp->id_expediente)
                        ->update(['id_estado' => $idEstado]);
                }
            }
        });

        // 4. Crear FK (después de migrar datos)
        Schema::table('expedientes', function (Blueprint $table) {
            $table->foreign('id_estado')
                ->references('id_estado')
                ->on('estados_expediente')
                ->onDelete('restrict');
        });

        // 5. Eliminar columna ENUM
        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }

    public function down(): void
    {
        // Restaurar columna ENUM
        Schema::table('expedientes', function (Blueprint $table) {
            $table->enum('estado', [
                'pendiente', 'registrado', 'recepcionado', 'clasificado',
                'derivado', 'asignado', 'en_proceso', 'en_revision',
                'observado', 'resuelto', 'aprobado', 'rechazado', 'archivado'
            ])->default('pendiente')->after('id_estado');
        });

        // Migrar datos: FK → ENUM
        $mapeo = DB::table('estados_expediente')->pluck('slug', 'id_estado');

        DB::table('expedientes')->whereNotNull('id_estado')->orderBy('id_expediente')->chunk(100, function ($expedientes) use ($mapeo) {
            foreach ($expedientes as $exp) {
                $estado = $mapeo[$exp->id_estado] ?? 'pendiente';
                DB::table('expedientes')
                    ->where('id_expediente', $exp->id_expediente)
                    ->update(['estado' => $estado]);
            }
        });

        // Eliminar FK y columna id_estado
        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropForeign(['id_estado']);
            $table->dropColumn('id_estado');
        });

        // Eliminar estados agregados (opcional)
        DB::table('estados_expediente')->whereIn('slug', ['pendiente', 'en_revision', 'aprobado', 'rechazado'])->delete();
    }
};
