<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Models\Role;
use App\Models\Persona;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        // El LoginRequest ya validó y sanitizó los datos
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();
            $user->load('role');

            // Verificar si el usuario está activo
            if (!$user->activo) {
                Auth::logout();
                $request->incrementLoginAttempts();

                return back()->withErrors([
                    'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
                ])->withInput($request->only('email'));
            }

            // Regenerar sesión por seguridad
            $request->session()->regenerate();

            // Limpiar intentos de login fallidos
            $request->clearLoginAttempts();

            // Actualizar último login
            $user->update(['last_login_at' => now()]);

            // Log de auditoría (opcional)
            \Log::info('Usuario autenticado', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role->nombre ?? 'N/A',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Redirección según rol
            $roleName = $user->role->nombre ?? '';

            switch ($roleName) {
                case 'Administrador':
                    return redirect()->route('dashboard');
                case 'Mesa de Partes':
                    return redirect()->route('mesa-partes.index');
                case 'Jefe de Área':
                    return redirect()->route('jefe-area.dashboard');
                case 'Funcionario':
                    return redirect()->route('funcionario.index');
                case 'Ciudadano':
                    return redirect()->route('ciudadano.dashboard');
                case 'Soporte':
                    return redirect()->route('soporte.dashboard');
                default:
                    return redirect()->route('dashboard');
            }
        }

        // Incrementar contador de intentos fallidos
        $request->incrementLoginAttempts();

        // Log de intento fallido
        \Log::warning('Intento de login fallido', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Validación más estricta y profesional
        $validated = $request->validate([
            'nombres' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u'
            ],
            'apellido_paterno' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u'
            ],
            'apellido_materno' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u'
            ],
            'dni' => [
                'required',
                'string',
                'size:8',
                'regex:/^[0-9]{8}$/',
                'unique:personas,numero_documento',
                'unique:users,dni'
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
                'unique:personas,email'
            ],
            'telefono' => [
                'nullable',
                'string',
                'min:7',
                'max:20',
                'regex:/^[0-9\s\-\+\(\)]+$/'
            ],
            'password' => [
                'required',
                'min:8',
                'max:255',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/' // Al menos 1 mayúscula, 1 minúscula, 1 número
            ],
            'terms' => 'accepted' // Aceptación de términos
        ], [
            'nombres.required' => 'Los nombres son obligatorios',
            'nombres.min' => 'Los nombres deben tener al menos 2 caracteres',
            'nombres.regex' => 'Los nombres solo pueden contener letras y espacios',

            'apellido_paterno.required' => 'El apellido paterno es obligatorio',
            'apellido_paterno.min' => 'El apellido paterno debe tener al menos 2 caracteres',
            'apellido_paterno.regex' => 'El apellido paterno solo puede contener letras y espacios',

            'apellido_materno.regex' => 'El apellido materno solo puede contener letras y espacios',

            'dni.unique' => 'Este DNI ya está registrado en el sistema',
            'dni.regex' => 'El DNI debe contener exactamente 8 dígitos numéricos',
            'dni.size' => 'El DNI debe tener exactamente 8 dígitos',

            'email.unique' => 'Este correo electrónico ya está registrado',
            'email.email' => 'El formato del correo electrónico no es válido',

            'telefono.min' => 'El teléfono debe tener al menos 7 caracteres',
            'telefono.regex' => 'El teléfono solo puede contener números y símbolos telefónicos válidos',

            'password.confirmed' => 'Las contraseñas no coinciden',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número',

            'terms.accepted' => 'Debe aceptar los términos y condiciones'
        ]);

        // Sanitizar datos
        $nombres = ucwords(strtolower(trim($validated['nombres'])));
        $apellidoPaterno = ucwords(strtolower(trim($validated['apellido_paterno'])));
        $apellidoMaterno = $validated['apellido_materno'] ? ucwords(strtolower(trim($validated['apellido_materno']))) : null;
        $email = strtolower(trim($validated['email']));
        $telefono = isset($validated['telefono']) ? preg_replace('/[^0-9\-\+\(\)\s]/', '', $validated['telefono']) : null;

        // Crear persona con datos sanitizados
        $persona = Persona::create([
            'tipo_documento' => 'DNI',
            'numero_documento' => $validated['dni'],
            'tipo_persona' => 'NATURAL',
            'nombres' => $nombres,
            'apellido_paterno' => $apellidoPaterno,
            'apellido_materno' => $apellidoMaterno,
            'telefono' => $telefono,
            'email' => $email,
            'activo' => true
        ]);

        // Crear usuario con datos sanitizados
        $nombreCompleto = trim("{$nombres} {$apellidoPaterno} {$apellidoMaterno}");
        $user = User::create([
            'name' => $nombreCompleto,
            'email' => $email,
            'password' => Hash::make($validated['password']),
            'dni' => $validated['dni'],
            'telefono' => $telefono,
            'id_rol' => 6, // Rol Ciudadano
            'id_persona' => $persona->id_persona,
            'activo' => true
        ]);

        // Log de registro exitoso
        \Log::info('Nuevo usuario registrado', [
            'user_id' => $user->id,
            'email' => $user->email,
            'dni' => $user->dni,
            'ip' => $request->ip(),
        ]);

        // Autenticar automáticamente
        Auth::login($user);

        return redirect()->route('ciudadano.dashboard')
            ->with('success', '¡Bienvenido! Tu cuenta ha sido creada exitosamente. Ahora puedes realizar trámites virtuales las 24 horas.');
    }
}