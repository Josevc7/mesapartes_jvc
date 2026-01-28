<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class OrganigramaDRTCSeeder extends Seeder
{
    /**
     * Seeder para crear el organigrama completo de DRTC Apurímac
     *
     * Ejecutar con: php artisan db:seed --class=OrganigramaDRTCSeeder
     */
    public function run(): void
    {
        $this->log('Creando organigrama de DRTC Apurímac...');

        // ========== NIVEL 1: DIRECCIÓN REGIONAL ==========
        $drtc = Area::updateOrCreate(
            ['nombre' => 'Dirección Regional de Transportes y Comunicaciones'],
            [
                'descripcion' => 'Órgano de línea principal - DRTC Apurímac',
                'nivel' => Area::NIVEL_DIRECCION_REGIONAL,
                'id_area_padre' => null,
                'activo' => true
            ]
        );
        $this->log("Creada: {$drtc->nombre}");

        // ========== NIVEL 2: OCI ==========
        $oci = Area::updateOrCreate(
            ['nombre' => 'Órgano de Control Institucional'],
            [
                'descripcion' => 'OCI - Depende funcionalmente de la Contraloría',
                'nivel' => Area::NIVEL_OCI,
                'id_area_padre' => $drtc->id_area,
                'activo' => true
            ]
        );
        $this->log("  Creada: {$oci->nombre}");

        // ========== NIVEL 2: DIRECCIÓN DE ADMINISTRACIÓN ==========
        $dirAdministracion = Area::updateOrCreate(
            ['nombre' => 'Dirección de Administración'],
            [
                'descripcion' => 'Dirección de Administración',
                'nivel' => Area::NIVEL_DIRECCION,
                'id_area_padre' => $drtc->id_area,
                'activo' => true
            ]
        );
        $this->log("  Creada: {$dirAdministracion->nombre}");

        // Sub-direcciones de Administración
        $subAreasAdmin = [
            'Subdirección de Recursos Humanos',
            'Subdirección de Contabilidad y Tesorería',
            'Subdirección de Abastecimiento y Servicios Auxiliares',
            'Subdirección de Archivo Central',
        ];
        foreach ($subAreasAdmin as $nombre) {
            Area::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion' => $nombre,
                    'nivel' => Area::NIVEL_SUBDIRECCION,
                    'id_area_padre' => $dirAdministracion->id_area,
                    'activo' => true
                ]
            );
            $this->log("    Creada: {$nombre}");
        }

        // ========== NIVEL 2: DIRECCIÓN DE PLANIFICACIÓN Y PRESUPUESTO ==========
        $dirPlanificacion = Area::updateOrCreate(
            ['nombre' => 'Dirección de Planificación y Presupuesto'],
            [
                'descripcion' => 'Dirección de Planificación y Presupuesto',
                'nivel' => Area::NIVEL_DIRECCION,
                'id_area_padre' => $drtc->id_area,
                'activo' => true
            ]
        );
        $this->log("  Creada: {$dirPlanificacion->nombre}");

        // Sub-direcciones de Planificación
        $subAreasPlanif = [
            'Subdirección de Planificación',
            'Subdirección de Racionalización y Modernización',
        ];
        foreach ($subAreasPlanif as $nombre) {
            Area::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion' => $nombre,
                    'nivel' => Area::NIVEL_SUBDIRECCION,
                    'id_area_padre' => $dirPlanificacion->id_area,
                    'activo' => true
                ]
            );
            $this->log("    Creada: {$nombre}");
        }

        // ========== NIVEL 2: DIRECCIÓN DE ASESORÍA JURÍDICA ==========
        $dirAsesoriaJuridica = Area::updateOrCreate(
            ['nombre' => 'Dirección de Asesoría Jurídica'],
            [
                'descripcion' => 'Dirección de Asesoría Jurídica',
                'nivel' => Area::NIVEL_DIRECCION,
                'id_area_padre' => $drtc->id_area,
                'activo' => true
            ]
        );
        $this->log("  Creada: {$dirAsesoriaJuridica->nombre}");

        // ========== NIVEL 2: DIRECCIÓN DE CAMINOS ==========
        $dirCaminos = Area::updateOrCreate(
            ['nombre' => 'Dirección de Caminos'],
            [
                'descripcion' => 'Dirección de Caminos',
                'nivel' => Area::NIVEL_DIRECCION,
                'id_area_padre' => $drtc->id_area,
                'activo' => true
            ]
        );
        $this->log("  Creada: {$dirCaminos->nombre}");

        // Sub-direcciones de Caminos
        $subAreasCaminos = [
            'Subdirección de Estudios y Obras',
            'Subdirección de Equipomecánico',
        ];
        foreach ($subAreasCaminos as $nombre) {
            Area::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion' => $nombre,
                    'nivel' => Area::NIVEL_SUBDIRECCION,
                    'id_area_padre' => $dirCaminos->id_area,
                    'activo' => true
                ]
            );
            $this->log("    Creada: {$nombre}");
        }

        // Residencias Viales de Caminos
        $residencias = [
            'Residencia Vial Grau - Cotabambas',
            'Residencia Vial Aymaraes - Antabamba',
        ];
        foreach ($residencias as $nombre) {
            Area::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion' => $nombre,
                    'nivel' => Area::NIVEL_RESIDENCIA,
                    'id_area_padre' => $dirCaminos->id_area,
                    'activo' => true
                ]
            );
            $this->log("    Creada: {$nombre}");
        }

        // ========== NIVEL 2: DIRECCIÓN DE TELECOMUNICACIONES ==========
        $dirTelecomunicaciones = Area::updateOrCreate(
            ['nombre' => 'Dirección de Telecomunicaciones'],
            [
                'descripcion' => 'Dirección de Telecomunicaciones',
                'nivel' => Area::NIVEL_DIRECCION,
                'id_area_padre' => $drtc->id_area,
                'activo' => true
            ]
        );
        $this->log("  Creada: {$dirTelecomunicaciones->nombre}");

        // Sub-direcciones de Telecomunicaciones
        $subAreasTelecom = [
            'Subdirección de Control y Supervisión',
            'Subdirección de Proyectos, Concesiones y Autorizaciones',
        ];
        foreach ($subAreasTelecom as $nombre) {
            Area::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion' => $nombre,
                    'nivel' => Area::NIVEL_SUBDIRECCION,
                    'id_area_padre' => $dirTelecomunicaciones->id_area,
                    'activo' => true
                ]
            );
            $this->log("    Creada: {$nombre}");
        }

        // ========== NIVEL 2: DIRECCIÓN DE CIRCULACIÓN TERRESTRE ==========
        $dirCirculacion = Area::updateOrCreate(
            ['nombre' => 'Dirección de Circulación Terrestre'],
            [
                'descripcion' => 'Dirección de Circulación Terrestre',
                'nivel' => Area::NIVEL_DIRECCION,
                'id_area_padre' => $drtc->id_area,
                'activo' => true
            ]
        );
        $this->log("  Creada: {$dirCirculacion->nombre}");

        // Sub-direcciones de Circulación Terrestre
        $subAreasCirculacion = [
            'Subdirección de Pasajeros y Mercancías',
            'Subdirección de Licencia de Conducir y Educación Vial',
            'Subdirección de Supervisión, Fiscalización y Sanciones',
            'Subdirección de Mantenimiento y Operación',
        ];
        foreach ($subAreasCirculacion as $nombre) {
            Area::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion' => $nombre,
                    'nivel' => Area::NIVEL_SUBDIRECCION,
                    'id_area_padre' => $dirCirculacion->id_area,
                    'activo' => true
                ]
            );
            $this->log("    Creada: {$nombre}");
        }

        $totalAreas = Area::count();
        $this->log("Organigrama creado exitosamente. Total de áreas: {$totalAreas}");
    }

    /**
     * Helper para logging que funciona tanto desde artisan como desde controlador
     */
    private function log(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }
}
