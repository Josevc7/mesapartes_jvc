<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\TipoTramite;
use App\Models\Documento;
use App\Services\NumeracionService;
use App\Services\NotificacionService;
use App\Services\AuditoriaService;

class ExpedienteController extends Controller
{
    public function create()
    {
        $tipoTramites = TipoTramite::where('activo', true)->get();
        return view('expedientes.create', compact('tipoTramites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asunto' => 'required|string|max:500',
            'descripcion' => 'required|string',
            'documento' => 'required|file|mimes:pdf|max:10240'
        ]);

        $codigo = app(NumeracionService::class)->generarCodigo();
        
        $expediente = Expediente::create([
            'codigo_expediente' => $codigo,
            'asunto' => $request->asunto,
            'id_ciudadano' => auth()->user()->id_user,
            'fecha_registro' => now(),
            'estado' => 'Registrado',
            'canal' => 'Web'
        ]);

        if ($request->hasFile('documento')) {
            $path = $request->file('documento')->store('documentos', 'public');
            
            Documento::create([
                'id_expediente' => $expediente->id_expediente,
                'nombre' => 'Documento Principal',
                'ruta_archivo' => $path,
                'tipo' => 'Principal',
                'id_usuario' => auth()->user()->id_user
            ]);
        }

        // Registrar auditoría
        AuditoriaService::expedienteCreado($expediente);
        
        // Enviar notificaciones
        app(NotificacionService::class)->notificarNuevoExpediente($expediente);
        
        return redirect()->route('seguimiento.show', $codigo)
            ->with('success', 'Expediente registrado correctamente. Código: ' . $codigo);
    }
}