<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditoriaService
{
    public static function registrar($accion, $tabla, $registro_id = null, $datos_anteriores = null, $datos_nuevos = null)
    {
        try {
            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => $accion,
                'tabla' => $tabla,
                'registro_id' => $registro_id,
                'datos_anteriores' => $datos_anteriores ? json_encode($datos_anteriores) : null,
                'datos_nuevos' => $datos_nuevos ? json_encode($datos_nuevos) : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'fecha' => now()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error registrando auditoría: ' . $e->getMessage());
        }
    }

    public static function expedienteCreado($expediente)
    {
        self::registrar('CREAR', 'expedientes', $expediente->id, null, $expediente->toArray());
    }

    public static function expedienteActualizado($expediente, $datosAnteriores)
    {
        self::registrar('ACTUALIZAR', 'expedientes', $expediente->id, $datosAnteriores, $expediente->toArray());
    }

    public static function usuarioLogin($usuario)
    {
        self::registrar('LOGIN', 'users', $usuario->id, null, ['email' => $usuario->email]);
    }

    public static function usuarioLogout($usuario)
    {
        self::registrar('LOGOUT', 'users', $usuario->id, null, ['email' => $usuario->email]);
    }

    public static function passwordReset($usuario)
    {
        self::registrar('RESET_PASSWORD', 'users', $usuario->id, null, ['email' => $usuario->email]);
    }
}