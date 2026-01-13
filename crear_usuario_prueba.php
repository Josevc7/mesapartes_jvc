<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Persona;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

echo "=== CREANDO USUARIO CIUDADANO DE PRUEBA ===\n\n";

// Ver roles disponibles
echo "Roles disponibles:\n";
$roles = Role::all();
foreach ($roles as $role) {
    echo "  - ID: {$role->id_rol} | Nombre: {$role->nombre}\n";
}
echo "\n";

// Buscar rol Ciudadano
$rolCiudadano = Role::where('nombre', 'Ciudadano')->first();
if (!$rolCiudadano) {
    echo "ERROR: No se encontró el rol 'Ciudadano'\n";
    exit(1);
}

echo "Rol Ciudadano encontrado: ID {$rolCiudadano->id_rol}\n\n";

// Verificar si ya existe el usuario
$emailPrueba = 'ciudadano.prueba@test.com';
$dniPrueba = '12345678';

$usuarioExistente = User::where('email', $emailPrueba)->first();
if ($usuarioExistente) {
    echo "El usuario ya existe: {$emailPrueba}\n";
    echo "ID Usuario: {$usuarioExistente->id}\n";
    echo "Nombre: {$usuarioExistente->name}\n";
    echo "DNI: {$usuarioExistente->dni}\n";
    echo "Rol: {$usuarioExistente->role->nombre}\n";
    exit(0);
}

// Crear persona
$persona = Persona::create([
    'tipo_documento' => 'DNI',
    'numero_documento' => $dniPrueba,
    'tipo_persona' => 'NATURAL',
    'nombres' => 'Juan',
    'apellido_paterno' => 'Pérez',
    'apellido_materno' => 'García',
    'telefono' => '987654321',
    'email' => $emailPrueba,
    'activo' => true
]);

echo "Persona creada: {$persona->nombre_completo}\n";

// Crear usuario
$usuario = User::create([
    'name' => 'Juan Pérez García',
    'email' => $emailPrueba,
    'password' => Hash::make('ciudadano123'),
    'dni' => $dniPrueba,
    'telefono' => '987654321',
    'id_rol' => $rolCiudadano->id_rol,
    'id_persona' => $persona->id_persona,
    'activo' => true
]);

echo "\n=== USUARIO CREADO EXITOSAMENTE ===\n\n";
echo "Email: {$usuario->email}\n";
echo "Contraseña: ciudadano123\n";
echo "DNI: {$usuario->dni}\n";
echo "Nombre: {$usuario->name}\n";
echo "Rol: {$usuario->role->nombre}\n";
echo "ID Persona: {$usuario->id_persona}\n";
echo "\n";
echo "Ya puedes usar estas credenciales para probar el sistema.\n";
