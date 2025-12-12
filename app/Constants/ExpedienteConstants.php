<?php

namespace App\Constants;

class ExpedienteConstants
{
    // Estados del expediente
    const ESTADO_RECEPCIONADO = 'recepcionado';
    const ESTADO_REGISTRADO = 'registrado';
    const ESTADO_CLASIFICADO = 'clasificado';
    const ESTADO_DERIVADO = 'derivado';
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_OBSERVADO = 'observado';
    const ESTADO_RESUELTO = 'resuelto';
    const ESTADO_NOTIFICADO = 'notificado';
    const ESTADO_ARCHIVADO = 'archivado';

    // Prioridades
    const PRIORIDAD_BAJA = 'baja';
    const PRIORIDAD_MEDIA = 'media';
    const PRIORIDAD_ALTA = 'alta';
    const PRIORIDAD_URGENTE = 'urgente';

    // Canales de ingreso
    const CANAL_PRESENCIAL = 'presencial';
    const CANAL_VIRTUAL = 'virtual';
    const CANAL_CORREO = 'correo';

    // Arrays para validación
    public static function getEstados(): array
    {
        return [
            self::ESTADO_RECEPCIONADO,
            self::ESTADO_REGISTRADO,
            self::ESTADO_CLASIFICADO,
            self::ESTADO_DERIVADO,
            self::ESTADO_EN_PROCESO,
            self::ESTADO_OBSERVADO,
            self::ESTADO_RESUELTO,
            self::ESTADO_NOTIFICADO,
            self::ESTADO_ARCHIVADO,
        ];
    }

    public static function getPrioridades(): array
    {
        return [
            self::PRIORIDAD_BAJA,
            self::PRIORIDAD_MEDIA,
            self::PRIORIDAD_ALTA,
            self::PRIORIDAD_URGENTE,
        ];
    }

    public static function getCanales(): array
    {
        return [
            self::CANAL_PRESENCIAL,
            self::CANAL_VIRTUAL,
            self::CANAL_CORREO,
        ];
    }
}