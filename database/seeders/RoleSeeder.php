<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso completo al sistema'],
            ['nombre' => 'Mesa de Partes', 'descripcion' => 'Registro y clasificación de expedientes'],
            ['nombre' => 'Jefe de Área', 'descripcion' => 'Supervisa y controla expedientes del área'],
            ['nombre' => 'Funcionario', 'descripcion' => 'Atiende y resuelve expedientes'],
            ['nombre' => 'Soporte', 'descripcion' => 'Control interno y soporte técnico'],
            ['nombre' => 'Ciudadano', 'descripcion' => 'Usuario externo que presenta trámites']
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
