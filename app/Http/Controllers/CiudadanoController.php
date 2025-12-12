<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\TipoTramite;
use App\Models\Documento;
use App\Services\NumeracionService;
use Illuminate\Support\Facades\Storage;

class CiudadanoController extends Controller
{
    /**
     * Muestra el dashboard principal del ciudadano con estadísticas y expedientes recientes
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Obtener el ID del usuario autenticado (ciudadano logueado)
        $ciudadanoId = auth()->user()->id_user;
        
        // Calcular estadísticas de expedientes del ciudadano
        $stats = [
            // Contar total de expedientes que pertenecen al ciudadano
            'total_expedientes' => Expediente::where('id_ciudadano', $ciudadanoId)->count(),
            
            // Contar expedientes en proceso (estados activos)
            'en_proceso' => Expediente::where('id_ciudadano', $ciudadanoId)
                ->whereIn('estado', ['Registrado', 'Clasificado', 'Derivado', 'En Proceso'])
                ->count(),
            
            // Contar expedientes completados exitosamente
            'resueltos' => Expediente::where('id_ciudadano', $ciudadanoId)
                ->where('estado', 'Resuelto')
                ->count(),
            
            // Contar expedientes que requieren atención del ciudadano
            'observados' => Expediente::where('id_ciudadano', $ciudadanoId)
                ->where('estado', 'Observado')
                ->count()
        ];
        
        // Obtener los 5 expedientes más recientes del ciudadano
        $expedientes_recientes = Expediente::where('id_ciudadano', $ciudadanoId)
            ->with(['tipoTramite'])           // Eager loading: cargar relación tipoTramite para evitar N+1
            ->orderBy('created_at', 'desc')   // Ordenar por fecha de creación descendente
            ->limit(5)                       // Limitar a 5 resultados
            ->get();                         // Ejecutar consulta y obtener colección
            
        // Retornar vista del dashboard pasando las variables calculadas
        return view('ciudadano.dashboard', compact('stats', 'expedientes_recientes'));
    }

    public function misExpedientes()
    {
        $expedientes = Expediente::where('id_ciudadano', auth()->user()->id_user)
            ->with(['tipoTramite', 'area', 'documentos'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('ciudadano.mis-expedientes', compact('expedientes'));
    }

    public function seguimiento($codigo)
    {
        $expediente = Expediente::where('codigo_expediente', $codigo)
            ->where('id_ciudadano', auth()->user()->id_user)
            ->with(['tipoTramite', 'area', 'documentos', 'historial.usuario', 'derivaciones'])
            ->firstOrFail();
            
        return view('ciudadano.seguimiento', compact('expediente'));
    }

    public function descargarAcuse($codigo)
    {
        $expediente = Expediente::where('codigo_expediente', $codigo)
            ->where('id_ciudadano', auth()->id())
            ->firstOrFail();
            
        return view('ciudadano.acuse-recibo', compact('expediente'));
    }

    public function descargarDocumento($id_documento)
    {
        $documento = Documento::findOrFail($id_documento);
        $expediente = $documento->expediente;
        
        if ($expediente->id_ciudadano !== auth()->id()) {
            abort(403, 'No tienes acceso a este documento');
        }
        
        if (!Storage::disk('public')->exists($documento->ruta_pdf)) {
            abort(404, 'Archivo no encontrado');
        }
        
        return Storage::disk('public')->download($documento->ruta_pdf, $documento->nombre . '.pdf');
    }

    public function notificaciones()
    {
        $notificaciones = \App\Models\Notificacion::where('id_usuario', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('ciudadano.notificaciones', compact('notificaciones'));
    }

    public function registrarExpediente()
    {
        $tipoTramites = TipoTramite::where('activo', true)
            ->orderBy('nombre')
            ->get();
            
        return view('ciudadano.registrar-expediente', compact('tipoTramites'));
    }

    /**
     * Procesa y almacena un nuevo expediente enviado por el ciudadano
     * @param \Illuminate\Http\Request $request - Datos del formulario
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeExpediente(Request $request)
    {
        // VALIDACIÓN: Verificar que todos los datos del formulario sean correctos
        $request->validate([
            // === VALIDACIONES DE DATOS DEL SOLICITANTE ===
            'tipo_persona' => 'required|in:NATURAL,JURIDICA',              // Solo personas naturales o jurídicas
            'tipo_documento' => 'required|in:DNI,CE,RUC,PASAPORTE',        // Tipos de documento válidos
            'numero_documento' => 'required|string|max:20',                // Número de documento obligatorio
            
            // Campos requeridos solo para personas naturales
            'nombres' => 'required_if:tipo_persona,NATURAL|nullable|string|max:100',
            'apellido_paterno' => 'required_if:tipo_persona,NATURAL|nullable|string|max:50',
            'apellido_materno' => 'nullable|string|max:50',                // Apellido materno es opcional
            
            // Campos requeridos solo para personas jurídicas
            'razon_social' => 'required_if:tipo_persona,JURIDICA|nullable|string|max:200',
            'representante_legal' => 'nullable|string|max:150',            // Representante legal opcional
            
            // Datos de contacto (todos opcionales)
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',                          // Validar formato de email
            'direccion' => 'nullable|string',
            
            // === VALIDACIONES DE DATOS DEL TRÁMITE ===
            'id_tipo_tramite' => 'required|exists:tipo_tramites,id_tipo_tramite',       // Debe existir en la tabla
            'asunto' => 'required|string|max:500',                        // Asunto obligatorio
            'descripcion' => 'nullable|string|max:2000',                  // Descripción opcional
            
            // === VALIDACIONES DE ARCHIVOS ===
            'documento_principal' => 'required|file|mimes:pdf|max:10240',  // PDF obligatorio, máx 10MB
            'documentos_adicionales.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // Archivos opcionales, máx 5MB c/u
            
            // === VALIDACIONES ADICIONALES ===
            'prioridad' => 'in:baja,media,alta,urgente',                  // Prioridades válidas
            'acepta_terminos' => 'required|accepted'                      // Debe aceptar términos
        ]);

        // === CREAR O BUSCAR PERSONA EN LA BASE DE DATOS ===
        // firstOrCreate: busca por tipo y número de documento, si no existe lo crea
        $persona = \App\Models\Persona::firstOrCreate(
            [
                // Criterios de búsqueda: tipo y número de documento (deben ser únicos)
                'tipo_documento' => $request->tipo_documento,
                'numero_documento' => $request->numero_documento
            ],
            [
                // Datos para crear si no existe la persona
                'tipo_persona' => $request->tipo_persona,           // NATURAL o JURIDICA
                'nombres' => $request->nombres,                     // Solo para personas naturales
                'apellido_paterno' => $request->apellido_paterno,   // Solo para personas naturales
                'apellido_materno' => $request->apellido_materno,   // Opcional
                'razon_social' => $request->razon_social,           // Solo para personas jurídicas
                'representante_legal' => $request->representante_legal, // Opcional
                'telefono' => $request->telefono,                   // Datos de contacto
                'email' => $request->email,
                'direccion' => $request->direccion
            ]
        );

        // === GENERAR CÓDIGO ÚNCO DEL EXPEDIENTE ===
        // Usar servicio de numeración para generar código secuencial (ej: EXP-2024-00001)
        $codigo = app(NumeracionService::class)->generarCodigo();
        
        // === CREAR EL EXPEDIENTE EN LA BASE DE DATOS ===
        $expediente = Expediente::create([
            'codigo_expediente' => $codigo,                              // Código único generado
            'asunto' => $request->asunto,                               // Motivo del trámite
            'descripcion' => $request->descripcion,                     // Descripción detallada (opcional)
            'id_tipo_tramite' => $request->id_tipo_tramite,             // Tipo de trámite seleccionado
            'id_ciudadano' => auth()->user()->id_user,                     // Usuario autenticado que crea el expediente
            'id_persona' => $persona->id_persona,                               // Referencia a la persona (solicitante)
            'remitente' => $persona->nombre_completo,                   // Nombre completo para búsquedas rápidas
            'dni_remitente' => $persona->numero_documento,              // Documento para búsquedas rápidas
            'fecha_registro' => now(),                                  // Fecha y hora actual de registro
            'estado' => 'recepcionado',                                 // Estado inicial del expediente
            'prioridad' => $request->prioridad ?? 'media',              // Prioridad por defecto
            'canal' => 'virtual'                                        // Canal de ingreso (virtual/presencial)
        ]);

        // === GUARDAR DOCUMENTO PRINCIPAL ===
        // Verificar si se subió el archivo obligatorio
        if ($request->hasFile('documento_principal')) {
            // Almacenar archivo en storage/app/public/documentos/
            $path = $request->file('documento_principal')->store('documentos', 'public');
            
            // Crear registro en tabla documentos
            Documento::create([
                'id_expediente' => $expediente->id_expediente,    // Vincular con el expediente creado
                'nombre' => 'Documento Principal',     // Nombre descriptivo
                'ruta_pdf' => $path,                   // Ruta donde se guardó el archivo
                'tipo' => 'entrada'                    // Tipo: documento de entrada al sistema
            ]);
        }

        // === GUARDAR DOCUMENTOS ADICIONALES (OPCIONALES) ===
        // Verificar si se subieron archivos adicionales
        if ($request->hasFile('documentos_adicionales')) {
            // Iterar sobre cada archivo subido
            foreach ($request->file('documentos_adicionales') as $index => $file) {
                // Almacenar cada archivo por separado
                $path = $file->store('documentos', 'public');
                
                // Crear registro individual para cada documento
                Documento::create([
                    'id_expediente' => $expediente->id_expediente,                    // Mismo expediente
                    'nombre' => 'Documento Adicional ' . ($index + 1),     // Nombre numerado
                    'ruta_pdf' => $path,                                   // Ruta del archivo
                    'tipo' => 'entrada'                                    // Tipo: documento de entrada
                ]);
            }
        }

        // === RESPUESTA EXITOSA AL USUARIO ===
        // Redirigir de vuelta al formulario con mensaje de éxito
        // back(): vuelve a la página anterior
        // with(): pasa datos de sesión flash (se muestran una sola vez)
        return back()->with('success', 'SE ENVIÓ CORRECTAMENTE')      // Mensaje de éxito
                    ->with('codigo_expediente', $codigo);              // Código para mostrar al usuario
    }

    public function acuseRecibo($codigo)
    {
        $expediente = Expediente::where('codigo_expediente', $codigo)
            ->where('id_ciudadano', auth()->id())
            ->with(['tipoTramite', 'area', 'documentos', 'ciudadano'])
            ->firstOrFail();
            
        return view('ciudadano.acuse-recibo', compact('expediente'));
    }

    public function consultaPublica($codigo)
    {
        $expediente = Expediente::where('codigo_expediente', $codigo)
            ->with(['tipoTramite', 'area', 'historial.usuario'])
            ->first();
            
        if (!$expediente) {
            return view('seguimiento.consulta', [
                'expediente' => null,
                'codigo' => $codigo,
                'error' => 'Expediente no encontrado'
            ]);
        }
        
        return view('seguimiento.consulta', compact('expediente', 'codigo'));
    }

    public function marcarNotificacionLeida($id)
    {
        $notificacion = \App\Models\Notificacion::where('id', $id)
            ->where('id_usuario', auth()->id())
            ->firstOrFail();
            
        $notificacion->marcarComoLeida();
        
        return response()->json(['success' => true]);
    }

    public function marcarTodasNotificacionesLeidas()
    {
        \App\Models\Notificacion::where('id_usuario', auth()->id())
            ->where('leida', false)
            ->update(['leida' => true]);
            
        return response()->json(['success' => true]);
    }

    public function seguimientoForm()
    {
        return view('ciudadano.seguimiento-form');
    }

    public function buscarExpediente(Request $request)
    {
        $request->validate([
            'codigo_expediente' => 'required|string',
            'dni' => 'required|string|size:8'
        ]);

        $expediente = Expediente::where('codigo_expediente', $request->codigo_expediente)
            ->whereHas('persona', function($query) use ($request) {
                $query->where('numero_documento', $request->dni);
            })
            ->with(['tipoTramite', 'area', 'documentos', 'historial.usuario', 'derivaciones', 'persona'])
            ->first();

        if (!$expediente) {
            return back()->with('error', 'No se encontró el expediente o el DNI no coincide.');
        }

        return view('ciudadano.seguimiento', compact('expediente'));
    }

    public function eliminarExpediente($id)
    {
        $expediente = Expediente::findOrFail($id);
        
        if (!$expediente->puedeEliminar(auth()->user())) {
            return response()->json(['error' => 'No tiene permisos para eliminar este expediente'], 403);
        }
        
        // Eliminar documentos físicos
        foreach ($expediente->documentos as $documento) {
            if (Storage::disk('public')->exists($documento->ruta_pdf)) {
                Storage::disk('public')->delete($documento->ruta_pdf);
            }
        }
        
        // Eliminar expediente (cascade eliminará documentos, historial, etc.)
        $codigo = $expediente->codigo_expediente;
        $expediente->delete();
        
        return response()->json(['success' => "Expediente {$codigo} eliminado correctamente"]);
    }
}