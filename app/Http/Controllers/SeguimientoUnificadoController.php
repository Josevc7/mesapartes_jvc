<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use App\Models\HistorialExpediente;
use Illuminate\Http\Request;

class SeguimientoUnificadoController extends Controller
{
    /**
     * Lista de expedientes según el rol del usuario
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $rol = $user->role?->nombre;

        $query = Expediente::with(['tipoTramite', 'area', 'ciudadano', 'funcionarioAsignado', 'persona'])
            ->orderBy('created_at', 'desc');

        // Filtrar según rol
        switch ($rol) {
            case 'Administrador':
            case 'Mesa de Partes':
                // Ven todos los expedientes
                break;

            case 'Jefe de Área':
                // Solo expedientes de su área
                $query->where('id_area', $user->id_area);
                break;

            case 'Funcionario':
                // Solo expedientes asignados a él
                $query->where('id_funcionario_asignado', $user->id);
                break;

            case 'Ciudadano':
                // Solo sus propios expedientes
                $query->where('id_ciudadano', $user->id);
                break;

            default:
                // Sin rol definido, no ve nada
                $query->whereRaw('1 = 0');
        }

        // Filtros opcionales
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('area') && in_array($rol, ['Administrador', 'Mesa de Partes'])) {
            $query->where('id_area', $request->area);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('codigo_expediente', 'like', "%{$buscar}%")
                  ->orWhere('asunto', 'like', "%{$buscar}%")
                  ->orWhereHas('persona', function ($q2) use ($buscar) {
                      $q2->where('nombres', 'like', "%{$buscar}%")
                         ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                         ->orWhere('numero_documento', 'like', "%{$buscar}%");
                  });
            });
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $expedientes = $query->paginate(15)->withQueryString();

        // Obtener áreas para filtro (solo admin y mesa de partes)
        $areas = [];
        if (in_array($rol, ['Administrador', 'Mesa de Partes'])) {
            $areas = \App\Models\Area::where('activo', true)->orderBy('nombre')->get();
        }

        return view('seguimiento.panel', [
            'expedientes' => $expedientes,
            'estados' => Expediente::ESTADOS,
            'areas' => $areas,
            'rol' => $rol,
        ]);
    }

    /**
     * Detalle de un expediente con historial
     */
    public function show($codigo)
    {
        $user = auth()->user();
        $rol = $user->role?->nombre;

        $expediente = Expediente::with([
            'tipoTramite',
            'area',
            'ciudadano',
            'funcionarioAsignado',
            'persona',
            'documentos',
            'derivaciones.areaOrigen',
            'derivaciones.areaDestino',
            'derivaciones.funcionarioAsignado',
            'observaciones',
            'resolucion'
        ])->where('codigo_expediente', $codigo)->firstOrFail();

        // Verificar acceso según rol
        if (!$this->tieneAcceso($user, $expediente)) {
            abort(403, 'No tiene permiso para ver este expediente.');
        }

        // Cargar historial según rol
        $historial = $expediente->historial()
            ->with(['usuario', 'area'])
            ->orderBy('fecha', 'asc')
            ->get();

        // Para ciudadano: usar descripción pública
        $esCiudadano = $rol === 'Ciudadano';

        return view('seguimiento.detalle', [
            'expediente' => $expediente,
            'historial' => $historial,
            'rol' => $rol,
            'esCiudadano' => $esCiudadano,
        ]);
    }

    /**
     * API JSON del historial para AJAX
     */
    public function historial($codigo)
    {
        $user = auth()->user();
        $rol = $user->role?->nombre;

        $expediente = Expediente::where('codigo_expediente', $codigo)->firstOrFail();

        if (!$this->tieneAcceso($user, $expediente)) {
            return response()->json(['error' => 'Sin acceso'], 403);
        }

        $historial = $expediente->historial()
            ->with(['usuario', 'area'])
            ->orderBy('fecha', 'asc')
            ->get();

        // Para ciudadano: ocultar datos sensibles
        if ($rol === 'Ciudadano') {
            $historial = $historial->map(function ($item) {
                return [
                    'fecha' => $item->fecha?->format('d/m/Y H:i'),
                    'accion' => $item->accion_legible,
                    'descripcion' => $item->descripcion_publica,
                    'estado' => $item->estado,
                    'area' => $item->area?->nombre,
                ];
            });
        } else {
            $historial = $historial->map(function ($item) {
                return [
                    'fecha' => $item->fecha?->format('d/m/Y H:i'),
                    'funcionario' => $item->usuario?->name,
                    'area' => $item->area?->nombre,
                    'accion' => $item->accion_legible,
                    'descripcion' => $item->descripcion,
                    'detalle' => $item->detalle,
                    'estado' => $item->estado,
                ];
            });
        }

        return response()->json($historial);
    }

    /**
     * Verifica si el usuario tiene acceso al expediente
     */
    private function tieneAcceso($user, $expediente): bool
    {
        $rol = $user->role?->nombre;

        return match ($rol) {
            'Administrador', 'Mesa de Partes' => true,
            'Jefe de Área' => $expediente->id_area === $user->id_area,
            'Funcionario' => $expediente->id_funcionario_asignado === $user->id,
            'Ciudadano' => $expediente->id_ciudadano === $user->id,
            default => false,
        };
    }
}
