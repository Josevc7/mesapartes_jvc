<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoExpediente extends Model
{
    protected $table = 'estados_expediente';
    protected $primaryKey = 'id_estado';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'color',
        'icono',
        'orden',
        'es_inicial',
        'es_final',
        'requiere_accion',
        'activo'
    ];

    protected $casts = [
        'es_inicial' => 'boolean',
        'es_final' => 'boolean',
        'requiere_accion' => 'boolean',
        'activo' => 'boolean'
    ];

    public function transicionesOrigen()
    {
        return $this->hasMany(TransicionEstado::class, 'id_estado_origen', 'id_estado');
    }

    public function transicionesDestino()
    {
        return $this->hasMany(TransicionEstado::class, 'id_estado_destino', 'id_estado');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true)->orderBy('orden');
    }

    public function scopeIniciales($query)
    {
        return $query->where('es_inicial', true)->where('activo', true);
    }

    public function scopeFinales($query)
    {
        return $query->where('es_final', true)->where('activo', true);
    }

    public function getBadgeHtmlAttribute()
    {
        return '<span class="badge" style="background-color: ' . $this->color . '">' . $this->nombre . '</span>';
    }

    /**
     * Retorna los slugs de estados pendientes (no finalizados)
     */
    public static function estadosPendientes(): array
    {
        return ['derivado', 'en_proceso', 'asignado', 'observado'];
    }

    /**
     * Retorna los slugs de estados finalizados
     */
    public static function estadosFinalizados(): array
    {
        return ['resuelto', 'aprobado', 'notificado', 'archivado'];
    }
}
