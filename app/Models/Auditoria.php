<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';
    
    // Nueva llave primaria
    protected $primaryKey = 'id_auditoria';
    
    protected $fillable = [
        'id_usuario',
        'accion',
        'tabla',
        'id_registro',
        'datos_anteriores',
        'datos_nuevos',
        'ip',
        'user_agent'
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}