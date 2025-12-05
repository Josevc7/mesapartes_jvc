<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoTramite;
use App\Models\Area;

class TipoTramiteBreveteSeeder extends Seeder
{
    public function run(): void
    {
        // Área de Licencias de Conducir
        $areaLicencias = Area::where('nombre', 'like', '%Licencias%')->first() ?? Area::first();

        $tramitesBrevete = [
            // EXÁMENES MÁS FRECUENTES
            [
                'nombre' => 'Examen de Conocimientos - Licencia A-I',
                'descripcion' => 'Examen teórico para licencia de conducir categoría A-I (motocicleta hasta 125cc)',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 1,
                'requisitos' => 'DNI original, Certificado médico, Pago de derechos, Declaración jurada',
                'activo' => true
            ],
            [
                'nombre' => 'Examen de Conocimientos - Licencia A-IIa',
                'descripcion' => 'Examen teórico para licencia de conducir categoría A-IIa (motocicleta hasta 250cc)',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 1,
                'requisitos' => 'DNI original, Certificado médico, Pago de derechos, Declaración jurada',
                'activo' => true
            ],
            [
                'nombre' => 'Examen de Conocimientos - Licencia B-I',
                'descripcion' => 'Examen teórico para licencia de conducir categoría B-I (automóvil particular)',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 1,
                'requisitos' => 'DNI original, Certificado médico, Pago de derechos, Declaración jurada',
                'activo' => true
            ],
            [
                'nombre' => 'Examen de Conocimientos - Licencia B-IIa',
                'descripcion' => 'Examen teórico para licencia de conducir categoría B-IIa (taxi, remisse)',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 1,
                'requisitos' => 'DNI original, Certificado médico, Pago de derechos, Licencia B-I vigente',
                'activo' => true
            ],
            [
                'nombre' => 'Examen de Manejo - Licencia A-I',
                'descripcion' => 'Examen práctico de manejo para licencia categoría A-I (motocicleta)',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 1,
                'requisitos' => 'Examen de conocimientos aprobado, DNI original, Certificado médico vigente',
                'activo' => true
            ],
            [
                'nombre' => 'Examen de Manejo - Licencia B-I',
                'descripcion' => 'Examen práctico de manejo para licencia categoría B-I (automóvil)',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 1,
                'requisitos' => 'Examen de conocimientos aprobado, DNI original, Certificado médico vigente',
                'activo' => true
            ],
            [
                'nombre' => 'Cita para Examen de Conocimientos',
                'descripcion' => 'Programación de cita para rendir examen teórico de reglas de tránsito',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 3,
                'requisitos' => 'DNI original, Certificado médico vigente, Pago de derechos',
                'activo' => true
            ],
            [
                'nombre' => 'Cita para Examen de Manejo',
                'descripcion' => 'Programación de cita para rendir examen práctico de conducción',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 5,
                'requisitos' => 'Examen de conocimientos aprobado, DNI original, Vehículo para examen',
                'activo' => true
            ],
            [
                'nombre' => 'Reprogramación de Examen',
                'descripcion' => 'Solicitud de nueva fecha para examen de conocimientos o manejo',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 2,
                'requisitos' => 'DNI original, Justificación de inasistencia, Pago adicional si corresponde',
                'activo' => true
            ],
            [
                'nombre' => 'Certificado de Aptitud Psicosomática',
                'descripcion' => 'Evaluación médica y psicológica para obtener licencia de conducir',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 1,
                'requisitos' => 'DNI original, Pago de derechos, Formulario de evaluación',
                'activo' => true
            ],
            [
                'nombre' => 'Expedición de Licencia de Conducir',
                'descripcion' => 'Emisión de licencia física tras aprobar exámenes requeridos',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 3,
                'requisitos' => 'Exámenes aprobados, DNI original, Certificado médico, Pago completo',
                'activo' => true
            ],
            [
                'nombre' => 'Renovación de Licencia de Conducir',
                'descripcion' => 'Renovación de licencia vencida o próxima a vencer',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 2,
                'requisitos' => 'DNI original, Licencia anterior, Certificado médico actualizado, Pago de derechos',
                'activo' => true
            ],
            [
                'nombre' => 'Duplicado de Licencia por Pérdida',
                'descripcion' => 'Emisión de duplicado por pérdida, robo o deterioro de licencia',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 3,
                'requisitos' => 'DNI original, Denuncia policial (si es robo), Declaración jurada, Pago de derechos',
                'activo' => true
            ],
            [
                'nombre' => 'Cambio de Categoría de Licencia',
                'descripcion' => 'Solicitud para obtener categoría superior de licencia de conducir',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 5,
                'requisitos' => 'Licencia actual vigente, Exámenes adicionales, Certificado médico, Pago de derechos',
                'activo' => true
            ],
            [
                'nombre' => 'Constancia de No Adeudo',
                'descripcion' => 'Certificado de no tener multas o infracciones pendientes',
                'id_area' => $areaLicencias->id,
                'plazo_dias' => 1,
                'requisitos' => 'DNI original, Licencia de conducir, Pago de derechos',
                'activo' => true
            ]
        ];

        foreach ($tramitesBrevete as $tramite) {
            TipoTramite::create($tramite);
        }
    }
}