<?php

namespace App\Constants;

use App\Enums\EstadoExpediente;
use App\Enums\PrioridadExpediente;

/**
 * Constantes para Expedientes
 *
 * @deprecated Usar App\Enums\EstadoExpediente y App\Enums\PrioridadExpediente en su lugar.
 *             Esta clase se mantiene para compatibilidad con código legacy.
 */
class ExpedienteConstants
{
    // Estados del expediente - LISTA COMPLETA
    const ESTADO_RECEPCIONADO = 'recepcionado';
    const ESTADO_REGISTRADO = 'registrado';
    const ESTADO_CLASIFICADO = 'clasificado';
    const ESTADO_DERIVADO = 'derivado';
    const ESTADO_ASIGNADO = 'asignado';         // NUEVO - agregado
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_EN_REVISION = 'en_revision';   // NUEVO - agregado
    const ESTADO_OBSERVADO = 'observado';
    const ESTADO_RESUELTO = 'resuelto';
    const ESTADO_APROBADO = 'aprobado';         // NUEVO - agregado
    const ESTADO_RECHAZADO = 'rechazado';       // NUEVO - agregado
    const ESTADO_NOTIFICADO = 'notificado';
    const ESTADO_ARCHIVADO = 'archivado';

    // Prioridades
    const PRIORIDAD_BAJA = 'baja';
    const PRIORIDAD_NORMAL = 'normal';
    const PRIORIDAD_ALTA = 'alta';
    const PRIORIDAD_URGENTE = 'urgente';

    // Canales de ingreso
    const CANAL_PRESENCIAL = 'presencial';
    const CANAL_VIRTUAL = 'virtual';
    const CANAL_CORREO = 'correo';

    /**
     * Obtener todos los estados
     * @deprecated Use EstadoExpediente::values()
     */
    public static function getEstados(): array
    {
        return EstadoExpediente::values();
    }

    /**
     * Estados que indican expediente pendiente/activo
     */
    public static function getEstadosPendientes(): array
    {
        return EstadoExpediente::estadosPendientes();
    }

    /**
     * Estados que indican expediente finalizado
     */
    public static function getEstadosFinalizados(): array
    {
        return EstadoExpediente::estadosFinalizados();
    }

    /**
     * Estados activos (no finalizados)
     */
    public static function getEstadosActivos(): array
    {
        return EstadoExpediente::estadosActivos();
    }

    /**
     * Obtener todas las prioridades
     * @deprecated Use PrioridadExpediente::values()
     */
    public static function getPrioridades(): array
    {
        return PrioridadExpediente::values();
    }

    /**
     * Obtener todos los canales
     */
    public static function getCanales(): array
    {
        return [
            self::CANAL_PRESENCIAL,
            self::CANAL_VIRTUAL,
            self::CANAL_CORREO,
        ];
    }
}
