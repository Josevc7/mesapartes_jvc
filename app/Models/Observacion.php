<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Observacion extends Model
{
    protected $table = 'observaciones';
    
    // Nueva llave primaria
    protected $primaryKey = 'id_observacion';
    
    protected $fillable = [
        'id_expediente',
        'id_usuario',
        'tipo',
        'descripcion',
        'fecha_limite',
        'estado'
    ];

    protected $casts = [
        'fecha_limite' => 'date'
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'id_expediente', 'id_expediente');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}