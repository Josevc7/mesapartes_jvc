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

        // ===== NIVEL CENTRAL =====
        $direccionRegional = Area::create([
            'nombre' => 'Dirección Regional',
            'descripcion' => 'Máxima autoridad de la Dirección Regional de Transportes y Comunicaciones',
            'id_area_padre' => null,
            'nivel' => 'DIRECCION_REGIONAL',
            'activo' => true,
        ]);

        $oci = Area::create([
            'nombre' => 'Órgano de Control Institucional (OCI)',
            'descripcion' => 'Control y fiscalización institucional',
            'id_area_padre' => null,
            'nivel' => 'OCI',
            'activo' => true,
        ]);

        // ===== 1. DIRECCIÓN DE ADMINISTRACIÓN =====
        $dirAdministracion = Area::create([
            'nombre' => 'Dirección de Administración',
            'descripcion' => 'Gestión administrativa y recursos de la institución',
            'id_area_padre' => null,
            'nivel' => 'DIRECCION',
            'activo' => true,
        ]);

        // Subdirecciones de Administración
        Area::create([
            'nombre' => 'Subdirección de Logística',
            'descripcion' => 'Gestión de adquisiciones y abastecimiento',
            'id_area_padre' => $dirAdministracion->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        Area::create([
            'nombre' => 'Subdirección de Recursos Humanos',
            'descripcion' => 'Gestión del capital humano',
            'id_area_padre' => $dirAdministracion->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        Area::create([
            'nombre' => 'Subdirección de Contabilidad',
            'descripcion' => 'Gestión contable y financiera',
            'id_area_padre' => $dirAdministracion->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        Area::create([
            'nombre' => 'Subdirección de Tesorería',
            'descripcion' => 'Administración de recursos financieros',
            'id_area_padre' => $dirAdministracion->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        Area::create([
            'nombre' => 'Oficina de Tecnologías de Información y Sistemas (OTIS)',
            'descripcion' => 'Gestión de tecnologías de información y sistemas institucionales',
            'id_area_padre' => $dirAdministracion->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        // ===== 2. DIRECCIÓN DE PLANIFICACIÓN Y PRESUPUESTO =====
        $dirPlanificacion = Area::create([
            'nombre' => 'Dirección de Planificación y Presupuesto',
            'descripcion' => 'Planificación estratégica y gestión presupuestaria',
            'id_area_padre' => null,
            'nivel' => 'DIRECCION',
            'activo' => true,
        ]);

        // Subdirecciones de Planificación y Presupuesto
        Area::create([
            'nombre' => 'Subdirección de Presupuesto',
            'descripcion' => 'Formulación y seguimiento presupuestal',
            'id_area_padre' => $dirPlanificacion->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        Area::create([
            'nombre' => 'Subdirección de Proyectos',
            'descripcion' => 'Gestión de proyectos de inversión',
            'id_area_padre' => $dirPlanificacion->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        Area::create([
            'nombre' => 'Subdirección de Estadística e Informática',
            'descripcion' => 'Gestión de información estadística y sistemas informáticos',
            'id_area_padre' => $dirPlanificacion->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        // ===== 3. DIRECCIÓN DE ASESORÍA JURÍDICA =====
        $dirAsesoria = Area::create([
            'nombre' => 'Dirección de Asesoría Jurídica',
            'descripcion' => 'Asesoramiento legal y normativo institucional',
            'id_area_padre' => null,
            'nivel' => 'DIRECCION',
            'activo' => true,
        ]);

        // Subdirecciones de Asesoría Jurídica
        Area::create([
            'nombre' => 'Subdirección de Asuntos Legales',
            'descripcion' => 'Atención de procesos judiciales y administrativos',
            'id_area_padre' => $dirAsesoria->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        Area::create([
            'nombre' => 'Subdirección de Normatividad',
            'descripcion' => 'Desarrollo y revisión de normativa institucional',
            'id_area_padre' => $dirAsesoria->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        // ===== 4. DIRECCIÓN DE CAMINOS =====
        $dirCaminos = Area::create([
            'nombre' => 'Dirección de Caminos',
            'descripcion' => 'Gestión y mantenimiento de infraestructura vial',
            'id_area_padre' => null,
            'nivel' => 'DIRECCION',
            'activo' => true,
        ]);

        // Subdirecciones de Caminos
        Area::create([
            'nombre' => 'Subdirección de Infraestructura Vial',
            'descripcion' => 'Supervisión de obras viales',
            'id_area_padre' => $dirCaminos->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        Area::create([
            'nombre' => 'Subdirección de Mantenimiento Vial',
            'descripcion' => 'Mantenimiento de carreteras',
            'id_area_padre' => $dirCaminos->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        // Residencias de Caminos
        Area::create([
            'nombre' => 'Residencia de Caminos - Abancay',
            'descripcion' => 'Oficina zonal de infraestructura vial - Abancay',
            'id_area_padre' => $dirCaminos->id_area,
            'nivel' => 'RESIDENCIA',
            'activo' => true,
        ]);

        Area::create([
            'nombre' => 'Residencia de Caminos - Andahuaylas',
            'descripcion' => 'Oficina zonal de infraestructura vial - Andahuaylas',
            'id_area_padre' => $dirCaminos->id_area,
            'nivel' => 'RESIDENCIA',
            'activo' => true,
        ]);

        // ===== 5. DIRECCIÓN DE TELECOMUNICACIONES =====
        $dirTelecomunicaciones = Area::create([
            'nombre' => 'Dirección de Telecomunicaciones',
            'descripcion' => 'Supervisión de servicios de telecomunicaciones',
            'id_area_padre' => null,
            'nivel' => 'DIRECCION',
            'activo' => true,
        ]);

        // Subdirecciones de Telecomunicaciones
        Area::create([
            'nombre' => 'Subdirección de Concesiones y Autorizaciones',
            'descripcion' => 'Gestión de permisos de telecomunicaciones',
            'id_area_padre' => $dirTelecomunicaciones->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        Area::create([
            'nombre' => 'Subdirección de Control y Supervisión',
            'descripcion' => 'Fiscalización de servicios de telecomunicaciones',
            'id_area_padre' => $dirTelecomunicaciones->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        // ===== 6. DIRECCIÓN DE CIRCULACIÓN TERRESTRE =====
        $dirCirculacion = Area::create([
            'nombre' => 'Dirección de Circulación Terrestre',
            'descripcion' => 'Gestión de licencias de conducir y circulación vehicular',
            'id_area_padre' => null,
            'nivel' => 'DIRECCION',
            'activo' => true,
        ]);

        // Subdirecciones de Circulación Terrestre
        Area::create([
            'nombre' => 'Subdirección de Licencias de Conducir',
            'descripcion' => 'Emisión y renovación de licencias de conducir',
            'id_area_padre' => $dirCirculacion->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        Area::create([
            'nombre' => 'Subdirección de Vehículos',
            'descripcion' => 'Certificaciones e inspecciones vehiculares',
            'id_area_padre' => $dirCirculacion->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);

        Area::create([
            'nombre' => 'Subdirección de Transporte',
            'descripcion' => 'Regulación de servicios de transporte público',
            'id_area_padre' => $dirCirculacion->id_area,
            'nivel' => 'SUBDIRECCION',
            'activo' => true,
        ]);
    }
}