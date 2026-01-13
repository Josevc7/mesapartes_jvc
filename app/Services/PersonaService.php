<?php

namespace App\Services;

use App\Models\Persona;

class PersonaService
{
    /**
     * Obtiene una persona existente o crea una nueva
     */
    public function obtenerOCrear(array $data): Persona
    {
        if (isset($data['persona_existente_id'])) {
            return $this->actualizarPersona($data['persona_existente_id'], $data);
        }

        $persona = Persona::where('numero_documento', $data['numero_documento'])->first();

        if ($persona) {
            return $this->actualizarDatosContacto($persona, $data);
        }

        return $this->crearPersona($data);
    }

    public function crearPersona(array $data): Persona
    {
        return Persona::create([
            'tipo_documento' => $data['tipo_documento'],
            'numero_documento' => $data['numero_documento'],
            'tipo_persona' => $data['tipo_persona'],
            'nombres' => $data['nombres'] ?? null,
            'apellido_paterno' => $data['apellido_paterno'] ?? null,
            'apellido_materno' => $data['apellido_materno'] ?? null,
            'razon_social' => $data['razon_social'] ?? null,
            'representante_legal' => $data['representante_legal'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'activo' => true,
        ]);
    }

    protected function actualizarPersona(int $personaId, array $data): Persona
    {
        $persona = Persona::findOrFail($personaId);
        $persona->update([
            'telefono' => $data['telefono'] ?? $persona->telefono,
            'email' => $data['email'] ?? $persona->email,
            'direccion' => $data['direccion'] ?? $persona->direccion,
        ]);
        return $persona;
    }

    protected function actualizarDatosContacto(Persona $persona, array $data): Persona
    {
        $persona->update([
            'telefono' => $data['telefono'] ?? $persona->telefono,
            'email' => $data['email'] ?? $persona->email,
            'direccion' => $data['direccion'] ?? $persona->direccion,
        ]);
        return $persona;
    }

    public function obtenerNombreCompleto(Persona $persona): string
    {
        if ($persona->tipo_persona === 'JURIDICA') {
            return $persona->razon_social;
        }
        return trim("{$persona->nombres} {$persona->apellido_paterno} {$persona->apellido_materno}");
    }
}
