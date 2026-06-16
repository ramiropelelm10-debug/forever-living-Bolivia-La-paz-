<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    return view('welcome');
});

// 🔥 LA RUTA SECRETA PARA INSTALAR LA BASE DE DATOS 🔥
Route::get('/instalar-bd', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return '¡Base de datos creada con éxito, ingeniero!';
    } catch (\Exception $e) {
        return '🚨 ERROR REAL DE LARAVEL: ' . $e->getMessage();
    }
});

// 🔥 LA NUEVA RUTA SECRETA PARA ASCENDER AL PRIMER ADMIN 🔥
Route::get('/hacerme-admin', function () {
    try {
        $user = User::first();
        
        if ($user) {
            $user->role = 'admin'; 
            
            if (Schema::hasColumn('users', 'is_active')) {
                $user->is_active = true; 
            }
            if (Schema::hasColumn('users', 'status')) {
                $user->status = 'activo'; 
            }

            $user->save();
            
            return "¡Felicidades, {$user->name}! Ahora eres el Administrador Supremo de Forever Bolivia. Ya puedes entrar al panel y mostrarle el avance al inge.";
        }
        
        return "Primero tienes que registrar una cuenta desde la página de Vue.";
    } catch (\Exception $e) {
        return '🚨 ERROR AL ASCENDER ADMIN: ' . $e->getMessage();
    }
});

// 🔥 LA RUTA PARA CAMBIAR CONTRASEÑA EN CASO DE ERROR 🔥
Route::get('/reset-password/{email}/{nuevaPassword}', function ($email, $nuevaPassword) {
    $user = User::where('email', $email)->first();
    if (!$user) {
        return "El usuario {$email} no existe en la base de datos.";
    }
    $user->password = Hash::make($nuevaPassword);
    $user->save();
    return "Contraseña actualizada con éxito para {$email}. Ahora intenta loguearte.";
});

// 🔥 RUTA DE DEBUG PARA VER QUIÉN VIVE EN LA BD 🔥
Route::get('/quien-vive-aqui', function () {
    $emails = User::pluck('email')->toArray();
    if (empty($emails)) {
        return "La base de datos está totalmente VACÍA. No hay usuarios registrados. Regístrate en la web primero.";
    }
    return "Usuarios registrados en la nube: " . implode(', ', $emails);
});