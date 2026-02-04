<?php

namespace App\Enums;

/**
 * Enum para roles de usuario
 * Reemplaza el uso de IDs hardcodeados como ->where('id_rol', 4)
 */
enum RolUsuario: int
{
    case ADMINISTRADOR = 1;
    case MESA_PARTES = 2;
    case JEFE_AREA = 3;
    case FUNCIONARIO = 4;
    case CIUDADANO = 5;
    case SOPORTE = 6;

    /**
     * Obtener label para mostrar en UI
     */
    public function label(): string
    {
        return match($this) {
            self::ADMINISTRADOR => 'Administrador',
            self::MESA_PARTES => 'Mesa de Partes',
            self::JEFE_AREA => 'Jefe de Área',
            self::FUNCIONARIO => 'Funcionario',
            self::CIUDADANO => 'Ciudadano',
            self::SOPORTE => 'Soporte Técnico',
        };
    }

    /**
     * Obtener color Bootstrap para badges
     */
    public function color(): string
    {
        return match($this) {
            self::ADMINISTRADOR => 'danger',
            self::MESA_PARTES => 'primary',
            self::JEFE_AREA => 'success',
            self::FUNCIONARIO => 'info',
            self::CIUDADANO => 'secondary',
            self::SOPORTE => 'warning',
        };
    }

    /**
     * Verificar si es rol administrativo
     */
    public function esAdministrativo(): bool
    {
        return in_array($this, [
            self::ADMINISTRADOR,
            self::MESA_PARTES,
            self::SOPORTE,
        ]);
    }

    /**
     * Verificar si puede gestionar expedientes
     */
    public function puedeGestionarExpedientes(): bool
    {
        return in_array($this, [
            self::ADMINISTRADOR,
            self::MESA_PARTES,
            self::JEFE_AREA,
            self::FUNCIONARIO,
        ]);
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
     * Roles internos (empleados)
     */
    public static function rolesInternos(): array
    {
        return [
            self::ADMINISTRADOR->value,
            self::MESA_PARTES->value,
            self::JEFE_AREA->value,
            self::FUNCIONARIO->value,
            self::SOPORTE->value,
        ];
    }
}
