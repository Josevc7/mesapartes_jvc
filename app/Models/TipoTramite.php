<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoTramite extends Model
{
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
        return $this->hasMany(Expediente::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }
}
