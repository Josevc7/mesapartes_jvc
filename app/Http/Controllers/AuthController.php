<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Persona;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user()->load('role');
            
            // Verificar si el usuario está activo
            if (!$user->activo) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
                ]);
            }
            
            $request->session()->regenerate();
            
            // Actualizar último login
            $user->update(['last_login_at' => now()]);
            
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

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden.',
        ]);
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
        $request->validate([
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:50',
            'apellido_materno' => 'nullable|string|max:50',
            'dni' => 'required|string|size:8|regex:/^[0-9]{8}$/|unique:personas,numero_documento',
            'email' => 'required|email|unique:users,email',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|min:6|confirmed'
        ], [
            'dni.unique' => 'Este DNI ya está registrado en el sistema.',
            'email.unique' => 'Este correo ya está registrado.',
            'dni.regex' => 'El DNI debe tener exactamente 8 dígitos.',
            'password.confirmed' => 'Las contraseñas no coinciden.'
        ]);

        // Crear persona
        $persona = Persona::create([
            'tipo_documento' => 'DNI',
            'numero_documento' => $request->dni,
            'tipo_persona' => 'NATURAL',
            'nombres' => $request->nombres,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'activo' => true
        ]);

        // Crear usuario
        $user = User::create([
            'name' => trim("{$request->nombres} {$request->apellido_paterno} {$request->apellido_materno}"),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'dni' => $request->dni,
            'telefono' => $request->telefono,
            'id_rol' => 6, // Rol Ciudadano
            'id_persona' => $persona->id,
            'activo' => true
        ]);

        // Autenticar automáticamente
        Auth::login($user);

        return redirect()->route('ciudadano.dashboard')
            ->with('success', '¡Bienvenido! Tu cuenta ha sido creada exitosamente. Ahora puedes realizar trámites virtuales las 24 horas.');
    }
}