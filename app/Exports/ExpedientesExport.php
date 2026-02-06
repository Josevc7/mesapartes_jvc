<?php

namespace App\Exports;

use App\Models\Expediente;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpedientesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $estado;

    public function __construct($fechaInicio = null, $fechaFin = null, $estado = null)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->estado = $estado;
    }

    public function collection()
    {
        $query = Expediente::with(['ciudadano', 'tipoTramite', 'derivaciones.area']);

        if ($this->fechaInicio) {
            $query->where('fecha_registro', '>=', $this->fechaInicio);
        }

        if ($this->fechaFin) {
            $query->where('fecha_registro', '<=', $this->fechaFin);
        }

        if ($this->estado) {
            $query->whereHas('estadoExpediente', fn($q) => $q->where('slug', $this->estado));
        }

        return $query->orderBy('fecha_registro', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Código Expediente',
            'Fecha Registro',
            'Ciudadano',
            'DNI',
            'Tipo Trámite',
            'Asunto',
            'Estado',
            'Prioridad',
            'Canal',
            'Área Actual',
            'Días Transcurridos'
        ];
    }

    public function map($expediente): array
    {
        $areaActual = $expediente->derivaciones->last()?->area?->nombre ?? 'Mesa de Partes';
        $diasTranscurridos = now()->diffInDays($expediente->fecha_registro);

        return [
            $expediente->codigo_expediente,
            $expediente->fecha_registro,
            $expediente->ciudadano->name,
            $expediente->ciudadano->dni,
            $expediente->tipoTramite->nombre,
            $expediente->asunto,
            ucfirst(str_replace('_', ' ', $expediente->estado)),
            ucfirst($expediente->prioridad),
            ucfirst($expediente->canal),
            $areaActual,
            $diasTranscurridos
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}