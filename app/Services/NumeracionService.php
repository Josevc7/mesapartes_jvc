<?php

namespace App\Services;

use App\Models\Numeracion;
use Illuminate\Support\Facades\DB;

class NumeracionService
{
    public function generarCodigo()
    {
        return DB::transaction(function () {
            $year = now()->year;
            
            $numeracion = Numeracion::where('año', $year)->first();
            
            if (!$numeracion) {
                $numeracion = Numeracion::create([
                    'año' => $year,
                    'ultimo_numero' => 1
                ]);
                $numero = 1;
            } else {
                $numero = $numeracion->ultimo_numero + 1;
                $numeracion->update(['ultimo_numero' => $numero]);
            }
            
            return $year . '-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        });
    }
}