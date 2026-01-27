<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\TipoTramite;
use App\Models\Documento;
use App\Models\Observacion;
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
        $ciudadanoId = auth()->user()->id;

        // OPTIMIZACIÓN: Una sola consulta para todas las estadísticas (antes eran 4 consultas)
        $estadisticas = Expediente::where('id_ciudadano', $ciudadanoId)
            ->selectRaw("
                COUNT(*) as total_expedientes,
                SUM(CASE WHEN estado IN ('registrado', 'clasificado', 'derivado', 'en_proceso', 'recepcionado') THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN estado = 'resuelto' THEN 1 ELSE 0 END) as resueltos,
                SUM(CASE WHEN estado = 'observado' THEN 1 ELSE 0 END) as observados
            ")
            ->first();

        $stats = [
            'total_expedientes' => $estadisticas->total_expedientes ?? 0,
            'en_proceso' => $estadisticas->en_proceso ?? 0,
            'resueltos' => $estadisticas->resueltos ?? 0,
            'observados' => $estadisticas->observados ?? 0
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
        $expedientes = Expediente::where('id_ciudadano', auth()->user()->id)
            ->with(['tipoTramite', 'area', 'documentos'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('ciudadano.mis-expedientes', compact('expedientes'));
    }

    public function seguimiento($codigo)
    {
        $expediente = Expediente::where('codigo_expediente', $codigo)
            ->where('id_ciudadano', auth()->user()->id)
            ->with(['tipoTramite', 'area', 'documentos', 'historial.usuario', 'derivaciones'])
            ->firstOrFail();
            
        return view('ciudadano.seguimiento', compact('expediente'));
    }

    /**
     * MÉTODO DEPRECADO - Ahora se usa redirección a acuseRecibo()
     * Mantenido por compatibilidad, pero ya no se usa directamente en rutas
     */
    // public function descargarAcuse($codigo)
    // {
    //     $expediente = Expediente::where('codigo_expediente', $codigo)
    //         ->where('id_ciudadano', auth()->id())
    //         ->firstOrFail();
    //     return view('ciudadano.cargo', compact('expediente'));
    // }

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
            'tipo_documento' => 'required|in:DNI,CE,RUC,PASAPORTE,OTROS',        // Tipos de documento válidos

            // Validación dinámica según tipo de documento
            'numero_documento' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $tipoDoc = $request->input('tipo_documento');

                    switch ($tipoDoc) {
                        case 'DNI':
                            // DNI: Exactamente 8 dígitos numéricos
                            if (!preg_match('/^\d{8}$/', $value)) {
                                $fail('El DNI debe contener exactamente 8 dígitos numéricos.');
                            }
                            break;

                        case 'RUC':
                            // RUC: Exactamente 11 dígitos numéricos
                            if (!preg_match('/^\d{11}$/', $value)) {
                                $fail('El RUC debe contener exactamente 11 dígitos numéricos.');
                            }
                            break;

                        case 'CE':
                            // Carnet de Extranjería: 9 o 12 caracteres alfanuméricos
                            if (!preg_match('/^[A-Z0-9]{9}$|^[A-Z0-9]{12}$/', strtoupper($value))) {
                                $fail('El Carnet de Extranjería debe contener 9 o 12 caracteres alfanuméricos.');
                            }
                            break;

                        case 'PASAPORTE':
                            // Pasaporte: 7 a 12 caracteres alfanuméricos
                            if (!preg_match('/^[A-Z0-9]{7,12}$/', strtoupper($value))) {
                                $fail('El Pasaporte debe contener entre 7 y 12 caracteres alfanuméricos.');
                            }
                            break;

                        case 'OTROS':
                            // Otros documentos: 3 a 20 caracteres alfanuméricos
                            if (!preg_match('/^[A-Z0-9\-]{3,20}$/i', $value)) {
                                $fail('El documento debe contener entre 3 y 20 caracteres alfanuméricos.');
                            }
                            break;
                    }
                },
            ],
            
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
            'tipo_documento_entrante' => 'required|in:SOLICITUD,FUT,OFICIO,INFORME,MEMORANDUM,CARTA,RESOLUCION,OTROS', // Tipo de documento
            'folios' => 'required|integer|min:1|max:999',                 // Número de folios
            'asunto' => 'required|string|max:500',                        // Asunto obligatorio
            'descripcion' => 'nullable|string|max:2000',                  // Descripción opcional
            
            // === VALIDACIONES DE ARCHIVOS ===
            'documento_principal' => 'required|file|mimes:pdf|max:10240',  // PDF obligatorio, máx 10MB
            'documentos_adicionales.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // Archivos opcionales, máx 5MB c/u
            
            // === VALIDACIONES ADICIONALES ===
            'prioridad' => 'in:baja,normal,alta,urgente',                  // Prioridades válidas
            'acepta_terminos' => 'required|accepted'                      // Debe aceptar términos
        ]);

        // Normalizar número de documento (convertir a mayúsculas si es CE o PASAPORTE)
        $numeroDocumento = in_array($request->tipo_documento, ['CE', 'PASAPORTE'])
            ? strtoupper($request->numero_documento)
            : $request->numero_documento;

        // === CREAR O BUSCAR PERSONA EN LA BASE DE DATOS ===
        // firstOrCreate: busca por tipo y número de documento, si no existe lo crea
        $persona = \App\Models\Persona::firstOrCreate(
            [
                // Criterios de búsqueda: tipo y número de documento (deben ser únicos)
                'tipo_documento' => $request->tipo_documento,
                'numero_documento' => $numeroDocumento
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
            'tipo_documento_entrante' => $request->tipo_documento_entrante, // Tipo de documento (Solicitud, FUT, etc.)
            'folios' => $request->folios,                               // Número de folios del documento
            'id_ciudadano' => auth()->user()->id,                     // Usuario autenticado que crea el expediente
            'id_persona' => $persona->id_persona,                               // Referencia a la persona (solicitante)
            'remitente' => $persona->nombre_completo,                   // Nombre completo para búsquedas rápidas
            'dni_remitente' => $persona->numero_documento,              // Documento para búsquedas rápidas
            'fecha_registro' => now(),                                  // Fecha y hora actual de registro
            'estado' => 'recepcionado',                                 // Estado inicial del expediente
            'prioridad' => $request->prioridad ?? 'normal',              // Prioridad por defecto
            'canal' => 'virtual'                                        // Canal de ingreso (virtual/presencial)
        ]);

        // === GUARDAR DOCUMENTO PRINCIPAL ===
        // Estructura: expedientes/{año}/{codigo_expediente}/archivo.pdf
        $año = now()->year;
        $carpetaExpediente = "expedientes/{$año}/{$codigo}";

        // Verificar si se subió el archivo obligatorio
        if ($request->hasFile('documento_principal')) {
            $archivo = $request->file('documento_principal');
            $nombreOriginal = $archivo->getClientOriginalName();
            // Limpiar nombre de archivo (quitar caracteres especiales)
            $nombreLimpio = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreOriginal);

            // Almacenar archivo en storage/app/public/expedientes/{año}/{codigo}/
            $path = $archivo->storeAs($carpetaExpediente, $nombreLimpio, 'public');

            // Crear registro en tabla documentos
            Documento::create([
                'id_expediente' => $expediente->id_expediente,
                'nombre' => pathinfo($nombreOriginal, PATHINFO_FILENAME), // Nombre sin extensión
                'ruta_pdf' => $path,
                'tipo' => 'entrada'
            ]);
        }

        // === GUARDAR DOCUMENTOS ADICIONALES (OPCIONALES) ===
        // Verificar si se subieron archivos adicionales
        if ($request->hasFile('documentos_adicionales')) {
            foreach ($request->file('documentos_adicionales') as $index => $file) {
                $nombreOriginal = $file->getClientOriginalName();
                $nombreLimpio = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreOriginal);

                // Almacenar en la misma carpeta del expediente
                $path = $file->storeAs($carpetaExpediente, $nombreLimpio, 'public');

                Documento::create([
                    'id_expediente' => $expediente->id_expediente,
                    'nombre' => pathinfo($nombreOriginal, PATHINFO_FILENAME),
                    'ruta_pdf' => $path,
                    'tipo' => 'entrada'
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

        return view('ciudadano.cargo', compact('expediente'));
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

    /**
     * Muestra las observaciones pendientes del ciudadano
     */
    public function observaciones()
    {
        $ciudadanoId = auth()->user()->id;

        // Obtener expedientes con observaciones pendientes del ciudadano
        $expedientes = Expediente::where('id_ciudadano', $ciudadanoId)
            ->where('estado', 'observado')
            ->with(['observaciones' => function($query) {
                $query->where('estado', 'pendiente')
                      ->orderBy('created_at', 'desc');
            }, 'tipoTramite', 'area'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('ciudadano.observaciones', compact('expedientes'));
    }

    /**
     * Muestra el detalle de una observación específica
     */
    public function verObservacion(Expediente $expediente)
    {
        // Verificar que el expediente pertenece al ciudadano
        if ($expediente->id_ciudadano != auth()->user()->id) {
            abort(403, 'No tiene permisos para ver esta observación');
        }

        // Cargar observaciones pendientes
        $expediente->load(['observaciones' => function($query) {
            $query->where('estado', 'pendiente')
                  ->orderBy('created_at', 'desc');
        }, 'tipoTramite', 'area', 'documentos']);

        return view('ciudadano.ver-observacion', compact('expediente'));
    }

    /**
     * Responde a una observación con documentos adjuntos
     */
    public function responderObservacion(Request $request, Expediente $expediente)
    {
        // Verificar que el expediente pertenece al ciudadano
        if ($expediente->id_ciudadano != auth()->user()->id) {
            return redirect()->back()->with('error', 'No tiene permisos para responder esta observación');
        }

        // Validar datos
        $request->validate([
            'respuesta' => 'required|string|max:1000',
            'documentos.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120'
        ]);

        try {
            \DB::beginTransaction();

            // Actualizar todas las observaciones pendientes con la respuesta
            foreach ($expediente->observaciones()->where('estado', 'pendiente')->get() as $observacion) {
                $observacion->update([
                    'respuesta' => $request->respuesta,
                    'fecha_respuesta' => now(),
                    'estado' => 'respondido'
                ]);
            }

            // Adjuntar documentos de subsanación si existen
            if ($request->hasFile('documentos')) {
                foreach ($request->file('documentos') as $index => $archivo) {
                    $nombreArchivo = 'subsanacion_' . time() . '_' . $index . '.' . $archivo->getClientOriginalExtension();
                    $ruta = $archivo->storeAs('documentos/subsanaciones/' . $expediente->id_expediente, $nombreArchivo, 'public');

                    Documento::create([
                        'id_expediente' => $expediente->id_expediente,
                        'nombre' => 'Subsanación ' . ($index + 1),
                        'tipo' => 'subsanacion',
                        'ruta_pdf' => $ruta
                    ]);
                }
            }

            // Cambiar estado del expediente a "en_proceso" para que el funcionario lo revise
            $expediente->update(['estado' => 'en_proceso']);

            // Registrar en historial
            $expediente->agregarHistorial(
                'Ciudadano respondió observación con documentos de subsanación',
                auth()->id()
            );

            \DB::commit();

            return redirect()->route('ciudadano.observaciones')
                ->with('success', 'Respuesta enviada correctamente. El funcionario revisará su subsanación.');

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Error al enviar respuesta: ' . $e->getMessage());
        }
    }
}