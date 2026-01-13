<?php

namespace App\Policies;

use App\Models\Expediente;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Policy para gestionar autorizaciones sobre Expedientes
 * Centraliza la lógica de permisos que antes estaba repetida en controladores
 */
class ExpedientePolicy
{
    /**
     * Determine si el usuario puede ver cualquier expediente
     */
    public function viewAny(User $user): bool
    {
        // Administradores, Mesa de Partes y Jefes de Área pueden ver todos
        return in_array($user->role->nombre, ['Administrador', 'Mesa de Partes', 'Jefe de Área']);
    }

    /**
     * Determine si el usuario puede ver el expediente
     */
    public function view(User $user, Expediente $expediente): bool
    {
        // Administrador puede ver todos
        if ($user->role->nombre === 'Administrador') {
            return true;
        }

        // Ciudadano solo puede ver sus propios expedientes
        if ($user->role->nombre === 'Ciudadano') {
            return $expediente->id_ciudadano === $user->id;
        }

        // Mesa de Partes puede ver todos
        if ($user->role->nombre === 'Mesa de Partes') {
            return true;
        }

        // Jefe de Área puede ver expedientes de su área
        if ($user->role->nombre === 'Jefe de Área') {
            return $expediente->id_area === $user->id_area;
        }

        // Funcionario solo puede ver expedientes asignados a él
        if ($user->role->nombre === 'Funcionario') {
            return $expediente->id_funcionario_asignado === $user->id;
        }

        return false;
    }

    /**
     * Determine si el funcionario puede procesar el expediente
     * Reemplaza: if ($expediente->id_funcionario_asignado !== auth()->user()->id) { abort(403); }
     */
    public function process(User $user, Expediente $expediente): bool
    {
        // Solo funcionarios asignados pueden procesar
        return $user->role->nombre === 'Funcionario'
            && $expediente->id_funcionario_asignado === $user->id;
    }

    /**
     * Determine si el usuario puede actualizar el expediente
     */
    public function update(User $user, Expediente $expediente): bool
    {
        // Administrador puede actualizar todos
        if ($user->role->nombre === 'Administrador') {
            return true;
        }

        // Mesa de Partes puede actualizar (clasificar, derivar)
        if ($user->role->nombre === 'Mesa de Partes') {
            return true;
        }

        // Jefe de Área puede actualizar expedientes de su área
        if ($user->role->nombre === 'Jefe de Área') {
            return $expediente->id_area === $user->id_area;
        }

        // Funcionario puede actualizar si está asignado
        if ($user->role->nombre === 'Funcionario') {
            return $expediente->id_funcionario_asignado === $user->id;
        }

        return false;
    }

    /**
     * Determine si el usuario puede eliminar el expediente
     */
    public function delete(User $user, Expediente $expediente): bool
    {
        // Solo administrador puede eliminar
        if ($user->role->nombre === 'Administrador') {
            return true;
        }

        // Ciudadano puede eliminar sus expedientes si están en estado "Registrado"
        if ($user->role->nombre === 'Ciudadano') {
            return $expediente->id_ciudadano === $user->id
                && $expediente->estado === 'Registrado';
        }

        return false;
    }

    /**
     * Determine si el usuario puede derivar el expediente
     */
    public function derivar(User $user, Expediente $expediente): bool
    {
        // Mesa de Partes puede derivar cualquier expediente
        if ($user->role->nombre === 'Mesa de Partes') {
            return true;
        }

        // Jefe de Área puede derivar expedientes de su área
        if ($user->role->nombre === 'Jefe de Área') {
            return $expediente->id_area === $user->id_area;
        }

        // Funcionario puede derivar si está asignado
        if ($user->role->nombre === 'Funcionario') {
            return $expediente->id_funcionario_asignado === $user->id;
        }

        return false;
    }

    /**
     * Determine si el usuario puede resolver el expediente
     */
    public function resolver(User $user, Expediente $expediente): bool
    {
        // Solo funcionarios asignados pueden resolver
        return $user->role->nombre === 'Funcionario'
            && $expediente->id_funcionario_asignado === $user->id;
    }

    /**
     * Determine si el usuario puede archivar el expediente
     */
    public function archivar(User $user, Expediente $expediente): bool
    {
        // Solo Mesa de Partes y Administrador pueden archivar
        return in_array($user->role->nombre, ['Mesa de Partes', 'Administrador']);
    }

    /**
     * Determine si el usuario puede descargar documentos del expediente
     */
    public function descargarDocumentos(User $user, Expediente $expediente): bool
    {
        // Todos los roles pueden descargar si tienen acceso al expediente
        return $this->view($user, $expediente);
    }

    /**
     * Determine si el usuario puede aprobar el expediente
     * Usado por Jefe de Área para aprobar resoluciones de funcionarios
     */
    public function approve(User $user, Expediente $expediente): bool
    {
        // Solo Jefe de Área puede aprobar expedientes de su área
        if ($user->role->nombre === 'Jefe de Área') {
            return $expediente->id_area === $user->id_area;
        }

        // Administrador puede aprobar cualquier expediente
        if ($user->role->nombre === 'Administrador') {
            return true;
        }

        return false;
    }

    /**
     * Determine si el usuario puede rechazar el expediente
     * Usado por Jefe de Área para rechazar resoluciones de funcionarios
     */
    public function reject(User $user, Expediente $expediente): bool
    {
        // Solo Jefe de Área puede rechazar expedientes de su área
        if ($user->role->nombre === 'Jefe de Área') {
            return $expediente->id_area === $user->id_area;
        }

        // Administrador puede rechazar cualquier expediente
        if ($user->role->nombre === 'Administrador') {
            return true;
        }

        return false;
    }

    /**
     * Determine si el usuario puede extender el plazo del expediente
     * Usado por Jefe de Área para dar extensiones por conflictos
     */
    public function extendDeadline(User $user, Expediente $expediente): bool
    {
        // Solo Jefe de Área puede extender plazos en su área
        if ($user->role->nombre === 'Jefe de Área') {
            return $expediente->id_area === $user->id_area;
        }

        // Administrador puede extender plazos en cualquier expediente
        if ($user->role->nombre === 'Administrador') {
            return true;
        }

        return false;
    }

    /**
     * Determine si el usuario puede otorgar autorización especial al expediente
     * Usado por Jefe de Área para autorizar casos especiales
     */
    public function grantSpecialAuthorization(User $user, Expediente $expediente): bool
    {
        // Solo Jefe de Área puede otorgar autorizaciones especiales en su área
        if ($user->role->nombre === 'Jefe de Área') {
            return $expediente->id_area === $user->id_area;
        }

        // Administrador puede otorgar autorizaciones en cualquier expediente
        if ($user->role->nombre === 'Administrador') {
            return true;
        }

        return false;
    }
}
