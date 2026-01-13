<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Role;

echo "=== USUARIOS CIUDADANOS EN EL SISTEMA ===\n\n";

// Buscar rol Ciudadano
$rolCiudadano = Role::where('nombre', 'Ciudadano')->first();

if (!$rolCiudadano) {
    echo "ERROR: No se encontró el rol 'Ciudadano'\n";
    exit(1);
}

// Obtener todos los usuarios con rol Ciudadano
$usuarios = User::where('id_rol', $rolCiudadano->id_rol)
    ->with('role')
    ->get();

if ($usuarios->isEmpty()) {
    echo "No hay usuarios ciudadanos registrados.\n";
} else {
    echo "Total de usuarios ciudadanos: " . $usuarios->count() . "\n\n";

    foreach ($usuarios as $usuario) {
        echo "------------------------\n";
        echo "ID: {$usuario->id}\n";
        echo "Nombre: {$usuario->name}\n";
        echo "Email: {$usuario->email}\n";
        echo "DNI: {$usuario->dni}\n";
        echo "Teléfono: {$usuario->telefono}\n";
        echo "Activo: " . ($usuario->activo ? 'Sí' : 'No') . "\n";
        echo "Rol: {$usuario->role->nombre}\n";
        echo "Creado: {$usuario->created_at}\n";
    }
}

echo "\n";
