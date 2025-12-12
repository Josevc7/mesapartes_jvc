<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Numeracion extends Model
{
    protected $table = 'numeracion';
    protected $primaryKey = 'id_numeracion';
    
    protected $fillable = [
        'año',
        'ultimo_numero',
        'prefijo'
    ];
}