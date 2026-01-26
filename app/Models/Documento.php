<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $primaryKey = 'id_documento';
    
    protected $fillable = [
        'id_expediente',
        'id_derivacion',
        'nombre',
        'ruta_pdf',
        'tipo',
        'tamaño_archivo'
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'id_expediente', 'id_expediente');
    }

    public function derivacion()
    {
        return $this->belongsTo(Derivacion::class, 'id_derivacion', 'id_derivacion');
    }
}