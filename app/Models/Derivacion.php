<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Derivacion extends Model
{
    protected $table = 'derivaciones';
    protected $primaryKey = 'id_derivacion';
    
    protected $fillable = [
        'id_expediente',
        'id_area_origen',
        'id_area_destino',
        'id_funcionario_origen',
        'id_funcionario_destino',
        'id_funcionario_asignado',
        'fecha_derivacion',
        'fecha_recepcion',
        'fecha_limite',
        'plazo_dias',
        'estado',
        'observaciones'
    ];

    protected $casts = [
        //'fecha_derivacion' => 'date',
        //'fecha_recepcion' => 'date',
        //'fecha_limite' => 'date'
          'fecha_derivacion' => 'datetime',
          'fecha_recepcion'  => 'datetime',
          'fecha_limite'     => 'date', // o datetime si también quieres hora
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'id_expediente', 'id_expediente');
    }

    public function areaOrigen()
    {
        return $this->belongsTo(Area::class, 'id_area_origen', 'id_area');
    }

    public function areaDestino()
    {
        return $this->belongsTo(Area::class, 'id_area_destino', 'id_area');
    }

    // Alias para compatibilidad - apunta a areaDestino
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area_destino', 'id_area');
    }

    public function funcionarioOrigen()
    {
        return $this->belongsTo(User::class, 'id_funcionario_origen', 'id');
    }

    public function funcionarioDestino()
    {
        return $this->belongsTo(User::class, 'id_funcionario_destino', 'id');
    }

    public function funcionarioAsignado()
    {
        return $this->belongsTo(User::class, 'id_funcionario_asignado', 'id');
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'id_derivacion', 'id_derivacion');
    }
}