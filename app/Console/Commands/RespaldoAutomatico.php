<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RespaldoAutomatico extends Command
{
    protected $signature = 'respaldo:automatico';
    protected $description = 'Crear respaldo automático de la base de datos';

    public function handle()
    {
        try {
            $filename = 'backup_auto_' . date('Y-m-d_H-i-s') . '.sql';
            $path = storage_path('app/backups/' . $filename);
            
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            
            // Use Symfony Process for safe command execution
            $process = new Process([
                'mysqldump',
                '-h', config('database.connections.mysql.host'),
                '-u', config('database.connections.mysql.username'),
                '-p' . config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
            ]);
            
            // Redirect output to file
            $process->run();
            
            if ($process->isSuccessful()) {
                // Write output to file
                file_put_contents($path, $process->getOutput());
                Log::info('Respaldo automático creado exitosamente: ' . $filename);
                $this->info('Respaldo creado: ' . $filename);
                
                // Limpiar respaldos antiguos (mantener solo los últimos 7)
                $this->limpiarRespaldosAntiguos();
            } else {
                Log::error('Error creando respaldo automático: ' . $process->getErrorOutput());
                $this->error('Error creando respaldo: ' . $process->getErrorOutput());
            }
            
        } catch (\Exception $e) {
            Log::error('Excepción en respaldo automático: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
        }
    }

    private function limpiarRespaldosAntiguos()
    {
        $backupPath = storage_path('app/backups/');
        $files = glob($backupPath . 'backup_auto_*.sql');
        
        if (count($files) > 7) {
            // Ordenar por fecha de modificación
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Eliminar los más antiguos
            $filesToDelete = array_slice($files, 0, count($files) - 7);
            foreach ($filesToDelete as $file) {
                unlink($file);
                Log::info('Respaldo antiguo eliminado: ' . basename($file));
            }
        }
    }
}