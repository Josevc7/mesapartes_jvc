<?php

namespace App\Enums;

/**
 * Enum para prioridades de expedientes
 * Centraliza todos los valores válidos y evita inconsistencias de mayúsculas/minúsculas
 */
enum PrioridadExpediente: string
{
    case BAJA = 'baja';
    case NORMAL = 'normal';
    case ALTA = 'alta';
    case URGENTE = 'urgente';

    /**
     * Obtener label para mostrar en UI
     */
    public function label(): string
    {
        return match($this) {
            self::BAJA => 'Baja',
            self::NORMAL => 'Normal',
            self::ALTA => 'Alta',
            self::URGENTE => 'Urgente',
        };
    }

    /**
     * Obtener color Bootstrap para badges
     */
    public function color(): string
    {
        return match($this) {
            self::BAJA => 'secondary',
            self::NORMAL => 'info',
            self::ALTA => 'warning',
            self::URGENTE => 'danger',
        };
    }

    /**
     * Obtener icono para UI
     */
    public function icono(): string
    {
        return match($this) {
            self::BAJA => 'arrow-down',
            self::NORMAL => 'minus',
            self::ALTA => 'arrow-up',
            self::URGENTE => 'alert-triangle',
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

    /**
     * Prioridades que requieren atención urgente
     */
    public static function prioridadesAltas(): array
    {
        return [
            self::ALTA->value,
            self::URGENTE->value,
        ];
    }
}
