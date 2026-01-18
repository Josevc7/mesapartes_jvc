<?php

namespace App\Http\Requests\Expediente;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpedienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo usuarios autenticados con rol Mesa de Partes o Admin
        return auth()->check() &&
            in_array(auth()->user()->role->nombre, ['Mesa de Partes', 'Administrador']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            // Datos del expediente
            'asunto' => 'required|string|max:500',
            'asunto_documento' => 'required|string|max:500',
            'tipo_documento_entrante' => 'required|string|in:SOLICITUD,FUT,OFICIO,INFORME,MEMORANDUM,CARTA,RESOLUCION',
            'folios' => 'required|integer|min:1|max:9999',
            'id_tipo_tramite' => 'required|exists:tipo_tramites,id_tipo_tramite',
            'observaciones' => 'nullable|string',

            // Datos de la persona (remitente)
            'persona_existente_id' => 'nullable|exists:personas,id_persona',
            'tipo_documento' => 'required|in:DNI,CE,RUC,PASAPORTE',
            'tipo_persona' => 'required|in:NATURAL,JURIDICA',

            // Validación dinámica del número de documento según tipo
            'numero_documento' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $tipoDoc = $this->input('tipo_documento');

                    switch ($tipoDoc) {
                        case 'DNI':
                            // DNI: Exactamente 8 dígitos numéricos
                            if (!preg_match('/^\d{8}$/', $value)) {
                                $fail('El DNI debe contener exactamente 8 dígitos numéricos.');
                            }
                            break;

                        case 'RUC':
                            // RUC: Exactamente 11 dígitos numéricos
                            if (!preg_match('/^\d{11}$/', $value)) {
                                $fail('El RUC debe contener exactamente 11 dígitos numéricos.');
                            }
                            break;

                        case 'CE':
                            // Carnet de Extranjería: 9 o 12 caracteres alfanuméricos
                            if (!preg_match('/^[A-Z0-9]{9}$|^[A-Z0-9]{12}$/', $value)) {
                                $fail('El Carnet de Extranjería debe contener 9 o 12 caracteres alfanuméricos.');
                            }
                            break;

                        case 'PASAPORTE':
                            // Pasaporte: 7 a 12 caracteres alfanuméricos
                            if (!preg_match('/^[A-Z0-9]{7,12}$/', $value)) {
                                $fail('El Pasaporte debe contener entre 7 y 12 caracteres alfanuméricos.');
                            }
                            break;
                    }
                },
            ],

            // Persona Natural
            'nombres' => 'required_if:tipo_persona,NATURAL|nullable|string|max:100',
            'apellido_paterno' => 'required_if:tipo_persona,NATURAL|nullable|string|max:50',
            'apellido_materno' => 'nullable|string|max:50',

            // Persona Jurídica
            'razon_social' => 'required_if:tipo_persona,JURIDICA|nullable|string|max:200',
            'representante_legal' => 'nullable|string|max:150',

            // Datos de contacto
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',

            // Documentos adjuntos
            'documento' => 'nullable|file|mimes:pdf|max:10240', // 10MB - Opcional
            'documentos_verificados' => 'nullable|array',
            'documentos_verificados.*' => 'in:dni,fut,pago',
            'documentos_adicionales' => 'nullable|array',
            'observaciones_documentos' => 'nullable|string',

            // Campos de clasificación
            'id_area' => 'required|exists:areas,id_area',
            'prioridad' => 'required|in:baja,normal,alta,urgente',
            'observaciones_clasificacion' => 'nullable|string|max:500',

            // Campos de derivación
            'id_funcionario_asignado' => 'nullable|exists:users,id',
            'plazo_dias' => 'required|integer|min:1|max:365',
            'prioridad_derivacion' => 'nullable|in:baja,normal,alta,urgente',
            'observaciones_derivacion' => 'nullable|string|max:1000',
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Expediente
            'asunto.required' => 'El asunto del trámite es obligatorio',
            'asunto.max' => 'El asunto no puede exceder 500 caracteres',
            'asunto_documento.required' => 'El asunto del documento es obligatorio',
            'asunto_documento.max' => 'El asunto del documento no puede exceder 500 caracteres',
            'tipo_documento_entrante.required' => 'Debe seleccionar el tipo de documento',
            'tipo_documento_entrante.in' => 'El tipo de documento seleccionado no es válido',
            'folios.required' => 'Debe indicar el número de folios',
            'folios.integer' => 'El número de folios debe ser un número entero',
            'folios.min' => 'El número de folios debe ser al menos 1',
            'folios.max' => 'El número de folios no puede exceder 9999',
            'id_tipo_tramite.required' => 'Debe seleccionar un tipo de trámite',
            'id_tipo_tramite.exists' => 'El tipo de trámite seleccionado no existe',

            // Documento (opcional)
            'documento.mimes' => 'El documento debe ser un archivo PDF',
            'documento.max' => 'El archivo no puede superar los 10MB',

            // Persona
            'tipo_documento.required' => 'El tipo de documento es obligatorio',
            'numero_documento.required' => 'El número de documento es obligatorio',
            'tipo_persona.required' => 'Debe indicar si es persona natural o jurídica',

            // Persona Natural
            'nombres.required_if' => 'Los nombres son obligatorios para personas naturales',
            'apellido_paterno.required_if' => 'El apellido paterno es obligatorio para personas naturales',

            // Persona Jurídica
            'razon_social.required_if' => 'La razón social es obligatoria para personas jurídicas',

            // Contacto
            'email.email' => 'El correo electrónico debe ser válido',

            // Clasificación
            'id_area.required' => 'Debe seleccionar un área de destino',
            'id_area.exists' => 'El área seleccionada no existe',
            'prioridad.required' => 'Debe seleccionar una prioridad',
            'prioridad.in' => 'La prioridad debe ser: baja, normal, alta o urgente',

            // Derivación
            'id_funcionario_asignado.exists' => 'El funcionario seleccionado no existe',
            'plazo_dias.required' => 'Debe especificar el plazo en días',
            'plazo_dias.integer' => 'El plazo debe ser un número entero',
            'plazo_dias.min' => 'El plazo mínimo es 1 día',
            'plazo_dias.max' => 'El plazo máximo es 365 días',
            'prioridad_derivacion.in' => 'La prioridad de derivación debe ser: baja, normal, alta o urgente',

            // Validación de número de documento
            'numero_documento.required' => 'El número de documento es obligatorio',
            'numero_documento.regex' => 'El formato del número de documento no es válido',
        ];
    }

    /**
     * Prepara los datos para la validación
     * Convierte el número de documento a mayúsculas para CE y PASAPORTE
     */
    protected function prepareForValidation()
    {
        if ($this->has('numero_documento') && in_array($this->tipo_documento, ['CE', 'PASAPORTE'])) {
            $this->merge([
                'numero_documento' => strtoupper($this->numero_documento),
            ]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'asunto' => 'asunto del trámite',
            'id_tipo_tramite' => 'tipo de trámite',
            'tipo_documento' => 'tipo de documento',
            'numero_documento' => 'número de documento',
            'tipo_persona' => 'tipo de persona',
            'nombres' => 'nombres',
            'apellido_paterno' => 'apellido paterno',
            'apellido_materno' => 'apellido materno',
            'razon_social' => 'razón social',
            'representante_legal' => 'representante legal',
            'telefono' => 'teléfono',
            'email' => 'correo electrónico',
            'direccion' => 'dirección',
            'documento' => 'documento PDF',
            'documentos_verificados' => 'documentos verificados',
            'documentos_adicionales' => 'documentos adicionales',
            'observaciones' => 'observaciones',
            'observaciones_documentos' => 'observaciones de documentos',
        ];
    }
}
