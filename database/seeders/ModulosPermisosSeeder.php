<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\Role;

class ModulosPermisosSeeder extends Seeder
{
    public function run(): void
    {
        // Definir módulos del sistema
        $modulos = [
            [
                'nombre' => 'Dashboard',
                'slug' => 'dashboard',
                'descripcion' => 'Panel principal y estadísticas',
                'icono' => 'fas fa-tachometer-alt',
                'orden' => 1,
                'permisos' => [
                    ['nombre' => 'Ver Dashboard', 'slug' => 'dashboard.ver'],
                    ['nombre' => 'Ver Estadísticas Globales', 'slug' => 'dashboard.estadisticas'],
                ]
            ],
            [
                'nombre' => 'Expedientes',
                'slug' => 'expedientes',
                'descripcion' => 'Gestión de expedientes',
                'icono' => 'fas fa-folder-open',
                'orden' => 2,
                'permisos' => [
                    ['nombre' => 'Ver Expedientes', 'slug' => 'expedientes.ver'],
                    ['nombre' => 'Ver Todos los Expedientes', 'slug' => 'expedientes.ver_todos'],
                    ['nombre' => 'Registrar Expedientes', 'slug' => 'expedientes.registrar'],
                    ['nombre' => 'Clasificar Expedientes', 'slug' => 'expedientes.clasificar'],
                    ['nombre' => 'Derivar Expedientes', 'slug' => 'expedientes.derivar'],
                    ['nombre' => 'Reasignar Expedientes', 'slug' => 'expedientes.reasignar'],
                    ['nombre' => 'Cambiar Estado', 'slug' => 'expedientes.cambiar_estado'],
                    ['nombre' => 'Archivar Expedientes', 'slug' => 'expedientes.archivar'],
                    ['nombre' => 'Eliminar Expedientes', 'slug' => 'expedientes.eliminar'],
                    ['nombre' => 'Ver Historial', 'slug' => 'expedientes.historial'],
                    ['nombre' => 'Imprimir Cargo', 'slug' => 'expedientes.imprimir_cargo'],
                ]
            ],
            [
                'nombre' => 'Mesa de Partes',
                'slug' => 'mesa-partes',
                'descripcion' => 'Funciones de Mesa de Partes',
                'icono' => 'fas fa-inbox',
                'orden' => 3,
                'permisos' => [
                    ['nombre' => 'Acceder a Mesa de Partes', 'slug' => 'mesa-partes.acceso'],
                    ['nombre' => 'Monitoreo', 'slug' => 'mesa-partes.monitoreo'],
                    ['nombre' => 'Control de Numeración', 'slug' => 'mesa-partes.numeracion'],
                    ['nombre' => 'Gestionar Expedientes Virtuales', 'slug' => 'mesa-partes.virtuales'],
                ]
            ],
            [
                'nombre' => 'Documentos',
                'slug' => 'documentos',
                'descripcion' => 'Gestión de documentos adjuntos',
                'icono' => 'fas fa-file-pdf',
                'orden' => 4,
                'permisos' => [
                    ['nombre' => 'Ver Documentos', 'slug' => 'documentos.ver'],
                    ['nombre' => 'Ver Todos los Documentos', 'slug' => 'documentos.ver_todos'],
                    ['nombre' => 'Descargar Documentos', 'slug' => 'documentos.descargar'],
                    ['nombre' => 'Subir Documentos', 'slug' => 'documentos.subir'],
                    ['nombre' => 'Eliminar Documentos', 'slug' => 'documentos.eliminar'],
                    ['nombre' => 'Validar Documentos', 'slug' => 'documentos.validar'],
                ]
            ],
            [
                'nombre' => 'Usuarios',
                'slug' => 'usuarios',
                'descripcion' => 'Gestión de usuarios del sistema',
                'icono' => 'fas fa-users',
                'orden' => 5,
                'permisos' => [
                    ['nombre' => 'Ver Usuarios', 'slug' => 'usuarios.ver'],
                    ['nombre' => 'Crear Usuarios', 'slug' => 'usuarios.crear'],
                    ['nombre' => 'Editar Usuarios', 'slug' => 'usuarios.editar'],
                    ['nombre' => 'Eliminar Usuarios', 'slug' => 'usuarios.eliminar'],
                    ['nombre' => 'Activar/Desactivar Usuarios', 'slug' => 'usuarios.toggle'],
                    ['nombre' => 'Restablecer Contraseñas', 'slug' => 'usuarios.reset_password'],
                ]
            ],
            [
                'nombre' => 'Roles y Permisos',
                'slug' => 'roles',
                'descripcion' => 'Gestión de roles y permisos',
                'icono' => 'fas fa-user-shield',
                'orden' => 6,
                'permisos' => [
                    ['nombre' => 'Ver Roles', 'slug' => 'roles.ver'],
                    ['nombre' => 'Crear Roles', 'slug' => 'roles.crear'],
                    ['nombre' => 'Editar Roles', 'slug' => 'roles.editar'],
                    ['nombre' => 'Eliminar Roles', 'slug' => 'roles.eliminar'],
                    ['nombre' => 'Asignar Permisos', 'slug' => 'roles.asignar_permisos'],
                ]
            ],
            [
                'nombre' => 'Áreas',
                'slug' => 'areas',
                'descripcion' => 'Gestión de áreas institucionales',
                'icono' => 'fas fa-building',
                'orden' => 7,
                'permisos' => [
                    ['nombre' => 'Ver Áreas', 'slug' => 'areas.ver'],
                    ['nombre' => 'Crear Áreas', 'slug' => 'areas.crear'],
                    ['nombre' => 'Editar Áreas', 'slug' => 'areas.editar'],
                    ['nombre' => 'Activar/Desactivar Áreas', 'slug' => 'areas.toggle'],
                    ['nombre' => 'Asignar Jefe de Área', 'slug' => 'areas.asignar_jefe'],
                ]
            ],
            [
                'nombre' => 'Tipos de Trámite',
                'slug' => 'tipos-tramite',
                'descripcion' => 'Configuración de tipos de trámite',
                'icono' => 'fas fa-list-alt',
                'orden' => 8,
                'permisos' => [
                    ['nombre' => 'Ver Tipos de Trámite', 'slug' => 'tipos-tramite.ver'],
                    ['nombre' => 'Crear Tipos de Trámite', 'slug' => 'tipos-tramite.crear'],
                    ['nombre' => 'Editar Tipos de Trámite', 'slug' => 'tipos-tramite.editar'],
                    ['nombre' => 'Activar/Desactivar', 'slug' => 'tipos-tramite.toggle'],
                ]
            ],
            [
                'nombre' => 'Reportes',
                'slug' => 'reportes',
                'descripcion' => 'Generación de reportes',
                'icono' => 'fas fa-chart-bar',
                'orden' => 9,
                'permisos' => [
                    ['nombre' => 'Ver Reportes', 'slug' => 'reportes.ver'],
                    ['nombre' => 'Reportes por Fecha', 'slug' => 'reportes.por_fecha'],
                    ['nombre' => 'Reportes por Área', 'slug' => 'reportes.por_area'],
                    ['nombre' => 'Reportes por Tipo Trámite', 'slug' => 'reportes.por_tipo'],
                    ['nombre' => 'Reportes por Remitente', 'slug' => 'reportes.por_remitente'],
                    ['nombre' => 'Exportar PDF', 'slug' => 'reportes.exportar_pdf'],
                    ['nombre' => 'Exportar Excel', 'slug' => 'reportes.exportar_excel'],
                ]
            ],
            [
                'nombre' => 'Auditoría',
                'slug' => 'auditoria',
                'descripcion' => 'Registros de auditoría del sistema',
                'icono' => 'fas fa-history',
                'orden' => 10,
                'permisos' => [
                    ['nombre' => 'Ver Auditoría', 'slug' => 'auditoria.ver'],
                    ['nombre' => 'Ver Detalles de Auditoría', 'slug' => 'auditoria.detalles'],
                    ['nombre' => 'Exportar Auditoría', 'slug' => 'auditoria.exportar'],
                ]
            ],
            [
                'nombre' => 'Configuraciones',
                'slug' => 'configuraciones',
                'descripcion' => 'Configuración del sistema',
                'icono' => 'fas fa-cogs',
                'orden' => 11,
                'permisos' => [
                    ['nombre' => 'Ver Configuraciones', 'slug' => 'configuraciones.ver'],
                    ['nombre' => 'Editar Configuraciones', 'slug' => 'configuraciones.editar'],
                    ['nombre' => 'Configurar Estados', 'slug' => 'configuraciones.estados'],
                    ['nombre' => 'Configurar Numeración', 'slug' => 'configuraciones.numeracion'],
                ]
            ],
            [
                'nombre' => 'Mesa Virtual',
                'slug' => 'mesa-virtual',
                'descripcion' => 'Mesa de Partes Virtual para ciudadanos',
                'icono' => 'fas fa-desktop',
                'orden' => 12,
                'permisos' => [
                    ['nombre' => 'Supervisar Trámites Virtuales', 'slug' => 'mesa-virtual.supervisar'],
                    ['nombre' => 'Validar Documentos Virtuales', 'slug' => 'mesa-virtual.validar'],
                    ['nombre' => 'Rechazar Trámites', 'slug' => 'mesa-virtual.rechazar'],
                    ['nombre' => 'Convertir a Expediente', 'slug' => 'mesa-virtual.convertir'],
                ]
            ],
        ];

        // Crear módulos y permisos
        foreach ($modulos as $moduloData) {
            $permisos = $moduloData['permisos'];
            unset($moduloData['permisos']);

            $modulo = Modulo::updateOrCreate(
                ['slug' => $moduloData['slug']],
                $moduloData
            );

            foreach ($permisos as $permisoData) {
                Permiso::updateOrCreate(
                    ['slug' => $permisoData['slug']],
                    array_merge($permisoData, ['id_modulo' => $modulo->id_modulo])
                );
            }
        }

        // Asignar permisos por defecto a roles existentes
        $this->asignarPermisosRoles();
    }

