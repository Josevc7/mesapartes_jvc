<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialExpediente extends Model
{
    // Nueva llave primaria
    protected $primaryKey = 'id_historial';
    
    protected $fillable = [
        'id_expediente',
        'id_usuario',
        'descripcion',
        'fecha'
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'id_expediente', 'id_expediente');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
