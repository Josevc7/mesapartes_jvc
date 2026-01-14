<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:clear {--force : No pedir confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia las sesiones expiradas de la base de datos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Obtener el tiempo de expiración de sesiones (en minutos)
        $sessionLifetime = config('session.lifetime');
        $expirationTime = now()->subMinutes($sessionLifetime)->timestamp;

        // Contar sesiones expiradas
        $expiredCount = DB::table('sessions')
            ->where('last_activity', '<=', $expirationTime)
            ->count();

        if ($expiredCount === 0) {
            $this->info('✓ No hay sesiones expiradas para limpiar.');
            return;
        }

        $this->warn("Se encontraron {$expiredCount} sesiones expiradas.");

        // Pedir confirmación si no se usa --force
        if (!$this->option('force')) {
            if (!$this->confirm('¿Deseas eliminar estas sesiones?', true)) {
                $this->info('Operación cancelada.');
                return;
            }
        }

        // Eliminar sesiones expiradas
        $deleted = DB::table('sessions')
            ->where('last_activity', '<=', $expirationTime)
            ->delete();

        $this->info("✓ Se eliminaron {$deleted} sesiones expiradas correctamente.");
    }
}
