<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoTramite extends Model
{
    // Usar clave primaria personalizada
    protected $primaryKey = 'id_tipo_tramite';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'plazo_dias',
        'id_area',
        'requisitos',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'id_tipo_tramite', 'id_tipo_tramite');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }
}
