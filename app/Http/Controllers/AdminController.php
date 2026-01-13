<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Area;
use App\Models\TipoTramite;
use App\Models\Configuracion;
use App\Models\Auditoria;
use App\Models\Persona;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Gestión de Usuarios
    public function usuarios()
    {
        $usuarios = User::paginate(10);
        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function crearUsuario()
    {
        $roles = Role::all();
        $areas = Area::all();
        return view('admin.usuarios.create', compact('roles', 'areas'));
    }

    public function storeUsuario(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'dni' => 'required|string|max:20|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'id_rol' => 'required|exists:roles,id_rol',
            'id_area' => 'nullable|exists:areas,id_area',
            'telefono' => 'nullable|string|max:20'
        ]);

        User::create([
            'name' => $request->name,
            'dni' => $request->dni,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => Hash::make($request->password),
            'id_rol' => $request->id_rol,
            'id_area' => $request->id_area,
            'activo' => $request->has('activo')
        ]);

        return redirect()->route('admin.usuarios')->with('success', 'Usuario creado correctamente');
    }

    public function showUsuario($id_user)
    {
        $usuario = User::findOrFail($id_user);
        return view('admin.usuarios.show', compact('usuario'));
    }

    public function editUsuario($id_user)
    {
        $usuario = User::findOrFail($id_user);
        $roles = Role::all();
        $areas = Area::all();
        return view('admin.usuarios.edit', compact('usuario', 'roles', 'areas'));
    }

    public function updateUsuario(Request $request, $id_user)
    {
        $usuario = User::findOrFail($id_user);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'dni' => 'required|string|max:20|unique:users,dni,' . $id_user . ',id',
            'email' => 'required|email|unique:users,email,' . $id_user . ',id',
            'password' => 'nullable|min:6|confirmed',
            'id_rol' => 'required|exists:roles,id_rol',
            'id_area' => 'nullable|exists:areas,id_area',
            'telefono' => 'nullable|string|max:20'
        ]);

        $data = [
            'name' => $request->name,
            'dni' => $request->dni,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'id_rol' => $request->id_rol,
            'id_area' => $request->id_area,
            'activo' => $request->has('activo')
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);
        return redirect()->route('admin.usuarios')->with('success', 'Usuario actualizado correctamente');
    }

    public function destroyUsuario($id_user)
    {
        $usuario = User::findOrFail($id_user);
        
        if ($usuario->id == auth()->user()->id) {
            return redirect()->route('admin.usuarios')->with('error', 'No puedes eliminar tu propio usuario');
        }
        
        if ($usuario->expedientesComoCiudadano()->count() > 0 || $usuario->expedientesAsignados()->count() > 0) {
            return redirect()->route('admin.usuarios')->with('error', 'No se puede eliminar. El usuario tiene expedientes asociados.');
        }
        
        $usuario->delete();
        return redirect()->route('admin.usuarios')->with('success', 'Usuario eliminado correctamente');
    }

    // Gestión de Áreas
    public function areas()
    {
        $areas = Area::with('jefe')->paginate(10);
        $jefes = User::where('id_rol', 3)->where('activo', true)->get();
        return view('admin.areas.index', compact('areas', 'jefes'));
    }

    public function storeArea(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'id_jefe' => 'nullable|exists:users,id'
        ]);

        $area = Area::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'id_jefe' => $request->id_jefe,
            'activo' => true
        ]);

        // Si se asignó un jefe, actualizar el id_area del usuario
        if ($request->id_jefe) {
            User::where('id', $request->id_jefe)->update(['id_area' => $area->id_area]);
        }

        return redirect()->route('admin.areas')->with('success', 'Área creada correctamente');
    }

    public function editArea($id_area)
    {
        $area = Area::findOrFail($id_area);
        $jefes = User::where('id_rol', 3)->where('activo', true)->get();
        return response()->json(['area' => $area, 'jefes' => $jefes]);
    }

    public function updateArea(Request $request, $id_area)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'id_jefe' => 'nullable|exists:users,id'
        ]);

        $area = Area::findOrFail($id_area);
        
        // Si cambió el jefe, actualizar relaciones
        if ($area->id_jefe != $request->id_jefe) {
            // Quitar área del jefe anterior
            if ($area->id_jefe) {
                User::where('id', $area->id_jefe)->update(['id_area' => null]);
            }
            // Asignar área al nuevo jefe
            if ($request->id_jefe) {
                User::where('id', $request->id_jefe)->update(['id_area' => $area->id_area]);
            }
        }

        $area->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'id_jefe' => $request->id_jefe
        ]);

        return redirect()->route('admin.areas')->with('success', 'Área actualizada correctamente');
    }

    public function toggleArea($id_area)
    {
        $area = Area::findOrFail($id_area);
        $area->update(['activo' => !$area->activo]);
        
        $estado = $area->activo ? 'activada' : 'desactivada';
        return redirect()->route('admin.areas')->with('success', "Área {$estado} correctamente");
    }

    // Gestión de Tipos de Trámite
    public function tipoTramites()
    {
        $tipoTramites = TipoTramite::with('area')->paginate(10);
        $areas = Area::where('activo', true)->get();
        return view('admin.tipo-tramites.index', compact('tipoTramites', 'areas'));
    }

    public function storeTipoTramite(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'id_area' => 'required|exists:areas,id_area',
            'plazo_dias' => 'required|integer|min:1',
            'requisitos' => 'nullable|string'
        ]);

        TipoTramite::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'id_area' => $request->id_area,
            'plazo_dias' => $request->plazo_dias,
            'requisitos' => $request->requisitos,
            'activo' => true
        ]);
        
        return redirect()->route('admin.tipo-tramites')->with('success', 'Tipo de trámite creado correctamente');
    }

    public function editTipoTramite($id_tipo_tramite)
    {
        $tipoTramite = TipoTramite::findOrFail($id_tipo_tramite);
        $areas = Area::where('activo', true)->get();
        return response()->json(['tipoTramite' => $tipoTramite, 'areas' => $areas]);
    }

    public function updateTipoTramite(Request $request, $id_tipo_tramite)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'id_area' => 'required|exists:areas,id_area',
            'plazo_dias' => 'required|integer|min:1',
            'requisitos' => 'nullable|string'
        ]);

        $tipoTramite = TipoTramite::findOrFail($id_tipo_tramite);
        $tipoTramite->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'id_area' => $request->id_area,
            'plazo_dias' => $request->plazo_dias,
            'requisitos' => $request->requisitos
        ]);

        return redirect()->route('admin.tipo-tramites')->with('success', 'Tipo de trámite actualizado correctamente');
    }

    public function toggleTipoTramite($id_tipo_tramite)
    {
        $tipoTramite = TipoTramite::findOrFail($id_tipo_tramite);
        $tipoTramite->update(['activo' => !$tipoTramite->activo]);
        
        $estado = $tipoTramite->activo ? 'activado' : 'desactivado';
        return redirect()->route('admin.tipo-tramites')->with('success', "Tipo de trámite {$estado} correctamente");
    }

    // Configuraciones del Sistema
    public function configuraciones()
    {
        $configuraciones = Configuracion::all()->pluck('valor', 'clave');
        return view('admin.configuraciones', compact('configuraciones'));
    }

    public function updateConfiguraciones(Request $request)
    {
        foreach ($request->except('_token') as $clave => $valor) {
            Configuracion::updateOrCreate(
                ['clave' => $clave],
                ['valor' => $valor]
            );
        }

        return redirect()->route('admin.configuraciones')->with('success', 'Configuraciones actualizadas');
    }

    // Auditoría
    public function auditoria()
    {
        $auditorias = Auditoria::with('usuario')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.auditoria.index', compact('auditorias'));
    }

    public function auditoriaDetalles($id_auditoria)
    {
        $auditoria = Auditoria::with('usuario')->findOrFail($id_auditoria);
        return response()->json($auditoria);
    }

    // Gestión de Personas
    public function personas()
    {
        $personas = Persona::with('expedientes')->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.personas.index', compact('personas'));
    }

    public function storePersona(Request $request)
    {
        $request->validate([
            'tipo_documento' => 'required|in:DNI,CE,RUC,PASAPORTE',
            'numero_documento' => 'required|string|max:20|unique:personas,numero_documento,NULL,id_persona',
            'tipo_persona' => 'required|in:NATURAL,JURIDICA',
            'nombres' => 'required_if:tipo_persona,NATURAL|string|max:100',
            'apellido_paterno' => 'required_if:tipo_persona,NATURAL|string|max:50',
            'apellido_materno' => 'nullable|string|max:50',
            'razon_social' => 'required_if:tipo_persona,JURIDICA|string|max:200',
            'representante_legal' => 'nullable|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string'
        ]);

        Persona::create($request->all());
        return redirect()->route('admin.personas')->with('success', 'Persona creada correctamente');
    }

    public function showPersona($id_persona)
    {
        $persona = Persona::with('expedientes')->findOrFail($id_persona);
        return response()->json($persona);
    }

    public function updatePersona(Request $request, $id_persona)
    {
        $persona = Persona::findOrFail($id_persona);
        
        $request->validate([
            'tipo_documento' => 'required|in:DNI,CE,RUC,PASAPORTE',
            'numero_documento' => 'required|string|max:20|unique:personas,numero_documento,' . $id_persona . ',id_persona',
            'tipo_persona' => 'required|in:NATURAL,JURIDICA',
            'nombres' => 'required_if:tipo_persona,NATURAL|string|max:100',
            'apellido_paterno' => 'required_if:tipo_persona,NATURAL|string|max:50',
            'apellido_materno' => 'nullable|string|max:50',
            'razon_social' => 'required_if:tipo_persona,JURIDICA|string|max:200',
            'representante_legal' => 'nullable|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string'
        ]);

        $persona->update($request->all());
        return redirect()->route('admin.personas')->with('success', 'Persona actualizada correctamente');
    }

    public function destroyPersona($id_persona)
    {
        $persona = Persona::findOrFail($id_persona);
        
        if ($persona->expedientes()->count() > 0) {
            return redirect()->route('admin.personas')->with('error', 'No se puede eliminar. La persona tiene expedientes asociados.');
        }
        
        $persona->delete();
        return redirect()->route('admin.personas')->with('success', 'Persona eliminada correctamente');
    }

    // Matriz de Control
    public function matrizControl()
    {
        $usuarios = User::all();
        $roles = Role::all();
        $areas = Area::all();
        
        // Resumen por roles
        $resumenRoles = [];
        foreach ($roles as $rol) {
            $usuariosRol = $usuarios->where('id_rol', $rol->id_rol);
            $resumenRoles[$rol->nombre] = [
                'total' => $usuariosRol->count(),
                'activos' => $usuariosRol->where('activo', true)->count(),
                'inactivos' => $usuariosRol->where('activo', false)->count()
            ];
        }
        
        // Resumen por áreas
        $resumenAreas = [];
        $resumenAreas['Sin Área'] = $usuarios->whereNull('id_area')->count();
        foreach ($areas as $area) {
            $resumenAreas[$area->nombre] = $usuarios->where('id_area', $area->id_area)->count();
        }
        
        return view('admin.matriz-control', compact('usuarios', 'roles', 'areas', 'resumenRoles', 'resumenAreas'));
    }

    public function toggleEstadoUsuario($id_user)
    {
        $usuario = User::findOrFail($id_user);
        $usuario->update(['activo' => !$usuario->activo]);
        
        return response()->json(['success' => true, 'estado' => $usuario->activo]);
    }

    // Dashboard Administrativo
    public function dashboard()
    {
        $metricas = [
            'total_expedientes' => \App\Models\Expediente::count(),
            'usuarios_activos' => User::where('activo', true)->count(),
            'expedientes_pendientes' => \App\Models\Expediente::whereIn('estado', ['pendiente', 'derivado'])->count(),
            'expedientes_vencidos' => \App\Models\Expediente::whereIn('estado', ['derivado', 'en_proceso'])
                ->whereHas('derivaciones', function($q) {
                    $q->where('fecha_limite', '<', now())->where('estado', 'Pendiente');
                })->count()
        ];

        $actividadReciente = \App\Models\Auditoria::with('usuario')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $alertas = [];
        if ($metricas['expedientes_vencidos'] > 0) {
            $alertas[] = ['tipo' => 'danger', 'titulo' => 'Expedientes Vencidos', 'mensaje' => "Hay {$metricas['expedientes_vencidos']} expedientes vencidos."];
        }

        // Datos para gráficos
        $validEstados = ['pendiente', 'derivado', 'en_proceso', 'completado', 'archivado', 'resuelto'];
        $graficoMeses = ['labels' => [], 'data' => []];
        $graficoEstados = ['labels' => ['Pendiente', 'Derivado', 'En Proceso', 'Completado', 'Archivado'], 'data' => []];
        
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $graficoMeses['labels'][] = $fecha->format('M Y');
            $graficoMeses['data'][] = \App\Models\Expediente::whereMonth('created_at', $fecha->month)
                ->whereYear('created_at', $fecha->year)->count();
        }

        foreach ($graficoEstados['labels'] as $estado) {
            $estadoLower = strtolower($estado);
            // Only query valid estados to prevent SQL injection
            if (in_array($estadoLower, $validEstados)) {
                $graficoEstados['data'][] = \App\Models\Expediente::where('estado', $estadoLower)->count();
            }
        }

        $rendimientoPorArea = \App\Models\Area::withCount(['expedientes'])
            ->get()
            ->map(function($area) {
                $total = $area->expedientes_count;
                $completados = \App\Models\Expediente::where('id_area', $area->id_area)->where('estado', 'completado')->count();
                $pendientes = \App\Models\Expediente::where('id_area', $area->id_area)->whereIn('estado', ['pendiente', 'derivado', 'en_proceso'])->count();
                $vencidos = \App\Models\Expediente::where('id_area', $area->id_area)
                    ->whereIn('estado', ['derivado', 'en_proceso'])
                    ->whereHas('derivaciones', function($q) {
                        $q->where('fecha_limite', '<', now());
                    })->count();
                
                return [
                    'nombre' => $area->nombre,
                    'total' => $total,
                    'completados' => $completados,
                    'pendientes' => $pendientes,
                    'vencidos' => $vencidos,
                    'eficiencia' => $total > 0 ? round(($completados / $total) * 100) : 0
                ];
            });

        return view('admin.dashboard', compact('metricas', 'actividadReciente', 'alertas', 'graficoMeses', 'graficoEstados', 'rendimientoPorArea'));
    }

    // CRUD de Roles
    public function roles()
    {
        $roles = Role::withCount('users')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function storeRol(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,nombre,NULL,id_rol',
            'descripcion' => 'nullable|string'
        ]);

        Role::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo')
        ]);

        return redirect()->route('admin.roles')->with('success', 'Rol creado correctamente');
    }

    public function showRol($id_rol)
    {
        $rol = Role::findOrFail($id_rol);
        return response()->json($rol);
    }

    public function updateRol(Request $request, $id_rol)
    {
        $rol = Role::findOrFail($id_rol);
        
        $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,nombre,' . $id_rol . ',id_rol',
            'descripcion' => 'nullable|string'
        ]);

        $rol->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo')
        ]);

        return redirect()->route('admin.roles')->with('success', 'Rol actualizado correctamente');
    }

    public function destroyRol($id_rol)
    {
        $rol = Role::findOrFail($id_rol);
        
        if ($rol->users()->count() > 0) {
            return redirect()->route('admin.roles')->with('error', 'No se puede eliminar. El rol tiene usuarios asignados.');
        }
        
        $rol->delete();
        return redirect()->route('admin.roles')->with('success', 'Rol eliminado correctamente');
    }

    // Logs del Sistema
    public function logs(Request $request)
    {
        $query = \App\Models\Auditoria::with('usuario');
        
        if ($request->accion) {
            $query->where('accion', $request->accion);
        }
        
        if ($request->id_usuario) {
            $query->where('id_usuario', $request->id_usuario);
        }
        
        if ($request->fecha) {
            $query->whereDate('created_at', $request->fecha);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->paginate(50);
        $usuarios = User::select('id', 'name')->get();
        
        return view('admin.logs', compact('logs', 'usuarios'));
    }

    public function logDetalles($id_auditoria)
    {
        $log = \App\Models\Auditoria::findOrFail($id_auditoria);
        return response()->json($log);
    }

    // Estadísticas Globales
    public function estadisticas(Request $request)
    {
        $fechaInicio = $request->fecha_inicio ?? now()->startOfMonth()->toDateString();
        $fechaFin = $request->fecha_fin ?? now()->toDateString();
        
        $expedientes = \App\Models\Expediente::whereBetween('created_at', [$fechaInicio, $fechaFin]);
        
        $kpis = [
            'total_expedientes' => $expedientes->count(),
            'completados' => $expedientes->where('estado', 'completado')->count(),
            'en_proceso' => $expedientes->whereIn('estado', ['derivado', 'en_proceso'])->count(),
            'vencidos' => $expedientes->whereIn('estado', ['derivado', 'en_proceso'])
                ->whereHas('derivaciones', function($q) {
                    $q->where('fecha_limite', '<', now());
                })->count(),
            'tiempo_promedio' => 15.5, // Calcular promedio real
            'eficiencia' => 85 // Calcular eficiencia real
        ];
        
        // Datos para gráficos
        $graficoTendencia = ['labels' => [], 'registrados' => [], 'completados' => []];
        $graficoEstados = ['labels' => ['Pendiente', 'Derivado', 'En Proceso', 'Completado'], 'data' => []];
        $graficoAreas = ['labels' => [], 'data' => []];
        $graficoTiposTramite = ['labels' => [], 'data' => []];
        
        // Llenar datos de gráficos (simplificado)
        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $graficoTendencia['labels'][] = $fecha->format('d/m');
            $graficoTendencia['registrados'][] = \App\Models\Expediente::whereDate('created_at', $fecha)->count();
            $graficoTendencia['completados'][] = \App\Models\Expediente::whereDate('updated_at', $fecha)->where('estado', 'completado')->count();
        }
        
        $rendimientoUsuarios = [];
        $analisisTiempos = [];
        
        return view('admin.estadisticas', compact(
            'fechaInicio', 'fechaFin', 'kpis', 'graficoTendencia', 'graficoEstados', 
            'graficoAreas', 'graficoTiposTramite', 'rendimientoUsuarios', 'analisisTiempos'
        ));
    }
}