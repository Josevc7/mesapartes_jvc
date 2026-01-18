<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Documento;
use App\Models\Expediente;
use App\Services\DocumentoAccessService;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    protected $accessService;

    public function __construct(DocumentoAccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    /**
     * Listar documentos accesibles para el usuario
     */
    public function index()
    {
        $user = auth()->user();
        $documentos = $this->accessService->getDocumentosAccesibles($user);

        return view('admin.documentos.index', compact('documentos'));
    }

    /**
     * Ver/Descargar un documento
     */
    public function show($id_documento)
    {
        $documento = Documento::with('expediente')->findOrFail($id_documento);
        $user = auth()->user();

        // Verificar acceso
        if (!$this->accessService->puedeAcceder($user, $documento)) {
            abort(403, 'No tiene permiso para acceder a este documento.');
        }

        // Verificar que el archivo existe
        if (!Storage::disk('public')->exists($documento->ruta_pdf)) {
            abort(404, 'El archivo no existe.');
        }

        return Storage::disk('public')->download($documento->ruta_pdf, $documento->nombre);
    }

    /**
     * Ver documento en el navegador
     */
    public function visualizar($id_documento)
    {
        $documento = Documento::with('expediente')->findOrFail($id_documento);
        $user = auth()->user();

        // Verificar acceso
        if (!$this->accessService->puedeAcceder($user, $documento)) {
            abort(403, 'No tiene permiso para acceder a este documento.');
        }

        // Verificar que el archivo existe
        if (!Storage::disk('public')->exists($documento->ruta_pdf)) {
            abort(404, 'El archivo no existe.');
        }

        $path = Storage::disk('public')->path($documento->ruta_pdf);
        $mimeType = Storage::disk('public')->mimeType($documento->ruta_pdf);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $documento->nombre . '"'
        ]);
    }

    /**
     * Subir un documento a un expediente
     */
    public function store(Request $request, $id_expediente)
    {
        $expediente = Expediente::findOrFail($id_expediente);
        $user = auth()->user();

        // Verificar permiso de subida
        if (!$this->accessService->puedeSubir($user, $expediente)) {
            return redirect()->back()->with('error', 'No tiene permiso para subir documentos a este expediente.');
        }

        $request->validate([
            'documento' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'nombre' => 'nullable|string|max:255',
            'tipo' => 'nullable|string|max:100',
        ]);

        $archivo = $request->file('documento');
        $nombreOriginal = $archivo->getClientOriginalName();
        $nombreGuardado = time() . '_' . $nombreOriginal;
        $ruta = $archivo->storeAs('documentos/' . $expediente->codigo_expediente, $nombreGuardado, 'public');

        Documento::create([
            'id_expediente' => $expediente->id_expediente,
            'nombre' => $request->nombre ?? $nombreOriginal,
            'ruta_pdf' => $ruta,
            'tipo' => $request->tipo ?? 'Adjunto',
            'tamaño_archivo' => $archivo->getSize(),
        ]);

        // Registrar en historial
        $expediente->historial()->create([
            'id_usuario' => $user->id,
            'descripcion' => 'Documento adjuntado: ' . ($request->nombre ?? $nombreOriginal),
            'fecha' => now(),
        ]);

        return redirect()->back()->with('success', 'Documento subido correctamente.');
    }

    /**
     * Eliminar un documento
     */
    public function destroy($id_documento)
    {
        $documento = Documento::with('expediente')->findOrFail($id_documento);
        $user = auth()->user();

        // Verificar permiso de eliminación
        if (!$this->accessService->puedeEliminar($user, $documento)) {
            return redirect()->back()->with('error', 'No tiene permiso para eliminar este documento.');
        }

        // Eliminar archivo físico
        if (Storage::disk('public')->exists($documento->ruta_pdf)) {
            Storage::disk('public')->delete($documento->ruta_pdf);
        }

        // Registrar en historial
        $documento->expediente->historial()->create([
            'id_usuario' => $user->id,
            'descripcion' => 'Documento eliminado: ' . $documento->nombre,
            'fecha' => now(),
        ]);

        $documento->delete();

        return redirect()->back()->with('success', 'Documento eliminado correctamente.');
    }

    /**
     * Validar un documento (para Mesa de Partes o Jefe de Área)
     */
    public function validar(Request $request, $id_documento)
    {
        $documento = Documento::with('expediente')->findOrFail($id_documento);
        $user = auth()->user();

        // Solo Admin, Mesa de Partes y Jefe de Área pueden validar
        if (!in_array($user->role?->nombre, ['Administrador', 'Mesa de Partes', 'Jefe de Área'])) {
            return redirect()->back()->with('error', 'No tiene permiso para validar documentos.');
        }

        $request->validate([
            'validado' => 'required|boolean',
            'observacion' => 'nullable|string',
        ]);

        // Agregar campo de validación si existe en la tabla
        // Por ahora, registramos la validación en el historial
        $accion = $request->validado ? 'validado' : 'rechazado';

        $documento->expediente->historial()->create([
            'id_usuario' => $user->id,
            'descripcion' => "Documento {$accion}: {$documento->nombre}" .
                ($request->observacion ? ". Observación: {$request->observacion}" : ''),
            'fecha' => now(),
        ]);

        return redirect()->back()->with('success', "Documento {$accion} correctamente.");
    }
}
