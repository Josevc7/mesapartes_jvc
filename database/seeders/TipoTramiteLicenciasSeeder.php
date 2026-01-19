<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoTramite;
use App\Models\Area;

class TipoTramiteLicenciasSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el ID del área de Licencias
        $areaLicencias = Area::where('nombre', 'Subdirección de Licencias de Conducir')->first();

        if (!$areaLicencias) {
            $this->command->error('No se encontró el área "Subdirección de Licencias de Conducir". Ejecute primero el AreaSeeder.');
            return;
        }

        $tiposTramite = [
            [
                'nombre' => 'Emisión de licencia de conducir',
                'descripcion' => 'Trámite para obtener licencia de conducir por primera vez',
                'plazo_dias' => 15,
                'activo' => true,
                'id_area' => $areaLicencias->id_area
            ],
            [
                'nombre' => 'Revalidación de licencia',
                'descripcion' => 'Renovación de licencia de conducir vencida o por vencer',
                'plazo_dias' => 10,
                'activo' => true,
                'id_area' => $areaLicencias->id_area
            ],
            [
                'nombre' => 'Duplicado de licencia',
                'descripcion' => 'Emisión de duplicado por pérdida, robo o deterioro de licencia',
                'plazo_dias' => 7,
                'activo' => true,
                'id_area' => $areaLicencias->id_area
            ],
            [
                'nombre' => 'Recategorización de licencia',
                'descripcion' => 'Cambio de categoría de licencia de conducir (A, B, C, etc.)',
                'plazo_dias' => 15,
                'activo' => true,
                'id_area' => $areaLicencias->id_area
            ],
            [
                'nombre' => 'Canje de licencia',
                'descripcion' => 'Canje de licencia de conducir extranjera o de otra región',
                'plazo_dias' => 20,
                'activo' => true,
                'id_area' => $areaLicencias->id_area
            ],
            [
                'nombre' => 'Corrección de datos en licencia',
                'descripcion' => 'Corrección de errores o actualización de datos personales en la licencia',
                'plazo_dias' => 10,
                'activo' => true,
                'id_area' => $areaLicencias->id_area
            ],
            [
                'nombre' => 'Programación de examen',
                'descripcion' => 'Solicitud de programación para examen teórico o práctico de manejo',
                'plazo_dias' => 5,
                'activo' => true,
                'id_area' => $areaLicencias->id_area
            ],
            [
                'nombre' => 'Educación vial / charlas',
                'descripcion' => 'Solicitud de charlas o capacitaciones en educación y seguridad vial',
                'plazo_dias' => 15,
                'activo' => true,
                'id_area' => $areaLicencias->id_area
            ],
            [
                'nombre' => 'Entrega de resultados / constancias',
                'descripcion' => 'Solicitud de resultados de exámenes o constancias de trámites realizados',
                'plazo_dias' => 5,
                'activo' => true,
                'id_area' => $areaLicencias->id_area
            ],
            [
                'nombre' => 'Reclamos / reconsideraciones',
                'descripcion' => 'Presentación de reclamos o solicitudes de reconsideración sobre trámites de licencias',
                'plazo_dias' => 30,
                'activo' => true,
                'id_area' => $areaLicencias->id_area
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

        $this->command->info('Tipos de trámite de Licencias de Conducir creados correctamente.');
    }
}
