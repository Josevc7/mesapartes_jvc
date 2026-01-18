<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoExpediente;
use App\Models\TransicionEstado;

class EstadosExpedienteSeeder extends Seeder
{
    public function run(): void
    {
        // Estados del expediente según el flujo definido
        $estados = [
            [
                'nombre' => 'Recepcionado',
                'slug' => 'recepcionado',
                'descripcion' => 'Documento recibido por Mesa de Partes',
                'color' => '#6c757d',
                'icono' => 'fas fa-inbox',
                'orden' => 1,
                'es_inicial' => true,
                'es_final' => false,
                'requiere_accion' => true,
            ],
            [
                'nombre' => 'Registrado',
                'slug' => 'registrado',
                'descripcion' => 'Documento registrado con número de expediente',
                'color' => '#17a2b8',
                'icono' => 'fas fa-file-alt',
                'orden' => 2,
                'es_inicial' => false,
                'es_final' => false,
                'requiere_accion' => true,
            ],
            [
                'nombre' => 'Clasificado',
                'slug' => 'clasificado',
                'descripcion' => 'Expediente clasificado según tipo de trámite',
                'color' => '#007bff',
                'icono' => 'fas fa-tags',
                'orden' => 3,
                'es_inicial' => false,
                'es_final' => false,
                'requiere_accion' => true,
            ],
            [
                'nombre' => 'Derivado',
                'slug' => 'derivado',
                'descripcion' => 'Expediente derivado a área competente',
                'color' => '#fd7e14',
                'icono' => 'fas fa-share',
                'orden' => 4,
                'es_inicial' => false,
                'es_final' => false,
                'requiere_accion' => true,
            ],
            [
                'nombre' => 'En Proceso',
                'slug' => 'en_proceso',
                'descripcion' => 'Expediente en procesamiento por funcionario',
                'color' => '#ffc107',
                'icono' => 'fas fa-spinner',
                'orden' => 5,
                'es_inicial' => false,
                'es_final' => false,
                'requiere_accion' => true,
            ],
            [
                'nombre' => 'Observado',
                'slug' => 'observado',
                'descripcion' => 'Expediente con observaciones pendientes',
                'color' => '#dc3545',
                'icono' => 'fas fa-exclamation-triangle',
                'orden' => 6,
                'es_inicial' => false,
                'es_final' => false,
                'requiere_accion' => true,
            ],
            [
                'nombre' => 'Resuelto',
                'slug' => 'resuelto',
                'descripcion' => 'Expediente resuelto con respuesta oficial',
                'color' => '#28a745',
                'icono' => 'fas fa-check-circle',
                'orden' => 7,
                'es_inicial' => false,
                'es_final' => false,
                'requiere_accion' => true,
            ],
            [
                'nombre' => 'Notificado',
                'slug' => 'notificado',
                'descripcion' => 'Resolución notificada al administrado',
                'color' => '#20c997',
                'icono' => 'fas fa-bell',
                'orden' => 8,
                'es_inicial' => false,
                'es_final' => false,
                'requiere_accion' => true,
            ],
            [
                'nombre' => 'Archivado',
                'slug' => 'archivado',
                'descripcion' => 'Expediente archivado definitivamente',
                'color' => '#6f42c1',
                'icono' => 'fas fa-archive',
                'orden' => 9,
                'es_inicial' => false,
                'es_final' => true,
                'requiere_accion' => false,
            ],
        ];

        foreach ($estados as $estado) {
            EstadoExpediente::updateOrCreate(
                ['slug' => $estado['slug']],
                $estado
            );
        }

        // Definir transiciones de estado permitidas
        $this->crearTransiciones();
    }

    private function crearTransiciones()
    {
        $transiciones = [
            // Desde Recepcionado
            ['recepcionado', 'registrado', 'Registrar', [1, 2]], // Admin, Mesa de Partes

            // Desde Registrado
            ['registrado', 'clasificado', 'Clasificar', [1, 2]],

            // Desde Clasificado
            ['clasificado', 'derivado', 'Derivar', [1, 2]],

            // Desde Derivado
            ['derivado', 'en_proceso', 'Procesar', [1, 3, 4]], // Admin, Jefe, Funcionario
            ['derivado', 'derivado', 'Re-derivar', [1, 3]], // Admin, Jefe

            // Desde En Proceso
            ['en_proceso', 'observado', 'Observar', [1, 3, 4]],
            ['en_proceso', 'resuelto', 'Resolver', [1, 3, 4]],
            ['en_proceso', 'derivado', 'Derivar', [1, 3, 4]],

            // Desde Observado
            ['observado', 'en_proceso', 'Retomar', [1, 3, 4]],
            ['observado', 'resuelto', 'Resolver', [1, 3, 4]],

            // Desde Resuelto
            ['resuelto', 'notificado', 'Notificar', [1, 3, 4]],

            // Desde Notificado
            ['notificado', 'archivado', 'Archivar', [1, 2, 3]],

            // Transiciones especiales del Administrador (puede hacer cualquier cambio)
            ['recepcionado', 'archivado', 'Archivar Directo', [1]],
            ['registrado', 'archivado', 'Archivar Directo', [1]],
            ['clasificado', 'archivado', 'Archivar Directo', [1]],
            ['derivado', 'archivado', 'Archivar Directo', [1]],
            ['en_proceso', 'archivado', 'Archivar Directo', [1]],
            ['observado', 'archivado', 'Archivar Directo', [1]],
            ['resuelto', 'archivado', 'Archivar Directo', [1]],
        ];

        foreach ($transiciones as $trans) {
            $estadoOrigen = EstadoExpediente::where('slug', $trans[0])->first();
            $estadoDestino = EstadoExpediente::where('slug', $trans[1])->first();

            if ($estadoOrigen && $estadoDestino) {
                TransicionEstado::updateOrCreate(
                    [
                        'id_estado_origen' => $estadoOrigen->id_estado,
                        'id_estado_destino' => $estadoDestino->id_estado,
                    ],
                    [
                        'nombre_accion' => $trans[2],
                        'roles_permitidos' => $trans[3],
                        'activo' => true,
                    ]
                );
            }
        }
    }
}
