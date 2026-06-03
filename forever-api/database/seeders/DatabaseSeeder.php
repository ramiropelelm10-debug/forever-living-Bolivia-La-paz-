<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear el usuario Administrador Maestro (Acceso Total)
        User::firstOrCreate(
            ['email' => 'admin@forever.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'tipo_usuario' => 'admin',
                'status' => 'activo', 
            ]
        );

        // 2. Crear el usuario Encargado de Almacén (Acceso Solo Inventario)
        // 🔥 Añadimos esto para cumplir con lo que querías separar
        User::firstOrCreate(
            ['email' => 'inventario@forever.com'],
            [
                'name' => 'Encargado de Almacén',
                'password' => Hash::make('12345678'), // Usa la misma clave para pruebas rápidas
                'role' => 'inventario', // Su rol restringido
                'tipo_usuario' => 'empleado',
                'status' => 'activo',
            ]
        );

        // Llamamos a tus otros seeders
        $this->call([
            ProductSeeder::class,
        ]);
    }
}