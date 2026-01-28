<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialExpediente extends Model
{
    // Nueva llave primaria
    protected $primaryKey = 'id_historial';
    
    protected $fillable = [
        'id_expediente',
        'id_usuario',
        'id_area',
        'accion',
        'descripcion',
        'detalle',
        'estado',
        'fecha'
    ];

    // Constantes para tipos de acción
    const ACCION_REGISTRO = 'REGISTRO';
    const ACCION_CLASIFICACION = 'CLASIFICACION';
    const ACCION_DERIVACION = 'DERIVACION';
    const ACCION_RECEPCION = 'RECEPCION';
    const ACCION_EN_PROCESO = 'EN_PROCESO';
    const ACCION_RESOLUCION = 'RESOLUCION';
    const ACCION_APROBACION = 'APROBACION';
    const ACCION_RECHAZO = 'RECHAZO';
    const ACCION_OBSERVACION = 'OBSERVACION';
    const ACCION_ARCHIVO = 'ARCHIVO';
    const ACCION_ASIGNACION = 'ASIGNACION';

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'id_expediente', 'id_expediente');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    /**
     * Obtiene el nombre legible de la acción
     */
    public function getAccionLegibleAttribute()
    {
        return match($this->accion) {
            self::ACCION_REGISTRO => 'REGISTRÓ',
            self::ACCION_CLASIFICACION => 'CLASIFICÓ',
            self::ACCION_DERIVACION => 'DERIVÓ',
            self::ACCION_RECEPCION => 'RECEPCIONÓ',
            self::ACCION_EN_PROCESO => 'CAMBIÓ ESTADO',
            self::ACCION_RESOLUCION => 'RESOLVIÓ',
            self::ACCION_APROBACION => 'APROBÓ',
            self::ACCION_RECHAZO => 'RECHAZÓ',
            self::ACCION_OBSERVACION => 'OBSERVÓ',
            self::ACCION_ARCHIVO => 'ARCHIVÓ',
            self::ACCION_ASIGNACION => 'ASIGNÓ',
            default => $this->accion ?? 'ACCIÓN'
        };
    }
}
