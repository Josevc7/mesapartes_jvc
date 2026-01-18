<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $primaryKey = 'id_permiso';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'id_modulo',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo', 'id_modulo');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'rol_permiso', 'id_permiso', 'id_rol');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
