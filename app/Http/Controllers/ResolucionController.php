<?php

namespace App\Http\Controllers;

use App\Models\Resolucion;
use App\Models\Expediente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ResolucionController extends Controller
{
    public function index()
    {
        $resoluciones = Resolucion::with(['expediente', 'funcionarioResolutor'])
            ->orderBy('fecha_resolucion', 'desc')
            ->paginate(10);
        
        return view('resoluciones.index', compact('resoluciones'));
    }

    public function create($expedienteId)
    {
        $expediente = Expediente::with(['tipoTramite', 'ciudadano'])->findOrFail($expedienteId);
        
        if ($expediente->resolucion) {
            return redirect()->back()->with('error', 'Este expediente ya tiene una resolución.');
        }
        
        return view('resoluciones.create', compact('expediente'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_expediente' => 'required|exists:expedientes,id_expediente',
            'numero_resolucion' => 'required|string|max:50|unique:resoluciones',
            'tipo_resolucion' => 'required|in:aprobado,rechazado,observado',
            'fundamento_legal' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'documento_resolucion' => 'nullable|file|mimes:pdf|max:10240'
        ]);

        $rutaDocumento = null;
        if ($request->hasFile('documento_resolucion')) {
            $rutaDocumento = $request->file('documento_resolucion')->store('resoluciones', 'public');
        }

        $resolucion = Resolucion::create([
            'id_expediente' => $request->id_expediente,
            'id_funcionario_resolutor' => Auth::id(),
            'numero_resolucion' => $request->numero_resolucion,
            'tipo_resolucion' => $request->tipo_resolucion,
            'fundamento_legal' => $request->fundamento_legal,
            'observaciones' => $request->observaciones,
            'ruta_documento_resolucion' => $rutaDocumento,
            'fecha_resolucion' => now()
        ]);

        // Actualizar estado del expediente
        $expediente = Expediente::where('id_expediente', $request->id_expediente)->first();
        $expediente->update(['estado' => 'resuelto']);
        
        // Agregar al historial
        $expediente->agregarHistorial(
            "Resolución creada: {$request->numero_resolucion} - {$request->tipo_resolucion}",
            Auth::id()
        );

        return redirect()->route('resoluciones.show', $resolucion)
            ->with('success', 'Resolución creada exitosamente.');
    }

    public function show($id_resolucion)
    {
        $resolucion = Resolucion::with(['expediente.tipoTramite', 'expediente.ciudadano', 'funcionarioResolutor'])->findOrFail($id_resolucion);
        return view('resoluciones.show', compact('resolucion'));
    }

    public function notificar($id_resolucion)
    {
        $resolucion = Resolucion::findOrFail($id_resolucion);
        $resolucion->update([
            'fecha_notificacion' => now(),
            'notificado' => true
        ]);

        return redirect()->back()->with('success', 'Resolución notificada exitosamente.');
    }

    public function descargar($id_resolucion)
    {
        $resolucion = Resolucion::findOrFail($id_resolucion);
        if (!$resolucion->ruta_documento_resolucion) {
            return redirect()->back()->with('error', 'No hay documento disponible.');
        }

        return Storage::disk('public')->download($resolucion->ruta_documento_resolucion);
    }
}