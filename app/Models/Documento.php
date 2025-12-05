<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $fillable = [
        'id_expediente',
        'nombre',
        'ruta_pdf',
        'tipo'
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'id_expediente');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
