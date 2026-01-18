<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_rol', 'id_rol');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'rol_permiso', 'id_rol', 'id_permiso');
    }

    public function tienePermiso($slugPermiso)
    {
        // Administrador tiene todos los permisos
        if ($this->nombre === 'Administrador') {
            return true;
        }
        return $this->permisos()->where('slug', $slugPermiso)->where('activo', true)->exists();
    }

    public function tieneAlgunPermiso(array $slugsPermisos)
    {
        if ($this->nombre === 'Administrador') {
            return true;
        }
        return $this->permisos()->whereIn('slug', $slugsPermisos)->where('activo', true)->exists();
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function sincronizarPermisos(array $permisosIds)
    {
        $this->permisos()->sync($permisosIds);
    }
}
