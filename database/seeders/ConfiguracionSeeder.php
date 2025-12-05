<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuracion;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $configuraciones = [
            [
                'clave' => 'institucion_nombre',
                'valor' => 'Municipalidad Provincial',
                'descripcion' => 'Nombre de la institución',
                'tipo' => 'texto'
            ],
            [
                'clave' => 'institucion_direccion',
                'valor' => 'Av. Principal 123, Ciudad',
                'descripcion' => 'Dirección de la institución',
                'tipo' => 'texto'
            ],
            [
                'clave' => 'plazo_default_dias',
                'valor' => '15',
                'descripcion' => 'Plazo por defecto en días para trámites',
                'tipo' => 'numero'
            ],
            [
                'clave' => 'alerta_vencimiento_dias',
                'valor' => '2',
                'descripcion' => 'Días antes del vencimiento para enviar alerta',
                'tipo' => 'numero'
            ],
            [
                'clave' => 'max_archivos_expediente',
                'valor' => '10',
                'descripcion' => 'Máximo número de archivos por expediente',
                'tipo' => 'numero'
            ],
            [
                'clave' => 'tamaño_max_archivo_mb',
                'valor' => '10',
                'descripcion' => 'Tamaño máximo de archivo en MB',
                'tipo' => 'numero'
            ],
            [
                'clave' => 'notificaciones_activas',
                'valor' => 'true',
                'descripcion' => 'Activar notificaciones por email',
                'tipo' => 'booleano'
            ]
        ];

        foreach ($configuraciones as $config) {
            Configuracion::updateOrCreate(
                ['clave' => $config['clave']],
                $config
            );
        }
    }
}