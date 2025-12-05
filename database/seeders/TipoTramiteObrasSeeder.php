<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoTramite;
use App\Models\Area;

class TipoTramiteObrasSeeder extends Seeder
{
    public function run(): void
    {
        // Área de Transporte para obras viales
        $areaTransporte = Area::where('nombre', 'like', '%Transporte%')->first() ?? Area::first();

        $tramitesObras = [
            // PERMISOS DE OBRAS EN CARRETERAS
            [
                'nombre' => 'Permiso de Trabajo en Vía Pública',
                'descripcion' => 'Autorización para realizar trabajos de construcción, mantenimiento o reparación en vías públicas',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 7,
                'requisitos' => 'Solicitud, Planos de obra, Memoria descriptiva, Cronograma de trabajo, Póliza de seguro, Plan de desvío vehicular',
                'activo' => true
            ],
            [
                'nombre' => 'Autorización de Corte de Tráfico',
                'descripcion' => 'Permiso para interrumpir temporalmente el tráfico vehicular por obras',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 5,
                'requisitos' => 'Solicitud justificada, Plan de desvío, Cronograma detallado, Señalización propuesta, Aval de Policía de Tránsito',
                'activo' => true
            ],
            [
                'nombre' => 'Permiso de Ocupación de Berma',
                'descripcion' => 'Autorización para ocupar bermas de carreteras para trabajos de construcción',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 10,
                'requisitos' => 'Solicitud, Planos de ubicación, Justificación técnica, Plan de seguridad vial, Póliza de responsabilidad civil',
                'activo' => true
            ],
            [
                'nombre' => 'Autorización de Desvío Vehicular',
                'descripcion' => 'Permiso para establecer rutas alternas durante ejecución de obras',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 7,
                'requisitos' => 'Plan de desvío, Señalización temporal, Cronograma de obra, Coordinación con autoridades locales',
                'activo' => true
            ],
            [
                'nombre' => 'Permiso de Instalación de Puente Bailey',
                'descripcion' => 'Autorización para instalar puentes temporales durante obras',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 15,
                'requisitos' => 'Estudio de suelos, Diseño estructural, Memoria de cálculo, Plan de instalación, Supervisión técnica',
                'activo' => true
            ],
            [
                'nombre' => 'Autorización de Trabajo Nocturno',
                'descripcion' => 'Permiso para realizar obras en horario nocturno en carreteras',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 3,
                'requisitos' => 'Justificación técnica, Plan de iluminación, Medidas de seguridad adicionales, Cronograma nocturno',
                'activo' => true
            ],
            [
                'nombre' => 'Permiso de Movimiento de Tierras',
                'descripcion' => 'Autorización para excavaciones y movimiento de material en zona de carretera',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 10,
                'requisitos' => 'Estudio de impacto vial, Plan de manejo ambiental, Cronograma de trabajo, Medidas de seguridad',
                'activo' => true
            ],
            [
                'nombre' => 'Autorización de Cruce de Servicios',
                'descripcion' => 'Permiso para cruzar servicios públicos (agua, luz, gas) bajo carreteras',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 12,
                'requisitos' => 'Planos de cruce, Método constructivo, Coordinación con empresas de servicios, Restauración de pavimento',
                'activo' => true
            ],
            [
                'nombre' => 'Permiso de Señalización Temporal',
                'descripcion' => 'Autorización para colocar señalización temporal durante obras viales',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 2,
                'requisitos' => 'Plan de señalización, Especificaciones técnicas, Cronograma de instalación, Responsable técnico',
                'activo' => true
            ],
            [
                'nombre' => 'Autorización de Acopio de Materiales',
                'descripcion' => 'Permiso para almacenar materiales de construcción en zona de carretera',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 5,
                'requisitos' => 'Ubicación propuesta, Tipo de materiales, Medidas de seguridad, Tiempo de permanencia, Plan de retiro',
                'activo' => true
            ],
            [
                'nombre' => 'Permiso de Uso de Maquinaria Pesada',
                'descripcion' => 'Autorización para circulación de maquinaria pesada en carreteras',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 7,
                'requisitos' => 'Especificaciones de maquinaria, Ruta de circulación, Horarios permitidos, Medidas de protección vial',
                'activo' => true
            ],
            [
                'nombre' => 'Autorización de Demolición Parcial',
                'descripcion' => 'Permiso para demoler estructuras que afecten obras viales',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 10,
                'requisitos' => 'Plan de demolición, Medidas de seguridad, Control de polvo y ruido, Disposición de escombros',
                'activo' => true
            ],
            [
                'nombre' => 'Permiso de Instalación de Alcantarillas',
                'descripcion' => 'Autorización para instalar o reparar sistemas de drenaje vial',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 8,
                'requisitos' => 'Diseño hidráulico, Especificaciones técnicas, Plan de construcción, Restauración de pavimento',
                'activo' => true
            ],
            [
                'nombre' => 'Autorización de Bacheo y Parchado',
                'descripcion' => 'Permiso para reparaciones menores de pavimento',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 3,
                'requisitos' => 'Ubicación de trabajos, Especificaciones de materiales, Cronograma de ejecución, Control de tráfico',
                'activo' => true
            ],
            [
                'nombre' => 'Permiso de Pintado de Señalización',
                'descripcion' => 'Autorización para pintar marcas viales y señalización horizontal',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 2,
                'requisitos' => 'Planos de señalización, Especificaciones de pintura, Horario de trabajo, Control de tráfico',
                'activo' => true
            ],
            [
                'nombre' => 'Autorización de Mantenimiento Rutinario',
                'descripcion' => 'Permiso para trabajos de mantenimiento regular de carreteras',
                'id_area' => $areaTransporte->id,
                'plazo_dias' => 5,
                'requisitos' => 'Plan de mantenimiento, Cronograma anual, Especificaciones técnicas, Medidas de seguridad vial',
                'activo' => true
            ]
        ];

        foreach ($tramitesObras as $tramite) {
            TipoTramite::create($tramite);
        }
    }
}
