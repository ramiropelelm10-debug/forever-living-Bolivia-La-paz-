<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    return view('welcome');
});

// 🔥 LA RUTA SECRETA PARA INSTALAR LA BASE DE DATOS Y CORRER SEEDERS 🔥
Route::get('/instalar-bd', function () {
    try {
        // Usamos migrate:fresh para garantizar una instalación limpia en la nube
        Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
        return '¡Base de datos creada y seeders ejecutados con éxito, ingeniero!';
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

// 🔥 RUTA PARA ASCENDER UN USUARIO ESPECÍFICO A ADMIN 🔥
Route::get('/ascender-usuario/{email}', function ($email) {
    $user = User::where('email', $email)->first();
    if (!$user) return "Usuario {$email} no encontrado.";
    
    $user->role = 'admin'; 
    if (Schema::hasColumn('users', 'is_active')) $user->is_active = true;
    if (Schema::hasColumn('users', 'status')) $user->status = 'activo';
    $user->save();
    
    return "¡Felicidades, {$user->email}! Ahora eres Admin.";
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

// 🔥 RUTA CORREGIDA PARA VER EL OTP (Busca en tabla users) 🔥
Route::get('/ver-otp/{email}', function ($email) {
    $user = User::where('email', $email)->first();
    if (!$user) return "Usuario no encontrado.";
    
    if (Schema::hasColumn('users', 'otp_code')) {
        return "El OTP actual para {$email} es: " . ($user->otp_code ?? 'No hay OTP activo');
    }
    return "La columna 'otp_code' no existe en la tabla users.";
});

// 🔥 NUEVA RUTA DE RAYOS X: PARA LEER EL DIARIO SECRETO DE ERRORES (LOGS) 🔥
Route::get('/ver-errores', function () {
    $logPath = storage_path('logs/laravel.log');
    if (!file_exists($logPath)) return 'No hay errores registrados aún.';
    
    $lines = file($logPath);
    $lastLines = array_slice($lines, -50); // Trae las últimas 50 líneas
    
    return response('<pre style="background:#111;color:#0f0;padding:20px;font-size:14px;white-space:pre-wrap;line-height:1.5;">' . implode("", $lastLines) . '</pre>');
});