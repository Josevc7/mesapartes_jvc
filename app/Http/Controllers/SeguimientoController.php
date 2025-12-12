<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;

class SeguimientoController extends Controller
{
    public function index()
    {
        $expedientes = Expediente::where('id_ciudadano', auth()->user()->id_usuario)
            ->with(['tipoTramite', 'area'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('seguimiento.index', compact('expedientes'));
    }

    public function show($codigo)
    {
        $expediente = Expediente::where('codigo_expediente', $codigo)
            ->with(['documentos', 'historial', 'derivaciones', 'tipoTramite', 'area', 'persona'])
            ->firstOrFail();
            
        if (auth()->check() && $expediente->id_ciudadano !== auth()->user()->id_usuario) {
            abort(403, 'No tienes acceso a este expediente');
        }
        
        return view('seguimiento.show', compact('expediente'));
    }

    public function consultaForm()
    {
        return view('seguimiento.consulta');
    }

    public function consulta($codigo)
    {
        $expediente = Expediente::where('codigo_expediente', $codigo)
            ->with(['tipoTramite', 'area', 'persona', 'derivaciones'])
            ->first();
            
        if (!$expediente) {
            return back()->withErrors(['codigo' => 'Expediente no encontrado']);
        }
        
        return view('seguimiento.resultado', compact('expediente'));
    }

    public function buscar(Request $request)
    {
        $request->validate([
            'codigo_expediente' => 'required|string',
            'dni' => 'required|string|size:8'
        ]);

        $expediente = Expediente::where('codigo_expediente', $request->codigo_expediente)
            ->whereHas('persona', function($query) use ($request) {
                $query->where('numero_documento', $request->dni);
            })
            ->with(['tipoTramite', 'area', 'persona', 'derivaciones.area', 'historial.usuario'])
            ->first();

        if (!$expediente) {
            return back()->with('error', 'No se encontró el expediente o el DNI no coincide.');
        }

        return view('seguimiento.resultado', compact('expediente'));
    }
}