<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Derivacion;
use App\Models\Expediente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DerivacionService
{
    protected NumeracionService $numeracionService;

    public function __construct(NumeracionService $numeracionService)
    {
        $this->numeracionService = $numeracionService;
    }
    /**
     * Crea una nueva derivación de expediente
     */
    public function derivarExpediente(
        Expediente $expediente,
        int $areaDestinoId,
        ?int $funcionarioAsignadoId,
        int $plazoDias,
        string $prioridad,
        ?string $observaciones = null
    ): Derivacion {
        return DB::transaction(function () use (
            $expediente,
            $areaDestinoId,
            $funcionarioAsignadoId,
            $plazoDias,
            $prioridad,
            $observaciones
        ) {
            $fechaLimite = now()->addDays($plazoDias);

            // Crear la derivación (sin número de registro aún - se genera al recepcionar)
            $derivacion = Derivacion::create([
                'id_expediente' => $expediente->id_expediente,
                'id_area_origen' => $expediente->id_area ?? auth()->user()->id_area,
                'id_area_destino' => $areaDestinoId,
                'numero_registro_area' => null,
                'id_funcionario_origen' => auth()->id(),
                'id_funcionario_asignado' => $funcionarioAsignadoId,
                'fecha_derivacion' => now(),
                'plazo_dias' => $plazoDias,
                'fecha_limite' => $fechaLimite,
                'observaciones' => $observaciones,
                'estado' => 'pendiente'
            ]);

            // Actualizar el expediente
            $expediente->estado = 'derivado';
            $expediente->update([
                'id_area' => $areaDestinoId,
                'id_funcionario_asignado' => $funcionarioAsignadoId,
                'prioridad' => $prioridad
            ]);
            $expediente->save();

            // Registrar en historial
            $mensaje = $this->generarMensajeHistorial($derivacion);
            $expediente->agregarHistorial($mensaje, auth()->id());

            return $derivacion;
        });
    }

    /**
     * Extiende el plazo de una derivación
     */
    public function extenderPlazo(
        Expediente $expediente,
        int $diasAdicionales,
        string $motivo
    ): void {
        DB::transaction(function () use ($expediente, $diasAdicionales, $motivo) {
            $derivacion = $expediente->derivaciones()
                ->where('estado', 'pendiente')
                ->latest()
                ->first();

            if (!$derivacion) {
                throw new \Exception('No se encontró una derivación activa');
            }

            $nuevoPlazo = $derivacion->plazo_dias + $diasAdicionales;
            $nuevaFechaLimite = Carbon::parse($derivacion->fecha_derivacion)
                ->addDays($nuevoPlazo);

            $derivacion->update([
                'plazo_dias' => $nuevoPlazo,
                'fecha_limite' => $nuevaFechaLimite
            ]);

            $expediente->agregarHistorial(
                "Plazo extendido {$diasAdicionales} días. Motivo: {$motivo}",
                auth()->id()
            );
        });
    }

    /**
     * Obtiene derivaciones vencidas de un área
     */
    public function obtenerDerivacionesVencidas(int $areaId)
    {
        return Derivacion::where('id_area_destino', $areaId)
            ->where('estado', 'pendiente')
            ->where('fecha_limite', '<', now())
            ->with(['expediente', 'funcionarioAsignado'])
            ->get();
    }

    /**
     * Obtiene derivaciones por vencer (próximos 3 días)
     */
    public function obtenerDerivacionesPorVencer(int $areaId)
    {
        return Derivacion::where('id_area_destino', $areaId)
            ->where('estado', 'pendiente')
            ->whereBetween('fecha_limite', [now(), now()->addDays(3)])
            ->with(['expediente', 'funcionarioAsignado'])
            ->get();
    }

    /**
     * Recepciona un expediente derivado - genera el número de registro del área
     * Este es el momento oficial donde el área acepta el documento
     */
    public function recepcionarExpediente(Derivacion $derivacion): Derivacion
    {
        return DB::transaction(function () use ($derivacion) {
            // Generar número de registro para el área que recepciona
            $numeroRegistroArea = $this->numeracionService->generarCodigoPorArea($derivacion->id_area_destino);

            $derivacion->update([
                'estado' => 'recepcionado',
                'fecha_recepcion' => now(),
                'numero_registro_area' => $numeroRegistroArea
            ]);

            // Actualizar estado del expediente
            $derivacion->expediente->estado = 'en_proceso';
            $derivacion->expediente->save();

            $areaDestino = $derivacion->areaDestino->nombre ?? 'Área';
            $derivacion->expediente->agregarHistorial(
                "Expediente recepcionado por {$areaDestino}. Número de registro: {$numeroRegistroArea}",
                auth()->id()
            );

            return $derivacion;
        });
    }

    /**
     * Marca una derivación como atendida (después de recepcionar)
     */
    public function marcarComoAtendida(Derivacion $derivacion): void
    {
        DB::transaction(function () use ($derivacion) {
            $derivacion->update([
                'estado' => 'atendido',
                'fecha_atencion' => now()
            ]);

            $derivacion->expediente->agregarHistorial(
                'Derivación atendida',
                auth()->id()
            );
        });
    }

    /**
     * Genera mensaje descriptivo para el historial
     */
    protected function generarMensajeHistorial(Derivacion $derivacion): string
    {
        $areaDestino = $derivacion->areaDestino->nombre ?? 'Sin área';
        $mensaje = "Expediente derivado a {$areaDestino}";

        if ($derivacion->numero_registro_area) {
            $mensaje .= " (Registro: {$derivacion->numero_registro_area})";
        }

        if ($derivacion->funcionarioAsignado) {
            $funcionario = $derivacion->funcionarioAsignado->name;
            $mensaje .= " - Asignado a: {$funcionario}";
        }

        if ($derivacion->plazo_dias) {
            $mensaje .= " - Plazo: {$derivacion->plazo_dias} días";
        }

        return $mensaje;
    }

    /**
     * Reasigna un expediente a otro funcionario
     */
    public function reasignarExpediente(
        Expediente $expediente,
        int $nuevoFuncionarioId,
        string $motivo
    ): void {
        DB::transaction(function () use ($expediente, $nuevoFuncionarioId, $motivo) {
            $funcionarioAnterior = $expediente->funcionarioAsignado?->name ?? 'No asignado';
            $nuevoFuncionario = User::findOrFail($nuevoFuncionarioId);

            $expediente->update([
                'id_funcionario_asignado' => $nuevoFuncionarioId
            ]);

            // Actualizar la derivación activa
            $derivacionActiva = $expediente->derivaciones()
                ->where('estado', 'pendiente')
                ->latest()
                ->first();

            if ($derivacionActiva) {
                $derivacionActiva->update([
                    'id_funcionario_asignado' => $nuevoFuncionarioId
                ]);
            }

            $expediente->agregarHistorial(
                "Reasignado de {$funcionarioAnterior} a {$nuevoFuncionario->name}. Motivo: {$motivo}",
                auth()->id()
            );
        });
    }

    // ========== MÉTODOS DE DERIVACIÓN JERÁRQUICA ==========

    /**
     * Obtiene las áreas disponibles para derivación según jerarquía
     * - Mesa de Partes: puede derivar a cualquier Dirección
     * - Director Regional: puede derivar a cualquier Dirección
     * - Director de área: puede derivar a sus Subdirecciones o a otras Direcciones
     * - Subdirector: puede derivar dentro de su misma Dirección o devolver al Director
     */
    public function obtenerAreasParaDerivacion(User $usuario): array
    {
        $areaUsuario = $usuario->area;
        $rol = $usuario->role?->nombre ?? '';

        // Administrador o Mesa de Partes: todas las áreas activas
        if (in_array($rol, ['Administrador', 'Mesa de Partes'])) {
            return $this->formatearAreasJerarquicas(Area::activos()->get());
        }

        // Si el usuario no tiene área asignada
        if (!$areaUsuario) {
            return [];
        }

        $areasDisponibles = collect();

        // Jefe de Área o Director
        if ($rol === 'Jefe de Área') {
            // Puede derivar a sus sub-áreas
            $areasDisponibles = $areasDisponibles->merge($areaUsuario->subAreasActivas);

            // Puede derivar a otras direcciones del mismo nivel o nivel superior
            $areasDisponibles = $areasDisponibles->merge(
                Area::activos()
                    ->whereIn('nivel', [Area::NIVEL_DIRECCION_REGIONAL, Area::NIVEL_OCI, Area::NIVEL_DIRECCION])
                    ->where('id_area', '!=', $areaUsuario->id_area)
                    ->get()
            );

            // También puede derivar a su área padre (devolver)
            if ($areaUsuario->areaPadre && $areaUsuario->areaPadre->activo) {
                $areasDisponibles->push($areaUsuario->areaPadre);
            }
        }

        // Funcionario
        if ($rol === 'Funcionario') {
            // Puede derivar dentro de su misma área (otros funcionarios)
            // Puede derivar a su jefe (área actual)
            $areasDisponibles->push($areaUsuario);

            // Puede derivar al área padre (devolver al Director)
            if ($areaUsuario->areaPadre && $areaUsuario->areaPadre->activo) {
                $areasDisponibles->push($areaUsuario->areaPadre);
            }

            // Puede derivar a áreas hermanas (mismo padre)
            if ($areaUsuario->id_area_padre) {
                $hermanas = Area::activos()
                    ->where('id_area_padre', $areaUsuario->id_area_padre)
                    ->where('id_area', '!=', $areaUsuario->id_area)
                    ->get();
                $areasDisponibles = $areasDisponibles->merge($hermanas);
            }
        }

        return $this->formatearAreasJerarquicas($areasDisponibles->unique('id_area'));
    }

    /**
     * Formatea las áreas en estructura jerárquica para el frontend
     */
    protected function formatearAreasJerarquicas($areas): array
    {
        $resultado = [];

        // Agrupar por nivel
        $direccionRegional = $areas->where('nivel', Area::NIVEL_DIRECCION_REGIONAL);
        $oci = $areas->where('nivel', Area::NIVEL_OCI);
        $direcciones = $areas->where('nivel', Area::NIVEL_DIRECCION);
        $subdirecciones = $areas->where('nivel', Area::NIVEL_SUBDIRECCION);
        $residencias = $areas->where('nivel', Area::NIVEL_RESIDENCIA);

        // Dirección Regional
        foreach ($direccionRegional as $area) {
            $resultado[] = [
                'id' => $area->id_area,
                'nombre' => $area->nombre,
                'nivel' => $area->nivel,
                'jefe' => $area->jefe?->name,
                'grupo' => 'Dirección Regional'
            ];
        }

        // OCI
        foreach ($oci as $area) {
            $resultado[] = [
                'id' => $area->id_area,
                'nombre' => $area->nombre,
                'nivel' => $area->nivel,
                'jefe' => $area->jefe?->name,
                'grupo' => 'Órgano de Control'
            ];
        }

        // Direcciones con sus subdirecciones
        foreach ($direcciones as $direccion) {
            $resultado[] = [
                'id' => $direccion->id_area,
                'nombre' => $direccion->nombre,
                'nivel' => $direccion->nivel,
                'jefe' => $direccion->jefe?->name,
                'grupo' => 'Direcciones'
            ];

            // Sub-áreas de esta dirección
            $subAreas = $subdirecciones->where('id_area_padre', $direccion->id_area);
            foreach ($subAreas as $sub) {
                $resultado[] = [
                    'id' => $sub->id_area,
                    'nombre' => '  └ ' . $sub->nombre,
                    'nivel' => $sub->nivel,
                    'jefe' => $sub->jefe?->name,
                    'grupo' => 'Direcciones',
                    'padre_id' => $direccion->id_area
                ];
            }

            // Residencias de esta dirección
            $resAreas = $residencias->where('id_area_padre', $direccion->id_area);
            foreach ($resAreas as $res) {
                $resultado[] = [
                    'id' => $res->id_area,
                    'nombre' => '  └ ' . $res->nombre,
                    'nivel' => $res->nivel,
                    'jefe' => $res->jefe?->name,
                    'grupo' => 'Direcciones',
                    'padre_id' => $direccion->id_area
                ];
            }
        }

        // Subdirecciones sin dirección padre en la lista
        $subSinPadre = $subdirecciones->filter(function($sub) use ($direcciones) {
            return !$direcciones->contains('id_area', $sub->id_area_padre);
        });
        foreach ($subSinPadre as $sub) {
            $resultado[] = [
                'id' => $sub->id_area,
                'nombre' => $sub->nombre,
                'nivel' => $sub->nivel,
                'jefe' => $sub->jefe?->name,
                'grupo' => 'Subdirecciones'
            ];
        }

        return $resultado;
    }

    /**
     * Verifica si un usuario puede derivar a un área específica
     */
    public function puedeDeriviarA(User $usuario, Area $areaDestino): bool
    {
        $areasDisponibles = $this->obtenerAreasParaDerivacion($usuario);
        return collect($areasDisponibles)->contains('id', $areaDestino->id_area);
    }

    /**
     * Deriva a la dirección padre (devolución jerárquica)
     */
    public function devolverADireccion(
        Expediente $expediente,
        string $motivo,
        int $plazoDias = 5
    ): ?Derivacion {
        $areaActual = $expediente->area;

        if (!$areaActual || !$areaActual->areaPadre) {
            return null;
        }

        $areaPadre = $areaActual->areaPadre;

        return $this->derivarExpediente(
            $expediente,
            $areaPadre->id_area,
            $areaPadre->id_jefe, // Al jefe de la dirección padre
            $plazoDias,
            $expediente->prioridad ?? 'normal',
            "Devuelto desde {$areaActual->nombre}. Motivo: {$motivo}"
        );
    }

    /**
     * Obtiene funcionarios disponibles para asignación en un área
     * Considera la jerarquía: si es dirección, incluye jefe y funcionarios de sub-áreas
     */
    public function obtenerFuncionariosParaAsignacion(int $areaId): array
    {
        $area = Area::with(['jefe', 'funcionarios', 'subAreas.funcionarios', 'subAreas.jefe'])->find($areaId);

        if (!$area) {
            return [];
        }

        $funcionarios = collect();

        // Jefe del área
        if ($area->jefe) {
            $funcionarios->push([
                'id' => $area->jefe->id,
                'nombre' => $area->jefe->name,
                'cargo' => 'Jefe de ' . $area->nombre,
                'email' => $area->jefe->email,
                'area' => $area->nombre
            ]);
        }

        // Funcionarios directos del área
        foreach ($area->funcionarios as $func) {
            if ($func->id !== $area->id_jefe && $func->activo) {
                $funcionarios->push([
                    'id' => $func->id,
                    'nombre' => $func->name,
                    'cargo' => 'Funcionario',
                    'email' => $func->email,
                    'area' => $area->nombre
                ]);
            }
        }

        // Si es una dirección, incluir funcionarios de sub-áreas
        if (in_array($area->nivel, [Area::NIVEL_DIRECCION_REGIONAL, Area::NIVEL_DIRECCION])) {
            foreach ($area->subAreas as $subArea) {
                if ($subArea->jefe && $subArea->jefe->activo) {
                    $funcionarios->push([
                        'id' => $subArea->jefe->id,
                        'nombre' => $subArea->jefe->name,
                        'cargo' => 'Jefe de ' . $subArea->nombre,
                        'email' => $subArea->jefe->email,
                        'area' => $subArea->nombre
                    ]);
                }

                foreach ($subArea->funcionarios as $func) {
                    if ($func->id !== $subArea->id_jefe && $func->activo) {
                        $funcionarios->push([
                            'id' => $func->id,
                            'nombre' => $func->name,
                            'cargo' => 'Funcionario',
                            'email' => $func->email,
                            'area' => $subArea->nombre
                        ]);
                    }
                }
            }
        }

        return $funcionarios->unique('id')->values()->toArray();
    }
}
