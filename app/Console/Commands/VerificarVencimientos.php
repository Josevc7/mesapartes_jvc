<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Expediente;
use App\Models\Derivacion;
use App\Services\NotificacionService;
use Carbon\Carbon;

class VerificarVencimientos extends Command
{
    protected $signature = 'expedientes:verificar-vencimientos';
    protected $description = 'Verificar expedientes próximos a vencer y enviar alertas';

    public function handle()
    {
        $notificacionService = new NotificacionService();
        
        // Expedientes que vencen en 2 días
        $proximosVencer = Derivacion::with(['expediente', 'funcionarioAsignado'])
            ->where('estado', 'recibido')
            ->whereRaw('DATE_ADD(fecha_derivacion, INTERVAL plazo_dias DAY) <= DATE_ADD(NOW(), INTERVAL 2 DAY)')
            ->whereRaw('DATE_ADD(fecha_derivacion, INTERVAL plazo_dias DAY) > NOW()')
            ->get();

        foreach ($proximosVencer as $derivacion) {
            // Enviar alerta al funcionario
            if ($derivacion->funcionarioAsignado) {
                $notificacionService->enviarAlertaVencimiento($derivacion);
            }
        }

        // Expedientes vencidos
        $vencidos = Derivacion::with(['expediente'])
            ->where('estado', 'recibido')
            ->whereRaw('DATE_ADD(fecha_derivacion, INTERVAL plazo_dias DAY) < NOW()')
            ->get();

        foreach ($vencidos as $derivacion) {
            $derivacion->update(['estado' => 'vencido']);
            $notificacionService->enviarAlertaVencido($derivacion);
        }

        $this->info("Verificación completada. Próximos a vencer: {$proximosVencer->count()}, Vencidos: {$vencidos->count()}");
    }
}