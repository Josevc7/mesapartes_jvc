<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $primaryKey = 'id_documento';
    
    protected $fillable = [
        'id_expediente',
        'nombre',
        'ruta_pdf',
        'tipo',
        'tamaño_archivo'
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'id_expediente', 'id_expediente');
    }
}