    private function asignarPermisosRoles()
    {
        // Mesa de Partes
        $rolMesaPartes = Role::where('nombre', 'Mesa de Partes')->first();
        if ($rolMesaPartes) {
            $permisosMesa = Permiso::whereIn('slug', [
                'dashboard.ver',
                'expedientes.ver', 'expedientes.registrar', 'expedientes.clasificar', 'expedientes.derivar',
                'expedientes.historial', 'expedientes.imprimir_cargo',
                'mesa-partes.acceso', 'mesa-partes.monitoreo', 'mesa-partes.numeracion', 'mesa-partes.virtuales',
                'documentos.ver', 'documentos.descargar', 'documentos.subir',
            ])->pluck('id_permiso')->toArray();
            $rolMesaPartes->sincronizarPermisos($permisosMesa);
        }

        // Jefe de Área
        $rolJefe = Role::where('nombre', 'Jefe de Área')->first();
        if ($rolJefe) {
            $permisosJefe = Permiso::whereIn('slug', [
                'dashboard.ver', 'dashboard.estadisticas',
                'expedientes.ver', 'expedientes.derivar', 'expedientes.reasignar',
                'expedientes.cambiar_estado', 'expedientes.historial',
                'documentos.ver', 'documentos.descargar', 'documentos.validar',
                'reportes.ver', 'reportes.por_fecha', 'reportes.por_area', 'reportes.exportar_excel',
            ])->pluck('id_permiso')->toArray();
            $rolJefe->sincronizarPermisos($permisosJefe);
        }

        // Funcionario
        $rolFuncionario = Role::where('nombre', 'Funcionario')->first();
        if ($rolFuncionario) {
            $permisosFuncionario = Permiso::whereIn('slug', [
                'dashboard.ver',
                'expedientes.ver', 'expedientes.derivar', 'expedientes.cambiar_estado', 'expedientes.historial',
                'documentos.ver', 'documentos.descargar', 'documentos.subir',
            ])->pluck('id_permiso')->toArray();
            $rolFuncionario->sincronizarPermisos($permisosFuncionario);
        }

        // Soporte
        $rolSoporte = Role::where('nombre', 'Soporte')->first();
        if ($rolSoporte) {
            $permisosSoporte = Permiso::whereIn('slug', [
                'dashboard.ver',
                'usuarios.ver', 'usuarios.reset_password', 'usuarios.toggle',
                'auditoria.ver', 'auditoria.detalles',
            ])->pluck('id_permiso')->toArray();
            $rolSoporte->sincronizarPermisos($permisosSoporte);
        }

        // Ciudadano - permisos mínimos
        $rolCiudadano = Role::where('nombre', 'Ciudadano')->first();
        if ($rolCiudadano) {
            $permisosCiudadano = Permiso::whereIn('slug', [
                'dashboard.ver',
                'expedientes.ver', 'expedientes.registrar',
                'documentos.ver', 'documentos.descargar', 'documentos.subir',
            ])->pluck('id_permiso')->toArray();
            $rolCiudadano->sincronizarPermisos($permisosCiudadano);
        }
    }
}
