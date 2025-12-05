<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['nombre' => 'Dirección de Circulación Terrestre', 'descripcion' => 'Gestión de licencias de conducir y circulación vehicular'],
            ['nombre' => 'Dirección de Transporte', 'descripcion' => 'Regulación y control del transporte público y privado'],
            ['nombre' => 'Dirección de Comunicaciones', 'descripcion' => 'Supervisión de servicios de telecomunicaciones'],
            ['nombre' => 'Oficina de Licencias de Conducir', 'descripcion' => 'Emisión y renovación de licencias de conducir'],
            ['nombre' => 'Oficina de Inspecciones Técnicas', 'descripcion' => 'Inspecciones técnicas vehiculares y certificaciones'],
            ['nombre' => 'Oficina de Transporte Público', 'descripcion' => 'Autorización y control de servicios de transporte público'],
            ['nombre' => 'Oficina de Transporte de Carga', 'descripcion' => 'Permisos y control de transporte de mercancías'],
            ['nombre' => 'Oficina de Infracciones y Sanciones', 'descripcion' => 'Procesamiento de infracciones de tránsito'],
            ['nombre' => 'Administración General', 'descripcion' => 'Área de administración y gestión general']
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }
    }
}