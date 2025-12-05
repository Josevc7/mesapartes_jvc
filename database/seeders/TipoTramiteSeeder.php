<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoTramite;

class TipoTramiteSeeder extends Seeder
{
    public function run(): void
    {
        $tiposTramite = [
            // Licencias de Conducir
            ['nombre' => 'Licencia de Conducir A-I', 'descripcion' => 'Licencia para motocicletas hasta 125cc', 'plazo_dias' => 15, 'activo' => true],
            ['nombre' => 'Licencia de Conducir A-IIa', 'descripcion' => 'Licencia para motocicletas de 126cc a 250cc', 'plazo_dias' => 15, 'activo' => true],
            ['nombre' => 'Licencia de Conducir A-IIb', 'descripcion' => 'Licencia para motocicletas mayores a 250cc', 'plazo_dias' => 15, 'activo' => true],
            ['nombre' => 'Licencia de Conducir B-I', 'descripcion' => 'Licencia para vehículos particulares', 'plazo_dias' => 15, 'activo' => true],
            ['nombre' => 'Licencia de Conducir B-IIa', 'descripcion' => 'Licencia para taxi y transporte remunerado', 'plazo_dias' => 20, 'activo' => true],
            ['nombre' => 'Licencia de Conducir B-IIb', 'descripcion' => 'Licencia para transporte público de pasajeros', 'plazo_dias' => 20, 'activo' => true],
            ['nombre' => 'Licencia de Conducir B-IIc', 'descripcion' => 'Licencia para transporte de carga', 'plazo_dias' => 20, 'activo' => true],
            
            // Inspecciones Técnicas
            ['nombre' => 'Certificado de Inspección Técnica Vehicular', 'descripcion' => 'Certificado ITV para vehículos particulares', 'plazo_dias' => 5, 'activo' => true],
            ['nombre' => 'Certificado ITV Transporte Público', 'descripcion' => 'Certificado ITV para vehículos de transporte público', 'plazo_dias' => 7, 'activo' => true],
            ['nombre' => 'Certificado de Homologación Vehicular', 'descripcion' => 'Homologación de vehículos importados', 'plazo_dias' => 30, 'activo' => true],
            
            // Permisos de Transporte
            ['nombre' => 'Permiso de Operación de Transporte Público', 'descripcion' => 'Autorización para servicio de transporte público', 'plazo_dias' => 45, 'activo' => true],
            ['nombre' => 'Autorización de Ruta de Transporte', 'descripcion' => 'Aprobación de rutas de transporte público', 'plazo_dias' => 30, 'activo' => true],
            ['nombre' => 'Permiso de Transporte de Carga', 'descripcion' => 'Autorización para transporte de mercancías', 'plazo_dias' => 20, 'activo' => true],
            ['nombre' => 'Permiso de Transporte Especial', 'descripcion' => 'Autorización para transporte de carga especial', 'plazo_dias' => 25, 'activo' => true],
            
            // Infracciones y Sanciones
            ['nombre' => 'Recurso de Apelación de Infracción', 'descripcion' => 'Apelación contra multas de tránsito', 'plazo_dias' => 15, 'activo' => true],
            ['nombre' => 'Solicitud de Fraccionamiento de Multa', 'descripcion' => 'Fraccionamiento de pago de infracciones', 'plazo_dias' => 10, 'activo' => true],
            
            // Comunicaciones
            ['nombre' => 'Autorización de Estación de Radio', 'descripcion' => 'Permiso para instalación de estación de radio', 'plazo_dias' => 60, 'activo' => true],
            ['nombre' => 'Certificado de Cobertura de Señal', 'descripcion' => 'Certificación de cobertura de telecomunicaciones', 'plazo_dias' => 20, 'activo' => true],
            
            // Generales
            ['nombre' => 'Solicitud de Información Pública', 'descripcion' => 'Acceso a información pública', 'plazo_dias' => 7, 'activo' => true],
            ['nombre' => 'Denuncia Administrativa', 'descripcion' => 'Denuncia por infracciones administrativas', 'plazo_dias' => 30, 'activo' => true],
        ];

        foreach ($tiposTramite as $tipo) {
            TipoTramite::create($tipo);
        }
    }
}