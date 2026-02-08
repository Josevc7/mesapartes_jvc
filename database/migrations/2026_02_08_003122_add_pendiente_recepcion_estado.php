<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Insertar el estado 'pendiente_recepcion' en la tabla estados_expediente
     * y crear la transición pendiente_recepcion -> recepcionado
     */
    public function up(): void
    {
        // Insertar nuevo estado solo si no existe
        $existe = DB::table('estados_expediente')->where('slug', 'pendiente_recepcion')->exists();

        if (!$existe) {
            DB::table('estados_expediente')->insert([
                'nombre' => 'Pendiente de Recepción',
                'slug' => 'pendiente_recepcion',
                'descripcion' => 'Expediente enviado por ciudadano virtual, pendiente de recepción por Mesa de Partes',
                'color' => '#f39c12',
                'icono' => 'fas fa-hourglass-half',
                'orden' => 0,
                'es_inicial' => true,
                'es_final' => false,
                'requiere_accion' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Crear transición: pendiente_recepcion -> recepcionado
        $idPendiente = DB::table('estados_expediente')->where('slug', 'pendiente_recepcion')->value('id_estado');
        $idRecepcionado = DB::table('estados_expediente')->where('slug', 'recepcionado')->value('id_estado');

        if ($idPendiente && $idRecepcionado) {
            $existeTransicion = DB::table('transiciones_estado')
                ->where('id_estado_origen', $idPendiente)
                ->where('id_estado_destino', $idRecepcionado)
                ->exists();

            if (!$existeTransicion) {
                DB::table('transiciones_estado')->insert([
                    'id_estado_origen' => $idPendiente,
                    'id_estado_destino' => $idRecepcionado,
                    'nombre_accion' => 'Recepcionar',
                    'roles_permitidos' => json_encode([1, 2]),
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $idPendiente = DB::table('estados_expediente')->where('slug', 'pendiente_recepcion')->value('id_estado');

        if ($idPendiente) {
            DB::table('transiciones_estado')->where('id_estado_origen', $idPendiente)->delete();
            DB::table('transiciones_estado')->where('id_estado_destino', $idPendiente)->delete();
            DB::table('estados_expediente')->where('id_estado', $idPendiente)->delete();
        }
    }
};
