<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Persona;

class PersonaController extends Controller
{
    // Para Administrador - CRUD completo
    public function index()
    {
        $personas = Persona::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.personas.index', compact('personas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_documento' => 'required|in:DNI,CE,RUC,PASAPORTE',
            'numero_documento' => 'required|string|max:20|unique:personas',
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

    public function update(Request $request, $id)
    {
        $persona = Persona::findOrFail($id);
        
        $request->validate([
            'tipo_documento' => 'required|in:DNI,CE,RUC,PASAPORTE',
            'numero_documento' => 'required|string|max:20|unique:personas,numero_documento,' . $id,
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

    public function destroy($id)
    {
        $persona = Persona::findOrFail($id);
        
        // Verificar si tiene expedientes
        if ($persona->expedientes()->count() > 0) {
            return redirect()->route('admin.personas')->with('error', 'No se puede eliminar. La persona tiene expedientes asociados.');
        }
        
        $persona->delete();
        return redirect()->route('admin.personas')->with('success', 'Persona eliminada correctamente');
    }

    // Para Mesa de Partes - Búsqueda y creación
    public function buscar(Request $request)
    {
        $query = $request->get('q');
        $personas = Persona::where('numero_documento', 'like', "%{$query}%")
            ->orWhere('nombres', 'like', "%{$query}%")
            ->orWhere('apellido_paterno', 'like', "%{$query}%")
            ->orWhere('razon_social', 'like', "%{$query}%")
            ->limit(10)
            ->get();
            
        return response()->json($personas);
    }

    public function show($id)
    {
        $persona = Persona::with('expedientes')->findOrFail($id);
        return response()->json($persona);
    }
}