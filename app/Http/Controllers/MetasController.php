<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Expediente;
use App\Models\Configuracion;

class MetasController extends Controller
{
    public function index()
    {
        $funcionarios = User::where('id_rol', 4)
            ->where('activo', true)
            ->with(['area'])
            ->withCount([
                'expedientesAsignados as total_asignados',
                'expedientesAsignados as resueltos_mes' => function($q) {
                    $q->where('estado', 'Resuelto')
                      ->whereMonth('updated_at', now()->month);
                }
            ])
            ->get()
            ->map(function($funcionario) {
                $metaMensual = Configuracion::where('clave', 'meta_mensual_funcionario')->value('valor') ?? 20;
                $funcionario->meta_mensual = $metaMensual;
                $funcionario->cumplimiento = $funcionario->resueltos_mes > 0 ? 
                    round(($funcionario->resueltos_mes / $metaMensual) * 100) : 0;
                return $funcionario;
            });

        return view('admin.metas.index', compact('funcionarios'));
    }

    public function configurar(Request $request)
    {
        $request->validate([
            'meta_mensual_funcionario' => 'required|integer|min:1',
            'meta_tiempo_atencion' => 'required|integer|min:1',
            'meta_cumplimiento_area' => 'required|integer|min:1|max:100'
        ]);

        foreach ($request->only(['meta_mensual_funcionario', 'meta_tiempo_atencion', 'meta_cumplimiento_area']) as $clave => $valor) {
            Configuracion::updateOrCreate(
                ['clave' => $clave],
                ['valor' => $valor]
            );
        }

        return back()->with('success', 'Metas configuradas correctamente');
    }

    public function reporteKpis()
    {
        $kpis = [
            'expedientes_mes' => Expediente::whereMonth('created_at', now()->month)->count(),
            'tiempo_promedio' => Expediente::where('estado', 'Resuelto')
                ->whereMonth('updated_at', now()->month)
                ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as promedio')
                ->value('promedio') ?? 0,
            'cumplimiento_plazos' => $this->calcularCumplimientoPlazos(),
            'satisfaccion_ciudadano' => 85, // Simulado
            'funcionarios_meta' => User::where('id_rol', 4)
                ->whereHas('expedientesAsignados', function($q) {
                    $q->where('estado', 'Resuelto')
                      ->whereMonth('updated_at', now()->month)
                      ->havingRaw('COUNT(*) >= ?', [20]);
                })->count()
        ];

        return view('admin.metas.kpis', compact('kpis'));
    }

    private function calcularCumplimientoPlazos()
    {
        $totalResueltos = Expediente::where('estado', 'Resuelto')
            ->whereMonth('updated_at', now()->month)
            ->count();

        if ($totalResueltos == 0) return 0;

        $enPlazo = Expediente::where('estado', 'Resuelto')
            ->whereMonth('updated_at', now()->month)
            ->whereHas('tipoTramite', function($q) {
                $q->whereRaw('DATEDIFF(expedientes.updated_at, expedientes.created_at) <= tipo_tramites.dias_limite');
            })
            ->count();

        return round(($enPlazo / $totalResueltos) * 100);
    }
}