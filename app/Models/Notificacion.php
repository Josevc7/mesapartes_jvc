<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    
    protected $fillable = [
        'id_usuario',
        'titulo',
        'mensaje',
        'expediente_codigo',
        'tipo',
        'leida'
    ];

    protected $casts = [
        'leida' => 'boolean'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_codigo', 'codigo_expediente');
    }

    public function marcarComoLeida()
    {
        $this->update(['leida' => true]);
    }

    /**
     * Crea una nueva notificación para un usuario específico
     * @param int $usuarioId - ID del usuario que recibirá la notificación
     * @param string $titulo - Título de la notificación
     * @param string $mensaje - Contenido del mensaje
     * @param string|null $expedienteCodigo - Código del expediente relacionado (opcional)
     * @param string $tipo - Tipo de notificación (info, warning, success, error)
     * @return \App\Models\Notificacion
     */
    public static function crearNotificacion($usuarioId, $titulo, $mensaje, $expedienteCodigo = null, $tipo = 'info')
    {
        // Crear nueva notificación en la base de datos
        return self::create([
            'id_usuario' => $usuarioId,              // Usuario destinatario (sin coma extra)
            'titulo' => $titulo,                     // Título de la notificación
            'mensaje' => $mensaje,                   // Contenido del mensaje
            'expediente_codigo' => $expedienteCodigo, // Expediente relacionado (opcional)
            'tipo' => $tipo                          // Tipo de notificación
        ]);
    }
}