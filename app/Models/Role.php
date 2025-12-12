<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Usar clave primaria personalizada
    protected $primaryKey = 'id_rol';
    
    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_rol', 'id_rol');
    }
}
