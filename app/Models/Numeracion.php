<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Numeracion extends Model
{
    protected $table = 'numeracion';
    
    protected $fillable = [
        'año',
        'ultimo_numero'
    ];

    protected $casts = [
        'año' => 'integer',
        'ultimo_numero' => 'integer'
    ];
}