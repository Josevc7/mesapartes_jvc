<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Expediente;

class SoporteController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'usuarios_activos' => User::where('activo', true)->count(),
            'expedientes_hoy' => Expediente::whereDate('created_at', today())->count(),
            'errores_sistema' => $this->contarErrores(),
            'espacio_bd' => $this->obtenerEspacioBD()
        ];
        
        return view('soporte.dashboard', compact('stats'));
    }

    public function logs()
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];
        
        if (file_exists($logPath)) {
            $content = file_get_contents($logPath);
            $lines = array_slice(explode("\n", $content), -100);
            
            foreach ($lines as $line) {
                if (preg_match('/\[(.*?)\] (\w+)\.(\w+): (.*)/', $line, $matches)) {
                    $logs[] = [
                        'fecha' => $matches[1],
                        'nivel' => $matches[2],
                        'canal' => $matches[3],
                        'mensaje' => $matches[4]
                    ];
                }
            }
        }
        
        return view('soporte.logs', compact('logs'));
    }

    public function usuarios()
    {
        $usuarios = User::withCount('expedientesAsignados')
            ->paginate(15);
            
        return view('soporte.usuarios', compact('usuarios'));
    }

    public function resetearPassword(Request $request, $id_usuario)
    {
        $usuario = User::findOrFail($id_usuario);
        
        $request->validate([
            'nueva_password' => 'required|min:6'
        ]);
        
        $usuario->update([
            'password' => bcrypt($request->nueva_password)
        ]);
        
        Log::info('Password reseteada por soporte', [
            'usuario_afectado' => $usuario->email,
            'soporte_user' => auth()->user()->email
        ]);
        
        return back()->with('success', 'Contraseña actualizada correctamente');
    }

    public function toggleUsuario($id_usuario)
    {
        $usuario = User::findOrFail($id_usuario);
        $usuario->update(['activo' => !$usuario->activo]);
        
        Log::info('Usuario ' . ($usuario->activo ? 'activado' : 'desactivado'), [
            'usuario_afectado' => $usuario->email,
            'soporte_user' => auth()->user()->email
        ]);
        
        return back()->with('success', 'Estado del usuario actualizado');
    }

    public function respaldo()
    {
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $path = storage_path('app/backups/' . $filename);
            
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            
            $command = sprintf(
                'mysqldump -h %s -u %s -p%s %s > %s',
                config('database.connections.mysql.host'),
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
                $path
            );
            
            exec($command);
            
            Log::info('Respaldo creado', ['archivo' => $filename, 'usuario' => auth()->user()->email]);
            
            return back()->with('success', 'Respaldo creado: ' . $filename);
        } catch (\Exception $e) {
            Log::error('Error al crear respaldo', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error al crear respaldo');
        }
    }

    public function limpiarCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            Log::info('Cache limpiado por soporte', ['usuario' => auth()->user()->email]);
            
            return back()->with('success', 'Cache limpiado correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al limpiar cache');
        }
    }

    private function contarErrores()
    {
        $logPath = storage_path('logs/laravel.log');
        if (!file_exists($logPath)) return 0;
        
        $content = file_get_contents($logPath);
        return substr_count($content, '.ERROR:');
    }

    private function obtenerEspacioBD()
    {
        try {
            $result = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'size_mb' FROM information_schema.tables WHERE table_schema = ?", [config('database.connections.mysql.database')]);
            return $result[0]->size_mb ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}