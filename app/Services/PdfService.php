<?php

namespace App\Services;

use App\Models\Expediente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    /**
     * Generar PDF de acuse de recibo
     */
    public function generarAcuseRecibo(Expediente $expediente): string
    {
        $data = [
            'expediente' => $expediente,
            'fecha_generacion' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = Pdf::loadView('pdf.acuse-recibo', $data);
        
        $filename = 'acuses/' . $expediente->codigo_expediente . '_acuse.pdf';
        $pdfContent = $pdf->output();
        
        Storage::put($filename, $pdfContent);
        
        return $filename;
    }

    /**
     * Generar PDF de constancia de trámite
     */
    public function generarConstanciaTramite(Expediente $expediente): string
    {
        $data = [
            'expediente' => $expediente,
            'fecha_generacion' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = Pdf::loadView('pdf.constancia-tramite', $data);
        
        $filename = 'constancias/' . $expediente->codigo_expediente . '_constancia.pdf';
        $pdfContent = $pdf->output();
        
        Storage::put($filename, $pdfContent);
        
        return $filename;
    }
}