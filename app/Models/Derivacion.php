<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Derivacion extends Model
{
    protected $fillable = [
        'id_expediente',
        'id_area_origen',
        'id_area_destino',
        'id_funcionario_asignado',
        'id_funcionario_origen',
        'id_funcionario_destino',
        'fecha_derivacion',
        'fecha_recepcion',
        'fecha_limite',
        'estado',
        'plazo_dias',
        'observaciones'
    ];

    protected $casts = [
        'fecha_derivacion' => 'date',
        'fecha_recepcion' => 'date',
        'fecha_limite' => 'date'
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'id_expediente');
    }

    public function origenArea()
    {
        return $this->belongsTo(Area::class, 'id_area_origen');
    }

    public function destinoArea()
    {
        return $this->belongsTo(Area::class, 'id_area_destino');
    }

    public function funcionarioOrigen()
    {
        return $this->belongsTo(User::class, 'id_funcionario_origen');
    }

    public function funcionarioDestino()
    {
        return $this->belongsTo(User::class, 'id_funcionario_destino');
    }

    public function funcionario()
    {
        return $this->belongsTo(User::class, 'id_funcionario_asignado');
    }
}
