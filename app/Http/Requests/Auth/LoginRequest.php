<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Email: validación estándar (sin validación DNS para desarrollo)
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],

            // Contraseña: validación básica (no revelamos requisitos por seguridad)
            'password' => [
                'required',
                'string',
                'min:6',
                'max:255',
            ],

            // Remember me: opcional
            'remember' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El formato del correo electrónico no es válido',
            'email.regex' => 'El correo electrónico debe tener un formato válido',
            'email.max' => 'El correo electrónico no puede exceder 255 caracteres',

            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.max' => 'La contraseña no puede exceder 255 caracteres',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'email' => 'correo electrónico',
            'password' => 'contraseña',
        ];
    }

    /**
     * Prepara los datos para la validación
     */
    protected function prepareForValidation()
    {
        $data = [];

        // Sanitizar email: convertir a minúsculas y eliminar espacios
        if ($this->has('email')) {
            $data['email'] = strtolower(trim($this->email));
        }

        // Sanitizar contraseña: solo eliminar espacios en blanco al inicio y final
        if ($this->has('password')) {
            $data['password'] = trim($this->password);
        }

        // Convertir remember a booleano
        if ($this->has('remember')) {
            $data['remember'] = filter_var($this->remember, FILTER_VALIDATE_BOOLEAN);
        }

        $this->merge($data);
    }

    /**
     * Verifica si el login está limitado por rate limiting
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $minutes = ceil($seconds / 60);

        throw ValidationException::withMessages([
            'email' => "Demasiados intentos de inicio de sesión. Por favor, intente nuevamente en {$minutes} minuto(s).",
        ]);
    }

    /**
     * Incrementa el contador de intentos de login
     */
    public function incrementLoginAttempts(): void
    {
        RateLimiter::hit($this->throttleKey(), 60); // 60 segundos = 1 minuto
    }

    /**
     * Limpia los intentos de login después de un login exitoso
     */
    public function clearLoginAttempts(): void
    {
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Obtiene la clave para el rate limiting
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }

    /**
     * Validación adicional después de las reglas básicas
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verificar rate limiting antes de procesar
            $this->ensureIsNotRateLimited();

            // Validación adicional: detectar posibles intentos de SQL injection
            $email = $this->input('email');
            $suspiciousPatterns = [
                '/(\bOR\b|\bAND\b)/i',
                '/[\'\";]/',
                '/--/',
                '/<script/i',
                '/union\s+select/i',
                '/drop\s+table/i',
            ];

            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $email)) {
                    $validator->errors()->add(
                        'email',
                        'El correo electrónico contiene caracteres no permitidos'
                    );
                    break;
                }
            }
        });
    }
}
