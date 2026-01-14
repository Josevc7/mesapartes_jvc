<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class ShowActiveSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:active {--all : Mostrar todas las sesiones incluyendo expiradas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muestra las sesiones activas del sistema con información detallada';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== SESIONES DEL SISTEMA ===');
        $this->newLine();

        // Obtener el tiempo de expiración de sesiones (en minutos)
        $sessionLifetime = config('session.lifetime');
        $expirationTime = now()->subMinutes($sessionLifetime)->timestamp;

        // Query base
        $query = DB::table('sessions')
            ->leftJoin('users', 'sessions.id_user', '=', 'users.id')
            ->select(
                'sessions.id',
                'sessions.id_user',
                'sessions.ip_address',
                'sessions.last_activity',
                'users.name as user_name',
                'users.email as user_email'
            );

        // Filtrar solo sesiones activas si no se especifica --all
        if (!$this->option('all')) {
            $query->where('sessions.last_activity', '>', $expirationTime);
        }

        $sessions = $query->orderBy('sessions.last_activity', 'desc')->get();

        if ($sessions->isEmpty()) {
            $this->warn('No hay sesiones activas en este momento.');
            return;
        }

        // Preparar datos para la tabla
        $tableData = [];
        foreach ($sessions as $session) {
            $lastActivity = Carbon::createFromTimestamp($session->last_activity);
            $isActive = $session->last_activity > $expirationTime;

            $tableData[] = [
                substr($session->id, 0, 12) . '...',
                $session->user_name ?? 'Invitado',
                $session->user_email ?? 'N/A',
                $session->ip_address ?? 'N/A',
                $lastActivity->diffForHumans(),
                $isActive ? '✓ Activa' : '✗ Expirada'
            ];
        }

        // Mostrar tabla
        $this->table(
            ['ID Sesión', 'Usuario', 'Email', 'IP', 'Última Actividad', 'Estado'],
            $tableData
        );

        // Estadísticas
        $this->newLine();
        $activeSessions = collect($sessions)->filter(fn($s) => $s->last_activity > $expirationTime)->count();
        $expiredSessions = $sessions->count() - $activeSessions;

        $this->info("📊 Estadísticas:");
        $this->line("   Total de sesiones: {$sessions->count()}");
        $this->line("   Sesiones activas: {$activeSessions}");
        if ($this->option('all') && $expiredSessions > 0) {
            $this->line("   Sesiones expiradas: {$expiredSessions}");
        }
        $this->line("   Tiempo de expiración: {$sessionLifetime} minutos");

        $this->newLine();
    }
}
