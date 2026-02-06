<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Expediente;
use App\Services\NotificacionService;
use App\Enums\EstadoExpediente;
use Carbon\Carbon;

class EnviarRecordatorios extends Command
{
    protected $signature = 'recordatorios:enviar';
    protected $description = 'Enviar recordatorios automáticos por expedientes próximos a vencer';

    public function handle()
    {
        $notificacionService = new NotificacionService();

        // Expedientes que vencen en 3 días
        $expedientesPorVencer = Expediente::whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', EstadoExpediente::estadosPendientes()))
            ->whereHas('derivaciones', function($q) {
                $q->whereBetween('fecha_limite', [now(), now()->addDays(3)]);
            })
            ->with(['funcionarioAsignado', 'area'])
            ->get();

        foreach ($expedientesPorVencer as $expediente) {
            $notificacionService->notificarProximoVencimiento($expediente);
            $this->info('Recordatorio enviado para: ' . $expediente->codigo_expediente);
        }

        // Expedientes vencidos
        $expedientesVencidos = Expediente::whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', EstadoExpediente::estadosPendientes()))
            ->whereHas('derivaciones', function($q) {
                $q->where('fecha_limite', '<', now());
            })
            ->with(['funcionarioAsignado', 'area'])
            ->get();

        foreach ($expedientesVencidos as $expediente) {
            $notificacionService->notificarVencimiento($expediente);
            $this->error('Alerta vencimiento enviada para: ' . $expediente->codigo_expediente);
        }

        $this->info('Proceso de recordatorios completado');
        $this->info('Por vencer: ' . $expedientesPorVencer->count());
        $this->info('Vencidos: ' . $expedientesVencidos->count());
    }
}
