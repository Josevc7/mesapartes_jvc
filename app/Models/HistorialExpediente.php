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

    protected $casts = [
        'fecha' => 'datetime',
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
    const ACCION_ADJUNTO = 'ADJUNTO';
    const ACCION_DEVOLUCION_JEFE = 'DEVOLUCION_JEFE';
    const ACCION_CAMBIO_ESTADO = 'CAMBIO_ESTADO';

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
            self::ACCION_REGISTRO => 'REGISTRÓ EXPEDIENTE',
            self::ACCION_CLASIFICACION => 'CLASIFICÓ',
            self::ACCION_DERIVACION => 'DERIVÓ',
            self::ACCION_RECEPCION => 'RECEPCIONÓ EXPEDIENTE',
            self::ACCION_EN_PROCESO => 'EN PROCESO',
            self::ACCION_RESOLUCION => 'RESOLUCIÓN EMITIDA',
            self::ACCION_APROBACION => 'APROBÓ',
            self::ACCION_RECHAZO => 'RECHAZÓ',
            self::ACCION_OBSERVACION => 'OBSERVÓ',
            self::ACCION_ARCHIVO => 'ARCHIVÓ',
            self::ACCION_ASIGNACION => 'ASIGNÓ A FUNCIONARIO',
            self::ACCION_ADJUNTO => 'ADJUNTÓ DOCUMENTO',
            self::ACCION_DEVOLUCION_JEFE => 'DEVUELTO AL JEFE DE ÁREA',
            self::ACCION_CAMBIO_ESTADO => 'CAMBIÓ ESTADO',
            default => $this->accion ?? 'ACCIÓN'
        };
    }

    /**
     * Scope para filtrar historial según rol del usuario
     * El ciudadano ve información limitada (sin nombres de funcionarios ni observaciones internas)
     */
    public function scopeParaRol($query, $rol)
    {
        if ($rol === 'Ciudadano') {
            // Para ciudadano: ocultar campos sensibles
            return $query->select([
                'id_historial',
                'id_expediente',
                'id_area',
                'accion',
                'estado',
                'fecha',
                'created_at',
                'updated_at'
            ]);
        }

        // Para otros roles: mostrar todo
        return $query;
    }

    /**
     * Obtiene la descripción para vista de ciudadano (sin datos internos)
     */
    public function getDescripcionPublicaAttribute()
    {
        // Descripción genérica sin mencionar funcionarios
        return match($this->accion) {
            self::ACCION_REGISTRO => 'Expediente registrado',
            self::ACCION_CLASIFICACION => 'Expediente clasificado',
            self::ACCION_DERIVACION => 'Derivado a ' . ($this->area?->nombre ?? 'área correspondiente'),
            self::ACCION_RECEPCION => 'Expediente recepcionado',
            self::ACCION_EN_PROCESO => 'Expediente en proceso',
            self::ACCION_RESOLUCION => 'Resolución emitida',
            self::ACCION_APROBACION => 'Solicitud aprobada',
            self::ACCION_RECHAZO => 'Solicitud rechazada',
            self::ACCION_OBSERVACION => 'Expediente observado',
            self::ACCION_ARCHIVO => 'Expediente archivado',
            self::ACCION_ASIGNACION => 'Asignado para atención',
            self::ACCION_ADJUNTO => 'Documento adjuntado',
            self::ACCION_DEVOLUCION_JEFE => 'Expediente devuelto al Jefe de Área',
            self::ACCION_CAMBIO_ESTADO => 'Estado actualizado a: ' . ($this->estado ?? ''),
            default => 'Acción registrada'
        };
    }
}
