<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TipoTramiteFactory extends Factory
{
    public function definition(): array
    {
        $tramites = [
            'Licencia de Funcionamiento',
            'Certificado de Compatibilidad',
            'Permiso de Construcción',
            'Reclamo Tributario',
            'Solicitud de Información',
            'Denuncia Administrativa',
            'Recurso de Apelación',
            'Solicitud de Subvención'
        ];

        return [
            'nombre' => fake()->randomElement($tramites) . ' - ' . fake()->company(),
            'descripcion' => fake()->sentence(10),
            'dias_plazo' => fake()->numberBetween(7, 60),
            'activo' => true,
        ];
    }
}