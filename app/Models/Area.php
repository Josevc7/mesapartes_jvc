<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'id_jefe',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relaciones
    public function jefe()
    {
        return $this->belongsTo(User::class, 'id_jefe');
    }

    public function funcionarios()
    {
        return $this->hasMany(User::class, 'id_area');
    }

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'id_area');
    }

    public function tipoTramites()
    {
        return $this->hasMany(TipoTramite::class, 'id_area');
    }

    public function derivacionesOrigen()
    {
        return $this->hasMany(Derivacion::class, 'id_area_origen');
    }

    public function derivacionesDestino()
    {
        return $this->hasMany(Derivacion::class, 'id_area_destino');
    }

    public function metas()
    {
        return $this->hasMany(Meta::class, 'id_area');
    }
}
