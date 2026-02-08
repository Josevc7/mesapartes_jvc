<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\TipoTramite;
use App\Models\Documento;
use App\Services\NumeracionService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\EstadoExpediente as EstadoExpedienteModel;
use App\Enums\EstadoExpediente as EstadoExpedienteEnum;

class CiudadanoController extends Controller
{
    /**
     * Muestra el dashboard principal del ciudadano con estadísticas y expedientes recientes
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Obtener el ID del usuario autenticado (ciudadano logueado)
        //$ciudadanoId = auth()->user()->id;
        $ciudadanoId = auth()->id();
       

        // OPTIMIZACIÓN: Una sola consulta para todas las estadísticas
        $idEnProceso = EstadoExpedienteModel::whereIn('slug', ['registrado', 'clasificado', 'derivado', 'en_proceso', 'recepcionado'])->pluck('id_estado')->toArray();
        $idResuelto = EstadoExpedienteModel::where('slug', 'resuelto')->value('id_estado');
        $idObservado = EstadoExpedienteModel::where('slug', 'observado')->value('id_estado');

        // Proteger contra IN() vacío: si no hay estados, usar 0 (nunca coincide)
        $inClause = !empty($idEnProceso) ? implode(',', $idEnProceso) : '0';

        $estadisticas = Expediente::where('id_ciudadano', $ciudadanoId)
            ->selectRaw("
                COUNT(*) as total_expedientes,
                SUM(CASE WHEN id_estado IN ({$inClause}) THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN id_estado = ? THEN 1 ELSE 0 END) as resueltos,
                SUM(CASE WHEN id_estado = ? THEN 1 ELSE 0 END) as observados
            ", [$idResuelto, $idObservado])
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
        $expedientes = Expediente::where('id_ciudadano', auth()->id())
            ->with(['tipoTramite', 'area', 'documentos'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('ciudadano.mis-expedientes', compact('expedientes'));
    }

    public function seguimiento($codigo)
    {
        $expediente = Expediente::where('codigo_expediente', $codigo)
            ->where('id_ciudadano', auth()->id())
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
        
        $extension = pathinfo($documento->ruta_pdf, PATHINFO_EXTENSION);
        $nombreDescarga = $documento->nombre . '.' . $extension;
        return Storage::disk('public')->download($documento->ruta_pdf, $nombreDescarga);
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

         // 🔹 OBTENER ESTADO INICIAL (ANTES DE LA TRANSACCIÓN)
         $idEstadoInicial = EstadoExpedienteModel::where(
         'slug',
          EstadoExpedienteEnum::RECEPCIONADO->value
         )->value('id_estado');

        
         if (!$idEstadoInicial) {
         return back()
         ->withInput()
         ->with('error', 'No existe el estado inicial RECEPCIONADO en estados_expediente.');
         }
         // 🔹 ahora recién inicia la transacción
         $archivosGuardados = [];

        try {
             
            DB::beginTransaction();

            // === CREAR O ACTUALIZAR PERSONA EN LA BASE DE DATOS ===
            $persona = \App\Models\Persona::firstOrCreate(
                [
                    'tipo_documento' => $request->tipo_documento,
                    'numero_documento' => $numeroDocumento
                ],
                [
                    'tipo_persona' => $request->tipo_persona,
                    'nombres' => $request->nombres,
                    'apellido_paterno' => $request->apellido_paterno,
                    'apellido_materno' => $request->apellido_materno,
                    'razon_social' => $request->razon_social,
                    'representante_legal' => $request->representante_legal,
                    'telefono' => $request->telefono,
                    'email' => $request->email,
                    'direccion' => $request->direccion
                ]
            );

            // Si la persona ya existía, actualizar sus datos de contacto
            if (!$persona->wasRecentlyCreated) {
                $persona->update(array_filter([
                    'nombres' => $request->nombres,
                    'apellido_paterno' => $request->apellido_paterno,
                    'apellido_materno' => $request->apellido_materno,
                    'razon_social' => $request->razon_social,
                    'representante_legal' => $request->representante_legal,
                    'telefono' => $request->telefono,
                    'email' => $request->email,
                    'direccion' => $request->direccion
                ], fn($v) => $v !== null));
            }

            // === GENERAR CÓDIGO ÚNICO DEL EXPEDIENTE ===
            $codigo = app(NumeracionService::class)->generarCodigo();

            // === CREAR EL EXPEDIENTE EN LA BASE DE DATOS ===
            $expediente = Expediente::create([
                'codigo_expediente' => $codigo,
                'asunto' => $request->asunto,
                'descripcion' => $request->descripcion,
                'id_tipo_tramite' => $request->id_tipo_tramite,
                'tipo_documento_entrante' => $request->tipo_documento_entrante,
                'folios' => $request->folios,
                'id_ciudadano' => auth()->id(),
                'id_persona' => $persona->id_persona,
                'remitente' => $persona->nombre_completo,
                'dni_remitente' => $persona->numero_documento,
                'fecha_registro' => now(),
                'id_estado' => $idEstadoInicial,
                'prioridad' => $request->prioridad ?? 'normal',
                'canal' => 'virtual'
            ]);

            // === GUARDAR DOCUMENTO PRINCIPAL ===
            $anio = now()->year;
            $carpetaExpediente = "expedientes/{$anio}/{$codigo}";

            if ($request->hasFile('documento_principal')) {
                $archivo = $request->file('documento_principal');
                $nombreOriginal = $archivo->getClientOriginalName();
                $extension = $archivo->getClientOriginalExtension();
                $nombreBase = pathinfo($nombreOriginal, PATHINFO_FILENAME);
                $nombreLimpio = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreBase);
                // uniqid evita colisión si dos archivos tienen el mismo nombre
                $nombreFinal = $nombreLimpio . '_' . uniqid() . '.' . $extension;

                $path = $archivo->storeAs($carpetaExpediente, $nombreFinal, 'public');
                $archivosGuardados[] = $path;

                Documento::create([
                    'id_expediente' => $expediente->id_expediente,
                    'nombre' => $nombreBase,
                    'ruta_pdf' => $path,
                    'tipo' => 'entrada'
                ]);
            }

            // === GUARDAR DOCUMENTOS ADICIONALES (OPCIONALES) ===
            if ($request->hasFile('documentos_adicionales')) {
                foreach ($request->file('documentos_adicionales') as $file) {
                    $nombreOriginal = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $nombreBase = pathinfo($nombreOriginal, PATHINFO_FILENAME);
                    $nombreLimpio = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreBase);
                    $nombreFinal = $nombreLimpio . '_' . uniqid() . '.' . $extension;

                    $path = $file->storeAs($carpetaExpediente, $nombreFinal, 'public');
                    $archivosGuardados[] = $path;

                    Documento::create([
                        'id_expediente' => $expediente->id_expediente,
                        'nombre' => $nombreBase,
                        'ruta_pdf' => $path,
                        'tipo' => 'entrada'
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'SE ENVIÓ CORRECTAMENTE')
                        ->with('codigo_expediente', $codigo);

        } catch (\Exception $e) {
            DB::rollBack();

            // Limpiar archivos ya guardados en disco
            foreach ($archivosGuardados as $archivoPath) {
                Storage::disk('public')->delete($archivoPath);
            }

            return back()->withInput()
                        ->with('error', 'Error al registrar el expediente. Intente nuevamente.');
        }
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
            'numero_documento' => 'required|string|min:3|max:20'
        ]);

        $expediente = Expediente::where('codigo_expediente', $request->codigo_expediente)
            ->whereHas('persona', function($query) use ($request) {
                $query->where('numero_documento', $request->numero_documento);
            })
            ->with(['tipoTramite', 'area', 'documentos', 'historial.usuario', 'derivaciones', 'persona'])
            ->first();

        if (!$expediente) {
            return back()->with('error', 'No se encontró el expediente o el número de documento no coincide.');
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
        $ciudadanoId = auth()->id();

        // Obtener expedientes con observaciones pendientes del ciudadano
        $expedientes = Expediente::where('id_ciudadano', $ciudadanoId)
            ->whereHas('estadoExpediente', fn($q) => $q->where('slug', EstadoExpediente::OBSERVADO->value))
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
        if ($expediente->id_ciudadano != auth()->id()) {
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
        if ($expediente->id_ciudadano != auth()->id()) {
            return redirect()->back()->with('error', 'No tiene permisos para responder esta observación');
        }

        // Validar datos
        $request->validate([
            'respuesta' => 'required|string|max:1000',
            'documentos.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120'
        ]);

        try {
            DB::beginTransaction();

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
            //$expediente->estado = EstadoExpediente::EN_PROCESO->value;
            //$expediente->save();
            
            $idEnProceso = EstadoExpedienteModel::where('slug', EstadoExpedienteEnum::EN_PROCESO->value)->value('id_estado');
            if (!$idEnProceso) {
            throw new \Exception('No existe el estado EN_PROCESO en estados_expediente.');
            }
            $expediente->id_estado = $idEnProceso;
            $expediente->save();


            // Registrar en historial
            $expediente->agregarHistorial(
                'Ciudadano respondió observación con documentos de subsanación',
                auth()->id()
            );

            DB::commit();

            return redirect()->route('ciudadano.observaciones')
                ->with('success', 'Respuesta enviada correctamente. El funcionario revisará su subsanación.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al enviar respuesta: ' . $e->getMessage());
        }
    }
}