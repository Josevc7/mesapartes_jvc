<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    // Usar clave primaria personalizada
    protected $primaryKey = 'id_area';

    protected $fillable = [
        'nombre',
        'siglas',
        'descripcion',
        'id_jefe',
        'activo',
        'id_area_padre',
        'nivel'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Constantes para niveles jerárquicos
    const NIVEL_DIRECCION_REGIONAL = 'DIRECCION_REGIONAL';
    const NIVEL_OCI = 'OCI';
    const NIVEL_DIRECCION = 'DIRECCION';
    const NIVEL_SUBDIRECCION = 'SUBDIRECCION';
    const NIVEL_RESIDENCIA = 'RESIDENCIA';

    public static function getNiveles()
    {
        return [
            self::NIVEL_DIRECCION_REGIONAL => 'Dirección Regional',
            self::NIVEL_OCI => 'Órgano de Control Institucional',
            self::NIVEL_DIRECCION => 'Dirección',
            self::NIVEL_SUBDIRECCION => 'Subdirección / Oficina',
            self::NIVEL_RESIDENCIA => 'Residencia Vial',
        ];
    }

    // ========== RELACIONES DE JERARQUÍA ==========

    /**
     * Área padre (superior jerárquico)
     */
    public function areaPadre()
    {
        return $this->belongsTo(Area::class, 'id_area_padre', 'id_area');
    }

    /**
     * Sub-áreas (hijos directos)
     */
    public function subAreas()
    {
        return $this->hasMany(Area::class, 'id_area_padre', 'id_area');
    }

    /**
     * Sub-áreas activas
     */
    public function subAreasActivas()
    {
        return $this->hasMany(Area::class, 'id_area_padre', 'id_area')->where('activo', true);
    }

    /**
     * Obtener todas las sub-áreas recursivamente (descendientes)
     */
    public function getDescendientes()
    {
        $descendientes = collect();
        foreach ($this->subAreas as $subArea) {
            $descendientes->push($subArea);
            $descendientes = $descendientes->merge($subArea->getDescendientes());
        }
        return $descendientes;
    }

    /**
     * Obtener la cadena de áreas padres (ancestros)
     */
    public function getAncestros()
    {
        $ancestros = collect();
        $padre = $this->areaPadre;
        while ($padre) {
            $ancestros->push($padre);
            $padre = $padre->areaPadre;
        }
        return $ancestros;
    }

    /**
     * Obtener el nombre completo con jerarquía
     */
    public function getNombreCompletoAttribute()
    {
        $ancestros = $this->getAncestros()->reverse();
        if ($ancestros->isEmpty()) {
            return $this->nombre;
        }
        return $ancestros->pluck('nombre')->push($this->nombre)->implode(' > ');
    }

    /**
     * Verificar si es área raíz (sin padre)
     */
    public function esRaiz()
    {
        return is_null($this->id_area_padre);
    }

    /**
     * Verificar si tiene sub-áreas
     */
    public function tieneSubAreas()
    {
        return $this->subAreas()->exists();
    }

    /**
     * Obtener el nivel de profundidad en el árbol (0 = raíz)
     */
    public function getProfundidad()
    {
        return $this->getAncestros()->count();
    }

    // ========== RELACIONES EXISTENTES ==========

    public function jefe()
    {
        return $this->belongsTo(User::class, 'id_jefe', 'id');
    }

    public function funcionarios()
    {
        return $this->hasMany(User::class, 'id_area', 'id_area');
    }

    /**
     * Funcionarios del área y todas sus sub-áreas
     */
    public function funcionariosConSubAreas()
    {
        $idsAreas = collect([$this->id_area]);
        $idsAreas = $idsAreas->merge($this->getDescendientes()->pluck('id_area'));
        return User::whereIn('id_area', $idsAreas)->get();
    }

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'id_area', 'id_area');
    }

    public function tipoTramites()
    {
        return $this->hasMany(TipoTramite::class, 'id_area', 'id_area');
    }

    public function derivacionesOrigen()
    {
        return $this->hasMany(Derivacion::class, 'id_area_origen', 'id_area');
    }

    public function derivacionesDestino()
    {
        return $this->hasMany(Derivacion::class, 'id_area_destino', 'id_area');
    }

    // ========== SCOPES ==========

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeRaices($query)
    {
        return $query->whereNull('id_area_padre');
    }

    public function scopePorNivel($query, $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    public function scopeDirecciones($query)
    {
        return $query->whereIn('nivel', [self::NIVEL_DIRECCION_REGIONAL, self::NIVEL_OCI, self::NIVEL_DIRECCION]);
    }

    /**
     * Scope para obtener subdirecciones directas de un área
     * Reemplaza: Area::where('id_area_padre', $areaId)->pluck('id_area')->toArray()
     */
    public function scopeSubdireccionesDeArea($query, int $areaId)
    {
        return $query->where('id_area_padre', $areaId)->where('activo', true);
    }

    /**
     * Obtener IDs de subdirecciones como array (método estático)
     * Uso: Area::getSubdireccionesIds($areaId)
     */
    public static function getSubdireccionesIds(int $areaId): array
    {
        return static::subdireccionesDeArea($areaId)->pluck('id_area')->toArray();
    }

    /**
     * Obtener IDs del área y sus subdirecciones
     * Uso: Area::getAreaYSubdireccionesIds($areaId)
     */
    public static function getAreaYSubdireccionesIds(int $areaId): array
    {
        $subdirecciones = static::getSubdireccionesIds($areaId);
        return array_merge([$areaId], $subdirecciones);
    }

    /**
     * Obtener árbol completo de áreas para select jerárquico
     */
    public static function getArbolParaSelect($soloActivos = true)
    {
        $query = self::query()->orderBy('nivel')->orderBy('nombre');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        $areas = $query->get();
        $resultado = [];

        foreach ($areas->where('id_area_padre', null) as $raiz) {
            $resultado[] = [
                'id' => $raiz->id_area,
                'nombre' => $raiz->nombre,
                'nivel' => $raiz->nivel,
                'profundidad' => 0
            ];
            self::agregarHijosAlArbol($resultado, $raiz, $areas, 1);
        }

        return $resultado;
    }

    private static function agregarHijosAlArbol(&$resultado, $padre, $todas, $profundidad)
    {
        $hijos = $todas->where('id_area_padre', $padre->id_area);
        foreach ($hijos as $hijo) {
            $resultado[] = [
                'id' => $hijo->id_area,
                'nombre' => str_repeat('— ', $profundidad) . $hijo->nombre,
                'nivel' => $hijo->nivel,
                'profundidad' => $profundidad
            ];
            self::agregarHijosAlArbol($resultado, $hijo, $todas, $profundidad + 1);
        }
    }
}
