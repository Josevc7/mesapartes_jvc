<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\Area;
use App\Models\TipoTramite;

class ReporteController extends Controller
{
    public function index()
    {
        $stats = [
            'total_expedientes' => Expediente::count(),
            'expedientes_mes' => Expediente::whereMonth('created_at', now()->month)->count(),
            'pendientes' => Expediente::whereIn('estado', ['Registrado', 'Clasificado'])->count(),
            'resueltos' => Expediente::where('estado', 'Resuelto')->count()
        ];
        
        return view('reportes.index', compact('stats'));
    }

    public function tramitesPorMes()
    {
        $tramites = Expediente::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('mes')
            ->get();
            
        return response()->json($tramites);
    }

    public function tiemposAtencion()
    {
        $tiempos = Expediente::where('estado', 'Resuelto')
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as promedio_dias')
            ->first();
            
        return response()->json($tiempos);
    }
}