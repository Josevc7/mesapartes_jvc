<?php

namespace App\Enums;

/**
 * Enum para estados de expedientes
 * Centraliza todos los valores válidos y evita inconsistencias de mayúsculas/minúsculas
 */
enum EstadoExpediente: string
{
    // Estados del flujo principal
    case PENDIENTE_RECEPCION = 'pendiente_recepcion';
    case RECEPCIONADO = 'recepcionado';
    case REGISTRADO = 'registrado';
    case CLASIFICADO = 'clasificado';
    case DERIVADO = 'derivado';
    case ASIGNADO = 'asignado';
    case EN_PROCESO = 'en_proceso';
    case DEVUELTO_JEFE = 'devuelto_jefe';
    case EN_REVISION = 'en_revision';
    case OBSERVADO = 'observado';
    case RESUELTO = 'resuelto';
    case APROBADO = 'aprobado';
    case RECHAZADO = 'rechazado';
    case NOTIFICADO = 'notificado';
    case ARCHIVADO = 'archivado';

    /**
     * Estados que indican expediente pendiente/activo
     */
    public static function estadosPendientes(): array
    {
        return [
            self::DERIVADO->value,
            self::ASIGNADO->value,
            self::EN_PROCESO->value,
            self::DEVUELTO_JEFE->value,
        ];
    }

    /**
     * Estados que indican expediente finalizado
     */
    public static function estadosFinalizados(): array
    {
        return [
            self::RESUELTO->value,
            self::APROBADO->value,
            self::ARCHIVADO->value,
        ];
    }

    /**
     * Estados que requieren aprobación del jefe
     */
    public static function estadosParaAprobar(): array
    {
        return [
            self::EN_REVISION->value,
            self::RESUELTO->value,
        ];
    }

    /**
     * Estados activos (no finalizados ni archivados)
     */
    public static function estadosActivos(): array
    {
        return [
            self::PENDIENTE_RECEPCION->value,
            self::RECEPCIONADO->value,
            self::REGISTRADO->value,
            self::CLASIFICADO->value,
            self::DERIVADO->value,
            self::ASIGNADO->value,
            self::EN_PROCESO->value,
            self::DEVUELTO_JEFE->value,
            self::EN_REVISION->value,
            self::OBSERVADO->value,
        ];
    }

    /**
     * Obtener label para mostrar en UI
     */
    public function label(): string
    {
        return match($this) {
            self::PENDIENTE_RECEPCION => 'Pendiente de Recepción',
            self::RECEPCIONADO => 'Recepcionado',
            self::REGISTRADO => 'Registrado',
            self::CLASIFICADO => 'Clasificado',
            self::DERIVADO => 'Derivado',
            self::ASIGNADO => 'Asignado',
            self::EN_PROCESO => 'En Proceso',
            self::DEVUELTO_JEFE => 'Devuelto al Jefe',
            self::EN_REVISION => 'En Revisión',
            self::OBSERVADO => 'Observado',
            self::RESUELTO => 'Resuelto',
            self::APROBADO => 'Aprobado',
            self::RECHAZADO => 'Rechazado',
            self::NOTIFICADO => 'Notificado',
            self::ARCHIVADO => 'Archivado',
        };
    }

    /**
     * Obtener color Bootstrap para badges
     */
    public function color(): string
    {
        return match($this) {
            self::PENDIENTE_RECEPCION => 'warning',
            self::RECEPCIONADO => 'secondary',
            self::REGISTRADO => 'info',
            self::CLASIFICADO => 'info',
            self::DERIVADO => 'primary',
            self::ASIGNADO => 'primary',
            self::EN_PROCESO => 'warning',
            self::DEVUELTO_JEFE => 'warning',
            self::EN_REVISION => 'warning',
            self::OBSERVADO => 'danger',
            self::RESUELTO => 'success',
            self::APROBADO => 'success',
            self::RECHAZADO => 'danger',
            self::NOTIFICADO => 'info',
            self::ARCHIVADO => 'secondary',
        };
    }

    /**
     * Obtener icono para UI
     */
    public function icono(): string
    {
        return match($this) {
            self::PENDIENTE_RECEPCION => 'hourglass-half',
            self::RECEPCIONADO => 'inbox',
            self::REGISTRADO => 'file-text',
            self::CLASIFICADO => 'folder',
            self::DERIVADO => 'send',
            self::ASIGNADO => 'user-check',
            self::EN_PROCESO => 'clock',
            self::DEVUELTO_JEFE => 'undo-alt',
            self::EN_REVISION => 'eye',
            self::OBSERVADO => 'alert-circle',
            self::RESUELTO => 'check-circle',
            self::APROBADO => 'check-square',
            self::RECHAZADO => 'x-circle',
            self::NOTIFICADO => 'bell',
            self::ARCHIVADO => 'archive',
        };
    }

    /**
     * Obtener todos los valores como array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Obtener opciones para select (value => label)
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label()
        ])->toArray();
    }

    /**
     * Verificar si un valor es válido
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values());
    }

    /**
     * Intentar crear desde string (retorna null si no es válido)
     */
    public static function tryFromString(string $value): ?self
    {
        return self::tryFrom(strtolower($value));
    }
}
