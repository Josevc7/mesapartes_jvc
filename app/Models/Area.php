<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    // Usar clave primaria personalizada
    protected $primaryKey = 'id_area';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'id_jefe',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relaciones esenciales
    public function jefe()
    {
        return $this->belongsTo(User::class, 'id_jefe', 'id');
    }

    public function funcionarios()
    {
        return $this->hasMany(User::class, 'id_area', 'id_area');
    }

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'id_area', 'id_area');
    }

    public function tipoTramites()
    {
        return $this->hasMany(TipoTramite::class, 'id_area', 'id_area');
    }

    public function derivacionesOrigen()
    {
        return $this->hasMany(Derivacion::class, 'id_area_origen', 'id_area');
    }

    public function derivacionesDestino()
    {
        return $this->hasMany(Derivacion::class, 'id_area_destino', 'id_area');
    }
}
