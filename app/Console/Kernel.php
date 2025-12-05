<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\RespaldoAutomatico::class,
        Commands\EnviarRecordatorios::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Respaldo automático diario a las 2:00 AM
        $schedule->command('respaldo:automatico')
                 ->dailyAt('02:00')
                 ->withoutOverlapping();

        // Recordatorios cada 4 horas en horario laboral
        $schedule->command('recordatorios:enviar')
                 ->cron('0 8,12,16 * * 1-5')
                 ->withoutOverlapping();

        // Reasignación automática por carga cada lunes a las 8:00 AM
        $schedule->call(function () {
            app(\App\Services\ReasignacionService::class)->reasignarPorCarga();
        })->weeklyOn(1, '08:00');

        // Reasignación por vencimiento diaria a las 9:00 AM
        $schedule->call(function () {
            app(\App\Services\ReasignacionService::class)->reasignarPorVencimiento();
        })->dailyAt('09:00');

        // Limpiar logs antiguos semanalmente
        $schedule->command('log:clear')
                 ->weekly()
                 ->sundays()
                 ->at('03:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}