<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario Administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@mesapartes.gob.pe',
            'dni' => '12345678',
            'password' => Hash::make('admin123'),
            'id_rol' => 1,
            'activo' => true
        ]);

        // Usuario Mesa de Partes
        User::create([
            'name' => 'Mesa de Partes',
            'email' => 'mesapartes@mesapartes.gob.pe',
            'dni' => '23456789',
            'password' => Hash::make('mesa123'),
            'id_rol' => 2,
            'activo' => true
        ]);

        // Usuario Jefe de Área
        User::create([
            'name' => 'Jefe de Área',
            'email' => 'jefe@mesapartes.gob.pe',
            'dni' => '34567890',
            'password' => Hash::make('jefe123'),
            'id_rol' => 3,
            'id_area' => 1,
            'activo' => true
        ]);

        // Usuario Funcionario
        User::create([
            'name' => 'Funcionario Test',
            'email' => 'funcionario@mesapartes.gob.pe',
            'dni' => '45678901',
            'password' => Hash::make('func123'),
            'id_rol' => 4,
            'id_area' => 1,
            'activo' => true
        ]);

        // Usuario Ciudadano
        User::create([
            'name' => 'Jose Villafuerte Condori',
            'email' => 'villafuerte@gmail.com',
            'dni' => '56789012',
            'telefono' => '987654321',
            'password' => Hash::make('ciudadano123'),
            'id_rol' => 6,
            'activo' => true
        ]);
        
        // Mostrar las credenciales en consola para referencia
        $this->command->info('=== CREDENCIALES DE ACCESO ===');
        $this->command->info('Administrador: admin@mesapartes.gob.pe / admin123');
        $this->command->info('Mesa de Partes: mesapartes@mesapartes.gob.pe / mesa123');
        $this->command->info('Jefe de Área: jefe@mesapartes.gob.pe / jefe123');
        $this->command->info('Funcionario: funcionario@mesapartes.gob.pe / func123');
        $this->command->info('Ciudadano: villafuerte@gmail.com / ciudadano123');
        $this->command->info('===============================');
    }
}