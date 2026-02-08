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
                'nombre' => 'Pendiente de Recepción',
                'slug' => 'pendiente_recepcion',
                'descripcion' => 'Expediente enviado por ciudadano virtual, pendiente de recepción por Mesa de Partes',
                'color' => '#f39c12',
                'icono' => 'fas fa-hourglass-half',
                'orden' => 0,
                'es_inicial' => true,
                'es_final' => false,
                'requiere_accion' => true,
            ],
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
                'nombre' => 'Asignado',
                'slug' => 'asignado',
                'descripcion' => 'Expediente asignado a un funcionario por el Jefe de Área',
                'color' => '#007bff',
                'icono' => 'fas fa-user-check',
                'orden' => 5,
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
                'orden' => 6,
                'es_inicial' => false,
                'es_final' => false,
                'requiere_accion' => true,
            ],
            [
                'nombre' => 'Devuelto al Jefe',
                'slug' => 'devuelto_jefe',
                'descripcion' => 'Expediente devuelto al Jefe de Área por el funcionario (falta info, error de asignación, caso complejo, etc.)',
                'color' => '#e67e22',
                'icono' => 'fas fa-undo-alt',
                'orden' => 7,
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
                'orden' => 8,
                'es_inicial' => false,
                'es_final' => false,
                'requiere_accion' => true,
            ],
            [
                'nombre' => 'En Revisión',
                'slug' => 'en_revision',
                'descripcion' => 'Expediente devuelto por funcionario con documentos, pendiente de revisión del Jefe',
                'color' => '#fd7e14',
                'icono' => 'fas fa-eye',
                'orden' => 9,
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
                'orden' => 10,
                'es_inicial' => false,
                'es_final' => false,
                'requiere_accion' => true,
            ],
            [
                'nombre' => 'Aprobado',
                'slug' => 'aprobado',
                'descripcion' => 'Expediente aprobado por el Jefe de Área',
                'color' => '#28a745',
                'icono' => 'fas fa-check-square',
                'orden' => 11,
                'es_inicial' => false,
                'es_final' => false,
                'requiere_accion' => true,
            ],
            [
                'nombre' => 'Rechazado',
                'slug' => 'rechazado',
                'descripcion' => 'Expediente rechazado por el Jefe de Área, devuelto al funcionario',
                'color' => '#dc3545',
                'icono' => 'fas fa-times-circle',
                'orden' => 12,
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
                'orden' => 13,
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
                'orden' => 14,
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
            // Desde Pendiente de Recepción (virtual)
            ['pendiente_recepcion', 'recepcionado', 'Recepcionar', [1, 2]], // Admin, Mesa de Partes

            // Desde Recepcionado
            ['recepcionado', 'registrado', 'Registrar', [1, 2]], // Admin, Mesa de Partes

            // Desde Registrado
            ['registrado', 'clasificado', 'Clasificar', [1, 2]],

            // Desde Clasificado
            ['clasificado', 'derivado', 'Derivar', [1, 2]],

            // Desde Derivado
            ['derivado', 'asignado', 'Asignar', [1, 3]], // Admin, Jefe
            ['derivado', 'en_proceso', 'Procesar', [1, 3, 4]], // Admin, Jefe, Funcionario
            ['derivado', 'derivado', 'Re-derivar', [1, 3]], // Admin, Jefe

            // Desde Asignado
            ['asignado', 'en_proceso', 'Recibir', [1, 3, 4]], // Admin, Jefe, Funcionario

            // Desde En Proceso
            ['en_proceso', 'observado', 'Observar', [1, 3, 4]],
            ['en_proceso', 'en_revision', 'Devolver para Revisión', [1, 4]], // Admin, Funcionario
            ['en_proceso', 'devuelto_jefe', 'Devolver al Jefe', [1, 4]], // Admin, Funcionario
            ['en_proceso', 'resuelto', 'Resolver', [1, 3, 4]],
            ['en_proceso', 'derivado', 'Derivar', [1, 3, 4]],

            // Desde Devuelto al Jefe
            ['devuelto_jefe', 'en_proceso', 'Reasignar', [1, 3]], // Admin, Jefe
            ['devuelto_jefe', 'derivado', 'Derivar', [1, 3]], // Admin, Jefe

            // Desde En Revisión
            ['en_revision', 'aprobado', 'Aprobar', [1, 3]], // Admin, Jefe
            ['en_revision', 'en_proceso', 'Rechazar/Devolver', [1, 3]], // Admin, Jefe

            // Desde Observado
            ['observado', 'en_proceso', 'Retomar', [1, 3, 4]],
            ['observado', 'resuelto', 'Resolver', [1, 3, 4]],

            // Desde Aprobado
            ['aprobado', 'resuelto', 'Resolver/Finalizar', [1, 3]], // Admin, Jefe
            ['aprobado', 'en_proceso', 'Devolver al Funcionario', [1, 3]], // Admin, Jefe

            // Desde Resuelto
            ['resuelto', 'archivado', 'Archivar', [1, 2, 3]],
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
