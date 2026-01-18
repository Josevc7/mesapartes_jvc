<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransicionEstado extends Model
{
    protected $table = 'transiciones_estado';
    protected $primaryKey = 'id_transicion';

    protected $fillable = [
        'id_estado_origen',
        'id_estado_destino',
        'nombre_accion',
        'roles_permitidos',
        'activo'
    ];

    protected $casts = [
        'roles_permitidos' => 'array',
        'activo' => 'boolean'
    ];

    public function estadoOrigen()
    {
        return $this->belongsTo(EstadoExpediente::class, 'id_estado_origen', 'id_estado');
    }

    public function estadoDestino()
    {
        return $this->belongsTo(EstadoExpediente::class, 'id_estado_destino', 'id_estado');
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function puedeTransicionar($rolId)
    {
        if (empty($this->roles_permitidos)) {
            return true; // Si no hay restricción, todos pueden
        }
        return in_array($rolId, $this->roles_permitidos);
    }
}
