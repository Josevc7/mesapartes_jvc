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
            'tipo_documento' => 'required|in:DNI,RUC',
            'numero_documento' => [
                'required',
                'string',
                'regex:/^[0-9]+$/',
                function ($attribute, $value, $fail) use ($request) {
                    $tipo = $request->input('tipo_documento');
                    if ($tipo === 'DNI' && strlen($value) !== 8) {
                        $fail('El DNI debe contener exactamente 8 dígitos.');
                    } elseif ($tipo === 'RUC' && strlen($value) !== 11) {
                        $fail('El RUC debe contener exactamente 11 dígitos.');
                    }
                },
            ],
        ], [
            'codigo_expediente.required' => 'El número de expediente es obligatorio.',
            'tipo_documento.required' => 'Debe seleccionar el tipo de documento.',
            'tipo_documento.in' => 'El tipo de documento debe ser DNI o RUC.',
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.regex' => 'El documento solo debe contener números.',
        ]);

        $expediente = Expediente::where('codigo_expediente', $request->codigo_expediente)
            ->whereHas('persona', function($query) use ($request) {
                $query->where('numero_documento', $request->numero_documento);
            })
            ->with(['tipoTramite', 'area', 'persona', 'derivaciones.area', 'historial.usuario'])
            ->first();

        if (!$expediente) {
            $tipoDoc = $request->tipo_documento === 'RUC' ? 'RUC' : 'DNI';
            return back()->withInput()->withErrors(['numero_documento' => "No se encontró el expediente o el {$tipoDoc} no coincide con el solicitante."]);
        }

        return view('seguimiento.resultado', compact('expediente'));
    }
}