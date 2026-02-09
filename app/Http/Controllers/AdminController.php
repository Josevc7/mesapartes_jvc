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
use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\EstadoExpediente;
use App\Models\TransicionEstado;
use App\Models\Expediente;
use App\Models\Numeracion;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Gestión de Usuarios
    public function usuarios(Request $request)
    {
        $query = User::with(['role', 'area']);

        if ($request->filled('id_rol')) {
            $query->where('id_rol', $request->id_rol);
        }

        if ($request->filled('id_area')) {
            $query->where('id_area', $request->id_area);
        }

        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")
                  ->orWhere('dni', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        $usuarios = $query->orderBy('name')->paginate(15)->withQueryString();
        $roles = Role::all();
        $areas = Area::where('activo', true)->orderBy('nombre')->get();

        return view('admin.usuarios.index', compact('usuarios', 'roles', 'areas'));
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

    // Gestión de Áreas (con jerarquía)
    public function areas()
    {
        // Obtener áreas organizadas jerárquicamente con carga recursiva
        $areasRaiz = Area::with(['jefe', 'funcionarios', 'subAreasRecursivas'])
            ->whereNull('id_area_padre')
            ->orderBy('nivel')
            ->orderBy('nombre')
            ->get();

        // También obtener lista plana para el select de área padre
        $todasLasAreas = Area::orderBy('nivel')->orderBy('nombre')->get();
        $jefes = User::where('id_rol', 3)->where('activo', true)->get();
        $niveles = Area::getNiveles();

        return view('admin.areas.index', compact('areasRaiz', 'todasLasAreas', 'jefes', 'niveles'));
    }

    public function storeArea(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'siglas' => 'required|string|max:20|unique:areas,siglas',
            'descripcion' => 'nullable|string',
            'id_jefe' => 'nullable|exists:users,id',
            'id_area_padre' => 'nullable|exists:areas,id_area',
            'nivel' => 'required|in:DIRECCION_REGIONAL,OCI,DIRECCION,SUBDIRECCION,RESIDENCIA'
        ]);

        $area = Area::create([
            'nombre' => $request->nombre,
            'siglas' => strtoupper($request->siglas),
            'descripcion' => $request->descripcion,
            'id_jefe' => $request->id_jefe,
            'id_area_padre' => $request->id_area_padre,
            'nivel' => $request->nivel,
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
        $area = Area::with('areaPadre')->findOrFail($id_area);
        $jefes = User::where('id_rol', 3)->where('activo', true)->get();
        $todasLasAreas = Area::where('id_area', '!=', $id_area)->orderBy('nivel')->orderBy('nombre')->get();
        $niveles = Area::getNiveles();
        return response()->json([
            'area' => $area,
            'jefes' => $jefes,
            'areas' => $todasLasAreas,
            'niveles' => $niveles
        ]);
    }

    public function updateArea(Request $request, $id_area)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'siglas' => 'required|string|max:20|unique:areas,siglas,' . $id_area . ',id_area',
            'descripcion' => 'nullable|string',
            'id_jefe' => 'nullable|exists:users,id',
            'id_area_padre' => 'nullable|exists:areas,id_area',
            'nivel' => 'required|in:DIRECCION_REGIONAL,OCI,DIRECCION,SUBDIRECCION,RESIDENCIA'
        ]);

        $area = Area::findOrFail($id_area);

        // Validar que no se asigne a sí misma como padre
        if ($request->id_area_padre == $id_area) {
            return redirect()->route('admin.areas')->with('error', 'Un área no puede ser su propio padre');
        }

        // Validar que no se cree un ciclo (el padre no puede ser un descendiente)
        if ($request->id_area_padre) {
            $descendientes = $area->getDescendientes()->pluck('id_area')->toArray();
            if (in_array($request->id_area_padre, $descendientes)) {
                return redirect()->route('admin.areas')->with('error', 'No se puede asignar un área hija como padre');
            }
        }

        // Si cambió el jefe, actualizar relaciones
        if ($area->id_jefe != $request->id_jefe) {
            if ($area->id_jefe) {
                User::where('id', $area->id_jefe)->update(['id_area' => null]);
            }
            if ($request->id_jefe) {
                User::where('id', $request->id_jefe)->update(['id_area' => $area->id_area]);
            }
        }

        $area->update([
            'nombre' => $request->nombre,
            'siglas' => strtoupper($request->siglas),
            'descripcion' => $request->descripcion,
            'id_jefe' => $request->id_jefe,
            'id_area_padre' => $request->id_area_padre,
            'nivel' => $request->nivel
        ]);

        return redirect()->route('admin.areas')->with('success', 'Área actualizada correctamente');
    }

    public function cargarOrganigrama()
    {
        try {
            // Ejecutar el seeder del organigrama
            $seeder = new \Database\Seeders\OrganigramaDRTCSeeder();
            $seeder->setCommand(new \Symfony\Component\Console\Output\NullOutput());
            $seeder->run();

            $totalAreas = Area::count();
            return response()->json([
                'success' => true,
                'message' => "Organigrama cargado exitosamente. Total de áreas: {$totalAreas}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el organigrama: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleArea($id_area)
    {
        $area = Area::findOrFail($id_area);
        $area->update(['activo' => !$area->activo]);

        $estado = $area->activo ? 'activada' : 'desactivada';
        return redirect()->route('admin.areas')->with('success', "Área {$estado} correctamente");
    }

    public function destroyArea($id_area)
    {
        $area = Area::findOrFail($id_area);

        // Verificar dependencias del área y todas sus sub-áreas recursivamente
        $areasAVerificar = collect([$area])->merge($area->getDescendientes());

        foreach ($areasAVerificar as $areaCheck) {
            if ($areaCheck->expedientes()->count() > 0) {
                $msg = $areaCheck->id_area == $id_area
                    ? 'No se puede eliminar. El área tiene expedientes asociados.'
                    : "No se puede eliminar. La sub-área '{$areaCheck->nombre}' tiene expedientes asociados.";
                return redirect()->route('admin.areas')->with('error', $msg);
            }

            if ($areaCheck->funcionarios()->count() > 0) {
                $msg = $areaCheck->id_area == $id_area
                    ? 'No se puede eliminar. El área tiene funcionarios asignados.'
                    : "No se puede eliminar. La sub-área '{$areaCheck->nombre}' tiene funcionarios asignados.";
                return redirect()->route('admin.areas')->with('error', $msg);
            }

            if ($areaCheck->tipoTramites()->count() > 0) {
                $msg = $areaCheck->id_area == $id_area
                    ? 'No se puede eliminar. El área tiene tipos de trámite asociados.'
                    : "No se puede eliminar. La sub-área '{$areaCheck->nombre}' tiene tipos de trámite asociados.";
                return redirect()->route('admin.areas')->with('error', $msg);
            }
        }

        $nombreArea = $area->nombre;
        $area->delete();

        return redirect()->route('admin.areas')
            ->with('success', "Área '{$nombreArea}' y sus sub-áreas eliminadas correctamente");
    }

    // Gestión de Tipos de Trámite
    public function tipoTramites(Request $request)
    {
        $query = TipoTramite::with('area');

        if ($request->filled('id_area')) {
            $query->where('id_area', $request->id_area);
        }

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $tipoTramites = $query->orderBy('id_area')->orderBy('nombre')->paginate(15)->withQueryString();
        $areas = Area::where('activo', true)->orderBy('nombre')->get();
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

    public function destroyTipoTramite($id_tipo_tramite)
    {
        $tipoTramite = TipoTramite::findOrFail($id_tipo_tramite);

        if ($tipoTramite->expedientes()->count() > 0) {
            return redirect()->route('admin.tipo-tramites')
                ->with('error', 'No se puede eliminar este tipo de trámite porque tiene expedientes asociados. Puede desactivarlo en su lugar.');
        }

        $nombreTipo = $tipoTramite->nombre;
        $tipoTramite->delete();

        return redirect()->route('admin.tipo-tramites')
            ->with('success', "Tipo de trámite '{$nombreTipo}' eliminado correctamente");
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
            'tipo_documento' => 'required|in:DNI,CE,RUC,PASAPORTE,OTROS',
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
            'tipo_documento' => 'required|in:DNI,CE,RUC,PASAPORTE,OTROS',
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
        // Estados válidos del sistema
        $estadosActivos = ['recepcionado', 'registrado', 'clasificado', 'derivado', 'en_proceso', 'observado'];
        $estadosFinalizados = ['resuelto', 'notificado', 'archivado'];

        $metricas = [
            'total_expedientes' => \App\Models\Expediente::count(),
            'usuarios_activos' => User::where('activo', true)->count(),
            'expedientes_pendientes' => \App\Models\Expediente::whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', $estadosActivos))->count(),
            'expedientes_vencidos' => \App\Models\Expediente::whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso']))
                ->whereHas('derivaciones', function($q) {
                    $q->where('fecha_limite', '<', now())->where('estado', 'pendiente');
                })->count()
        ];

        // Actividad reciente desde historial de expedientes
        $actividadReciente = \App\Models\HistorialExpediente::with('usuario')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return (object)[
                    'accion' => 'Expediente',
                    'descripcion' => $item->descripcion,
                    'created_at' => $item->created_at
                ];
            });

        $alertas = [];
        if ($metricas['expedientes_vencidos'] > 0) {
            $alertas[] = ['tipo' => 'danger', 'titulo' => 'Expedientes Vencidos', 'mensaje' => "Hay {$metricas['expedientes_vencidos']} expedientes vencidos."];
        }

        $pendientesClasificar = \App\Models\Expediente::whereHas('estadoExpediente', fn($q) => $q->where('slug', 'recepcionado'))->count();
        if ($pendientesClasificar > 0) {
            $alertas[] = ['tipo' => 'warning', 'titulo' => 'Pendientes de Clasificar', 'mensaje' => "Hay {$pendientesClasificar} expedientes pendientes de clasificar."];
        }

        // Datos para gráficos - últimos 6 meses
        $graficoMeses = ['labels' => [], 'data' => []];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $graficoMeses['labels'][] = $fecha->format('M Y');
            $graficoMeses['data'][] = \App\Models\Expediente::whereMonth('created_at', $fecha->month)
                ->whereYear('created_at', $fecha->year)->count();
        }

        // Gráfico por estados reales del sistema
        $estadosGrafico = [
            'Recepcionado' => 'recepcionado',
            'Clasificado' => 'clasificado',
            'Derivado' => 'derivado',
            'En Proceso' => 'en_proceso',
            'Observado' => 'observado',
            'Resuelto' => 'resuelto',
            'Archivado' => 'archivado'
        ];

        $graficoEstados = ['labels' => [], 'data' => []];
        foreach ($estadosGrafico as $label => $estado) {
            $count = \App\Models\Expediente::whereHas('estadoExpediente', fn($q) => $q->where('slug', $estado))->count();
            if ($count > 0) {
                $graficoEstados['labels'][] = $label;
                $graficoEstados['data'][] = $count;
            }
        }

        // Si no hay datos, agregar valores por defecto para evitar errores en gráficos
        if (empty($graficoEstados['labels'])) {
            $graficoEstados = ['labels' => ['Sin datos'], 'data' => [0]];
        }

        // Rendimiento por área
        $rendimientoPorArea = \App\Models\Area::where('activo', true)
            ->withCount(['expedientes'])
            ->get()
            ->map(function($area) {
                $total = $area->expedientes_count;
                $resueltos = \App\Models\Expediente::where('id_area', $area->id_area)
                    ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto', 'notificado', 'archivado']))->count();
                $pendientes = \App\Models\Expediente::where('id_area', $area->id_area)
                    ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso', 'clasificado']))->count();
                $vencidos = \App\Models\Expediente::where('id_area', $area->id_area)
                    ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso']))
                    ->whereHas('derivaciones', function($q) {
                        $q->where('fecha_limite', '<', now());
                    })->count();

                return [
                    'nombre' => $area->nombre,
                    'total' => $total,
                    'completados' => $resueltos,
                    'pendientes' => $pendientes,
                    'vencidos' => $vencidos,
                    'eficiencia' => $total > 0 ? round(($resueltos / $total) * 100) : 0
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

        // KPIs con consultas separadas para evitar problemas de query builder
        $totalExpedientes = \App\Models\Expediente::whereBetween('created_at', [$fechaInicio, $fechaFin])->count();
        $resueltos = \App\Models\Expediente::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto', 'notificado', 'archivado']))->count();
        $enProceso = \App\Models\Expediente::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso', 'clasificado']))->count();
        $vencidos = \App\Models\Expediente::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso']))
            ->whereHas('derivaciones', function($q) {
                $q->where('fecha_limite', '<', now());
            })->count();

        // Calcular tiempo promedio real
        $tiempoPromedio = \App\Models\Expediente::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto', 'notificado', 'archivado']))
            ->whereNotNull('fecha_resolucion')
            ->selectRaw('AVG(DATEDIFF(fecha_resolucion, created_at)) as promedio')
            ->value('promedio') ?? 0;

        // Calcular eficiencia real
        $eficiencia = $totalExpedientes > 0 ? round(($resueltos / $totalExpedientes) * 100) : 0;

        $kpis = [
            'total_expedientes' => $totalExpedientes,
            'completados' => $resueltos,
            'en_proceso' => $enProceso,
            'vencidos' => $vencidos,
            'tiempo_promedio' => round($tiempoPromedio, 1),
            'eficiencia' => $eficiencia
        ];

        // Gráfico de tendencia - últimos 7 días
        $graficoTendencia = ['labels' => [], 'registrados' => [], 'completados' => []];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $graficoTendencia['labels'][] = $fecha->format('d/m');
            $graficoTendencia['registrados'][] = \App\Models\Expediente::whereDate('created_at', $fecha)->count();
            $graficoTendencia['completados'][] = \App\Models\Expediente::whereDate('updated_at', $fecha)
                ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto', 'notificado', 'archivado']))->count();
        }

        // Gráfico por estados reales
        $estadosGrafico = [
            'Recepcionado' => 'recepcionado',
            'Derivado' => 'derivado',
            'En Proceso' => 'en_proceso',
            'Observado' => 'observado',
            'Resuelto' => 'resuelto',
            'Archivado' => 'archivado'
        ];

        $graficoEstados = ['labels' => [], 'data' => []];
        foreach ($estadosGrafico as $label => $estado) {
            $count = \App\Models\Expediente::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->whereHas('estadoExpediente', fn($q) => $q->where('slug', $estado))->count();
            $graficoEstados['labels'][] = $label;
            $graficoEstados['data'][] = $count;
        }

        // Gráfico por áreas
        $graficoAreas = ['labels' => [], 'data' => []];
        $areas = \App\Models\Area::where('activo', true)->withCount(['expedientes' => function($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        }])->get();
        foreach ($areas as $area) {
            $graficoAreas['labels'][] = $area->nombre;
            $graficoAreas['data'][] = $area->expedientes_count;
        }

        // Gráfico por tipos de trámite
        $graficoTiposTramite = ['labels' => [], 'data' => []];
        $tiposTramite = \App\Models\TipoTramite::where('activo', true)
            ->withCount(['expedientes' => function($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('created_at', [$fechaInicio, $fechaFin]);
            }])
            ->orderBy('expedientes_count', 'desc')
            ->limit(10)
            ->get();
        foreach ($tiposTramite as $tipo) {
            $graficoTiposTramite['labels'][] = $tipo->nombre;
            $graficoTiposTramite['data'][] = $tipo->expedientes_count;
        }

        // Rendimiento por usuarios (funcionarios)
        $rendimientoUsuarios = User::where('id_rol', 4) // Funcionarios
            ->where('activo', true)
            ->get()
            ->map(function($usuario) use ($fechaInicio, $fechaFin) {
                $asignados = \App\Models\Expediente::where('id_funcionario_asignado', $usuario->id)
                    ->whereBetween('created_at', [$fechaInicio, $fechaFin])->count();
                $completados = \App\Models\Expediente::where('id_funcionario_asignado', $usuario->id)
                    ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                    ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto', 'notificado', 'archivado']))->count();

                return [
                    'nombre' => $usuario->name,
                    'asignados' => $asignados,
                    'completados' => $completados,
                    'eficiencia' => $asignados > 0 ? round(($completados / $asignados) * 100) : 0
                ];
            })
            ->filter(fn($u) => $u['asignados'] > 0)
            ->values();

        // Análisis de tiempos por tipo de trámite
        $analisisTiempos = \App\Models\TipoTramite::where('activo', true)->get()->map(function($tipo) use ($fechaInicio, $fechaFin) {
            $expedientes = \App\Models\Expediente::where('id_tipo_tramite', $tipo->id_tipo_tramite)
                ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto', 'notificado', 'archivado']))
                ->whereNotNull('fecha_resolucion');

            $promedio = $expedientes->clone()->selectRaw('AVG(DATEDIFF(fecha_resolucion, created_at)) as prom')->value('prom') ?? 0;
            $total = $expedientes->count();
            $dentroDelPlazo = $expedientes->clone()->whereRaw('DATEDIFF(fecha_resolucion, created_at) <= ?', [$tipo->plazo_dias])->count();

            return [
                'tipo' => $tipo->nombre,
                'plazo' => $tipo->plazo_dias,
                'promedio' => round($promedio, 1),
                'cumplimiento' => $total > 0 ? round(($dentroDelPlazo / $total) * 100) : 0
            ];
        })->filter(fn($t) => $t['promedio'] > 0)->values();

        return view('admin.estadisticas', compact(
            'fechaInicio', 'fechaFin', 'kpis', 'graficoTendencia', 'graficoEstados',
            'graficoAreas', 'graficoTiposTramite', 'rendimientoUsuarios', 'analisisTiempos'
        ));
    }

    // ==========================================
    // GESTIÓN DE PERMISOS
    // ==========================================

    public function permisos()
    {
        $modulos = Modulo::with('permisos')->orderBy('orden')->get();
        $roles = Role::withCount('users')->get();
        return view('admin.permisos.index', compact('modulos', 'roles'));
    }

    public function editarPermisosRol($id_rol)
    {
        $rol = Role::with('permisos')->findOrFail($id_rol);
        $modulos = Modulo::with('permisos')->activos()->orderBy('orden')->get();
        $permisosRol = $rol->permisos->pluck('id_permiso')->toArray();

        return view('admin.permisos.editar', compact('rol', 'modulos', 'permisosRol'));
    }

    public function actualizarPermisosRol(Request $request, $id_rol)
    {
        $rol = Role::findOrFail($id_rol);

        // No permitir editar permisos del Administrador
        if ($rol->nombre === 'Administrador') {
            return redirect()->route('admin.permisos')->with('warning', 'El rol Administrador tiene todos los permisos por defecto.');
        }

        $permisos = $request->input('permisos', []);
        $rol->sincronizarPermisos($permisos);

        return redirect()->route('admin.permisos')->with('success', 'Permisos actualizados correctamente para el rol ' . $rol->nombre);
    }

    // ==========================================
    // GESTIÓN DE ESTADOS DEL EXPEDIENTE
    // ==========================================

    public function estados()
    {
        $estados = EstadoExpediente::orderBy('orden')->get();
        $transiciones = TransicionEstado::with(['estadoOrigen', 'estadoDestino'])->get();
        $roles = Role::activos()->get();

        return view('admin.estados.index', compact('estados', 'transiciones', 'roles'));
    }

    public function storeEstado(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'slug' => 'required|string|max:50|unique:estados_expediente,slug',
            'descripcion' => 'nullable|string',
            'color' => 'required|string|max:20',
            'icono' => 'nullable|string|max:50',
            'orden' => 'required|integer|min:0',
        ]);

        EstadoExpediente::create($request->all());

        return redirect()->route('admin.estados')->with('success', 'Estado creado correctamente');
    }

    public function updateEstado(Request $request, $id_estado)
    {
        $estado = EstadoExpediente::findOrFail($id_estado);

        $request->validate([
            'nombre' => 'required|string|max:50',
            'slug' => 'required|string|max:50|unique:estados_expediente,slug,' . $id_estado . ',id_estado',
            'descripcion' => 'nullable|string',
            'color' => 'required|string|max:20',
            'icono' => 'nullable|string|max:50',
            'orden' => 'required|integer|min:0',
        ]);

        $estado->update($request->all());

        return redirect()->route('admin.estados')->with('success', 'Estado actualizado correctamente');
    }

    public function toggleEstado($id_estado)
    {
        $estado = EstadoExpediente::findOrFail($id_estado);
        $estado->update(['activo' => !$estado->activo]);

        return redirect()->route('admin.estados')->with('success', 'Estado ' . ($estado->activo ? 'activado' : 'desactivado'));
    }

    public function storeTransicion(Request $request)
    {
        $request->validate([
            'id_estado_origen' => 'required|exists:estados_expediente,id_estado',
            'id_estado_destino' => 'required|exists:estados_expediente,id_estado|different:id_estado_origen',
            'nombre_accion' => 'nullable|string|max:100',
            'roles_permitidos' => 'nullable|array',
        ]);

        TransicionEstado::create([
            'id_estado_origen' => $request->id_estado_origen,
            'id_estado_destino' => $request->id_estado_destino,
            'nombre_accion' => $request->nombre_accion,
            'roles_permitidos' => $request->roles_permitidos,
            'activo' => true,
        ]);

        return redirect()->route('admin.estados')->with('success', 'Transición creada correctamente');
    }

    public function destroyTransicion($id_transicion)
    {
        $transicion = TransicionEstado::findOrFail($id_transicion);
        $transicion->delete();

        return redirect()->route('admin.estados')->with('success', 'Transición eliminada correctamente');
    }

    // ==========================================
    // GESTIÓN DE NUMERACIÓN
    // ==========================================

    public function numeracion()
    {
        $numeraciones = Numeracion::orderBy('anio', 'desc')->get();
        $configuraciones = Configuracion::all()->pluck('valor', 'clave');

        return view('admin.numeracion.index', compact('numeraciones', 'configuraciones'));
    }

    public function storeNumeracion(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer|min:2020|max:2100|unique:numeracion,anio',
            'ultimo_numero' => 'required|integer|min:0',
            'prefijo' => 'nullable|string|max:20',
        ]);

        Numeracion::create($request->all());

        return redirect()->route('admin.numeracion')->with('success', 'Numeración creada correctamente');
    }

    public function updateNumeracion(Request $request, $id_numeracion)
    {
        $numeracion = Numeracion::findOrFail($id_numeracion);

        $request->validate([
            'ultimo_numero' => 'required|integer|min:0',
            'prefijo' => 'nullable|string|max:20',
        ]);

        $numeracion->update($request->only(['ultimo_numero', 'prefijo']));

        return redirect()->route('admin.numeracion')->with('success', 'Numeración actualizada correctamente');
    }

    public function reiniciarNumeracion($id_numeracion)
    {
        $numeracion = Numeracion::findOrFail($id_numeracion);
        $numeracion->update(['ultimo_numero' => 0]);

        return redirect()->route('admin.numeracion')->with('success', 'Numeración reiniciada a 0');
    }

    // ==========================================
    // REASIGNACIÓN DE EXPEDIENTES
    // ==========================================

    public function expedientes(Request $request)
    {
        $query = Expediente::with(['area', 'tipoTramite', 'funcionarioAsignado', 'persona']);

        // Filtros
        if ($request->estado) {
            $query->whereHas('estadoExpediente', fn($q) => $q->where('slug', $request->estado));
        }
        if ($request->id_area) {
            $query->where('id_area', $request->id_area);
        }
        if ($request->buscar) {
            $query->where(function($q) use ($request) {
                $q->where('codigo_expediente', 'like', '%' . $request->buscar . '%')
                  ->orWhere('asunto', 'like', '%' . $request->buscar . '%')
                  ->orWhere('remitente', 'like', '%' . $request->buscar . '%');
            });
        }

        $expedientes = $query->orderBy('created_at', 'desc')->paginate(20);
        $areas = Area::activos()->get();
        $estados = EstadoExpediente::activos()->get();

        return view('admin.expedientes.index', compact('expedientes', 'areas', 'estados'));
    }

    public function showExpediente($id_expediente)
    {
        $expediente = Expediente::with([
            'area', 'tipoTramite', 'funcionarioAsignado', 'persona',
            'derivaciones.areaDestino', 'derivaciones.funcionarioDestino',
            'historial.usuario', 'documentos', 'observaciones'
        ])->findOrFail($id_expediente);

        $areas = Area::activos()->get();
        $funcionarios = User::where('id_rol', 4)->where('activo', true)->get();
        $estados = EstadoExpediente::activos()->get();

        return view('admin.expedientes.show', compact('expediente', 'areas', 'funcionarios', 'estados'));
    }

    public function cambiarEstadoExpediente(Request $request, $id_expediente)
    {
        $expediente = Expediente::findOrFail($id_expediente);

        $request->validate([
            'estado' => 'required|string',
            'observacion' => 'nullable|string',
        ]);

        $estadoAnterior = $expediente->estado;
        $expediente->estado = $request->estado;
        $expediente->save();

        // Registrar en historial
        $expediente->historial()->create([
            'id_usuario' => auth()->id(),
            'descripcion' => "Estado cambiado de '{$estadoAnterior}' a '{$request->estado}'" .
                           ($request->observacion ? ". Observación: {$request->observacion}" : ''),
            'fecha' => now(),
        ]);

        return redirect()->back()->with('success', 'Estado del expediente actualizado correctamente');
    }

    public function reasignarExpediente(Request $request, $id_expediente)
    {
        $expediente = Expediente::findOrFail($id_expediente);

        $request->validate([
            'id_funcionario' => 'required|exists:users,id',
            'id_area' => 'required|exists:areas,id_area',
            'observacion' => 'nullable|string',
        ]);

        $funcionarioAnterior = $expediente->funcionarioAsignado?->name ?? 'Sin asignar';
        $areaAnterior = $expediente->area?->nombre ?? 'Sin área';

        $expediente->update([
            'id_funcionario_asignado' => $request->id_funcionario,
            'id_area' => $request->id_area,
        ]);

        $funcionarioNuevo = User::find($request->id_funcionario)->name;
        $areaNueva = Area::find($request->id_area)->nombre;

        // Registrar en historial
        $expediente->historial()->create([
            'id_usuario' => auth()->id(),
            'descripcion' => "Reasignado de {$funcionarioAnterior} ({$areaAnterior}) a {$funcionarioNuevo} ({$areaNueva})" .
                           ($request->observacion ? ". Motivo: {$request->observacion}" : ''),
            'fecha' => now(),
        ]);

        return redirect()->back()->with('success', 'Expediente reasignado correctamente');
    }

    // ==========================================
    // SUPERVISIÓN MESA DE PARTES VIRTUAL
    // ==========================================

    public function mesaVirtual(Request $request)
    {
        $query = Expediente::with(['persona', 'tipoTramite', 'area', 'documentos'])
            ->where('canal', 'virtual');

        // Filtros
        if ($request->estado) {
            $query->whereHas('estadoExpediente', fn($q) => $q->where('slug', $request->estado));
        }
        if ($request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $expedientesVirtuales = $query->orderBy('created_at', 'desc')->paginate(20);

        // Estadísticas de mesa virtual
        $estadisticas = [
            'total' => Expediente::where('canal', 'virtual')->count(),
            'pendientes' => Expediente::where('canal', 'virtual')->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['recepcionado', 'registrado']))->count(),
            'clasificados' => Expediente::where('canal', 'virtual')->whereHas('estadoExpediente', fn($q) => $q->where('slug', 'clasificado'))->count(),
            'en_proceso' => Expediente::where('canal', 'virtual')->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['derivado', 'en_proceso']))->count(),
            'resueltos' => Expediente::where('canal', 'virtual')->whereHas('estadoExpediente', fn($q) => $q->whereIn('slug', ['resuelto', 'notificado', 'archivado']))->count(),
        ];

        return view('admin.mesa-virtual.index', compact('expedientesVirtuales', 'estadisticas'));
    }

    public function validarExpedienteVirtual(Request $request, $id_expediente)
    {
        $expediente = Expediente::findOrFail($id_expediente);

        $request->validate([
            'accion' => 'required|in:validar,rechazar,observar',
            'observacion' => 'required_if:accion,rechazar,observar|string',
        ]);

        switch ($request->accion) {
            case 'validar':
                $expediente->estado = 'clasificado';
                $mensaje = 'Expediente virtual validado y clasificado';
                break;
            case 'rechazar':
                $expediente->estado = 'archivado';
                $mensaje = 'Expediente virtual rechazado';
                break;
            case 'observar':
                $expediente->estado = 'observado';
                $mensaje = 'Expediente virtual marcado con observaciones';
                break;
        }
        $expediente->save();

        // Registrar en historial
        $expediente->historial()->create([
            'id_usuario' => auth()->id(),
            'descripcion' => $mensaje . ($request->observacion ? ". {$request->observacion}" : ''),
            'fecha' => now(),
        ]);

        return redirect()->back()->with('success', $mensaje);
    }

    // ==========================================
    // AUDITORÍA MEJORADA
    // ==========================================

    public function auditoriaCompleta(Request $request)
    {
        $query = Auditoria::with('usuario');

        // Filtros avanzados
        if ($request->accion) {
            $query->where('accion', $request->accion);
        }
        if ($request->id_usuario) {
            $query->where('id_usuario', $request->id_usuario);
        }
        if ($request->tabla) {
            $query->where('tabla', $request->tabla);
        }
        if ($request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }
        if ($request->ip) {
            $query->where('ip', 'like', '%' . $request->ip . '%');
        }

        $auditorias = $query->orderBy('created_at', 'desc')->paginate(50);
        $usuarios = User::select('id', 'name')->orderBy('name')->get();
        $acciones = Auditoria::distinct()->pluck('accion');
        $tablas = Auditoria::distinct()->pluck('tabla');

        // Estadísticas de auditoría
        $estadisticasAuditoria = [
            'total_registros' => Auditoria::count(),
            'hoy' => Auditoria::whereDate('created_at', today())->count(),
            'esta_semana' => Auditoria::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'por_accion' => Auditoria::select('accion', DB::raw('count(*) as total'))
                ->groupBy('accion')
                ->pluck('total', 'accion')
                ->toArray(),
        ];

        return view('admin.auditoria.completa', compact('auditorias', 'usuarios', 'acciones', 'tablas', 'estadisticasAuditoria'));
    }

    public function exportarAuditoria(Request $request)
    {
        $query = Auditoria::with('usuario');

        if ($request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $auditorias = $query->orderBy('created_at', 'desc')->get();

        // Generar CSV
        $filename = 'auditoria_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($auditorias) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Usuario', 'Acción', 'Tabla', 'Registro ID', 'IP', 'Fecha']);

            foreach ($auditorias as $auditoria) {
                fputcsv($file, [
                    $auditoria->id_auditoria,
                    $auditoria->usuario?->name ?? 'N/A',
                    $auditoria->accion,
                    $auditoria->tabla,
                    $auditoria->registro_id,
                    $auditoria->ip,
                    $auditoria->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    // API PARA FUNCIONARIOS POR ÁREA
    // ==========================================

    public function getFuncionariosPorArea($id_area)
    {
        $funcionarios = User::where('id_area', $id_area)
            ->where('id_rol', 4)
            ->where('activo', true)
            ->select('id', 'name', 'email')
            ->get();

        return response()->json(['funcionarios' => $funcionarios]);
    }
}