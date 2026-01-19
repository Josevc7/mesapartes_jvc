<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoTramite;
use App\Models\Area;

class TipoTramiteOTISSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el ID del área OTIS
        $areaOTIS = Area::where('nombre', 'like', '%OTIS%')->first();

        if (!$areaOTIS) {
            $this->command->error('No se encontró el área OTIS. Ejecute primero el AreaSeeder.');
            return;
        }

        $tiposTramite = [
            [
                'nombre' => 'Soporte técnico de hardware',
                'descripcion' => 'Atención de problemas relacionados con equipos físicos (computadoras, monitores, teclados, mouse, etc.)',
                'plazo_dias' => 3,
                'activo' => true,
                'id_area' => $areaOTIS->id_area
            ],
            [
                'nombre' => 'Soporte técnico de software',
                'descripcion' => 'Atención de problemas con programas, aplicaciones y sistemas operativos',
                'plazo_dias' => 3,
                'activo' => true,
                'id_area' => $areaOTIS->id_area
            ],
            [
                'nombre' => 'Instalación / actualización de software',
                'descripcion' => 'Instalación de nuevos programas o actualización de software existente',
                'plazo_dias' => 2,
                'activo' => true,
                'id_area' => $areaOTIS->id_area
            ],
            [
                'nombre' => 'Configuración de impresoras / escáner',
                'descripcion' => 'Configuración, instalación y solución de problemas de impresoras y escáneres',
                'plazo_dias' => 2,
                'activo' => true,
                'id_area' => $areaOTIS->id_area
            ],
            [
                'nombre' => 'Configuración de red / internet',
                'descripcion' => 'Configuración de conexión a red, internet, wifi y problemas de conectividad',
                'plazo_dias' => 2,
                'activo' => true,
                'id_area' => $areaOTIS->id_area
            ],
            [
                'nombre' => 'Creación de usuario / accesos a sistemas',
                'descripcion' => 'Creación de cuentas de usuario y asignación de permisos de acceso a sistemas institucionales',
                'plazo_dias' => 3,
                'activo' => true,
                'id_area' => $areaOTIS->id_area
            ],
            [
                'nombre' => 'Restablecer contraseña',
                'descripcion' => 'Restablecimiento de contraseñas de usuarios en sistemas y correos institucionales',
                'plazo_dias' => 1,
                'activo' => true,
                'id_area' => $areaOTIS->id_area
            ],
            [
                'nombre' => 'Reporte de incidencia del sistema',
                'descripcion' => 'Reporte de errores, fallas o incidencias en los sistemas institucionales',
                'plazo_dias' => 5,
                'activo' => true,
                'id_area' => $areaOTIS->id_area
            ],
            [
                'nombre' => 'Mantenimiento preventivo de equipos',
                'descripcion' => 'Solicitud de mantenimiento preventivo para equipos de cómputo',
                'plazo_dias' => 7,
                'activo' => true,
                'id_area' => $areaOTIS->id_area
            ],
            [
                'nombre' => 'Respaldo / recuperación de archivos (backup)',
                'descripcion' => 'Solicitud de respaldo de información o recuperación de archivos perdidos',
                'plazo_dias' => 5,
                'activo' => true,
                'id_area' => $areaOTIS->id_area
            ],
        ];

        foreach ($tiposTramite as $tipo) {
            // Verificar si ya existe para evitar duplicados
            $existe = TipoTramite::where('nombre', $tipo['nombre'])
                ->where('id_area', $tipo['id_area'])
                ->exists();

            if (!$existe) {
                TipoTramite::create($tipo);
                $this->command->info("Creado: {$tipo['nombre']}");
            } else {
                $this->command->warn("Ya existe: {$tipo['nombre']}");
            }
        }

        $this->command->info('Tipos de trámite de OTIS creados correctamente.');
    }
}
