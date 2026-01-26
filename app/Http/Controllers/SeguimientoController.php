<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\Derivacion;

class SeguimientoController extends Controller
{
    public function index()
    {
        // Corregido: usar auth()->id() en lugar de auth()->user()->id_usuario
        $expedientes = Expediente::where('id_ciudadano', auth()->id())
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

        // Corregido: usar auth()->id() en lugar de auth()->user()->id_usuario
        if (auth()->check() && $expediente->id_ciudadano !== auth()->id()) {
            abort(403, 'No tienes acceso a este expediente');
        }

        return view('seguimiento.show', compact('expediente'));
    }

    public function consultaForm()
    {
        return view('seguimiento.consulta');
    }

    /**
     * Consulta directa por código (redirige al formulario con el código pre-llenado)
     * Esta ruta es para acceso directo por URL
     */
    public function consulta($codigo)
    {
        // Redirigir al formulario de consulta con el código pre-llenado
        return redirect()->route('seguimiento.form')
            ->with('codigo_prellenado', $codigo)
            ->with('info', 'Ingrese su documento (DNI o RUC) para ver el expediente.');
    }

    /**
     * Buscar expedientes por código y documento del solicitante
     * Permite búsqueda parcial del código y retorna múltiples resultados
     */
    public function buscar(Request $request)
    {
        $request->validate([
            'codigo_expediente' => 'required|string|min:1',
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
            'codigo_expediente.min' => 'Ingrese al menos un carácter para buscar.',
            'tipo_documento.required' => 'Debe seleccionar el tipo de documento.',
            'tipo_documento.in' => 'El tipo de documento debe ser DNI o RUC.',
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.regex' => 'El documento solo debe contener números.',
        ]);

        // Búsqueda con LIKE para permitir códigos parciales
        $expedientes = Expediente::where('codigo_expediente', 'like', '%' . $request->codigo_expediente . '%')
            ->whereHas('persona', function($query) use ($request) {
                $query->where('numero_documento', $request->numero_documento);
            })
            ->with([
                'tipoTramite',
                'area',
                'persona',
                'documentos' => function($query) {
                    $query->where('tipo', 'entrada');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        if ($expedientes->isEmpty()) {
            $tipoDoc = $request->tipo_documento === 'RUC' ? 'RUC' : 'DNI';
            return back()->withInput()->withErrors([
                'numero_documento' => "No se encontraron expedientes con el código '{$request->codigo_expediente}' o el {$tipoDoc} no coincide con el solicitante."
            ]);
        }

        return view('seguimiento.resultado', [
            'expedientes' => $expedientes,
            'documento_busqueda' => $request->numero_documento,
            'tipo_documento' => $request->tipo_documento,
            'codigo_busqueda' => $request->codigo_expediente
        ]);
    }

    /**
     * Obtener movimientos/derivaciones de un expediente (API JSON)
     * Valida que el documento pertenezca al expediente consultado
     */
    public function getMovimientos(Request $request, $idExpediente)
    {
        $request->validate([
            'numero_documento' => 'required|string'
        ]);

        // Verificar que el expediente pertenece al documento proporcionado
        $expediente = Expediente::where('id_expediente', $idExpediente)
            ->whereHas('persona', function($query) use ($request) {
                $query->where('numero_documento', $request->numero_documento);
            })
            ->with(['persona', 'tipoTramite', 'area', 'derivaciones.areaDestino'])
            ->first();

        if (!$expediente) {
            return response()->json([
                'error' => 'Expediente no encontrado o no tiene acceso'
            ], 404);
        }

        // Obtener el área actual desde la última derivación
        $ultimaDerivacion = $expediente->derivaciones->sortByDesc('fecha_derivacion')->first();
        $areaActual = $ultimaDerivacion
            ? ($ultimaDerivacion->areaDestino->nombre ?? 'N/A')
            : ($expediente->area->nombre ?? 'Mesa de Partes');

        // Obtener derivaciones con sus relaciones
        $movimientos = Derivacion::where('id_expediente', $idExpediente)
            ->with([
                'areaOrigen',
                'areaDestino',
                'funcionarioOrigen',
                'funcionarioDestino',
                'funcionarioAsignado',
                'documentos'
            ])
            ->orderBy('fecha_derivacion', 'asc')
            ->get()
            ->map(function($derivacion) {
                // Obtener documento si existe (con manejo seguro)
                $documento = null;
                if ($derivacion->documentos && $derivacion->documentos->count() > 0) {
                    $primerDoc = $derivacion->documentos->first();
                    $documento = [
                        'id' => $primerDoc->id_documento,
                        'nombre' => $primerDoc->nombre,
                        'tipo' => $primerDoc->tipo
                    ];
                }

                return [
                    'id' => $derivacion->id_derivacion,
                    'fecha_movimiento' => $derivacion->fecha_derivacion
                        ? $derivacion->fecha_derivacion->format('d/m/Y H:i')
                        : '-',
                    'area_origen' => $derivacion->areaOrigen->nombre ?? 'N/A',
                    'area_destino' => $derivacion->areaDestino->nombre ?? 'N/A',
                    'funcionario_origen' => $derivacion->funcionarioOrigen->name ?? 'Sin asignar',
                    'recepcionado_por' => $derivacion->funcionarioDestino->name
                        ?? $derivacion->funcionarioAsignado->name
                        ?? 'Sin asignar',
                    'documento' => $documento,
                    'fecha_recepcion' => $derivacion->fecha_recepcion
                        ? $derivacion->fecha_recepcion->format('d/m/Y H:i')
                        : 'Pendiente',
                    'fecha_limite' => $derivacion->fecha_limite
                        ? $derivacion->fecha_limite->format('d/m/Y')
                        : '-',
                    'plazo_dias' => $derivacion->plazo_dias ?? '-',
                    'estado' => strtoupper($derivacion->estado ?? 'pendiente'),
                    'observaciones' => $derivacion->observaciones ?? '-'
                ];
            });

        return response()->json([
            'expediente' => [
                'id' => $expediente->id_expediente,
                'codigo' => $expediente->codigo_expediente,
                'asunto' => $expediente->asunto,
                'estado_actual' => ucfirst(str_replace('_', ' ', $expediente->estado)),
                'tipo_tramite' => $expediente->tipoTramite->nombre ?? 'N/A',
                'area_actual' => $areaActual, // Corregido: área desde última derivación
                'fecha_registro' => $expediente->created_at->format('d/m/Y H:i'),
                'solicitante' => $expediente->persona->nombre_completo ?? $expediente->persona->razon_social ?? 'N/A'
            ],
            'movimientos' => $movimientos,
            'total_movimientos' => $movimientos->count()
        ]);
    }

    /**
     * Eliminar expediente (solo Admin y Mesa de Partes)
     */
    public function eliminarExpediente($idExpediente)
    {
        $expediente = Expediente::findOrFail($idExpediente);

        // Verificar permisos usando el método del modelo
        if (!auth()->check() || !$expediente->puedeEliminar(auth()->user())) {
            return response()->json([
                'error' => 'No tiene permisos para eliminar este expediente'
            ], 403);
        }

        $codigo = $expediente->codigo_expediente;
        $expediente->delete();

        return response()->json([
            'success' => "Expediente {$codigo} eliminado correctamente"
        ]);
    }
}
