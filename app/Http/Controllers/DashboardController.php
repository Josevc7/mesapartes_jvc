<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = \App\Models\Role::where('id_rol', $user->id_rol)->first();
        $roleName = $role->nombre ?? '';
        
        switch ($roleName) {
            case 'Administrador':
                return $this->adminDashboard();
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
                return view('dashboard');
        }
    }
    
    private function adminDashboard()
    {
        $stats = [
            'total_expedientes' => Expediente::count(),
            'expedientes_pendientes' => Expediente::whereIn('estado', ['pendiente', 'clasificado'])->count(),
            'expedientes_proceso' => Expediente::where('estado', 'en_proceso')->count(),
            'total_usuarios' => User::where('activo', true)->count()
        ];

        return view('dashboard', compact('stats'));
    }
}