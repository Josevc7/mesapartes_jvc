<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TipoTramite;
use App\Models\Area;

class TipoTramiteSeeder extends Seeder
{
    public function run(): void
    {
        // Deshabilitar verificación de claves foráneas y limpiar tabla
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TipoTramite::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Obtener IDs de las áreas
        $areas = Area::pluck('id_area', 'nombre')->toArray();

        $tiposTramite = [
            // 1. Dirección de Administración
            ['nombre' => 'Pago de tasas administrativas', 'descripcion' => 'Trámite de pago de tasas y derechos administrativos', 'plazo_dias' => 5, 'activo' => true, 'id_area' => $areas['Dirección de Administración'] ?? null],
            ['nombre' => 'Solicitud de constancia', 'descripcion' => 'Emisión de constancias administrativas', 'plazo_dias' => 7, 'activo' => true, 'id_area' => $areas['Dirección de Administración'] ?? null],
            ['nombre' => 'Trámite de recursos humanos', 'descripcion' => 'Gestión de trámites relacionados con personal', 'plazo_dias' => 15, 'activo' => true, 'id_area' => $areas['Dirección de Administración'] ?? null],
            ['nombre' => 'Solicitud de abastecimiento', 'descripcion' => 'Requerimientos de bienes y servicios', 'plazo_dias' => 10, 'activo' => true, 'id_area' => $areas['Dirección de Administración'] ?? null],
            ['nombre' => 'Trámite de archivo y custodia documental', 'descripcion' => 'Gestión de archivo y documentación institucional', 'plazo_dias' => 10, 'activo' => true, 'id_area' => $areas['Dirección de Administración'] ?? null],

            // 1.1 Subdirección de Recursos Humanos
            ['nombre' => 'Solicitud de constancia de trabajo', 'descripcion' => 'Emisión de constancia de trabajo para empleados activos', 'plazo_dias' => 5, 'activo' => true, 'id_area' => $areas['Subdirección de Recursos Humanos'] ?? null],
            ['nombre' => 'Solicitud de constancia de prácticas', 'descripcion' => 'Emisión de constancia de prácticas pre-profesionales o profesionales', 'plazo_dias' => 5, 'activo' => true, 'id_area' => $areas['Subdirección de Recursos Humanos'] ?? null],
            ['nombre' => 'Solicitud de vacaciones', 'descripcion' => 'Solicitud de periodo vacacional', 'plazo_dias' => 10, 'activo' => true, 'id_area' => $areas['Subdirección de Recursos Humanos'] ?? null],
            ['nombre' => 'Solicitud de licencia con goce de haber', 'descripcion' => 'Solicitud de licencia remunerada', 'plazo_dias' => 7, 'activo' => true, 'id_area' => $areas['Subdirección de Recursos Humanos'] ?? null],
            ['nombre' => 'Solicitud de licencia sin goce de haber', 'descripcion' => 'Solicitud de licencia no remunerada', 'plazo_dias' => 10, 'activo' => true, 'id_area' => $areas['Subdirección de Recursos Humanos'] ?? null],
            ['nombre' => 'Solicitud de certificado laboral', 'descripcion' => 'Emisión de certificado de trabajo y relación laboral', 'plazo_dias' => 7, 'activo' => true, 'id_area' => $areas['Subdirección de Recursos Humanos'] ?? null],
            ['nombre' => 'Solicitud de cambio de datos personales', 'descripcion' => 'Actualización de datos personales en el legajo', 'plazo_dias' => 5, 'activo' => true, 'id_area' => $areas['Subdirección de Recursos Humanos'] ?? null],
            ['nombre' => 'Presentación de documentos personales', 'descripcion' => 'Entrega de documentación personal para legajo', 'plazo_dias' => 3, 'activo' => true, 'id_area' => $areas['Subdirección de Recursos Humanos'] ?? null],
            ['nombre' => 'Solicitud de contrato o renovación', 'descripcion' => 'Solicitud de contratación o renovación contractual', 'plazo_dias' => 15, 'activo' => true, 'id_area' => $areas['Subdirección de Recursos Humanos'] ?? null],

            // 2. Dirección de Planificación y Presupuesto
            ['nombre' => 'Programación presupuestal', 'descripcion' => 'Trámites de programación y asignación presupuestal', 'plazo_dias' => 20, 'activo' => true, 'id_area' => $areas['Dirección de Planificación y Presupuesto'] ?? null],
            ['nombre' => 'Modificación presupuestal', 'descripcion' => 'Solicitud de modificaciones al presupuesto', 'plazo_dias' => 15, 'activo' => true, 'id_area' => $areas['Dirección de Planificación y Presupuesto'] ?? null],
            ['nombre' => 'Evaluación de proyectos', 'descripcion' => 'Evaluación técnica de proyectos de inversión', 'plazo_dias' => 30, 'activo' => true, 'id_area' => $areas['Dirección de Planificación y Presupuesto'] ?? null],
            ['nombre' => 'Planeamiento institucional', 'descripcion' => 'Trámites de planificación estratégica', 'plazo_dias' => 20, 'activo' => true, 'id_area' => $areas['Dirección de Planificación y Presupuesto'] ?? null],
            ['nombre' => 'Racionalización administrativa', 'descripcion' => 'Estudios y propuestas de racionalización', 'plazo_dias' => 25, 'activo' => true, 'id_area' => $areas['Dirección de Planificación y Presupuesto'] ?? null],
            ['nombre' => 'Solicitud de Certificación Presupuestal', 'descripcion' => 'Solicitud de certificación de disponibilidad presupuestal', 'plazo_dias' => 5, 'activo' => true, 'id_area' => $areas['Dirección de Planificación y Presupuesto'] ?? null],
            ['nombre' => 'Evaluación Presupuestal de Expediente', 'descripcion' => 'Evaluación presupuestal de expedientes administrativos', 'plazo_dias' => 10, 'activo' => true, 'id_area' => $areas['Dirección de Planificación y Presupuesto'] ?? null],
            ['nombre' => 'Informe de Planificación y Presupuesto', 'descripcion' => 'Elaboración de informes técnicos de planificación y presupuesto', 'plazo_dias' => 10, 'activo' => true, 'id_area' => $areas['Dirección de Planificación y Presupuesto'] ?? null],
            ['nombre' => 'Modificación Presupuestal', 'descripcion' => 'Trámite de modificación de asignación presupuestal', 'plazo_dias' => 15, 'activo' => true, 'id_area' => $areas['Dirección de Planificación y Presupuesto'] ?? null],
            ['nombre' => 'Opinión Técnica Presupuestal', 'descripcion' => 'Emisión de opinión técnica sobre asuntos presupuestales', 'plazo_dias' => 7, 'activo' => true, 'id_area' => $areas['Dirección de Planificación y Presupuesto'] ?? null],

            // 3. Dirección de Asesoría Jurídica
            ['nombre' => 'Opinión legal', 'descripcion' => 'Solicitud de opinión jurídica institucional', 'plazo_dias' => 15, 'activo' => true, 'id_area' => $areas['Dirección de Asesoría Jurídica'] ?? null],
            ['nombre' => 'Revisión de expedientes administrativos', 'descripcion' => 'Revisión legal de expedientes', 'plazo_dias' => 10, 'activo' => true, 'id_area' => $areas['Dirección de Asesoría Jurídica'] ?? null],
            ['nombre' => 'Recursos administrativos (apelación, reconsideración)', 'descripcion' => 'Trámite de recursos impugnativos', 'plazo_dias' => 30, 'activo' => true, 'id_area' => $areas['Dirección de Asesoría Jurídica'] ?? null],
            ['nombre' => 'Procesos sancionadores', 'descripcion' => 'Gestión de procedimientos sancionadores', 'plazo_dias' => 30, 'activo' => true, 'id_area' => $areas['Dirección de Asesoría Jurídica'] ?? null],
            ['nombre' => 'Asesoramiento jurídico institucional', 'descripcion' => 'Consultas y asesoría legal general', 'plazo_dias' => 10, 'activo' => true, 'id_area' => $areas['Dirección de Asesoría Jurídica'] ?? null],
            ['nombre' => 'Solicitud de Opinión Legal', 'descripcion' => 'Solicitud formal de opinión legal sobre asuntos específicos', 'plazo_dias' => 15, 'activo' => true, 'id_area' => $areas['Dirección de Asesoría Jurídica'] ?? null],
            ['nombre' => 'Emisión de Informe Legal', 'descripcion' => 'Elaboración y emisión de informes legales sobre materias consultadas', 'plazo_dias' => 10, 'activo' => true, 'id_area' => $areas['Dirección de Asesoría Jurídica'] ?? null],
            ['nombre' => 'Revisión Legal de Documentación', 'descripcion' => 'Revisión y validación legal de documentos institucionales', 'plazo_dias' => 7, 'activo' => true, 'id_area' => $areas['Dirección de Asesoría Jurídica'] ?? null],

            // 4. Dirección de Caminos
            ['nombre' => 'Mantenimiento de vías', 'descripcion' => 'Solicitudes de mantenimiento vial', 'plazo_dias' => 20, 'activo' => true, 'id_area' => $areas['Dirección de Caminos'] ?? null],
            ['nombre' => 'Estudios y obras viales', 'descripcion' => 'Trámites de estudios y ejecución de obras', 'plazo_dias' => 45, 'activo' => true, 'id_area' => $areas['Dirección de Caminos'] ?? null],
            ['nombre' => 'Supervisión de obras', 'descripcion' => 'Supervisión y control de obras viales', 'plazo_dias' => 30, 'activo' => true, 'id_area' => $areas['Dirección de Caminos'] ?? null],
            ['nombre' => 'Autorización de intervención en vías', 'descripcion' => 'Permisos para intervención en infraestructura vial', 'plazo_dias' => 15, 'activo' => true, 'id_area' => $areas['Dirección de Caminos'] ?? null],
            ['nombre' => 'Evaluación de infraestructura vial', 'descripcion' => 'Evaluación técnica de estado de vías', 'plazo_dias' => 20, 'activo' => true, 'id_area' => $areas['Dirección de Caminos'] ?? null],

            // 5. Dirección de Telecomunicaciones
            ['nombre' => 'Supervisión de servicios de telecomunicaciones', 'descripcion' => 'Control y supervisión de servicios', 'plazo_dias' => 20, 'activo' => true, 'id_area' => $areas['Dirección de Telecomunicaciones'] ?? null],
            ['nombre' => 'Control de estaciones y antenas', 'descripcion' => 'Fiscalización de instalaciones de telecomunicaciones', 'plazo_dias' => 15, 'activo' => true, 'id_area' => $areas['Dirección de Telecomunicaciones'] ?? null],
            ['nombre' => 'Autorizaciones técnicas', 'descripcion' => 'Permisos técnicos de telecomunicaciones', 'plazo_dias' => 30, 'activo' => true, 'id_area' => $areas['Dirección de Telecomunicaciones'] ?? null],
            ['nombre' => 'Fiscalización de servicios', 'descripcion' => 'Fiscalización de operadores de telecomunicaciones', 'plazo_dias' => 20, 'activo' => true, 'id_area' => $areas['Dirección de Telecomunicaciones'] ?? null],
            ['nombre' => 'Informes de control y supervisión', 'descripcion' => 'Elaboración de informes técnicos', 'plazo_dias' => 15, 'activo' => true, 'id_area' => $areas['Dirección de Telecomunicaciones'] ?? null],

            // 6. Dirección de Circulación Terrestre
            ['nombre' => 'Licencia de conducir', 'descripcion' => 'Emisión de licencia de conducir nueva', 'plazo_dias' => 15, 'activo' => true, 'id_area' => $areas['Dirección de Circulación Terrestre'] ?? null],
            ['nombre' => 'Renovación de licencia', 'descripcion' => 'Renovación de licencia de conducir', 'plazo_dias' => 10, 'activo' => true, 'id_area' => $areas['Dirección de Circulación Terrestre'] ?? null],
            ['nombre' => 'Duplicado de licencia', 'descripcion' => 'Emisión de duplicado por pérdida o deterioro', 'plazo_dias' => 7, 'activo' => true, 'id_area' => $areas['Dirección de Circulación Terrestre'] ?? null],
            ['nombre' => 'Permiso de transporte', 'descripcion' => 'Autorización para servicio de transporte', 'plazo_dias' => 30, 'activo' => true, 'id_area' => $areas['Dirección de Circulación Terrestre'] ?? null],
            ['nombre' => 'Autorización de rutas', 'descripcion' => 'Aprobación de rutas de transporte', 'plazo_dias' => 30, 'activo' => true, 'id_area' => $areas['Dirección de Circulación Terrestre'] ?? null],
            ['nombre' => 'Fiscalización y sanciones', 'descripcion' => 'Trámites de fiscalización y sanciones de tránsito', 'plazo_dias' => 20, 'activo' => true, 'id_area' => $areas['Dirección de Circulación Terrestre'] ?? null],
            ['nombre' => 'Transporte de pasajeros y mercancías', 'descripcion' => 'Autorización de transporte de pasajeros y carga', 'plazo_dias' => 25, 'activo' => true, 'id_area' => $areas['Dirección de Circulación Terrestre'] ?? null],
        ];

        foreach ($tiposTramite as $tipo) {
            TipoTramite::create($tipo);
        }
    }
}
