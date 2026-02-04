<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Numeracion;
use Illuminate\Support\Facades\DB;

class NumeracionService
{
    /**
     * Genera código global de expediente (Mesa de Partes)
     * Formato: AAAA-NNNNNN (ej: 2026-000001)
     */
    public function generarCodigo()
    {
        return DB::transaction(function () {
            $year = now()->year;

            $numeracion = Numeracion::where('año', $year)
                ->whereNull('id_area')
                ->first();

            if (!$numeracion) {
                $numeracion = Numeracion::create([
                    'año' => $year,
                    'id_area' => null,
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

    /**
     * Genera código de registro por área
     * Formato: AAAA-SIGLAS-NNNNNN (ej: 2026-DIR-000001)
     */
    public function generarCodigoPorArea(int $idArea): string
    {
        return DB::transaction(function () use ($idArea) {
            $year = now()->year;

            // Obtener el área con sus siglas
            $area = Area::findOrFail($idArea);
            $siglas = $area->siglas ?? 'AREA' . $idArea;

            // Buscar o crear numeración para este área y año
            $numeracion = Numeracion::where('año', $year)
                ->where('id_area', $idArea)
                ->first();

            if (!$numeracion) {
                $numeracion = Numeracion::create([
                    'año' => $year,
                    'id_area' => $idArea,
                    'ultimo_numero' => 1
                ]);
                $numero = 1;
            } else {
                $numero = $numeracion->ultimo_numero + 1;
                $numeracion->update(['ultimo_numero' => $numero]);
            }

            return $year . '-' . $siglas . '-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        });
    }
}