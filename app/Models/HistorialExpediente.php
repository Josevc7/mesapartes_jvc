<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialExpediente extends Model
{
    protected $fillable = [
        'id_expediente',
        'id_usuario',
        'descripcion',
        'fecha'
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
