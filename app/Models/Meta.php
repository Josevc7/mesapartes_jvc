<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    protected $fillable = [
        'id_area',
        'descripcion',
        'tipo',
        'valor_meta',
        'valor_actual',
        'periodo',
        'fecha_inicio',
        'fecha_fin',
        'activa'
    ];

    protected $casts = [
        'valor_meta' => 'decimal:2',
        'valor_actual' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activa' => 'boolean'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function getPorcentajeProgresoAttribute()
    {
        if ($this->valor_meta == 0) return 0;
        return min(($this->valor_actual / $this->valor_meta) * 100, 100);
    }

    public function actualizarProgreso($nuevoValor)
    {
        $this->update(['valor_actual' => $nuevoValor]);
    }

    public function estaVencida()
    {
        return now()->gt($this->fecha_fin);
    }

    public function estaCumplida()
    {
        return $this->valor_actual >= $this->valor_meta;
    }
}