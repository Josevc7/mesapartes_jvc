<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $primaryKey = 'id_modulo';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'icono',
        'orden',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function permisos()
    {
        return $this->hasMany(Permiso::class, 'id_modulo', 'id_modulo');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
