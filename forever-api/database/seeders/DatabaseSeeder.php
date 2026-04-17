<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear el usuario Administrador automáticamente para no depender de Tinker
        User::firstOrCreate(
            ['email' => 'admin@forever.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
            ]
        );

        // Llamamos a tu seeder con los datos reales de Bolivia
        $this->call([
            ProductSeeder::class,
        ]);
    }
}