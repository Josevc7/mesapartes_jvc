<?php

namespace App\Services;

use App\Models\User;
use App\Models\Documento;
use App\Models\Expediente;

class DocumentoAccessService
{
    /**
     * Verifica si un usuario tiene acceso a un documento específico
     */
    public function puedeAcceder(User $user, Documento $documento): bool
    {
        $expediente = $documento->expediente;

        if (!$expediente) {
            return false;
        }

        return $this->puedeAccederExpediente($user, $expediente);
    }

    /**
     * Verifica si un usuario tiene acceso a los documentos de un expediente
     */
    public function puedeAccederExpediente(User $user, Expediente $expediente): bool
    {
        $rol = $user->role?->nombre;

        // Administrador tiene acceso a todos los documentos
        if ($rol === 'Administrador') {
            return true;
        }

        // Verificar según el rol
        switch ($rol) {
            case 'Mesa de Partes':
                // Mesa de Partes puede ver todos los documentos
                return true;

            case 'Jefe de Área':
                // El jefe puede ver documentos de expedientes de su área
                return $expediente->id_area === $user->id_area;

            case 'Funcionario':
                // El funcionario puede ver documentos de expedientes asignados a él
                // o que están en su área
                //return $expediente->id_funcionario_asignado === $user->id
                //    || $expediente->id_area === $user->id_area;
                return $expediente->id_funcionario_asignado === $user->id;

            case 'Ciudadano':
                // El ciudadano solo puede ver documentos de sus propios expedientes
                //return $expediente->id_ciudadano === $user->id
                 //   || $expediente->id_persona === $user->id_persona;
                return $expediente->id_ciudadano === $user->id;

            case 'Soporte':
                // Soporte puede ver documentos para diagnóstico
                return true;

            default:
                return false;
        }
    }

    /**
     * Verifica si un usuario puede subir documentos a un expediente
     */
    public function puedeSubir(User $user, Expediente $expediente): bool
    {
        $rol = $user->role?->nombre;
        $slug = $expediente->estadoExpediente?->slug;

        // Administrador siempre puede
        if ($rol === 'Administrador') {
            return true;
        }

        switch ($rol) {
            case 'Mesa de Partes':
                // Mesa de Partes puede subir en estados iniciales
                //return in_array($expediente->estado, ['recepcionado', 'registrado', 'clasificado']);
                return in_array($slug, ['recepcionado', 'registrado', 'clasificado']);

            case 'Funcionario':
                // Funcionario puede subir si tiene el expediente asignado
                //return $expediente->id_funcionario_asignado === $user->id
                 //   && in_array($expediente->estado, ['derivado', 'en_proceso']);
                return $expediente->id_funcionario_asignado === $user->id
                    && in_array($slug, ['derivado', 'en_proceso', 'en_revision']);

            case 'Jefe de Área':
                // Jefe puede subir si el expediente está en su área
                return $expediente->id_area === $user->id_area;

            case 'Ciudadano':
                // Ciudadano puede subir si es su expediente y está en estado permitido
                //return ($expediente->id_ciudadano === $user->id || $expediente->id_persona === $user->id_persona)
                //    && in_array($expediente->estado, ['recepcionado', 'observado']);
                return $expediente->id_ciudadano === $user->id
                    && in_array($slug, ['recepcionado', 'observado']);

            default:
                return false;
        }
    }

    /**
     * Verifica si un usuario puede eliminar documentos
     */
    public function puedeEliminar(User $user, Documento $documento): bool
    {
        $rol = $user->role?->nombre;

        // Solo administrador puede eliminar documentos
        if ($rol === 'Administrador') {
            return true;
        }

        // El usuario que subió el documento puede eliminarlo si es reciente (menos de 24 horas)
        if ($documento->created_at->diffInHours(now()) < 24) {
            // Verificar si el usuario subió el documento (si hay campo de usuario)
            return false; // Por seguridad, solo admin puede eliminar
        }

        return false;
    }

    

    /**
     * Obtiene los documentos accesibles para un usuario
     */
    public function getDocumentosAccesibles(User $user, $limit = null)
    {
        $rol = $user->role?->nombre;

        $query = Documento::with('expediente.area');

        switch ($rol) {
            case 'Administrador':
            case 'Mesa de Partes':
            case 'Soporte':
                // Acceso a todos
                break;

            case 'Jefe de Área':
                $query->whereHas('expediente', function ($q) use ($user) {
                    $q->where('id_area', $user->id_area);
                });
                break;

            case 'Funcionario':
                $query->whereHas('expediente', function ($q) use ($user) {
                    // $q->where('id_funcionario_asignado', $user->id)
                    //     ->orWhere('id_area', $user->id_area);
                    $q->where('id_funcionario_asignado', $user->id);
                });
                break;

            case 'Ciudadano':
                $query->whereHas('expediente', function ($q) use ($user) {
                    // $q->where('id_ciudadano', $user->id)
                    //     ->orWhere('id_persona', $user->id_persona);
                    $q->where('id_ciudadano', $user->id);
                });
                break;

            default:
                return collect();
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
