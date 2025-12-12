<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    // Usar clave primaria personalizada
    protected $primaryKey = 'id_persona';
    
    protected $fillable = [
        'tipo_documento',
        'numero_documento',
        'tipo_persona',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'razon_social',
        'representante_legal',
        'telefono',
        'email',
        'direccion',
        'distrito',
        'provincia',
        'departamento',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Accessor para nombre completo
    public function getNombreCompletoAttribute()
    {
        if ($this->tipo_persona === 'NATURAL') {
            return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
        }
        return $this->razon_social;
    }

    // Relaciones
    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'id_persona', 'id_persona');
    }

    public function usuario()
    {
        return $this->hasOne(User::class, 'id_persona', 'id_persona');
    }

    // Scopes
    public function scopeNaturales($query)
    {
        return $query->where('tipo_persona', 'NATURAL');
    }

    public function scopeJuridicas($query)
    {
        return $query->where('tipo_persona', 'JURIDICA');
    }
}