<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Numeracion extends Model
{
    protected $table = 'numeracion';
    protected $primaryKey = 'id_numeracion';

    protected $fillable = [
        'anio',
        'id_area',
        'ultimo_numero',
        'prefijo'
    ];

    /**
     * Relación con el área
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }
}