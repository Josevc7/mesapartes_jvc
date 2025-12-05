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
        $canEditAll = false;
        
        if ($user->role->nombre === 'Administrador') {
            $canEditAll = true;
        } elseif ($user->role->nombre === 'Ciudadano') {
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
        $canEditAll = false;
        
        if ($user->role->nombre === 'Administrador') {
            $canEditAll = true;
        } elseif ($user->role->nombre === 'Ciudadano') {
            // Ciudadano puede editar solo si no tiene expedientes en trámite
            $expedientesEnTramite = \App\Models\Expediente::where('id_ciudadano', $user->id)
                ->whereNotIn('estado', ['archivado', 'resuelto'])
                ->exists();
            $canEditAll = !$expedientesEnTramite;
        }
        
        if ($canEditAll) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
                'dni' => ['nullable', 'string', 'size:8', Rule::unique('users')->ignore($user->id)],
                'telefono' => 'nullable|string|max:15',
                'direccion' => 'nullable|string|max:255',
                'password' => 'nullable|min:6|confirmed'
            ]);

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'dni' => $request->dni,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion
            ];
        } else {
            // Solo campos básicos para otros roles
            $request->validate([
                'telefono' => 'nullable|string|max:15',
                'direccion' => 'nullable|string|max:255',
                'password' => 'nullable|min:6|confirmed'
            ]);

            $data = [
                'telefono' => $request->telefono,
                'direccion' => $request->direccion
            ];
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('perfil.show')
            ->with('success', 'Perfil actualizado correctamente');
    }
}