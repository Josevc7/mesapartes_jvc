<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resolucion extends Model
{
    protected $table = 'resoluciones';
    protected $primaryKey = 'id_resolucion';
    
    protected $fillable = [
        'id_expediente',
        'id_funcionario_resolutor',
        'numero_resolucion',
        'tipo_resolucion',
        'fundamento_legal',
        'observaciones',
        'ruta_documento_resolucion',
        'fecha_resolucion',
        'fecha_notificacion',
        'notificado'
    ];

    protected $casts = [
        'fecha_resolucion' => 'datetime',
        'fecha_notificacion' => 'datetime',
        'notificado' => 'boolean'
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'id_expediente', 'id_expediente');
    }

    public function funcionarioResolutor()
    {
        return $this->belongsTo(User::class, 'id_funcionario_resolutor', 'id');
    }
}