<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\Expediente;
use App\Mail\ExpedienteNotificacion;
use App\Services\PdfService;

class EnviarNotificacionExpediente implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $expediente;
    public $tipo;
    public $generarPdf;

    /**
     * Create a new job instance.
     */
    public function __construct(Expediente $expediente, string $tipo, bool $generarPdf = false)
    {
        $this->expediente = $expediente;
        $this->tipo = $tipo;
        $this->generarPdf = $generarPdf;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pdfPath = null;

        // Generar PDF si es necesario
        if ($this->generarPdf && $this->tipo === 'registro') {
            $pdfService = new PdfService();
            $pdfPath = $pdfService->generarAcuseRecibo($this->expediente);
        }

        // Enviar notificación al ciudadano
        Mail::to($this->expediente->ciudadano->email)
            ->send(new ExpedienteNotificacion($this->expediente, $this->tipo, $pdfPath));

        // Enviar notificación adicional según el tipo
        $this->enviarNotificacionesAdicionales();
    }

    /**
     * Enviar notificaciones adicionales según el tipo
     */
    private function enviarNotificacionesAdicionales(): void
    {
        switch ($this->tipo) {
            case 'derivacion':
                // Notificar al funcionario asignado
                $derivacion = $this->expediente->derivaciones->last();
                if ($derivacion && $derivacion->funcionario_asignado_id) {
                    $funcionario = \App\Models\User::find($derivacion->funcionario_asignado_id);
                    if ($funcionario) {
                        Mail::to($funcionario->email)
                            ->send(new ExpedienteNotificacion($this->expediente, 'asignacion'));
                    }
                }
                break;

            case 'resolucion':
                // Notificar a mesa de partes
                $mesaPartes = \App\Models\User::whereHas('role', function($q) {
                    $q->where('nombre', 'Mesa de Partes');
                })->get();
                
                foreach ($mesaPartes as $usuario) {
                    Mail::to($usuario->email)
                        ->send(new ExpedienteNotificacion($this->expediente, 'para_archivo'));
                }
                break;
        }
    }
}