<?php

namespace App\Http\Requests\Derivacion;

use Illuminate\Foundation\Http\FormRequest;

class StoreDerivacionRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud
     */
    public function authorize(): bool
    {
        return auth()->check() &&
            in_array(auth()->user()->role->nombre, ['Mesa de Partes', 'Jefe de Área', 'Administrador']);
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            'id_area_destino' => 'required|exists:areas,id_area',
            'id_funcionario_asignado' => 'nullable|exists:users,id',
            'plazo_dias' => 'required|integer|min:1|max:365',
            'prioridad' => 'required|in:baja,normal,alta,urgente',
            'observaciones' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'id_area_destino.required' => 'Debe seleccionar un área de destino',
            'id_area_destino.exists' => 'El área seleccionada no existe',
            'id_funcionario_asignado.exists' => 'El funcionario seleccionado no existe',
            'plazo_dias.required' => 'Debe especificar el plazo en días',
            'plazo_dias.integer' => 'El plazo debe ser un número entero',
            'plazo_dias.min' => 'El plazo mínimo es 1 día',
            'plazo_dias.max' => 'El plazo máximo es 365 días',
            'prioridad.required' => 'Debe seleccionar una prioridad',
            'prioridad.in' => 'La prioridad debe ser: baja, normal, alta o urgente',
            'observaciones.max' => 'Las observaciones no pueden exceder 1000 caracteres'
        ];
    }

    /**
     * Nombres de atributos personalizados
     */
    public function attributes(): array
    {
        return [
            'id_area_destino' => 'área de destino',
            'id_funcionario_asignado' => 'funcionario asignado',
            'plazo_dias' => 'plazo en días',
            'prioridad' => 'prioridad',
            'observaciones' => 'observaciones'
        ];
    }
}
