<?php

namespace App\Exports;

use App\Models\Expediente;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TiemposAtencionExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($fechaInicio = null, $fechaFin = null)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function collection()
    {
        $query = Expediente::with(['ciudadano', 'tipoTramite', 'derivaciones'])
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto', 'archivado']));

        if ($this->fechaInicio) {
            $query->where('fecha_registro', '>=', $this->fechaInicio);
        }

        if ($this->fechaFin) {
            $query->where('fecha_registro', '<=', $this->fechaFin);
        }

        return $query->orderBy('fecha_registro', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Código Expediente',
            'Tipo Trámite',
            'Fecha Registro',
            'Fecha Resolución',
            'Plazo Establecido (días)',
            'Tiempo Real (días)',
            'Estado Cumplimiento',
            'Diferencia (días)',
            'Ciudadano',
            'Área que Resolvió'
        ];
    }

    public function map($expediente): array
    {
        $fechaResolucion = $expediente->updated_at;
        $tiempoReal = $fechaResolucion->diffInDays($expediente->fecha_registro);
        $plazoEstablecido = $expediente->tipoTramite->plazo_dias;
        $diferencia = $tiempoReal - $plazoEstablecido;
        $cumplimiento = $diferencia <= 0 ? 'A Tiempo' : 'Fuera de Plazo';
        $areaResolucion = $expediente->derivaciones->last()?->area?->nombre ?? 'Mesa de Partes';

        return [
            $expediente->codigo_expediente,
            $expediente->tipoTramite->nombre,
            $expediente->fecha_registro,
            $fechaResolucion->format('Y-m-d'),
            $plazoEstablecido,
            $tiempoReal,
            $cumplimiento,
            $diferencia,
            $expediente->ciudadano->name,
            $areaResolucion
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}