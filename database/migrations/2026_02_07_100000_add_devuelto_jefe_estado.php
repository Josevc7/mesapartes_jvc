<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Insertar nuevo estado 'devuelto_jefe' en la tabla estados_expediente
        $existe = DB::table('estados_expediente')->where('slug', 'devuelto_jefe')->exists();

        if (!$existe) {
            $idEstado = DB::table('estados_expediente')->insertGetId([
                'nombre' => 'Devuelto al Jefe',
                'slug' => 'devuelto_jefe',
                'descripcion' => 'Expediente devuelto al Jefe de Área por el funcionario (falta info, error de asignación, caso complejo, etc.)',
                'color' => '#e67e22',
                'icono' => 'fas fa-undo-alt',
                'orden' => 6, // entre en_proceso(5) y observado(6 actual)
                'es_inicial' => false,
                'es_final' => false,
                'requiere_accion' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Reordenar: mover observado y siguientes +1
            DB::table('estados_expediente')
                ->where('slug', '!=', 'devuelto_jefe')
                ->where('orden', '>=', 6)
                ->increment('orden');
        } else {
            $idEstado = DB::table('estados_expediente')->where('slug', 'devuelto_jefe')->value('id_estado');
        }

        // 2. Insertar transiciones para devuelto_jefe
        $idEnProceso = DB::table('estados_expediente')->where('slug', 'en_proceso')->value('id_estado');
        $idDerivado = DB::table('estados_expediente')->where('slug', 'derivado')->value('id_estado');
        $idDevuelto = $idEstado;

        $transiciones = [
            // Funcionario devuelve al Jefe
            [$idEnProceso, $idDevuelto, 'Devolver al Jefe', [1, 4]], // Admin, Funcionario
            // Jefe reasigna (vuelve a en_proceso)
            [$idDevuelto, $idEnProceso, 'Reasignar', [1, 3]], // Admin, Jefe
            // Jefe deriva a otra área
            [$idDevuelto, $idDerivado, 'Derivar', [1, 3]], // Admin, Jefe
        ];

        foreach ($transiciones as $trans) {
            if ($trans[0] && $trans[1]) {
                DB::table('transiciones_estado')->updateOrInsert(
                    [
                        'id_estado_origen' => $trans[0],
                        'id_estado_destino' => $trans[1],
                    ],
                    [
                        'nombre_accion' => $trans[2],
                        'roles_permitidos' => json_encode($trans[3]),
                        'activo' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        $idDevuelto = DB::table('estados_expediente')->where('slug', 'devuelto_jefe')->value('id_estado');

        if ($idDevuelto) {
            // Eliminar transiciones relacionadas
            DB::table('transiciones_estado')
                ->where('id_estado_origen', $idDevuelto)
                ->orWhere('id_estado_destino', $idDevuelto)
                ->delete();

            // Eliminar el estado
            DB::table('estados_expediente')->where('id_estado', $idDevuelto)->delete();
        }
    }
};
