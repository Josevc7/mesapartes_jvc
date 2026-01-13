<?php

namespace App\Http\Requests\Derivacion;

use Illuminate\Foundation\Http\FormRequest;

class ExtenderPlazoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud
     */
    public function authorize(): bool
    {
        return auth()->check() &&
            in_array(auth()->user()->role->nombre, ['Jefe de Área', 'Administrador']);
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            'dias_adicionales' => 'required|integer|min:1|max:180',
            'motivo' => 'required|string|min:10|max:500'
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'dias_adicionales.required' => 'Debe especificar los días adicionales',
            'dias_adicionales.integer' => 'Los días adicionales deben ser un número entero',
            'dias_adicionales.min' => 'Debe extender al menos 1 día',
            'dias_adicionales.max' => 'No puede extender más de 180 días',
            'motivo.required' => 'Debe especificar el motivo de la extensión',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres',
            'motivo.max' => 'El motivo no puede exceder 500 caracteres'
        ];
    }

    /**
     * Nombres de atributos personalizados
     */
    public function attributes(): array
    {
        return [
            'dias_adicionales' => 'días adicionales',
            'motivo' => 'motivo'
        ];
    }
}
