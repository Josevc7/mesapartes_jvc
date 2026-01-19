<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoTramite;
use App\Models\Area;

class TipoTramiteContabilidadTesoreriaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el ID de las áreas
        $areaContabilidad = Area::where('nombre', 'Subdirección de Contabilidad')->first();
        $areaTesoreria = Area::where('nombre', 'Subdirección de Tesorería')->first();

        if (!$areaContabilidad && !$areaTesoreria) {
            $this->command->error('No se encontraron las áreas de Contabilidad ni Tesorería. Ejecute primero el AreaSeeder.');
            return;
        }

        // Tipos de trámite para Contabilidad
        if ($areaContabilidad) {
            $tiposContabilidad = [
                [
                    'nombre' => 'Rendición de cuentas',
                    'descripcion' => 'Presentación de rendición de cuentas de viáticos, encargos y otros',
                    'plazo_dias' => 10,
                    'activo' => true,
                    'id_area' => $areaContabilidad->id_area
                ],
                [
                    'nombre' => 'Presentación de comprobantes',
                    'descripcion' => 'Entrega de comprobantes de pago, facturas, boletas y otros documentos contables',
                    'plazo_dias' => 5,
                    'activo' => true,
                    'id_area' => $areaContabilidad->id_area
                ],
                [
                    'nombre' => 'Informe financiero / contable',
                    'descripcion' => 'Solicitud de informes financieros, estados de cuenta o reportes contables',
                    'plazo_dias' => 15,
                    'activo' => true,
                    'id_area' => $areaContabilidad->id_area
                ],
                [
                    'nombre' => 'Regularización de gastos',
                    'descripcion' => 'Trámite de regularización de gastos pendientes o no registrados',
                    'plazo_dias' => 10,
                    'activo' => true,
                    'id_area' => $areaContabilidad->id_area
                ],
                [
                    'nombre' => 'Conciliación / devoluciones',
                    'descripcion' => 'Solicitud de conciliación de cuentas o devolución de montos',
                    'plazo_dias' => 15,
                    'activo' => true,
                    'id_area' => $areaContabilidad->id_area
                ],
            ];

            foreach ($tiposContabilidad as $tipo) {
                $existe = TipoTramite::where('nombre', $tipo['nombre'])
                    ->where('id_area', $tipo['id_area'])
                    ->exists();

                if (!$existe) {
                    TipoTramite::create($tipo);
                    $this->command->info("Creado (Contabilidad): {$tipo['nombre']}");
                } else {
                    $this->command->warn("Ya existe (Contabilidad): {$tipo['nombre']}");
                }
            }
        }

        // Tipos de trámite para Tesorería
        if ($areaTesoreria) {
            $tiposTesoreria = [
                [
                    'nombre' => 'Solicitud de pago',
                    'descripcion' => 'Solicitud de pago a proveedores, contratistas o personal',
                    'plazo_dias' => 10,
                    'activo' => true,
                    'id_area' => $areaTesoreria->id_area
                ],
                [
                    'nombre' => 'Solicitud de reembolso',
                    'descripcion' => 'Solicitud de reembolso de gastos realizados',
                    'plazo_dias' => 10,
                    'activo' => true,
                    'id_area' => $areaTesoreria->id_area
                ],
                [
                    'nombre' => 'Solicitud de viáticos',
                    'descripcion' => 'Solicitud de asignación de viáticos para comisiones de servicio',
                    'plazo_dias' => 5,
                    'activo' => true,
                    'id_area' => $areaTesoreria->id_area
                ],
                [
                    'nombre' => 'Conformidad de pago / servicio',
                    'descripcion' => 'Trámite de conformidad para autorizar pagos por servicios recibidos',
                    'plazo_dias' => 7,
                    'activo' => true,
                    'id_area' => $areaTesoreria->id_area
                ],
                [
                    'nombre' => 'Solicitud de certificación presupuestal',
                    'descripcion' => 'Solicitud de certificación de disponibilidad presupuestal para gastos',
                    'plazo_dias' => 5,
                    'activo' => true,
                    'id_area' => $areaTesoreria->id_area
                ],
            ];

            foreach ($tiposTesoreria as $tipo) {
                $existe = TipoTramite::where('nombre', $tipo['nombre'])
                    ->where('id_area', $tipo['id_area'])
                    ->exists();

                if (!$existe) {
                    TipoTramite::create($tipo);
                    $this->command->info("Creado (Tesorería): {$tipo['nombre']}");
                } else {
                    $this->command->warn("Ya existe (Tesorería): {$tipo['nombre']}");
                }
            }
        }

        $this->command->info('Tipos de trámite de Contabilidad y Tesorería creados correctamente.');
    }
}
