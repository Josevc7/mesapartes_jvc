<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Mejoras de integridad de datos (prioridad media)
 *
 * A) users: Eliminar campos duplicados (dni, telefono, direccion)
 *    - La fuente de verdad es la tabla personas (via id_persona)
 *    - users solo debe tener: login (email/password), rol, área
 *
 * B) resoluciones: UNIQUE(id_expediente)
 *    - Garantiza 1 resolución final por expediente
 *
 * C) tipo_tramites: UNIQUE(id_area, nombre)
 *    - Evita trámites duplicados por área
 */
return new class extends Migration
{
    public function up(): void
    {
        // A) Migrar datos de users a personas antes de eliminar columnas
        $this->migrarDatosUsersAPersonas();

        // A) Eliminar campos duplicados de users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dni', 'telefono', 'direccion']);
        });

        // B) UNIQUE en resoluciones (1 resolución por expediente)
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->unique('id_expediente', 'resoluciones_expediente_unique');
        });

        // C) UNIQUE en tipo_tramites (no duplicar trámites por área)
        Schema::table('tipo_tramites', function (Blueprint $table) {
            $table->unique(['id_area', 'nombre'], 'tipo_tramites_area_nombre_unique');
        });
    }

    public function down(): void
    {
        // C) Quitar UNIQUE de tipo_tramites
        Schema::table('tipo_tramites', function (Blueprint $table) {
            $table->dropUnique('tipo_tramites_area_nombre_unique');
        });

        // B) Quitar UNIQUE de resoluciones
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->dropUnique('resoluciones_expediente_unique');
        });

        // A) Restaurar columnas en users
        Schema::table('users', function (Blueprint $table) {
            $table->string('dni', 8)->nullable()->after('id_persona');
            $table->string('telefono')->nullable()->after('dni');
            $table->text('direccion')->nullable()->after('telefono');
        });
    }

    /**
     * Migra datos de users a personas si no están sincronizados
     */
    private function migrarDatosUsersAPersonas(): void
    {
        // Obtener users que tienen dni pero no tienen id_persona
        $usersSinPersona = DB::table('users')
            ->whereNotNull('dni')
            ->where('dni', '!=', '')
            ->whereNull('id_persona')
            ->get();

        foreach ($usersSinPersona as $user) {
            // Buscar si ya existe persona con ese DNI
            $persona = DB::table('personas')
                ->where('tipo_documento', 'DNI')
                ->where('numero_documento', $user->dni)
                ->first();

            if (!$persona) {
                // Crear persona con datos del user
                $idPersona = DB::table('personas')->insertGetId([
                    'tipo_documento' => 'DNI',
                    'numero_documento' => $user->dni,
                    'tipo_persona' => 'NATURAL',
                    'nombres' => $user->name ?? '',
                    'telefono' => $user->telefono,
                    'direccion' => $user->direccion,
                    'email' => $user->email,
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $idPersona = $persona->id_persona;

                // Actualizar persona con datos faltantes
                $updates = [];
                if (empty($persona->telefono) && !empty($user->telefono)) {
                    $updates['telefono'] = $user->telefono;
                }
                if (empty($persona->direccion) && !empty($user->direccion)) {
                    $updates['direccion'] = $user->direccion;
                }
                if (!empty($updates)) {
                    $updates['updated_at'] = now();
                    DB::table('personas')->where('id_persona', $idPersona)->update($updates);
                }
            }

            // Vincular user con persona
            DB::table('users')->where('id', $user->id)->update(['id_persona' => $idPersona]);
        }
    }
};
