<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PerfilController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        return view('perfil.show', compact('user'));
    }

    public function edit()
    {
        $user = auth()->user();
        $role = \App\Models\Role::where('id_rol', $user->id_rol)->first();
        $canEditAll = false;
        
        if ($role && $role->nombre === 'Administrador') {
            $canEditAll = true;
        } elseif ($role && $role->nombre === 'Ciudadano') {
            // Ciudadano puede editar solo si no tiene expedientes en trámite
            $expedientesEnTramite = \App\Models\Expediente::where('id_ciudadano', $user->id)
                ->whereNotIn('estado', ['archivado', 'resuelto'])
                ->exists();
            $canEditAll = !$expedientesEnTramite;
        }
        
        return view('perfil.edit', compact('user', 'canEditAll'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $role = \App\Models\Role::where('id_rol', $user->id_rol)->first();
        $canEditAll = false;
        
        if ($role && $role->nombre === 'Administrador') {
            $canEditAll = true;
        } elseif ($role && $role->nombre === 'Ciudadano') {
            // Ciudadano puede editar solo si no tiene expedientes en trámite
            $expedientesEnTramite = \App\Models\Expediente::where('id_ciudadano', $user->id)
                ->whereNotIn('estado', ['archivado', 'resuelto'])
                ->exists();
            $canEditAll = !$expedientesEnTramite;
        }
        
        if ($canEditAll) {
            $personaId = $user->id_persona;
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
                'dni' => ['nullable', 'string', 'size:8', Rule::unique('personas', 'numero_documento')->ignore($personaId, 'id_persona')],
                'telefono' => 'nullable|string|max:15',
                'direccion' => 'nullable|string|max:255',
                'password' => 'nullable|min:6|confirmed'
            ]);

            // Actualizar datos personales en la tabla personas
            if ($user->persona) {
                $user->persona->update([
                    'numero_documento' => $request->dni,
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                ]);
            } elseif ($request->dni) {
                $persona = \App\Models\Persona::firstOrCreate(
                    ['numero_documento' => $request->dni],
                    [
                        'tipo_documento' => 'DNI',
                        'tipo_persona' => 'NATURAL',
                        'nombres' => $request->name,
                        'telefono' => $request->telefono,
                        'direccion' => $request->direccion,
                    ]
                );
                $user->id_persona = $persona->id_persona;
            }

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'id_persona' => $user->id_persona,
            ];
        } else {
            // Solo campos básicos para otros roles
            $request->validate([
                'telefono' => 'nullable|string|max:15',
                'direccion' => 'nullable|string|max:255',
                'password' => 'nullable|min:6|confirmed'
            ]);

            // Actualizar en persona si existe
            if ($user->persona) {
                $user->persona->update([
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                ]);
            }

            $data = [];
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if (!empty($data)) {
            $user->update($data);
        }

        return redirect()->route('perfil.show')
            ->with('success', 'Perfil actualizado correctamente');
    }
}