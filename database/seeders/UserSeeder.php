<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario de prueba — Ana como admin
        User::create([
            'name'     => 'Ana Admin',
            'email'    => 'ana@argos.mx',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        // Usuario operador de prueba
        User::create([
            'name'     => 'Operador Prueba',
            'email'    => 'operador@argos.mx',
            'password' => Hash::make('password123'),
            'role'     => 'operator',
        ]);
    }
}