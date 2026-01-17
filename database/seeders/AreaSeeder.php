<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Area;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        // Deshabilitar verificación de claves foráneas y limpiar tabla
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Area::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $areas = [
            ['nombre' => 'Dirección de Administración', 'descripcion' => 'Gestión administrativa y recursos de la institución'],
            ['nombre' => 'Dirección de Planificación y Presupuesto', 'descripcion' => 'Planificación estratégica y gestión presupuestaria'],
            ['nombre' => 'Dirección de Asesoría Jurídica', 'descripcion' => 'Asesoramiento legal y normativo institucional'],
            ['nombre' => 'Dirección de Caminos', 'descripcion' => 'Gestión y mantenimiento de infraestructura vial'],
            ['nombre' => 'Dirección de Telecomunicaciones', 'descripcion' => 'Supervisión de servicios de telecomunicaciones'],
            ['nombre' => 'Dirección de Circulación Terrestre', 'descripcion' => 'Gestión de licencias de conducir y circulación vehicular'],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }
    }
}