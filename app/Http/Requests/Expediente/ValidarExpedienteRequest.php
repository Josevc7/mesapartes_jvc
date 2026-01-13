<?php

namespace App\Http\Requests\Expediente;

use Illuminate\Foundation\Http\FormRequest;

class ValidarExpedienteRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud
     */
    public function authorize(): bool
    {
        return auth()->check() &&
            auth()->user()->role->nombre === 'Jefe de Área';
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            'accion' => 'required|in:aprobar,rechazar',
            'observaciones' => 'required_if:accion,rechazar|nullable|string|min:10|max:500'
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'accion.required' => 'Debe seleccionar una acción (aprobar o rechazar)',
            'accion.in' => 'La acción debe ser aprobar o rechazar',
            'observaciones.required_if' => 'Debe proporcionar observaciones al rechazar un expediente',
            'observaciones.min' => 'Las observaciones deben tener al menos 10 caracteres',
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres'
        ];
    }

    /**
     * Nombres de atributos personalizados
     */
    public function attributes(): array
    {
        return [
            'accion' => 'acción',
            'observaciones' => 'observaciones'
        ];
    }
}
