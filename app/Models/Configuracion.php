<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';
    
    // Nueva llave primaria
    protected $primaryKey = 'id_configuracion';
    
    protected $fillable = [
        'clave',
        'valor',
        'descripcion',
        'tipo'
    ];

    public static function obtener(string $clave, $default = null)
    {
        $config = static::where('clave', $clave)->first();
        
        if (!$config) {
            return $default;
        }

        return match($config->tipo) {
            'numero' => (int) $config->valor,
            'booleano' => $config->valor === 'true',
            'json' => json_decode($config->valor, true),
            default => $config->valor
        };
    }
}